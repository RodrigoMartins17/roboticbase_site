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
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            // Isto faz o PDO avisar-me com um erro sempre que algo corre mal,
            // em vez de falhar em silêncio.
            $opcoes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            // Bases de dados online (ex: Aiven) exigem ligação encriptada (SSL).
            // Ativo isto com a variável de ambiente DB_SSL=1 no servidor.
            if (getenv('DB_SSL')) {
                $opcoes[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
                $opcoes[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            try {
                // Crio a ligação com o PDO (a forma segura de falar com a BD em PHP).
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $opcoes);

                // Alinho o relógio do MySQL com o de Portugal. A base de dados online
                // trabalha em UTC, por isso o NOW() dela ficava 1 hora atrasado no verão.
                // Calculo o desvio atual de Lisboa no PHP (+01:00 no verão, +00:00 no
                // inverno — o PHP trata da mudança da hora sozinho) e aplico-o à sessão.
                try {
                    $desvio = (new \DateTime('now', new \DateTimeZone('Europe/Lisbon')))->format('P');
                    self::$instance->exec("SET time_zone = '" . $desvio . "'");
                } catch (\Throwable $e) {
                    // Se falhar, o site continua a funcionar (só as horas da BD ficam em UTC).
                }
            } catch (PDOException $e) {
                // Se não conseguir ligar (ex: MySQL desligado), paro e mostro o erro.
                die('Erro de ligação à BD: ' . $e->getMessage());
            }
        }
        // Devolvo sempre a mesma ligação.
        return self::$instance;
    }
}
