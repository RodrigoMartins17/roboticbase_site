<?php
// Este ficheiro guarda as "definições" do site num sítio só.
// Assim, se um dia mudar de servidor ou de base de dados, só mexo aqui.

// Segredos locais (passwords) ficam num ficheiro à parte que NÃO vai para o GitHub.
// Se existir, carrego-o primeiro — ele define constantes como SMTP_PASS.
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Fuso horário de Portugal continental. Sem isto o PHP usava o fuso do servidor
// (às vezes UTC+2, tipo Berlim) e o calendário ficava 1 hora adiantado.
date_default_timezone_set('Europe/Lisbon');

// Endereço base do site. Uso isto em todos os links para não andar a escrever
// o caminho completo à mão em todo o lado.
// Em vez de o deixar fixo (só servia no meu PC), descubro-o sozinho a partir do
// pedido. Assim o mesmo código funciona no XAMPP local E quando alojo o site
// online, sem ter de andar a mudar isto à mão.
if (!defined('BASE_URL')) {
    if (getenv('BASE_URL')) {
        // No servidor online (ex: Vercel) defino BASE_URL como variável de ambiente.
        define('BASE_URL', getenv('BASE_URL'));
    } elseif (php_sapi_name() === 'cli') {
        // Na linha de comandos (ex: o cron dos avisos) não há pedido, uso um valor fixo.
        define('BASE_URL', 'http://localhost/roboticbase_site/public/');
    } else {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $dominio   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // A pasta onde está o index.php (ex: /roboticbase_site/public/ ou só / se estiver na raiz).
        $pasta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/';
        define('BASE_URL', $protocolo . '://' . $dominio . $pasta);
    }
}

// Dados de acesso à base de dados MySQL.
// No XAMPP local é "root" sem password. Quando alojo o site online, o host dá-me
// outros dados — basta mudá-los AQUI (ou pôr variáveis de ambiente no servidor).
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'clube_robotica');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

// Definições do email, para o site poder enviar mensagens (ex: validar conta,
// avisar que um pedido foi aceite). Se existirem variáveis de ambiente uso essas,
// senão fico com estes valores por defeito.
// A password do email NUNCA fica aqui — vem do config.local.php ou de variáveis
// de ambiente no servidor. Se faltar, o envio de email simplesmente não funciona.
if (!defined('SMTP_HOST')) define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
if (!defined('SMTP_USER')) define('SMTP_USER', getenv('SMTP_USER') ?: 'roboticaxl.aeffl@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'roboticaxl.aeffl@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'RoboticaXL');

// Começo a sessão (é onde guardo quem está com login).
// Só a inicio se ainda não estiver iniciada, para não dar erro.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handler global: se acontecer um erro NÃO tratado (por exemplo um erro devolvido
// por uma procedure/trigger da base de dados), registo-o no log de erros e mostro
// uma página simpática em vez do erro técnico feio do PHP.
set_exception_handler(function (\Throwable $e) {
    try {
        require_once __DIR__ . '/../models/Log.php';
        $sqlstate = null;
        $codigo = (string)$e->getCode();
        if ($e instanceof PDOException && isset($e->errorInfo[0])) {
            $sqlstate = $e->errorInfo[0];
            $codigo = isset($e->errorInfo[1]) ? (string)$e->errorInfo[1] : $codigo;
        }
        (new Log())->registarErro('Exceção não tratada', $e->getMessage(), $sqlstate, $codigo, $_SESSION['user']['id'] ?? null);
    } catch (\Throwable $x) {
    }
    http_response_code(500);
    echo '<div style="font-family:Arial,sans-serif;max-width:640px;margin:60px auto;padding:28px;border:1px solid #eee;border-radius:14px;text-align:center;">'
        . '<h2 style="color:#d93025;margin:0 0 10px;">Ocorreu um erro inesperado</h2>'
        . '<p style="color:#555;margin:0;">O erro foi registado para análise. Tenta novamente ou contacta o administrador.</p>'
        . '</div>';
});
