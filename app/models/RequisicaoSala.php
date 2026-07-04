<?php
require_once __DIR__ . '/../core/Model.php';

class RequisicaoSala extends Model
{
    public function todas()
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                ORDER BY rs.data DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porUtilizador(int $utilizadorId)
    {
        $sql = "SELECT rs.*, 
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.id_utilizador = ?
                ORDER BY rs.data DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$utilizadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pendentes()
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.estado_sala = 'PENDENTE'
                ORDER BY rs.data_inicio ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Requisições aceites (a entregar / reservas confirmadas). */
    public function aceites()
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.estado_sala IN ('ACEITE', 'EM_USO')
                ORDER BY rs.data_inicio ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function historico()
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                ORDER BY rs.data DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agenda()
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.estado_sala IN ('PENDENTE', 'ACEITE', 'EM_USO', 'CONCLUIDO')
                ORDER BY rs.data_inicio ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porSala(int $salaId, ?string $dataInicio = null, ?string $dataFim = null)
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                WHERE rs.id_sala = ? AND rs.estado_sala = 'ACEITE'";
        
        $params = [$salaId];
        
        if ($dataInicio) {
            $sql .= " AND rs.data_fim >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND rs.data_inicio <= ?";
            $params[] = $dataFim;
        }
        
        $sql .= " ORDER BY rs.data_inicio ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarConflito(int $salaId, string $dataInicio, string $dataFim, ?int $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as count
                FROM requisicao_sala
                WHERE id_sala = ? 
                AND estado_sala = 'ACEITE'
                AND (
                    (data_inicio <= ? AND data_fim >= ?) OR
                    (data_inicio <= ? AND data_fim >= ?) OR
                    (data_inicio >= ? AND data_fim <= ?)
                )";
        
        if ($excluirId) {
            $sql .= " AND id != ?";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [$salaId, $dataInicio, $dataInicio, $dataFim, $dataFim, $dataInicio, $dataFim];
        if ($excluirId) {
            $params[] = $excluirId;
        }
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function find(int $id)
    {
        $sql = "SELECT rs.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                s.numero as sala_numero, s.andar, s.bloco, s.descricao as sala_descricao
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(int $utilizadorId, int $salaId, string $dataInicio, string $dataFim, ?string $observacao = null)
    {
        $sql = "INSERT INTO requisicao_sala (id_utilizador, id_sala, data_inicio, data_fim, observacao)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$utilizadorId, $salaId, $dataInicio, $dataFim, $observacao]);
        // Devolvo o id da nova requisição (para poder enviar o email de confirmação).
        return $ok ? (int)$this->db->lastInsertId() : 0;
    }

    public function atualizarEstado(int $id, string $estadoPedido)
    {
        $stmt = $this->db->prepare("UPDATE requisicao_sala SET estado_sala = ? WHERE id = ?");
        return $stmt->execute([$estadoPedido, $id]);
    }

    public function aceitar(int $id)
    {
        return $this->atualizarEstado($id, 'ACEITE');
    }

    public function marcarEntrega(int $id)
    {
        $stmt = $this->db->prepare("UPDATE requisicao_sala SET estado_sala = 'EM_USO', estado_entrega = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function finalizar(int $id, string $estadoDevolucao = 'NORMAL')
    {
        $stmt = $this->db->prepare("UPDATE requisicao_sala SET estado_sala = 'CONCLUIDO', estado_devolucao = ? WHERE id = ?");
        return $stmt->execute([$estadoDevolucao, $id]);
    }

    public function rejeitar(int $id)
    {
        return $this->atualizarEstado($id, 'REJEITADO');
    }

    /** Total de requisições (todas). */
    public function total()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM requisicao_sala");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /** Requisições por dia (últimos N dias) para gráfico. */
    public function porDiaUltimosDias(int $dias = 30)
    {
        $sql = "SELECT dia, COUNT(*) as total FROM (
                    SELECT DATE(data) as dia FROM requisicao_sala WHERE data >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    UNION ALL
                    SELECT DATE(data_pedido) as dia FROM requisicao_exemplar WHERE data_pedido >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ) as combined
                GROUP BY dia
                ORDER BY dia ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias, $dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Requisições por mês (últimos N meses) para gráfico. */
    public function porMesUltimosMeses(int $meses = 6)
    {
        $sql = "SELECT mes, COUNT(*) as total FROM (
                    SELECT DATE_FORMAT(data, '%Y-%m') as mes FROM requisicao_sala WHERE data >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    UNION ALL
                    SELECT DATE_FORMAT(data_pedido, '%Y-%m') as mes FROM requisicao_exemplar WHERE data_pedido >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                ) as combined
                GROUP BY mes
                ORDER BY mes ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$meses, $meses]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarEstadoDevolucao(int $id, string $estadoDevolucao)
    {
        $stmt = $this->db->prepare("UPDATE requisicao_sala SET estado_devolucao = ? WHERE id = ?");
        return $stmt->execute([$estadoDevolucao, $id]);
    }

    /** Atualizar requisição (utilizador, sala, datas, estado, observação). */
    public function update(int $id, array $data)
    {
        $sql = "UPDATE requisicao_sala SET
                    id_utilizador = :id_utilizador,
                    id_sala = :id_sala,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    estado_sala = :estado_sala,
                    estado_devolucao = :estado_devolucao,
                    estado_entrega = :estado_entrega,
                    observacao = :observacao
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_utilizador' => (int)($data['id_utilizador'] ?? 0),
            'id_sala' => (int)($data['id_sala'] ?? 0),
            'data_inicio' => $data['data_inicio'] ?? '',
            'data_fim' => $data['data_fim'] ?? '',
            'estado_sala' => $data['estado_sala'] ?? 'PENDENTE',
            'estado_devolucao' => $data['estado_devolucao'] ?? 'NORMAL',
            'estado_entrega' => (int)($data['estado_entrega'] ?? 0),
            'observacao' => $data['observacao'] ?? null,
            'id' => $id,
        ]);
    }

    public function delete(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM requisicao_sala WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getEventosCalendario($inicio, $fim, ?int $utilizadorId = null) {
        $eventos = [];
        $hoje = date('Y-m-d');

        // Get all relevant requisitions first
        $sql = "SELECT rs.*,
                       u.nome as user, s.numero as sala_num, s.bloco
                FROM requisicao_sala rs
                JOIN utilizador u ON rs.id_utilizador = u.id
                JOIN sala s ON rs.id_sala = s.id
                WHERE rs.estado_sala IN ('PENDENTE', 'ACEITE', 'EM_USO', 'CONCLUIDO')";
        // Se for passado um utilizador, mostro só as requisições DELE (para os alunos).
        $params = [];
        if ($utilizadorId !== null) {
            $sql .= " AND rs.id_utilizador = ?";
            $params[] = $utilizadorId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $requisicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($requisicoes as $row) {
            switch ($row['estado_sala']) {
                case 'PENDENTE':
                    // Pending: show request date/time
                    $data = date('Y-m-d', strtotime($row['data']));
                    if ($data >= $inicio && $data <= $fim) {
                        $eventos[] = [
                            'id_req' => $row['id'],
                            'tipo_req' => 'sala',
                            'data' => $data,
                            'hora' => date('H:i', strtotime($row['data'])),
                            'tipo' => 'pedido',
                            'titulo' => '[P] ' . $row['bloco'] . ' - Sala ' . $row['sala_num'],
                            'user' => $row['user'],
                            'urgente' => ($data <= $hoje),
                            'data_entrega_prevista' => $row['data_inicio'],
                            'data_devolucao_prevista' => $row['data_fim']
                        ];
                    }
                    break;
                case 'ACEITE':
                    // Accepted: show delivery date/time
                    if (!empty($row['data_inicio'])) {
                        $data = date('Y-m-d', strtotime($row['data_inicio']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'sala',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_inicio'])),
                                'tipo' => 'entregar',
                                'titulo' => $row['bloco'] . ' - Sala ' . $row['sala_num'],
                                'user' => $row['user'],
                                'urgente' => ($data <= $hoje),
                                'data_entrega_prevista' => $row['data_inicio'],
                                'data_devolucao_prevista' => $row['data_fim']
                            ];
                        }
                    }
                    break;
                case 'EM_USO':
                    // In use: show return date/time
                    if (!empty($row['data_fim'])) {
                        $data = date('Y-m-d', strtotime($row['data_fim']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'sala',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_fim'])),
                                'tipo' => 'receber',
                                'titulo' => $row['bloco'] . ' - Sala ' . $row['sala_num'],
                                'user' => $row['user'],
                                'urgente' => ($data <= $hoje),
                                'data_entrega_prevista' => $row['data_inicio'],
                                'data_devolucao_prevista' => $row['data_fim']
                            ];
                        }
                    }
                    break;
                case 'CONCLUIDO':
                    // Concluído: fica para sempre no dia/hora em que a sala foi devolvida ao clube.
                    if (!empty($row['data_fim'])) {
                        $data = date('Y-m-d', strtotime($row['data_fim']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'sala',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_fim'])),
                                'tipo' => 'concluido',
                                'titulo' => $row['bloco'] . ' - Sala ' . $row['sala_num'],
                                'user' => $row['user'],
                                'urgente' => false, // já concluído, nunca é urgente
                                'data_entrega_prevista' => $row['data_inicio'],
                                'data_devolucao_prevista' => $row['data_fim']
                            ];
                        }
                    }
                    break;
            }
        }
        return $eventos;
    }
}
