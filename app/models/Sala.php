<?php
require_once __DIR__ . '/../core/Model.php';

class Sala extends Model
{
    public function todas()
    {
        return $this->db->query("SELECT * FROM sala ORDER BY bloco, andar, numero")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sala WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO sala (numero, andar, bloco, capacidade, descricao, estado)
                VALUES (:numero, :andar, :bloco, :capacidade, :descricao, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateById(int $id, array $data)
    {
        $data['id'] = $id;
        $sql = "UPDATE sala
                SET numero = :numero,
                    andar = :andar,
                    bloco = :bloco,
                    capacidade = :capacidade,
                    descricao = :descricao,
                    estado = :estado
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteById(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM sala WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
