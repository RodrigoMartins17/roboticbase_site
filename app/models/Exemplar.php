<?php
require_once __DIR__ . '/../core/Model.php';

class Exemplar extends Model
{
    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT e.*, m.designacao as material_nome FROM exemplar e JOIN material m ON e.id_material = m.id WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(int $id)
    {
        // Remove associações primeiro
        $this->db->prepare("DELETE FROM exemplar_sala WHERE id_exemplar = ?")->execute([$id]);
        $stmt = $this->db->prepare("DELETE FROM exemplar WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>

