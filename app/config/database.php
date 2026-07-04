<?php
require_once __DIR__ . '/config.php';

// Esta classe serve só para fazer a ligação à base de dados MySQL.
// Uso um truque chamado "singleton": guardo a ligação numa variável e,
// se já existir, aproveito-a em vez de estar sempre a criar ligações novas
// (assim o site fica mais rápido e não sobrecarrega o MySQL).
class Database
{
    // Aqui fica guardada a ligação. Começa a null (ainda não existe).
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        // Só faço a ligação na primeira vez que alguém precisar dela.
        if (self::$instance === null) {
            // A "DSN" é a morada da base de dados: onde está e como se chama.
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                // Crio a ligação com o PDO (a forma segura de falar com a BD em PHP).
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // Isto faz o PDO avisar-me com um erro sempre que algo corre mal,
                    // em vez de falhar em silêncio.
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } catch (PDOException $e) {
                // Se não conseguir ligar (ex: MySQL desligado), paro e mostro o erro.
                die('Erro de ligação à BD: ' . $e->getMessage());
            }
        }
        // Devolvo sempre a mesma ligação.
        return self::$instance;
    }
}
