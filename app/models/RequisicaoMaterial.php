<?php
require_once __DIR__ . '/../core/Model.php';

class RequisicaoMaterial extends Model
{
    public function todas()
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                ORDER BY rm.data_pedido DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porUtilizador(int $utilizadorId)
    {
        $sql = "SELECT rm.*, 
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.id_utilizador = ?
                ORDER BY rm.data_pedido DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$utilizadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pendentes()
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.estado_pedido = 'PENDENTE'
                ORDER BY rm.data_pedido ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Requisições aceites (a entregar / em curso). */
    public function aceites()
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.estado_pedido IN ('ACEITE', 'EM_USO')
                ORDER BY rm.data_levantamento ASC, rm.data_pedido ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function historico()
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome,
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                ORDER BY rm.data_pedido DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agenda()
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome,
                e.num_referencia,
                m.id as material_id,
                m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.estado_pedido IN ('PENDENTE', 'ACEITE', 'CONCLUIDO')
                ORDER BY rm.data_pedido ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id)
    {
        $sql = "SELECT rm.*, 
                u.nome as utilizador_nome, u.email as utilizador_email,
                e.num_referencia, m.designacao as material_designacao
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(
        int $utilizadorId,
        int $exemplarId,
        ?string $observacao = null,
        ?string $dataLevantamento = null,
        ?string $dataDevolucao = null
    ) {
        $sql = "INSERT INTO requisicao_exemplar (id_utilizador, id_exemplar, observacao, data_levantamento, data_devolucao)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$utilizadorId, $exemplarId, $observacao, $dataLevantamento, $dataDevolucao]);
        // Devolvo o id da nova requisição (para poder enviar o email de confirmação).
        return $ok ? (int)$this->db->lastInsertId() : 0;
    }

    public function atualizarEstado(int $id, string $estadoPedido, ?string $dataEntrega = null, ?string $dataDevolucao = null, ?string $estadoDevolucao = 'OK')
    {
        // Se estadoDevolucao for null, usa o padrão 'OK' para evitar erro 1265 (Data truncated) em colunas NOT NULL
        $estadoDevolucao = $estadoDevolucao ?? 'OK';
        
        $sql = "UPDATE requisicao_exemplar 
                SET estado_pedido = ?, 
                    data_levantamento = ?, 
                    data_devolucao = ?, 
                    estado_devolucao = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estadoPedido, $dataEntrega, $dataDevolucao, $estadoDevolucao, $id]);
    }

    public function aceitar(int $id, ?string $dataEntrega = null)
    {
        // Aceitar agora não muda o estado do exemplar para 'EMPRESTADO' ainda, 
        // apenas quando for levantado (EM_USO).
        $req = $this->find($id);
        if ($req) {
            // IMPORTANTE: manter a data de levantamento que o aluno pediu (senão
            // ficava NULL e a requisição não aparecia no calendário do admin).
            $dataEntrega = $dataEntrega ?? ($req['data_levantamento'] ?? null);
            $dataDevolucao = $req['data_devolucao'] ?? null;
            return $this->atualizarEstado($id, 'ACEITE', $dataEntrega, $dataDevolucao, null);
        }
        return false;
    }

    public function rejeitar(int $id)
    {
        return $this->atualizarEstado($id, 'REJEITADO', null, null, null);
    }

    public function marcarEmUso(int $id)
    {
        $req = $this->find($id);
        if (!$req) {
            return false;
        }
        // Quando aluno vai buscar o exemplar, estado muda para EMPRESTADO e estado_entrega para 1
        $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'EMPRESTADO' WHERE id = ?");
        $stmt->execute([$req['id_exemplar']]);
        
        // Se ainda não tinha data de levantamento, marco agora (o aluno foi buscar).
        $levantamento = $req['data_levantamento'] ?? date('Y-m-d H:i:s');
        if (empty($levantamento)) {
            $levantamento = date('Y-m-d H:i:s');
        }
        $sql = "UPDATE requisicao_exemplar
                SET estado_pedido = 'EM_USO',
                    data_levantamento = ?,
                    estado_entrega = 1
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$levantamento, $id]);
    }

    /** Total de requisições (todas). */
    public function total()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM requisicao_exemplar");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /** Requisições por dia (últimos N dias) para gráfico. */
    public function porDiaUltimosDias(int $dias = 30)
    {
        $sql = "SELECT DATE(data_pedido) as dia, COUNT(*) as total
                FROM requisicao_exemplar
                WHERE data_pedido >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(data_pedido)
                ORDER BY dia ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Requisições por mês (últimos N meses) para gráfico. */
    public function porMesUltimesMeses(int $meses = 6)
    {
        $sql = "SELECT DATE_FORMAT(data_pedido, '%Y-%m') as mes, COUNT(*) as total
                FROM requisicao_exemplar
                WHERE data_pedido >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(data_pedido, '%Y-%m')
                ORDER BY mes ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$meses]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function devolver(int $id, string $estadoDevolucao = 'OK')
    {
        $dataDevolucao = date('Y-m-d H:i:s');
        // Atualiza o estado do exemplar conforme devolução
        $req = $this->find($id);
        if ($req) {
            if ($estadoDevolucao === 'OK') {
                $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'DISPONIVEL' WHERE id = ?");
                $stmt->execute([$req['id_exemplar']]);
            } elseif ($estadoDevolucao === 'DANIFICADO') {
                $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'DANIFICADO' WHERE id = ?");
                $stmt->execute([$req['id_exemplar']]);
            } elseif ($estadoDevolucao === 'PERDIDO') {
                $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'PERDIDO' WHERE id = ?");
                $stmt->execute([$req['id_exemplar']]);
            }
            // O levantamento nunca pode ser DEPOIS da devolução (senão o trigger da BD
            // rejeita). Se ainda não havia levantamento, ou estava marcado para o futuro,
            // uso a hora da devolução como hora de levantamento.
            $dataLevantamento = $req['data_levantamento'] ?? null;
            if (empty($dataLevantamento) || strtotime($dataLevantamento) > strtotime($dataDevolucao)) {
                $dataLevantamento = $dataDevolucao;
            }
            return $this->atualizarEstado($id, 'CONCLUIDO', $dataLevantamento, $dataDevolucao, $estadoDevolucao);
        }
        return false;
    }

    /** Atualizar requisição (observação, datas, estados). Ajusta estado do exemplar se estado_pedido mudar. */
    public function update(int $id, array $data)
    {
        $req = $this->find($id);
        if (!$req) {
            return false;
        }
        $estadoAntigo = $req['estado_pedido'] ?? '';
        $estadoNovo = $data['estado_pedido'] ?? $estadoAntigo;
        $idExemplar = (int)($req['id_exemplar'] ?? 0);

        if ($estadoAntigo === 'EM_USO' && $estadoNovo !== 'EM_USO' && $estadoNovo !== 'CONCLUIDO') {
            $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'DISPONIVEL' WHERE id = ?");
            $stmt->execute([$idExemplar]);
        } elseif ($estadoAntigo !== 'EM_USO' && $estadoNovo === 'EM_USO') {
            $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'EMPRESTADO' WHERE id = ?");
            $stmt->execute([$idExemplar]);
        } elseif ($estadoNovo === 'CONCLUIDO' && $idExemplar) {
            $ed = $data['estado_devolucao'] ?? 'OK';
            $exEstado = $ed === 'OK' ? 'DISPONIVEL' : ($ed === 'PERDIDO' ? 'PERDIDO' : 'DANIFICADO');
            $stmt = $this->db->prepare("UPDATE exemplar SET estado = ? WHERE id = ?");
            $stmt->execute([$exEstado, $idExemplar]);
        }

        $sql = "UPDATE requisicao_exemplar SET
                    observacao = :observacao,
                    data_levantamento = :data_levantamento,
                    data_devolucao = :data_devolucao,
                    estado_pedido = :estado_pedido,
                    estado_devolucao = :estado_devolucao,
                    estado_entrega = :estado_entrega
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'observacao' => $data['observacao'] ?? null,
            'data_levantamento' => $data['data_levantamento'] ?? null,
            'data_devolucao' => $data['data_devolucao'] ?? null,
            'estado_pedido' => $estadoNovo,
            'estado_devolucao' => $data['estado_devolucao'] ?? 'OK',
            'estado_entrega' => (int)($data['estado_entrega'] ?? 0),
            'id' => $id,
        ]);
    }

    public function delete(int $id)
    {
        $req = $this->find($id);
        if ($req && ($req['estado_pedido'] ?? '') === 'EM_USO') {
            $stmt = $this->db->prepare("UPDATE exemplar SET estado = 'DISPONIVEL' WHERE id = ?");
            $stmt->execute([$req['id_exemplar']]);
        }
        $stmt = $this->db->prepare("DELETE FROM requisicao_exemplar WHERE id = ?");
        return $stmt->execute([$id]);
    }


    public function getEventosCalendario($inicio, $fim, ?int $utilizadorId = null) {
        $eventos = [];
        $hoje = date('Y-m-d');

        // Get all relevant requisitions first
        $sql = "SELECT rm.*,
                       u.nome as user, m.designacao as titulo
                FROM requisicao_exemplar rm
                JOIN utilizador u ON rm.id_utilizador = u.id
                JOIN exemplar e ON rm.id_exemplar = e.id
                JOIN material m ON e.id_material = m.id
                WHERE rm.estado_pedido IN ('PENDENTE', 'ACEITE', 'EM_USO', 'CONCLUIDO')";
        // Se for passado um utilizador, mostro só as requisições DELE (para os alunos).
        $params = [];
        if ($utilizadorId !== null) {
            $sql .= " AND rm.id_utilizador = ?";
            $params[] = $utilizadorId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $requisicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($requisicoes as $row) {
            switch ($row['estado_pedido']) {
                case 'PENDENTE':
                    // Pending: show request date/time
                    $data = date('Y-m-d', strtotime($row['data_pedido']));
                    if ($data >= $inicio && $data <= $fim) {
                        $eventos[] = [
                            'id_req' => $row['id'],
                            'tipo_req' => 'material',
                            'data' => $data,
                            'hora' => date('H:i', strtotime($row['data_pedido'])),
                            'tipo' => 'pedido',
                            'titulo' => '[P] ' . $row['titulo'],
                            'user' => $row['user'],
                            'urgente' => ($data <= $hoje),
                            'data_entrega_prevista' => $row['data_levantamento'],
                            'data_devolucao_prevista' => $row['data_devolucao']
                        ];
                    }
                    break;
                case 'ACEITE':
                    // Accepted: show delivery date/time
                    if (!empty($row['data_levantamento'])) {
                        $data = date('Y-m-d', strtotime($row['data_levantamento']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'material',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_levantamento'])),
                                'tipo' => 'entregar',
                                'titulo' => $row['titulo'],
                                'user' => $row['user'],
                                'urgente' => ($data <= $hoje),
                                'data_entrega_prevista' => $row['data_levantamento'],
                                'data_devolucao_prevista' => $row['data_devolucao']
                            ];
                        }
                    }
                    break;
                case 'EM_USO':
                    // In use: show return date/time
                    if (!empty($row['data_devolucao'])) {
                        $data = date('Y-m-d', strtotime($row['data_devolucao']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'material',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_devolucao'])),
                                'tipo' => 'receber',
                                'titulo' => $row['titulo'],
                                'user' => $row['user'],
                                'urgente' => ($data <= $hoje),
                                'data_entrega_prevista' => $row['data_levantamento'],
                                'data_devolucao_prevista' => $row['data_devolucao']
                            ];
                        }
                    }
                    break;
                case 'CONCLUIDO':
                    // Concluído: fica para sempre no dia/hora em que o material voltou ao clube.
                    if (!empty($row['data_devolucao'])) {
                        $data = date('Y-m-d', strtotime($row['data_devolucao']));
                        if ($data >= $inicio && $data <= $fim) {
                            $eventos[] = [
                                'id_req' => $row['id'],
                                'tipo_req' => 'material',
                                'data' => $data,
                                'hora' => date('H:i', strtotime($row['data_devolucao'])),
                                'tipo' => 'concluido',
                                'titulo' => $row['titulo'],
                                'user' => $row['user'],
                                'urgente' => false, // já concluído, nunca é urgente
                                'data_entrega_prevista' => $row['data_levantamento'],
                                'data_devolucao_prevista' => $row['data_devolucao']
                            ];
                        }
                    }
                    break;
            }
        }
        return $eventos;
    }
}
