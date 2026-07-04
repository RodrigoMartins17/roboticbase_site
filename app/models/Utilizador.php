<?php
require_once __DIR__ . '/../core/Model.php';

class Utilizador extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureEmailVerificationColumns();
    }

    private function ensureEmailVerificationColumns(): void
    {
        try {
            $this->db->exec("ALTER TABLE utilizador ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 0");
        } catch (\PDOException $e) {
        }
        try {
            $this->db->exec("ALTER TABLE utilizador ADD COLUMN email_verificacao_token VARCHAR(128) NULL");
        } catch (\PDOException $e) {
        }
    }
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function all()
    {
        return $this->db->query("SELECT * FROM utilizador ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO utilizador (nome, email, password_hash, tipo, telefone, linkedin, turma, data_nascimento, foto, email_verificado, email_verificacao_token)
                VALUES (:nome, :email, :password_hash, :tipo, :telefone, :linkedin, :turma, :data_nascimento, :foto, :email_verificado, :email_verificacao_token)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function findByVerificationToken(string $token)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE email_verificacao_token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markEmailAsVerified(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE utilizador SET email_verificado = 1, email_verificacao_token = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateById(int $id, array $data)
    {
        $fields = [
            "nome = :nome",
            "email = :email",
            "tipo = :tipo",
            "telefone = :telefone",
            "linkedin = :linkedin",
            "turma = :turma",
            "data_nascimento = :data_nascimento"
        ];

        if (array_key_exists('foto', $data)) {
            $fields[] = "foto = :foto";
        }

        $data['id'] = $id;
        $sql = "UPDATE utilizador SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateProfile(int $id, array $data)
    {
        $fields = [
            "nome = :nome",
            "telefone = :telefone",
            "linkedin = :linkedin",
            "turma = :turma",
            "data_nascimento = :data_nascimento"
        ];

        if (array_key_exists('foto', $data)) {
            $fields[] = "foto = :foto";
        }

        $data['id'] = $id;
        $sql = "UPDATE utilizador SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updatePassword(int $id, string $passwordHash)
    {
        $stmt = $this->db->prepare("UPDATE utilizador SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }

    // ---- Reposição de palavra-passe por email ----

    // Guarda um token de reposição e a data em que ele expira.
    public function definirResetToken(int $id, string $token, string $expira)
    {
        $stmt = $this->db->prepare("UPDATE utilizador SET reset_token = ?, reset_expira = ? WHERE id = ?");
        return $stmt->execute([$token, $expira, $id]);
    }

    // Procura um utilizador por token de reposição que ainda não tenha expirado.
    public function findByResetToken(string $token)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE reset_token = ? AND reset_expira > NOW() LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Apaga o token depois de usado (para não poder ser reutilizado).
    public function limparResetToken(int $id)
    {
        $stmt = $this->db->prepare("UPDATE utilizador SET reset_token = NULL, reset_expira = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteById(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM utilizador WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteByIds(array $ids)
    {
        if (empty($ids)) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM utilizador WHERE id IN ($placeholders)");
        return $stmt->execute($ids);
    }

    public function professores()
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE tipo IN ('PROFESSOR', 'RESPONSAVEL') ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function professoresResponsaveis()
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizador WHERE tipo = 'RESPONSAVEL' ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tornarResponsavel(int $id)
    {
        $user = $this->find($id);
        if (!$user || $user['tipo'] !== 'PROFESSOR') {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE utilizador SET tipo = 'RESPONSAVEL' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function removerResponsavel(int $id)
    {
        $user = $this->find($id);
        if (!$user || $user['tipo'] !== 'RESPONSAVEL') {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE utilizador SET tipo = 'PROFESSOR' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Gera o URL completo da imagem de perfil do usuário.
     * Trata caminhos relativos, URLs externas e fallbacks.
     *
     * @param array $user Array associativo com dados do usuário (deve conter 'foto' e 'nome').
     * @return string URL da imagem ou fallback.
     */
    public function getAvatarUrl(?array $user): string
    {
        // Se não houver utilizador (visitante), devolvo um avatar genérico.
        if (empty($user)) {
            return "https://ui-avatars.com/api/?name=RXL&size=64&background=1b2028&color=6ef0ff";
        }
        $foto = $user['foto'] ?? null;
        if (!empty($foto)) {
            if (is_string($foto) && str_starts_with($foto, 'http')) {
                return $foto;
            }
            if (is_string($foto) && str_starts_with($foto, 'uploads/')) {
                $baseUrl = rtrim(BASE_URL, '/');
                $relativePath = ltrim($foto, '/');
                return $baseUrl . '/' . $relativePath;
            }
            $mime = 'image/jpeg';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $detected = finfo_buffer($finfo, $foto);
                    if ($detected) {
                        $mime = $detected;
                    }
                    finfo_close($finfo);
                }
            }
            return 'data:' . $mime . ';base64,' . base64_encode($foto);
        }

        return "https://ui-avatars.com/api/?name=" . urlencode($user['nome']) . "&size=64&background=1b2028&color=6ef0ff";
    }
}
