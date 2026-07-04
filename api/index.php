<?php
// Em produção não mostro avisos do PHP aos visitantes (ficam só nos logs).
ini_set('display_errors', '0');
// Ponte para o Vercel: no XAMPP é o .htaccess que mete o caminho no
// parâmetro "url"; no Vercel sou eu a fazê-lo aqui, a partir do URL pedido.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($path, '/');
// Na página inicial o caminho fica vazio — nesse caso mando para home/index
// (tal como o Router faria no XAMPP quando não há parâmetro "url").
$_GET['url'] = ($path === '') ? 'home/index' : $path;
require __DIR__ . '/../public/index.php';
