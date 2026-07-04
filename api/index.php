<?php
// Ponte para o Vercel: no XAMPP é o .htaccess que mete o caminho no
// parâmetro "url"; no Vercel sou eu a fazê-lo aqui, a partir do URL pedido.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_GET['url'] = trim($path, '/');
require __DIR__ . '/../public/index.php';
