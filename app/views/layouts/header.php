<?php
// CABEÇALHO PARTILHADO — usado por TODAS as páginas (incluindo o dashboard).
// Estilo do dashboard (rb-nav), com o menu de 3 traços (☰) à esquerda do logo
// E os links das secções (Acesso, Sobre, Equipa, História, Eventos, Localização).
// À direita: avatar e sair. O ☰ abre uma barra lateral com a navegação do site.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/Utilizador.php';

$user = Auth::user();
$utilizadorModel = new Utilizador();
$avatarSrc = $utilizadorModel->getAvatarUrl($user);
$pageTitle = $pageTitle ?? 'Clube de Robótica';
$D = BASE_URL . 'dashboard/index';

// Links da barra lateral (navegação do site).
// As salas só podem ser reservadas por professores/responsáveis/admin, por isso
// os alunos nem sequer veem as opções de salas no menu.
$isAluno = Auth::isAluno();
$menu = [
    ['fa-house', 'Início', 'dashboard/index'],
    ['fa-microchip', 'Materiais', 'material/index'],
];
if (!$isAluno) {
    $menu[] = ['fa-door-open', 'Salas', 'sala/index'];
}
$menu[] = ['fa-calendar-days', 'Calendário', 'calendario/geral'];
$menu[] = ['fa-clipboard-list', 'Requisições de material', 'requisicaoMaterial/index'];
if (!$isAluno) {
    $menu[] = ['fa-calendar-check', 'Requisições de sala', 'requisicaoSala/index'];
}
$isStaff = Auth::isAdmin() || Auth::isResponsavel();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?> · RoboticaXL</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <!-- Afinações para telemóvel (carregado depois do style para o poder afinar) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/responsive.css">

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: #050505; color: #fff; margin: 0; overflow-x: hidden; }
        a { text-decoration: none; }

        /* Barra do topo, estilo do dashboard (rb-nav) */
        .rb-nav { position: sticky; top: 0; z-index: 200; background: rgba(5,5,5,0.95); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 1rem 0; }
        .rb-nav__in { display: flex; align-items: center; gap: 1rem; }
        .menu-toggle { width: 46px; height: 46px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04); color: #fff; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
        .menu-toggle:hover { background: rgba(59,130,246,0.18); border-color: rgba(59,130,246,0.5); }
        .rb-brand img { height: 50px; width: auto; object-fit: contain; }
        .rb-links { display: flex; align-items: center; gap: 0.25rem; list-style: none; margin: 0 0 0 auto; padding: 0; }
        .rb-links a { color: #fff; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; padding: 0.5rem 0.9rem; transition: color 0.25s ease; }
        .rb-links a:hover { color: #3b82f6; }
        .rb-nav-cta { display: flex; align-items: center; gap: 1.25rem; }
        .rb-avatar { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #3b82f6; object-fit: cover; transition: transform 0.25s ease; }
        .rb-avatar:hover { transform: scale(1.08); }
        .rb-logout-switch { color: #fff; font-size: 1.5rem; display: inline-flex; transition: all 0.25s ease; }
        .rb-logout-switch:hover { color: #dc2626; }
        .rb-entrar { display: inline-flex; align-items: center; gap: 0.5rem; background: #3b82f6; color: #fff; padding: 10px 22px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
        .rb-entrar:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(59,130,246,0.4); }
        /* Em ecrãs pequenos escondo os links inline; fica o ☰ para navegar. */
        @media (max-width: 991px) { .rb-links { display: none; } }

        /* Barra lateral (aberta pelo ☰) */
        .sb-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 300; opacity: 0; visibility: hidden; transition: all 0.25s; }
        .sb-overlay.open { opacity: 1; visibility: visible; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; max-width: 82vw; z-index: 310; background: #0b0d12; border-right: 1px solid rgba(255,255,255,0.08); transform: translateX(-100%); transition: transform 0.28s cubic-bezier(0.4,0,0.2,1); display: flex; flex-direction: column; padding: 1rem; }
        .sidebar.open { transform: translateX(0); }
        .sidebar__head { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0.5rem 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 0.75rem; }
        .sidebar__head img { height: 42px; }
        .sidebar__close { background: none; border: none; color: #9aa7bd; font-size: 1.6rem; cursor: pointer; line-height: 1; }
        .sidebar__close:hover { color: #fff; }
        .sidebar__links { display: flex; flex-direction: column; gap: 0.25rem; }
        .sidebar__links a { display: flex; align-items: center; gap: 0.9rem; padding: 0.8rem 0.9rem; border-radius: 10px; color: #cfd3da; font-weight: 500; font-size: 0.98rem; transition: all 0.18s; }
        .sidebar__links a i { width: 20px; text-align: center; color: #3b82f6; }
        .sidebar__links a:hover { background: rgba(59,130,246,0.14); color: #fff; }
        .sidebar__foot { margin-top: auto; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar__foot a { display: flex; align-items: center; gap: 0.9rem; padding: 0.8rem 0.9rem; border-radius: 10px; color: #fca5a5; font-weight: 500; }
        .sidebar__foot a:hover { background: rgba(239,68,68,0.14); }
    </style>
</head>
<body>

    <nav class="rb-nav">
        <div class="container rb-nav__in">
            <button class="menu-toggle" onclick="abrirMenu()" aria-label="Abrir menu"><i class="fas fa-bars"></i></button>
            <a class="rb-brand" href="<?php echo $D; ?>">
                <img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="Clube de Robótica">
            </a>

            <ul class="rb-links">
                <li><a href="<?php echo $D; ?>#materiais">Acesso</a></li>
                <li><a href="<?php echo $D; ?>#sobre">Sobre</a></li>
                <li><a href="<?php echo $D; ?>#professores">Equipa</a></li>
                <li><a href="<?php echo $D; ?>#historia">História</a></li>
                <li><a href="<?php echo $D; ?>#eventos">Eventos</a></li>
                <li><a href="<?php echo $D; ?>#localizacao">Localização</a></li>
            </ul>

            <div class="rb-nav-cta" style="margin-left: 1.25rem;">
                <?php if (Auth::check()): ?>
                    <a href="<?php echo BASE_URL; ?>auth/profile" title="O meu perfil">
                        <img src="<?php echo $avatarSrc; ?>" alt="Perfil" class="rb-avatar">
                    </a>
                    <a class="rb-logout-switch" href="<?php echo BASE_URL; ?>auth/logout" title="Sair">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                <?php else: ?>
                    <a class="rb-entrar" href="<?php echo BASE_URL; ?>auth/login"><i class="fa-solid fa-right-to-bracket"></i> Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="sb-overlay" id="sbOverlay" onclick="fecharMenu()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__head">
            <img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="RoboticaXL">
            <button class="sidebar__close" onclick="fecharMenu()" aria-label="Fechar">&times;</button>
        </div>
        <nav class="sidebar__links">
            <?php foreach ($menu as $m): ?>
                <a href="<?php echo BASE_URL . $m[2]; ?>"><i class="fas <?php echo $m[0]; ?>"></i> <?php echo $m[1]; ?></a>
            <?php endforeach; ?>
            <?php if ($isStaff): ?>
                <a href="<?php echo BASE_URL; ?>admin/index"><i class="fas fa-gauge-high"></i> Administração</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar__foot">
            <a href="<?php echo BASE_URL; ?>auth/logout"><i class="fas fa-arrow-right-from-bracket"></i> Terminar sessão</a>
        </div>
    </aside>

    <script>
        function abrirMenu() { document.getElementById('sidebar').classList.add('open'); document.getElementById('sbOverlay').classList.add('open'); }
        function fecharMenu() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sbOverlay').classList.remove('open'); }
    </script>

    <main>
