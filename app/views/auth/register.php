<?php
// Página de REGISTO (criar conta).
// Validação dupla: o browser valida com HTML (required, type, pattern) e o
// PHP volta a validar. Se der erro, NÃO se apagam os dados já escritos ($old).
require_once __DIR__ . '/../../config/config.php';
$old = $old ?? [];  // dados que o utilizador já tinha escrito (vêm do controller em caso de erro)
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta · RoboticaXL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        /* Página centrada com um cartão largo para os vários campos. */
        .reg { min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 2.5rem 1rem; }
        .reg__card { width: 100%; max-width: 640px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow); padding: 2.25rem; }
        .reg__head { text-align: center; margin-bottom: 1.75rem; }
        .reg__head .ic { width: 52px; height: 52px; border-radius: 14px; background: var(--blue-soft); color: var(--blue); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 0.9rem; }
        .reg__head h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
        .reg__head p { color: var(--text-muted); margin: 0.25rem 0 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.1rem; }
        .form-grid .full { grid-column: 1 / -1; }
        .sec-label { grid-column: 1 / -1; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--blue); margin: 0.5rem 0 0.75rem; }
        .upload { display: flex; align-items: center; gap: 0.6rem; border: 1px dashed var(--border-strong); border-radius: var(--r-sm); padding: 0.65rem 0.85rem; color: var(--text-muted); cursor: pointer; transition: var(--t); }
        .upload:hover { border-color: var(--blue); color: var(--blue); }
        .reg__foot { text-align: center; margin-top: 1.25rem; color: var(--text-muted); font-size: 0.9rem; }
        .reg__foot a { color: var(--blue); font-weight: 600; }
        /* Erro visual: campo fica vermelho quando é inválido (validação HTML). */
        .field input:user-invalid, .field input.erro { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,0.15) !important; }
        @media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/topbar_guest.php'; ?>
    <div class="reg">
        <div class="reg__card fade-in">
            <div class="reg__head">
                <div class="ic"><i class="fas fa-user-plus"></i></div>
                <h1>Criar conta</h1>
                <p>Junta-te ao Clube de Robótica.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
            <?php endif; ?>

            <form method="post" action="<?php echo BASE_URL; ?>auth/register" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="field full">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: João Silva" required minlength="3"
                               pattern="[A-Za-zÀ-ÿ' ]{3,}" title="Só letras e espaços (mínimo 3 caracteres)."
                               value="<?php echo htmlspecialchars($old['nome'] ?? ''); ?>">
                    </div>
                    <div class="field full">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="nome@escola.pt" required
                               value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" placeholder="912345678" required
                               pattern="(\+351)?(2[0-9]{8}|9[1236][0-9]{7})" title="Número de telefone português válido (ex: 912345678)."
                               value="<?php echo htmlspecialchars($old['telefone'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="nasc">Data de nascimento</label>
                        <input type="date" id="nasc" name="data_nascimento" required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($old['data_nascimento'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="turma">Turma / Ano</label>
                        <input type="text" id="turma" name="turma" placeholder="Ex: 12ºA"
                               value="<?php echo htmlspecialchars($old['turma'] ?? ''); ?>">
                    </div>
                    <div class="field">
                        <label for="linkedin">LinkedIn (opcional)</label>
                        <input type="url" id="linkedin" name="linkedin" placeholder="https://..."
                               value="<?php echo htmlspecialchars($old['linkedin'] ?? ''); ?>">
                    </div>
                    <div class="field full">
                        <label>Foto de perfil (opcional)</label>
                        <label class="upload" for="foto">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span id="fotoLabel">Escolher imagem (JPG, PNG)</span>
                        </label>
                        <input type="file" id="foto" name="foto_perfil" accept="image/*" style="display:none"
                               onchange="document.getElementById('fotoLabel').textContent = this.files[0] ? this.files[0].name : 'Escolher imagem (JPG, PNG)'">
                    </div>

                    <div class="sec-label">Segurança</div>
                    <div class="field">
                        <label for="pw">Palavra-passe</label>
                        <input type="password" id="pw" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="field">
                        <label for="pw2">Confirmar palavra-passe</label>
                        <input type="password" id="pw2" name="password_confirm" placeholder="Repete a palavra-passe" required minlength="6"
                               oninput="this.setCustomValidity(this.value !== document.getElementById('pw').value ? 'As palavras-passe não coincidem.' : '')">
                    </div>

                    <div class="full">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Criar conta</button>
                    </div>
                </div>
            </form>

            <div class="reg__foot">
                Já tens conta? <a href="<?php echo BASE_URL; ?>auth/login">Entrar</a>
            </div>
        </div>
    </div>
<script src="<?php echo BASE_URL; ?>js/rb-loading.js"></script>
</body>
</html>
