<?php
require_once __DIR__ . '/../core/Model.php';

class PortfolioEvento extends Model
{

  // Usado no Painel de Administração (Mostra TODOS, ativos e inativos)
    public function todos()
    {
        $stmt = $this->db->prepare("SELECT * FROM evento ORDER BY ordem ASC, created_at DESC");
        $stmt->execute();
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachImagemSrc($eventos);
    }

    // Usado na página pública do site (Mostra APENAS os ativos)
    public function obterAtivos()
    {
        $stmt = $this->db->prepare("SELECT * FROM evento WHERE ativo = 1 ORDER BY ordem ASC, created_at DESC");
        $stmt->execute();
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachImagemSrc($eventos);
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM evento WHERE id = ?");
        $stmt->execute([$id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($evento) {
            $evento['imagem_src'] = $this->getImagemSrc($evento['imagem_url'] ?? null);
        }
        return $evento;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO evento (titulo, descricao, imagem_url, url, ordem, ativo) 
                VALUES (:titulo, :descricao, :imagem_url, :url, :ordem, :ativo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateById(int $id, array $data)
    {
        $data['id'] = $id;
        $sql = "UPDATE evento 
                SET titulo = :titulo, descricao = :descricao, imagem_url = :imagem_url, 
                    url = :url, ordem = :ordem, ativo = :ativo
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteById(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM evento WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function attachImagemSrc(array $eventos): array
    {
        foreach ($eventos as &$evento) {
            $evento['imagem_src'] = $this->getImagemSrc($evento['imagem_url'] ?? null);
        }
        return $eventos;
    }

    public function getImagemSrc(?string $imagem): ?string
    {
        if (empty($imagem)) {
            return null;
        }

        if (is_string($imagem)) {
            if (str_starts_with($imagem, 'http')) {
                return $imagem;
            }
            if (str_starts_with($imagem, 'uploads/')) {
                return rtrim(BASE_URL, '/') . '/' . ltrim($imagem, '/');
            }
        }

        $mime = 'image/jpeg';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_buffer($finfo, $imagem);
                if ($detected) {
                    $mime = $detected;
                }
                finfo_close($finfo);
            }
        }

        return 'data:' . $mime . ';base64,' . base64_encode($imagem);
    }
}

