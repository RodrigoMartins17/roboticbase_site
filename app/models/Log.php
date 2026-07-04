<?php
require_once __DIR__ . '/../core/Model.php';

// Model dos LOGS do sistema.
// - log_acesso: entradas no sistema (login/logout), registos e auditoria de ações.
// - log_erro: erros vindos da base de dados (validações com SIGNAL nas procedures/triggers).
// Os registos são feitos através de STORED PROCEDURES (sp_registar_log / sp_registar_erro).
class Log extends Model
{
    // Regista um evento de acesso/auditoria. Nunca deixo isto rebentar a aplicação.
    public function registar(?int $idUtilizador, string $tipo, string $descricao, ?string $ip = null): void
    {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        try {
            $stmt = $this->db->prepare('CALL sp_registar_log(?, ?, ?, ?)');
            $stmt->execute([$idUtilizador, $tipo, mb_substr($descricao, 0, 255), $ip]);
            $stmt->closeCursor();
        } catch (\Throwable $e) {
            // Se o log falhar (ex: tabela ainda não criada), não faço nada.
        }
    }

    // Regista um erro (normalmente um erro devolvido pela própria base de dados).
    public function registarErro(string $origem, string $mensagem, ?string $sqlstate = null, ?string $codigo = null, ?int $idUtilizador = null): void
    {
        try {
            $stmt = $this->db->prepare('CALL sp_registar_erro(?, ?, ?, ?, ?)');
            $stmt->execute([mb_substr($origem, 0, 150), $sqlstate, $codigo, $mensagem, $idUtilizador]);
            $stmt->closeCursor();
        } catch (\Throwable $e) {
            // idem — o registo de erros nunca pode partir a app.
        }
    }

    // Lista os últimos acessos/auditoria (com o nome do utilizador, se existir).
    public function acessos(int $limite = 300): array
    {
        $limite = max(1, min(1000, $limite));
        $sql = "SELECT l.*, u.nome AS utilizador_nome, u.email AS utilizador_email
                FROM log_acesso l
                LEFT JOIN utilizador u ON u.id = l.id_utilizador
                ORDER BY l.data_hora DESC, l.id DESC
                LIMIT $limite";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista os últimos erros registados.
    public function erros(int $limite = 300): array
    {
        $limite = max(1, min(1000, $limite));
        $sql = "SELECT e.*, u.nome AS utilizador_nome
                FROM log_erro e
                LEFT JOIN utilizador u ON u.id = e.id_utilizador
                ORDER BY e.data_hora DESC, e.id DESC
                LIMIT $limite";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contagens rápidas para os cabeçalhos das abas.
    public function contarAcessos(): int
    {
        try { return (int)$this->db->query("SELECT COUNT(*) FROM log_acesso")->fetchColumn(); }
        catch (\Throwable $e) { return 0; }
    }

    public function contarErros(): int
    {
        try { return (int)$this->db->query("SELECT COUNT(*) FROM log_erro")->fetchColumn(); }
        catch (\Throwable $e) { return 0; }
    }
}
