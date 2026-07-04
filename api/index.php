<?php
// Ponte para o Vercel: no XAMPP e o .htaccess que mete o caminho no
// parametro "url"; no Vercel sou eu a faze-lo aqui, a partir do URL pedido.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($path, '/');
// Na pagina inicial o caminho fica vazio - nesse caso mando para home/index.
$_GET['url'] = ($path === '') ? 'home/index' : $path;
require __DIR__ . '/../public/index.php';
