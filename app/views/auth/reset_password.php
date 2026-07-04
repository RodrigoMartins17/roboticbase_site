<?php
// DEFINIR NOVA PALAVRA-PASSE — aberto a partir do link do email (traz um token).
require_once __DIR__ . '/../../config/config.php';
$token = $token ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nova palavra-passe · RoboticaXL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #050505; color: #fff; margin: 0; }
        .auth-wrap { min-height: calc(100vh - 84px); display: flex; align-items: center; justify-content: center; padding: 2.5rem 1rem; }
        .auth-card { width: 100%; max-width: 400px; background: #141a24; border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); padding: 2.25rem; }
        .auth-card .ic { width: 54px; height: 54px; border-radius: 14px; background: rgba(59,130,246,0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.1rem; }
        .auth-card h1 { text-align: center; font-size: 1.4rem; font-weight: 800; margin: 0 0 0.25rem; }
        .auth-card .sub { text-align: center; color: #9aa7bd; margin: 0 0 1.75rem; font-size: 0.92rem; }
        .field { margin-bottom: 1.1rem; }
        .field label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.4rem; }
        .field input { width: 100%; background: #1b2430; border: 1px solid #33415a; border-radius: 10px; color: #fff; padding: 0.7rem 0.9rem; font-family: inherit; font-size: 0.95rem; outline: none; transition: all 0.2s; }
        .field input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .field input:user-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
        .pw-wrap { position: relative; }
        .pw-wrap button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9aa7bd; cursor: pointer; }
        .btn-entrar { width: 100%; background: #3b82f6; color: #fff; border: none; padding: 0.85rem; border-radius: 10px; font-family: inherit; font-weight: 700; font-size: 0.95rem; cursor: pointer; margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn-entrar:hover { background: #2563eb; }
        .foot { text-align: center; margin-top: 1.5rem; color: #9aa7bd; font-size: 0.9rem; }
        .foot a { color: #3b82f6; font-weight: 600; }
        .alert { display: flex; align-items: center; gap: 0.6rem; padding: 0.8rem 1rem; border-radius: 10px; font-size: 0.9rem; font-weight: 500; margin-bottom: 1.2rem; }
        .alert-error { background: rgba(239,68,68,0.14); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/topbar_guest.php'; ?>

    <div class="auth-wrap">
        <div class="auth-card">
            <div class="ic"><i class="fas fa-lock"></i></div>
            <h1>Nova palavra-passe</h1>
            <p class="sub">Define a tua nova palavra-passe (mínimo 6 caracteres).</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
            <?php endif; ?>

            <form method="post" action="<?php echo BASE_URL; ?>auth/resetPassword">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="field">
                    <label for="pw">Nova palavra-passe</label>
                    <div class="pw-wrap">
                        <input type="password" id="pw" name="password" placeholder="••••••••" required minlength="6">
                        <button type="button" onclick="const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('fa-eye-slash')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="field">
                    <label for="pw2">Confirmar palavra-passe</label>
                    <input type="password" id="pw2" name="password_confirm" placeholder="••••••••" required minlength="6"
                           oninput="this.setCustomValidity(this.value !== document.getElementById('pw').value ? 'As palavras-passe não coincidem.' : '')">
                </div>
                <button type="submit" class="btn-entrar">Alterar palavra-passe <i class="fas fa-check"></i></button>
            </form>

            <div class="foot"><a href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-arrow-left"></i> Voltar ao login</a></div>
        </div>
    </div>
</body>
</html>
