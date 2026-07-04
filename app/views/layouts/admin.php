<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Auth.php';
$currentUser = Auth::user();
$admin_breadcrumb = $admin_breadcrumb ?? 'Visão Geral';
$sec = $admin_section ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração | Clube de Robótica</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-bg: #0f172a; 
            --sidebar-hover: #1e293b; 
            --accent-color: #2563eb; 
            --main-bg: #f8fafc;
        }
        body {
            background-color: var(--main-bg);
            font-family: 'Inter', sans-serif;
            color: #334155;
            margin: 0;
            overflow-x: hidden;
        }
        .wrapper { display: flex; width: 100%; min-height: 100vh; }
        
       #sidebar {
    min-width: 280px;
    max-width: 280px;
    background: var(--sidebar-bg);
    color: #e2e8f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 4px 0 24px rgba(0,0,0,0.06);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
}
        .sidebar-header {
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-logo-icon {
            width: 40px; height: 40px; background: var(--accent-color); border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white;
        }
        .sidebar-brand-text { font-weight: 700; font-size: 1.1rem; letter-spacing: 0.5px; color: #fff;}
        .sidebar-menu { padding: 1.5rem 1rem; flex-grow: 1; overflow-y: auto;}
        .menu-category { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin: 1rem 0 0.5rem 1rem; font-weight: 600;}
        
        #sidebar ul { list-style: none; padding: 0; margin: 0; }
        #sidebar ul li a {
            padding: 0.8rem 1rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            font-weight: 500;
        }
        #sidebar ul li a i { width: 24px; font-size: 1.1rem; transition: transform 0.2s; }
        #sidebar ul li a:hover { color: #f8fafc; background: var(--sidebar-hover); transform: translateX(4px); }
        #sidebar ul li a:hover i { transform: scale(1.1); color: var(--accent-color); }
        
        #sidebar ul li.active > a {
            color: #fff;
            background: var(--accent-color);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        #sidebar ul li.active > a i { color: #fff; }
        
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); }
        .user-info-card { background: var(--sidebar-hover); padding: 12px; border-radius: 12px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: #e2e8f0; transition: 0.2s;}
        .user-info-card:hover { background: rgba(255,255,255,0.1); color: white;}
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #cbd5e1; color: var(--sidebar-bg); display: flex; align-items: center; justify-content: center; font-weight: bold;}
        
        /* --- CONTENT AREA --- */
        #content { width: 100%; display: flex; flex-direction: column; overflow-x: hidden; }
        .top-navbar {
            background: #fff; height: 70px; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02); z-index: 10;
        }
        .main-container { padding: 2.5rem; flex-grow: 1; max-width: 1400px; margin: 0 auto; width: 100%;}
        
        @media (max-width: 768px) {
            #sidebar { margin-left: -280px; position: fixed; height: 100%; }
            #sidebar.active { margin-left: 0; }
            .main-container { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-icon"><i class="fas fa-robot"></i></div>
            <div class="sidebar-brand-text">Administração</div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-category">Dashboard</div>
            <ul>
                <li class="<?php echo $sec === 'inicio' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/index"><i class="fas fa-home"></i> Visão Geral</a></li>
            </ul>

            <div class="menu-category">Inventário</div>
            <ul>
                <li class="<?php echo $sec === 'categorias' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/categorias"><i class="fas fa-tags"></i> Categorias</a></li>
                <li class="<?php echo $sec === 'materiais' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/materiais"><i class="fas fa-box"></i> Materiais</a></li>
                <li class="<?php echo $sec === 'exemplares' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/exemplares"><i class="fas fa-cubes"></i> Exemplares</a></li>
            </ul>

            <div class="menu-category">Gestão</div>
            <ul>
                <li class="<?php echo $sec === 'req_materiais' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/requisicoesMateriais"><i class="fas fa-clipboard-list"></i> Req. Materiais</a></li>
                <li class="<?php echo $sec === 'req_salas' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/requisicoesSalas"><i class="fas fa-door-open"></i> Req. Salas</a></li>
                <li class="<?php echo $sec === 'salas' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/salas"><i class="fas fa-building"></i> Salas</a></li>
                <li class="<?php echo $sec === 'eventos' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/eventos"><i class="fas fa-bullhorn"></i> Eventos</a></li>
            </ul>

            <div class="menu-category">Sistema</div>
            <ul>
                <li class="<?php echo $sec === 'utilizadores' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/utilizadores"><i class="fas fa-users"></i> Utilizadores</a></li>
                <li class="<?php echo $sec === 'logs' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/logs"><i class="fas fa-clipboard-check"></i> Logs & Auditoria</a></li>
                <li class="<?php echo $sec === 'logs_erros' ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>admin/logsErros"><i class="fas fa-triangle-exclamation"></i> Logs de Erros</a></li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <a href="<?php echo BASE_URL; ?>dashboard/index" class="user-info-card" title="Voltar ao site">
                <div class="user-avatar"><i class="fas fa-user"></i></div>
                <div style="overflow: hidden;">
                    <div style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; text-overflow: ellipsis;"><?= htmlspecialchars($currentUser['nome'] ?? 'Administrador') ?></div>
                    <div style="font-size: 0.7rem; color: #94a3b8;"><i class="fas fa-sign-out-alt me-1"></i> Sair do Painel</div>
                </div>
            </a>
        </div>
    </nav>

    <div id="content">
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-3" id="sidebarToggle" style="border: none;"><i class="fas fa-bars"></i></button>
                <h5 class="mb-0 fw-bold" style="color: #1e293b;"><?= htmlspecialchars($admin_breadcrumb) ?></h5>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;"><i class="far fa-calendar-alt me-1"></i> <?= date('d M Y') ?></span>
            </div>
        </div>

        <div class="main-container">
            <?php require $content_view; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>