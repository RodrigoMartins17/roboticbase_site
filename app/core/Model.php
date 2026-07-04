<?php
require_once __DIR__ . '/../config/database.php';

// Esta é a classe "mãe" de todos os models (Material, Sala, Utilizador, etc.).
// A ideia é: todos os models precisam de falar com a base de dados, por isso
// ponho essa parte aqui uma vez só e depois os outros models "herdam" dela.
class Model
{
    // A ligação à base de dados. É "protected" para os models filhos poderem usar.
    protected PDO $db;

    // Quando crio um model, ele vai logo buscar a ligação à base de dados.
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Caso precise da ligação à base de dados fora do model, uso este método.
    public function getDb(): PDO
    {
        return $this->db;
    }
}
