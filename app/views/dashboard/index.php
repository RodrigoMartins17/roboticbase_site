<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/Utilizador.php';
$user = Auth::user();
$professores = $professores ?? [];
$eventos = $eventos ?? [];
$utilizadorModel = new Utilizador();
$avatarSrc = $utilizadorModel->getAvatarUrl($user);
$robotImage = BASE_URL . 'uploads/mascote2.png';
$robotHistoryImage = BASE_URL . 'uploads/mascote.png';
$responsavel = $professores[0] ?? null;
$responsavelAvatar = $responsavel ? $utilizadorModel->getAvatarUrl($responsavel) : BASE_URL . 'uploads/logosite.png';
$isAdmin = Auth::isAdmin();
$isResponsavel = Auth::isResponsavel();
$isProf = Auth::isProf();
$isAluno = Auth::isAluno();
?>

<!-- Dashboard como FRAGMENTO: a moldura (header + rodapé) vem do header.php/footer.php,
     igual às outras páginas. Aqui fica só o estilo das secções e o conteúdo. -->
<style>
        :root {
            --brand-blue: #0066ff;
            --brand-black: #050505;
            --brand-grey: #555555;
            --brand-dark-grey: #111111;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--brand-black);
            color: #fff;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* --- NAVBAR --- */
        .rb-nav {
            background-color: rgba(5, 5, 5, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .rb-nav .navbar-brand img {
            height: 50px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .rb-nav .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .rb-nav .nav-link {
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .rb-nav .nav-link:hover {
            color: var(--brand-blue);
            transform: translateY(-2px);
        }

        .rb-nav-cta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .rb-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--brand-blue);
            object-fit: cover;
            transition: all 0.3s ease;
        }
        .rb-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 102, 255, 0.4);
        }

        .rb-logout-switch {
            color: #fff;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        .rb-logout-switch:hover {
            color: #dc2626;
            transform: scale(1.1) translateX(2px);
        }

        /* --- HERO SECTION --- */
        .rb-hero {
            position: relative;
            min-height: 60vh;
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('<?php echo BASE_URL; ?>uploads/fundo_site.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding-top: 80px;
        }

        .rb-welcome-hero {
            text-align: left;
        }

        .rb-welcome-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 1rem;
            color: #fff;
            letter-spacing: 2px;
        }

        .rb-welcome-hero p {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .rb-welcome-hero .btn-primary {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
            padding: 10px 30px;
            text-transform: uppercase;
            font-weight: 700;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .rb-welcome-hero .btn-primary:hover {
            background: var(--brand-blue);
            color: #fff;
            border-color: var(--brand-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 102, 255, 0.4);
        }

        /* --- SECTIONS GERAIS & CORES INTERCALADAS --- */
        .rb-section {
            padding: 6rem 0;
        }
        
        .rb-black {
            background-color: var(--brand-black);
        }

        .rb-blue {
            background-color: var(--brand-blue);
            color: #fff;
        }

        .rb-section-title {
            margin-bottom: 3.5rem;
        }

        .rb-eyebrow {
            color: var(--brand-blue);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        /* Ajuste do eyebrow em secções azuis para destacar */
        .rb-blue .rb-eyebrow {
            color: #050505;
            background-color: #fff;
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 4px;
        }

        .rb-section-title h2 {
            font-size: 3.5rem;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1;
            color: #fff;
        }
        
        .rb-muted {
            color: #aaa;
            margin-top: 1rem;
        }
        
        .rb-blue .rb-muted {
            color: rgba(255, 255, 255, 0.8);
        }

        /* --- CARDS (Materiais) --- */
        .rb-card {
            background-color: var(--brand-dark-grey);
            border: 2px solid var(--brand-grey);
            padding: 2.5rem 1.5rem;
            border-radius: 8px;
            height: 100%;
            transition: all 0.3s ease;
            text-align: center;
            color: #fff;
        }

        .rb-card-link {
            display: block;
        }

        .rb-card-link:hover {
            border-color: var(--brand-blue);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 102, 255, 0.4);
            background-color: var(--brand-black);
        }

        .accent-icon {
            font-size: 2.5rem;
            color: var(--brand-blue);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        /* Fazer o ícone ficar branco quando o fundo for azul */
        .rb-card-link:hover .accent-icon {
            transform: scale(1.1);
            color: #fff; 
        }

        /* Fazer o texto cinzento claro (<p>) ficar branco para facilitar a leitura */
        .rb-card-link:hover p {
            color: #fff;
            transition: color 0.3s ease;
        }
        .rb-card h3 {
            font-weight: 800;
            font-size: 1.4rem;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .rb-card p {
            color: #bbb;
            font-size: 0.95rem;
            margin: 0;
        }

        /* --- EQUIPA (Professores) - Agora numa secção AZUL --- */
        .rb-prof-card {
            background-color: transparent;
            border: none;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .rb-prof-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--brand-black);
            margin: 0 auto 1.5rem;
            display: block;
            transition: all 0.4s ease;
            background-color: #fff;
        }

        .rb-prof-card:hover .rb-prof-avatar {
            transform: scale(1.1);
            border-color: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .rb-prof-card h3 {
            font-size: 1.3rem;
            color: #fff;
            transition: color 0.3s ease;
            font-weight: 700;
        }
        .rb-prof-card:hover h3 {
            color: var(--brand-black);
        }

        .rb-icon-btn {
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
            margin: 0 5px;
            transition: all 0.3s ease;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0,0,0,0.2);
        }
        .rb-icon-btn:hover {
            background-color: var(--brand-black);
            border-color: var(--brand-black);
            color: #fff;
            transform: translateY(-3px);
        }

        /* --- HISTÓRIA / TIMELINE --- */
        .rb-history {
            display: flex;
            align-items: flex-start;
            gap: 4rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .rb-history-robot {
            flex: 0 0 200px;
            text-align: center;
        }

        .rb-robot {
            max-width: 140%;
            transition: all 0.5s ease;
        }
        .rb-history:hover .rb-robot {
            transform: scale(1.05);
        }

        .rb-timeline {
            flex: 1;
            border-left: 3px solid var(--brand-blue);
            padding-left: 3rem;
        }

        .rb-timeline-item {
            position: relative;
            margin-bottom: 3rem;
        }
        .rb-timeline-item:last-child {
            margin-bottom: 0;
        }

        .rb-timeline-item::before {
            content: '';
            position: absolute;
            left: -53px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--brand-blue);
            border: 4px solid var(--brand-black);
            transition: transform 0.3s ease;
        }
        .rb-timeline-item:hover::before {
            transform: scale(1.3);
            background: #fff;
        }

        .rb-date {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--brand-blue);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        /* --- EVENTOS - Estilo "Home" numa secção AZUL --- */
        .rb-event-card {
            padding: 0;
            border: none;
            background-color: var(--brand-black);
            overflow: hidden;
            text-align: left;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            transition: all 0.4s ease;
        }

        .rb-event-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: all 0.5s ease;
            border-bottom: 3px solid var(--brand-grey);
        }

        .rb-event-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
        }

        .rb-event-info h3 {
            font-size: 1.3rem;
            margin: 0;
            color: #fff;
            flex: 1;
            padding-right: 15px;
        }

        .rb-event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }

        .rb-event-card:hover img {
            transform: scale(1.05);
            border-bottom-color: var(--brand-blue);
        }
        
        /* Botão "+" Circular */
        .btn-plus-event {
            background-color: var(--brand-blue);
            color: #fff;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.4s ease;
            text-decoration: none;
            flex-shrink: 0;
        }
        
        .btn-plus-event:hover {
            background-color: #fff;
            color: var(--brand-blue);
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .btn-ver-todos {
            background-color: var(--brand-black);
            color: #fff;
            border: 2px solid var(--brand-black);
            transition: all 0.3s ease;
        }
        
        .btn-ver-todos:hover {
            background-color: #fff;
            color: var(--brand-black);
            border-color: #fff;
        }

        /* --- SECÇÃO LOCALIZAÇÃO --- */
        .location-section { padding: 4rem 0; background: var(--brand-black); }
        .location-item { margin-bottom: 1.5rem; }
        .location-item h5 { font-weight: 700; font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--brand-blue); }
        .location-item p { font-size: 1rem; line-height: 1.6; }
        
        .map-container {
            width: 100%;
            height: 400px; 
            border: 2px solid var(--brand-blue);
            border-radius: 4px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .map-container:hover {
            box-shadow: 0 0 20px rgba(0, 102, 255, 0.3);
        }
        .map-container iframe { width: 100%; height: 100%; border: none; }

        /* --- RODAPÉ --- */
        .site-footer { padding: 4rem 0 2rem; text-align: center; }
        .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .social-icons a:hover { 
            color: var(--brand-blue); 
            transform: translateY(-5px);
        }
        .copyright { font-size: 0.85rem; color: #aaa; margin-top: 2rem; }

        .clearfix::after { content: ""; clear: both; display: table; }
         /* Logotipo */
        .logo-img {
            height: 90px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .logo-img:hover {
            transform: scale(1.05);
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .rb-welcome-hero h1 { font-size: 2.5rem; }
            .rb-section-title h2 { font-size: 2.5rem; }
            .rb-history { gap: 2rem; }
            .rb-timeline { padding-left: 2rem; }
            .rb-timeline-item::before { left: -39px; }
            .rb-history-robot { display: none; }
        }

         /* --- SECÇÃO SOBRE --- */
        .about-section { 
            padding: 6rem 0; 
        }
        .section-title {
            font-size: 4.5rem;
            font-weight: 800;
            transform: scaleX(1.1);
            transform-origin: left;
            line-height: 1;
            margin-bottom: 2rem;
            text-transform: uppercase;
            color: #fff;
        }
        .about-text { font-size: 1.1rem; line-height: 1.8; font-weight: 500;}
        
        .about-images-container {
            position: relative;
            min-height: 500px;
            width: 100%;
        }
        
        .about-img-1 {
            width: 70%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            border: 5px solid var(--brand-black);
            aspect-ratio: 4/3;
            transition: transform 0.5s ease;
        }
        .about-img-1:hover {
            transform: scale(1.02);
        }
        
        .about-img-2 {
            width: 50%;
            position: absolute;
            bottom: -60px;
            right: 0;
            z-index: 2;
            border: 5px solid var(--brand-black);
            background-color: var(--brand-blue);
            box-shadow: -15px 15px 0 var(--brand-black);
            transition: transform 0.5s ease;
        }
        .about-img-2:hover {
            transform: translateY(-10px);
        }

        /* --- NOVO: MODAL / BALÃO DE EVENTOS (Estilo Clean) --- */
        .rb-modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(5, 5, 5, 0.85);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .rb-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .rb-modal-content {
            background-color: var(--brand-dark-grey);
            border: 1px solid var(--brand-grey);
            padding: 2.5rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            position: relative;
            transform: translateY(-30px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        .rb-modal-overlay.active .rb-modal-content {
            transform: translateY(0) scale(1);
        }

        .rb-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: transparent;
            border: none;
            color: #aaa;
            font-size: 2rem;
            line-height: 1;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .rb-modal-close:hover {
            color: var(--brand-blue);
        }

        .rb-modal-content img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 2px solid var(--brand-black);
        }

        .rb-modal-content h3 {
            color: var(--brand-blue);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .rb-modal-content p {
            color: #ccc;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            text-align: left;
        }

        .rb-modal-btn {
            display: inline-block;
            width: 100%;
            background-color: var(--brand-blue);
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            text-transform: uppercase;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .rb-modal-btn:hover {
            background-color: #fff;
            color: var(--brand-blue);
            transform: translateY(-2px);
        }
    </style>

    <header class="rb-hero" id="topo">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="rb-welcome-hero">
                        <?php if (!empty($user)): ?>
                            <!-- Utilizador com sessão iniciada -->
                            <h1>Bem-vindo(a),<br><span style="color: var(--brand-blue);"><?php echo htmlspecialchars($user['nome']); ?></span></h1>
                            <p>Este é o portal do RoboticaXL, da Escola Secundária Dr. Francisco Fernandes Lopes.</p>
                            <a class="btn btn-primary" href="#materiais">ACESSO RÁPIDO</a>
                        <?php else: ?>
                            <!-- Visitante (home): mostra o botão de entrar por baixo -->
                            <h1>Bem-vindo ao<br><span style="color: var(--brand-blue);">Clube de Robótica RoboticaXL</span></h1>
                            <p>Requisita material, reserva salas e acompanha os teus pedidos. Cria conta ou inicia sessão para começar.</p>
                            <a class="btn btn-primary" href="<?php echo BASE_URL; ?>auth/login"><i class="fa-solid fa-right-to-bracket"></i> Entrar</a>
                            <a class="btn btn-primary" href="<?php echo BASE_URL; ?>auth/register" style="margin-left:0.5rem;">Criar conta</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="rb-section rb-blue" id="materiais">
        <div class="container">
            <div class="rb-section-title">
                <h2>Acesso Rápido</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <a class="rb-card rb-card-link" href="<?php echo BASE_URL; ?>material/index">
                        <i class="fas fa-boxes-stacked accent-icon"></i>
                        <h3>Materiais</h3>
                        <p>Ver e gerir equipamentos do clube.</p>
                    </a>
                </div>

                <?php if (!$isAluno): ?>
                    <div class="col-md-6 col-lg-3">
                        <a class="rb-card rb-card-link" href="<?php echo BASE_URL; ?>sala/index">
                            <i class="fas fa-door-closed accent-icon"></i>
                            <h3>Salas</h3>
                            <p>Gerir salas e reservas do espaço.</p>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="col-md-6 col-lg-3">
                    <a class="rb-card rb-card-link" href="<?php echo BASE_URL; ?>calendario/geral">
                        <i class="fas fa-calendar-days accent-icon"></i>
                        <h3>Calendário</h3>
                        <p>Salas e materiais no mesmo painel.</p>
                    </a>
                </div>

                <?php if ($isResponsavel || $isAdmin): ?>
                    <div class="col-md-6 col-lg-3">
                        <a class="rb-card rb-card-link" href="<?php echo BASE_URL; ?>dashboard/administracao">
                            <i class="fas fa-screwdriver-wrench accent-icon"></i>
                            <h3>Admin</h3>
                            <p>Painel técnico completo.</p>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

     <section class="about-section bg-black" id="sobre">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 pe-md-5 mb-5 mb-md-0">
                    <h2 class="section-title">SOBRE<br>NÓS</h2>
                    <p class="about-text fw-bold">O Clube de Robótica é um espaço de inovação, educação e tecnologia.</p>
                    <p class="about-text">Fundado com o objetivo de promover o interesse pela robótica e tecnologia, o nosso clube oferece aos alunos a oportunidade de aprender, criar e desenvolver projetos robóticos inovadores. Participamos em competições nacionais e internacionais, sempre com o objetivo de aprender e crescer.</p>
                </div>
                
                <div class="col-md-6">
                    <div class="about-images-container">
                        <img src="<?php echo BASE_URL; ?>uploads/fundo2_site.jpg" alt="Aula de Robótica" class="about-img-1 bw-image">
                        <img src="<?php echo BASE_URL; ?>uploads/mascote2.png" alt="Robô Mascote" class="about-img-2">
                    </div>
                </div>
            </div>
        </div>
    </section>

     <section class="rb-section rb-blue" id="professores">
        <div class="container">
            <div class="rb-section-title">
                <h2>A NOSSA Equipa</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <?php if (empty($professores)): ?>
                    <div class="col-12 text-center text-light">
                        <p>Nenhum professor responsável registado.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($professores as $professor): ?>
                        <?php $profAvatar = $utilizadorModel->getAvatarUrl($professor); ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="rb-card rb-prof-card">
                                <img src="<?php echo $profAvatar; ?>" alt="<?php echo htmlspecialchars($professor['nome']); ?>" class="rb-prof-avatar">
                                <h3><?php echo htmlspecialchars($professor['nome']); ?></h3>
                                <div class="rb-prof-actions">
                                    <?php if (!empty($professor['email'])): ?>
                                        <a class="btn btn-outline-light btn-sm rb-icon-btn" href="mailto:<?php echo htmlspecialchars($professor['email']); ?>" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($professor['linkedin'])): ?>
                                        <a class="btn btn-primary btn-sm rb-icon-btn" href="<?php echo htmlspecialchars($professor['linkedin']); ?>" target="_blank" rel="noopener" title="LinkedIn">
                                            <i class="fab fa-linkedin-in"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="rb-section rb-black" id="historia">
        <div class="container">
            <div class="rb-section-title">
                <p class="rb-eyebrow">Linha do tempo</p>
                <h2>História do clube</h2>
                <p class="rb-muted">A nossa evolução ano após ano.</p>
            </div>
            <div class="rb-history">
                <div class="rb-history-robot">
                    <img src="<?php echo $robotHistoryImage; ?>" alt="Robo do Clube" class="rb-robot rb-robot-history">
                </div>
                
                <div class="rb-timeline">
                    <div class="rb-timeline-item" data-growth="1.1">
                        <div class="rb-date">2026</div>
                        <div class="rb-content">Crescimento contínuo com eventos e missão social.</div>
                    </div>
                    <div class="rb-timeline-item" data-growth="1.0">
                        <div class="rb-date">2024</div>
                        <div class="rb-content">Projetos colaborativos com empresas locais.</div>
                    </div>
                    <div class="rb-timeline-item" data-growth="0.9">
                        <div class="rb-date">2022</div>
                        <div class="rb-content">Laboratório expandido e novos kits de robótica.</div>
                    </div>
                    <div class="rb-timeline-item" data-growth="0.8">
                        <div class="rb-date">2020</div>
                        <div class="rb-content">Primeiras competições e oficina de sensores.</div>
                    </div>
                    <div class="rb-timeline-item" data-growth="0.7">
                        <div class="rb-date">2019</div>
                        <div class="rb-content">Fundação do clube e primeiros protótipos.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rb-section rb-blue" id="eventos">
        <div class="container">
            <div class="rb-section-title">
                <p class="rb-eyebrow">Portfolio</p>
                <h2>Eventos e projetos</h2>
            </div>
            <div class="row g-4" id="eventosContainer">
                <?php if (empty($eventos)): ?>
                    <div class="col-12 text-center text-light">
                        <p>Nenhum evento encontrado.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $contador = 0;
                    foreach ($eventos as $evento): 
                        $contador++;
                        $hiddenClass = $contador > 3 ? 'd-none d-hidden-event' : '';
                        
                        // Configuração das Imagens vindas da BD (blob ou url de fallback)
                        $imgUrl = !empty($evento['imagem_url']) ? htmlspecialchars($evento['imagem_url']) : (!empty($evento['imagem_src']) ? htmlspecialchars($evento['imagem_src']) : BASE_URL . 'uploads/fundo_site.jpg');
                    ?>
                        <div class="col-md-6 col-lg-4 <?php echo $hiddenClass; ?>">
                            <div class="rb-card rb-event-card h-100 d-flex flex-column">
                                <img src="<?php echo $imgUrl; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>">
                                
                                <div class="rb-event-info mt-auto">
                                    <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                    <?php if (!empty($evento['id'])): ?>
                                        <a class="btn-plus-event" title="Ver mais info" style="cursor: pointer;"
                                           data-titulo="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                           data-descricao="<?php echo htmlspecialchars($evento['descricao'] ?? 'Sem descrição disponível para este evento.'); ?>"
                                           data-imagem="<?php echo $imgUrl; ?>"
                                           data-url="<?php echo htmlspecialchars($evento['url'] ?? ''); ?>"
                                           onclick="abrirModalEvento(this)">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (count($eventos) > 3): ?>
                <div class="row mt-5" id="btnVerTodosContainer">
                    <div class="col-12 text-center">
                        <button class="btn btn-ver-todos px-4 py-2 text-uppercase fw-bold" onclick="mostrarTodosEventos()">Ver todos os Eventos</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="rb-section rb-black" id="localizacao">
        <div class="container">
            <div class="rb-section-title">
                <h2>Localização</h2>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="location-item">
                        <h5>ES Dr. Francisco Fernandes Lopes</h5>
                        <p>Av. Dr. Francisco Sá Carneiro<br>8700-853 Olhão</p>
                    </div>
                    
                    <div class="location-item">
                        <h5>Horário de Funcionamento</h5>
                        <p>Quarta-Feira: 14h - 18h</p>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3183.1819385012574!2d-7.842778000000001!3d37.026402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0552b7ba5e1d51%3A0xc3c9f22ec10de950!2sEscola%20Secund%C3%A1ria%20Dr.%20Francisco%20Fernandes%20Lopes!5e0!3m2!1spt-PT!2spt!4v1700000000000!5m2!1spt-PT!2spt" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

<div id="eventoModal" class="rb-modal-overlay" onclick="fecharModalFora(event)">
    <div class="rb-modal-content">
        <button class="rb-modal-close" onclick="fecharModal()" title="Fechar">&times;</button>
        <img id="modalImagem" src="" alt="Imagem do Evento">
        <h3 id="modalTitulo">Título do Evento</h3>
        <p id="modalDescricao">Descrição vai aqui...</p>
        <a id="modalUrl" href="#" target="_blank" class="rb-modal-btn">Saber Mais / Aceder</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Função para mostrar os eventos ocultos
    function mostrarTodosEventos() {
        const hiddenEvents = document.querySelectorAll('.d-hidden-event');
        hiddenEvents.forEach(function(eventCard) {
            eventCard.classList.remove('d-none');
            eventCard.classList.remove('d-hidden-event');
        });
        document.getElementById('btnVerTodosContainer').style.display = 'none';
    }

    // Funções para gerir o Balão Interativo (Modal)
    function abrirModalEvento(btn) {
        const titulo = btn.getAttribute('data-titulo');
        const descricao = btn.getAttribute('data-descricao');
        const imagem = btn.getAttribute('data-imagem');
        const url = btn.getAttribute('data-url');

        document.getElementById('modalTitulo').innerText = titulo;
        document.getElementById('modalDescricao').innerText = descricao;
        document.getElementById('modalImagem').src = imagem;
        
        const btnUrl = document.getElementById('modalUrl');
        if (url && url.trim() !== '') {
            btnUrl.href = url;
            btnUrl.style.display = 'inline-block';
        } else {
            btnUrl.style.display = 'none';
        }

        document.getElementById('eventoModal').classList.add('active');
        document.body.style.overflow = 'hidden'; // Tranca o scroll principal
    }

    function fecharModal() {
        document.getElementById('eventoModal').classList.remove('active');
        document.body.style.overflow = 'auto'; // Destranca o scroll principal
    }

    function fecharModalFora(event) {
        if (event.target === document.getElementById('eventoModal')) {
            fecharModal();
        }
    }
</script>