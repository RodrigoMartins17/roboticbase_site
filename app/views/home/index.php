<?php
// PÁGINA INICIAL (para VISITANTES, ainda sem conta).
// Tem o mesmo aspeto escuro do dashboard, mas em vez de perfil/sair mostra
// os botões ENTRAR e REGISTAR, para "obrigar" a pessoa a criar conta antes
// de aceder às funcionalidades do clube.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/PortfolioEvento.php';

// Vou buscar alguns eventos para mostrar na montra.
$portfolioModel = new PortfolioEvento();
$eventos = $eventos ?? $portfolioModel->todos();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clube de Robótica · RoboticaXL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --blue: #0066ff; }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #050505; color: #fff; margin: 0; overflow-x: hidden; }
        a { text-decoration: none; }

        /* Navbar de visitante */
        .h-nav { position: sticky; top: 0; z-index: 100; background: rgba(5,5,5,0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 0.8rem 0; }
        .h-nav .navbar-brand img { height: 46px; }
        .h-nav .nav-link { color: #fff !important; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; padding: 0.5rem 1rem !important; }
        .h-nav .nav-link:hover { color: var(--blue) !important; }
        .btn-entrar { color: #fff; border: 2px solid rgba(255,255,255,0.6); padding: 8px 22px; border-radius: 6px; font-weight: 700; text-transform: uppercase; font-size: 0.82rem; transition: all 0.25s; }
        .btn-entrar:hover { border-color: #fff; background: rgba(255,255,255,0.08); color: #fff; }
        .btn-registar { background: var(--blue); color: #fff; padding: 10px 24px; border-radius: 6px; font-weight: 700; text-transform: uppercase; font-size: 0.82rem; transition: all 0.25s; }
        .btn-registar:hover { background: #0052cc; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,102,255,0.35); color: #fff; }

        /* Herói */
        .h-hero { position: relative; min-height: 92vh; display: flex; align-items: center; text-align: center;
            background-image: linear-gradient(rgba(0,0,0,0.72), rgba(5,5,5,0.94)), url('<?php echo BASE_URL; ?>uploads/fundo_site.jpg');
            background-size: cover; background-position: center; }
        .h-hero::after { content: ''; position: absolute; inset: 0; background: radial-gradient(800px 460px at 50% 40%, rgba(0,102,255,0.18), transparent 60%); pointer-events: none; }
        .h-hero-inner { position: relative; z-index: 1; width: 100%; }
        .h-eyebrow { color: var(--blue); font-weight: 700; letter-spacing: 6px; text-transform: uppercase; font-size: 1rem; margin-bottom: 1rem; }
        .h-hero h1 { font-size: clamp(2.6rem, 8vw, 5.5rem); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; line-height: 1; margin: 0 0 1.25rem; }
        .h-hero h1 span { color: var(--blue); }
        .h-hero p { color: #cfd3da; font-size: 1.15rem; max-width: 620px; margin: 0 auto 2.25rem; }
        .h-cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .h-cta .big-reg { background: var(--blue); color: #fff; padding: 14px 34px; border-radius: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.25s; }
        .h-cta .big-reg:hover { background: #0052cc; transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,102,255,0.45); color: #fff; }
        .h-cta .big-login { border: 2px solid rgba(255,255,255,0.7); color: #fff; padding: 14px 34px; border-radius: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.25s; }
        .h-cta .big-login:hover { background: #fff; color: #050505; }
        .h-note { margin-top: 1.5rem; color: #8a90a0; font-size: 0.9rem; }

        /* Secções */
        .h-section { padding: 5.5rem 0; }
        .h-section h2 { font-size: clamp(2rem,5vw,3rem); font-weight: 800; text-transform: uppercase; margin-bottom: 2.5rem; }
        .h-eyebrow-sm { color: var(--blue); font-weight: 700; letter-spacing: 3px; text-transform: uppercase; font-size: 0.85rem; }
        .h-feature { background: #111318; border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 2rem; height: 100%; transition: all 0.25s; }
        .h-feature:hover { transform: translateY(-5px); border-color: rgba(0,102,255,0.4); }
        .h-feature i { font-size: 2rem; color: var(--blue); margin-bottom: 1rem; }
        .h-feature h3 { font-size: 1.2rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.5rem; }
        .h-feature p { color: #aab; margin: 0; font-size: 0.95rem; }

        .h-event { border-radius: 14px; overflow: hidden; background: #111318; border: 1px solid rgba(255,255,255,0.07); height: 100%; }
        .h-event img { width: 100%; height: 200px; object-fit: cover; }
        .h-event .body { padding: 1.25rem; }
        .h-event h3 { font-size: 1.05rem; font-weight: 800; margin: 0 0 0.75rem; }
        .h-locked { display: inline-flex; align-items: center; gap: 6px; color: var(--blue); font-weight: 700; font-size: 0.85rem; }

        .h-banner { background: var(--blue); text-align: center; padding: 4rem 0; }
        .h-banner h2 { color: #fff; }
        .h-banner .big-reg { background: #fff; color: var(--blue); }
        .h-banner .big-reg:hover { background: #050505; color: #fff; }

        .h-footer { padding: 3rem 0 2rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.08); }
        .h-footer img { height: 64px; margin-bottom: 1rem; }
        .h-footer .socials a { color: #fff; font-size: 1.3rem; margin: 0 10px; transition: all 0.25s; }
        .h-footer .socials a:hover { color: var(--blue); transform: translateY(-3px); display: inline-block; }
        .h-footer .copy { color: #888; font-size: 0.85rem; margin-top: 1.25rem; }
    </style>
</head>
<body>

    <!-- Topo de visitante partilhado (menu de 3 traços) -->
    <?php include __DIR__ . '/../layouts/topbar_guest.php'; ?>

    <!-- HERÓI -->
    <header class="h-hero">
        <div class="container h-hero-inner">
            <p class="h-eyebrow">Bem-vindo ao</p>
            <h1>Clube de Robótica<br><span>RoboticaXL</span></h1>
            <p>Requisita material, reserva salas e acompanha os teus pedidos. Cria a tua conta para começares a fazer parte do clube.</p>
            <div class="h-cta">
                <a class="big-reg" href="<?php echo BASE_URL; ?>auth/register"><i class="fas fa-user-plus me-2"></i>Criar conta</a>
                <a class="big-login" href="<?php echo BASE_URL; ?>auth/login">Já tenho conta</a>
            </div>
            <p class="h-note"><i class="fas fa-lock me-1"></i> É preciso ter conta para aceder aos materiais, salas e requisições.</p>
        </div>
    </header>

    <!-- SOBRE -->
    <section class="h-section" id="sobre">
        <div class="container">
            <p class="h-eyebrow-sm">O que fazemos</p>
            <h2>Sobre o clube</h2>
            <div class="row g-4">
                <div class="col-md-4"><div class="h-feature"><i class="fas fa-microchip"></i><h3>Materiais</h3><p>Arduinos, sensores, motores e muito mais para os teus projetos.</p></div></div>
                <div class="col-md-4"><div class="h-feature"><i class="fas fa-door-open"></i><h3>Salas</h3><p>Reserva espaços do clube para trabalhar nas tuas atividades.</p></div></div>
                <div class="col-md-4"><div class="h-feature"><i class="fas fa-clipboard-check"></i><h3>Acompanhamento</h3><p>Segue o estado de cada pedido, do registo à devolução.</p></div></div>
            </div>
        </div>
    </section>

    <!-- EVENTOS (montra; para ver mais é preciso conta) -->
    <?php if (!empty($eventos)): ?>
    <section class="h-section" id="eventos" style="background:#0a0c10;">
        <div class="container">
            <p class="h-eyebrow-sm">Portfólio</p>
            <h2>Eventos e projetos</h2>
            <div class="row g-4">
                <?php foreach (array_slice($eventos, 0, 3) as $ev):
                    $img = !empty($ev['imagem_url']) ? htmlspecialchars($ev['imagem_url']) : (!empty($ev['imagem_src']) ? htmlspecialchars($ev['imagem_src']) : BASE_URL . 'uploads/fundo_site.jpg');
                ?>
                    <div class="col-md-4">
                        <div class="h-event">
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($ev['titulo'] ?? 'Evento'); ?>">
                            <div class="body">
                                <h3><?php echo htmlspecialchars($ev['titulo'] ?? 'Evento'); ?></h3>
                                <a class="h-locked" href="<?php echo BASE_URL; ?>auth/register"><i class="fas fa-lock"></i> Cria conta para ver mais</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FAIXA de chamada à ação -->
    <section class="h-banner">
        <div class="container">
            <h2>Pronto para começar?</h2>
            <p style="color:rgba(255,255,255,0.85); max-width:560px; margin:0 auto 1.75rem;">Cria a tua conta em segundos e passa a ter acesso a todo o material do clube.</p>
            <a class="big-reg" href="<?php echo BASE_URL; ?>auth/register" style="padding:14px 34px; border-radius:8px; font-weight:800; text-transform:uppercase;">Criar conta agora</a>
        </div>
    </section>

    <!-- LOCALIZAÇÃO -->
    <section class="h-section" id="local">
        <div class="container">
            <p class="h-eyebrow-sm">Onde estamos</p>
            <h2>Localização</h2>
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <h5 style="color:var(--blue);font-weight:800;">ES Dr. Francisco Fernandes Lopes</h5>
                    <p style="color:#bbb;">Av. Dr. Francisco Sá Carneiro<br>8700-853 Olhão</p>
                    <h5 style="color:var(--blue);font-weight:800;margin-top:1.25rem;">Horário</h5>
                    <p style="color:#bbb;">Quarta-feira: 14h – 18h</p>
                </div>
                <div class="col-lg-7">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3183.1819385012574!2d-7.842778000000001!3d37.026402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0552b7ba5e1d51%3A0xc3c9f22ec10de950!2sEscola%20Secund%C3%A1ria%20Dr.%20Francisco%20Fernandes%20Lopes!5e0!3m2!1spt-PT!2spt!4v1700000000000!5m2!1spt-PT!2spt"
                        style="width:100%;height:340px;border:1px solid rgba(0,102,255,0.4);border-radius:12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>

    <footer class="h-footer">
        <div class="container">
            <img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="RoboticaXL">
            <div class="socials">
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="https://www.facebook.com/roboticaxl/"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="mailto:roboticaxl.aeffl@gmail.com"><i class="fas fa-envelope"></i></a>
            </div>
            <div class="copy">&copy; <?php echo date('Y'); ?> Clube de Robótica · RoboticaXL — Escola Secundária Dr. Francisco Fernandes Lopes</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
