<?php
// TOPO PARA VISITANTES (sem login): mesmo header do dashboard, com o menu de 3
// traços (☰) e os links das secções. À direita mostra Entrar/Registar.
if (!defined('BASE_URL')) { require_once __DIR__ . '/../../config/config.php'; }
$D = BASE_URL . 'dashboard/index';
?>
<style>
    .rb-nav { position: sticky; top: 0; z-index: 200; background: rgba(5,5,5,0.95); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 1rem 0; }
    .rb-nav__in { max-width: 1140px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; gap: 1rem; }
    .menu-toggle { width: 46px; height: 46px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04); color: #fff; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
    .menu-toggle:hover { background: rgba(59,130,246,0.18); border-color: rgba(59,130,246,0.5); }
    .rb-brand img { height: 50px; width: auto; object-fit: contain; }
    .rb-links { display: flex; align-items: center; gap: 0.25rem; list-style: none; margin: 0 0 0 auto; padding: 0; }
    .rb-links a { color: #fff; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; padding: 0.5rem 0.9rem; transition: color 0.25s ease; }
    .rb-links a:hover { color: #3b82f6; }
    .gtb__cta { display: flex; align-items: center; gap: 0.6rem; margin-left: 1.25rem; }
    .gtb__entrar { color: #fff; border: 1.5px solid rgba(255,255,255,0.55); padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
    .gtb__entrar:hover { background: rgba(255,255,255,0.08); }
    .gtb__reg { background: #3b82f6; color: #fff; padding: 9px 20px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
    .gtb__reg:hover { background: #2563eb; }
    @media (max-width: 991px) { .rb-links { display: none; } }
    .gsb-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 300; opacity: 0; visibility: hidden; transition: all 0.25s; }
    .gsb-overlay.open { opacity: 1; visibility: visible; }
    .gsidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; max-width: 82vw; z-index: 310; background: #0b0d12; border-right: 1px solid rgba(255,255,255,0.08); transform: translateX(-100%); transition: transform 0.28s cubic-bezier(0.4,0,0.2,1); display: flex; flex-direction: column; padding: 1rem; }
    .gsidebar.open { transform: translateX(0); }
    .gsidebar__head { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0.5rem 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 0.75rem; }
    .gsidebar__head img { height: 42px; }
    .gsidebar__close { background: none; border: none; color: #9aa7bd; font-size: 1.6rem; cursor: pointer; line-height: 1; }
    .gsidebar a { display: flex; align-items: center; gap: 0.9rem; padding: 0.8rem 0.9rem; border-radius: 10px; color: #cfd3da; font-weight: 500; font-size: 0.98rem; transition: all 0.18s; }
    .gsidebar a i { width: 20px; text-align: center; color: #3b82f6; }
    .gsidebar a:hover { background: rgba(59,130,246,0.14); color: #fff; }
    .gsidebar__foot { margin-top: auto; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); }
</style>

<nav class="rb-nav">
    <div class="rb-nav__in">
        <button class="menu-toggle" onclick="gAbrir()" aria-label="Abrir menu"><i class="fas fa-bars"></i></button>
        <a class="rb-brand" href="<?php echo BASE_URL; ?>"><img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="RoboticaXL"></a>
        <ul class="rb-links">
            <li><a href="<?php echo $D; ?>#materiais">Acesso</a></li>
            <li><a href="<?php echo $D; ?>#sobre">Sobre</a></li>
            <li><a href="<?php echo $D; ?>#professores">Equipa</a></li>
            <li><a href="<?php echo $D; ?>#historia">História</a></li>
            <li><a href="<?php echo $D; ?>#eventos">Eventos</a></li>
            <li><a href="<?php echo $D; ?>#localizacao">Localização</a></li>
        </ul>
        <div class="gtb__cta">
            <a class="gtb__entrar" href="<?php echo BASE_URL; ?>auth/login">Entrar</a>
            <a class="gtb__reg" href="<?php echo BASE_URL; ?>auth/register">Registar</a>
        </div>
    </div>
</nav>

<div class="gsb-overlay" id="gsbOverlay" onclick="gFechar()"></div>
<aside class="gsidebar" id="gsidebar">
    <div class="gsidebar__head">
        <img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="RoboticaXL">
        <button class="gsidebar__close" onclick="gFechar()" aria-label="Fechar">&times;</button>
    </div>
    <nav>
        <a href="<?php echo BASE_URL; ?>"><i class="fas fa-house"></i> Início</a>
        <a href="<?php echo $D; ?>#sobre"><i class="fas fa-circle-info"></i> Sobre</a>
        <a href="<?php echo $D; ?>#eventos"><i class="fas fa-calendar-star"></i> Eventos</a>
        <a href="<?php echo $D; ?>#localizacao"><i class="fas fa-location-dot"></i> Localização</a>
    </nav>
    <div class="gsidebar__foot">
        <a href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-right-to-bracket"></i> Entrar</a>
        <a href="<?php echo BASE_URL; ?>auth/register"><i class="fas fa-user-plus"></i> Criar conta</a>
    </div>
</aside>

<script>
    function gAbrir() { document.getElementById('gsidebar').classList.add('open'); document.getElementById('gsbOverlay').classList.add('open'); }
    function gFechar() { document.getElementById('gsidebar').classList.remove('open'); document.getElementById('gsbOverlay').classList.remove('open'); }
</script>
