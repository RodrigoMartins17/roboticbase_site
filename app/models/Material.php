<?php
require_once __DIR__ . '/../core/Model.php';

class Material extends Model
{
    // =====================================================
    //   MATERIAL MODELO (Designação/Descrição)
    // =====================================================
    
    /** Total de modelos de material. */
    public function totalModelos()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM material");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    /** Total de exemplares (itens físicos). */
    public function totalExemplares()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM exemplar");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];
    }

    public function todosModelos()
    {
        $sql = "SELECT m.id, m.designacao, m.descricao, m.imagem,
                GROUP_CONCAT(DISTINCT c.categoria ORDER BY c.categoria SEPARATOR ', ') as categorias
                FROM material m
                LEFT JOIN categoria_material cm ON m.id = cm.id_material
                LEFT JOIN categoria c ON cm.id_categoria = c.id
                GROUP BY m.id
                ORDER BY m.designacao";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function modeloComCategorias(int $id)
    {
        $sql = "SELECT m.id, m.designacao, m.descricao, m.imagem,
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.id) as categoria_ids,
                GROUP_CONCAT(DISTINCT c.categoria ORDER BY c.categoria SEPARATOR ', ') as categorias
                FROM material m
                LEFT JOIN categoria_material cm ON m.id = cm.id_material
                LEFT JOIN categoria c ON cm.id_categoria = c.id
                WHERE m.id = ?
                GROUP BY m.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function findModelo(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM material WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createModelo(array $data)
    {
        $sql = "INSERT INTO material (designacao, descricao, imagem)
                VALUES (:designacao, :descricao, :imagem)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateModelo(int $id, array $data)
    {
        $data['id'] = $id;
        $sql = "UPDATE material
                SET designacao = :designacao,
                    descricao = :descricao,
                    imagem = :imagem
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteModelo(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM material WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function attachImagemSrc(array $modelos): array
    {
        foreach ($modelos as &$modelo) {
            $modelo['imagem_src'] = $this->getImagemSrc($modelo['imagem'] ?? null);
        }
        return $modelos;
    }

    // =====================================================
    //   MATERIAL ITEM (Itens físicos)
    // =====================================================

    public function todosItens()
    {
        $sql = "SELECT e.*, m.designacao, m.descricao
                FROM exemplar e
                JOIN material m ON e.id_material = m.id
                ORDER BY m.designacao, e.num_referencia";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function itensDisponiveis()
    {
        $sql = "SELECT e.*, m.designacao, m.descricao
                FROM exemplar e
                JOIN material m ON e.id_material = m.id
                WHERE e.estado = 'DISPONIVEL'
                ORDER BY m.designacao, e.num_referencia";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function itensPorModelo(int $materialId)
    {
        $sql = "SELECT e.*, m.designacao, m.descricao
                FROM exemplar e
                JOIN material m ON e.id_material = m.id
                WHERE e.id_material = ?
                ORDER BY e.num_referencia";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$materialId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findItem(int $id)
    {
        $sql = "SELECT e.*, m.designacao, m.descricao
                FROM exemplar e
                JOIN material m ON e.id_material = m.id
                WHERE e.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createItem(array $data)
    {
        $sql = "INSERT INTO exemplar (num_referencia, id_material, estado, observacao)
                VALUES (:num_referencia, :id_material, :estado, :observacao)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateItem(int $id, array $data)
    {
        $data['id'] = $id;
        $sql = "UPDATE exemplar
                SET num_referencia = :num_referencia,
                    id_material = :id_material,
                    estado = :estado,
                    observacao = :observacao
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteItem(int $id)
    {
        $this->db->prepare("DELETE FROM exemplar_sala WHERE id_exemplar = ?")->execute([$id]);
        $stmt = $this->db->prepare("DELETE FROM exemplar WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function adicionarSalaExemplar(int $exemplarId, int $salaId)
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO exemplar_sala (id_exemplar, id_sala) VALUES (?, ?)");
        return $stmt->execute([$exemplarId, $salaId]);
    }

    public function removerSalaExemplar(int $exemplarId, int $salaId = null)
    {
        if ($salaId) {
            $stmt = $this->db->prepare("DELETE FROM exemplar_sala WHERE id_exemplar = ? AND id_sala = ?");
            return $stmt->execute([$exemplarId, $salaId]);
        }
        $stmt = $this->db->prepare("DELETE FROM exemplar_sala WHERE id_exemplar = ?");
        return $stmt->execute([$exemplarId]);
    }

    // =====================================================
    //   CATEGORIA_MATERIAL (Relação N:N)
    // =====================================================

    public function adicionarCategoria(int $materialId, int $categoriaId)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO categoria_material (id_material, id_categoria) VALUES (?, ?)"
        );
        return $stmt->execute([$materialId, $categoriaId]);
    }

    public function removerCategoria(int $materialId, int $categoriaId)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM categoria_material WHERE id_material = ? AND id_categoria = ?"
        );
        return $stmt->execute([$materialId, $categoriaId]);
    }

    public function atualizarCategorias(int $materialId, array $categoriaIds)
    {
        // Remove todas as categorias existentes
        $stmt = $this->db->prepare("DELETE FROM categoria_material WHERE id_material = ?");
        $stmt->execute([$materialId]);

        // Adiciona as novas categorias
        if (!empty($categoriaIds)) {
            $sql = "INSERT INTO categoria_material (id_material, id_categoria) VALUES ";
            $values = [];
            $params = [];
            foreach ($categoriaIds as $catId) {
                $values[] = "(?, ?)";
                $params[] = $materialId;
                $params[] = $catId;
            }
            $sql .= implode(', ', $values);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return true;
    }

    // Métodos de compatibilidade (para não quebrar código existente)
    public function todosComCategoria()
    {
        return $this->todosModelos();
    }

    public function disponiveis()
    {
        return $this->itensDisponiveis();
    }

    public function find(int $id)
    {
        return $this->findItem($id);
    }

    public function create(array $data)
    {
        return $this->createItem($data);
    }

    public function updateById(int $id, array $data)
    {
        return $this->updateItem($id, $data);
    }

    public function deleteById(int $id)
    {
        return $this->deleteItem($id);
    }
}
