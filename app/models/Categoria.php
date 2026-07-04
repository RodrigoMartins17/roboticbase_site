<?php
require_once __DIR__ . '/../core/Model.php';

class Categoria extends Model
{
    public function all()
    {
        return $this->db->query("SELECT * FROM categoria ORDER BY categoria")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $nome, ?string $imagem = null)
    {
        $stmt = $this->db->prepare("INSERT INTO categoria (categoria, imagem) VALUES (?, ?)");
        return $stmt->execute([$nome, $imagem]);
    }

    public function update(int $id, string $nome, ?string $imagem = null)
    {
        $stmt = $this->db->prepare("UPDATE categoria SET categoria = ?, imagem = ? WHERE id = ?");
        return $stmt->execute([$nome, $imagem, $id]);
    }

    public function delete(int $id)
    {
        $db = $this->getDb();
        $db->beginTransaction();
        try {
            // Delete associations first
            $stmt = $db->prepare("DELETE FROM categoria_material WHERE id_categoria = ?");
            $stmt->execute([$id]);
            // Delete category
            $stmt = $db->prepare("DELETE FROM categoria WHERE id = ?");
            $stmt->execute([$id]);
            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function materiais(int $categoriaId)
    {
        $stmt = $this->db->prepare("
            SELECT m.* 
            FROM material m 
            INNER JOIN categoria_material cm ON m.id = cm.id_material 
            WHERE cm.id_categoria = ? 
            ORDER BY m.designacao
        ");
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

