<?php
// PERFIL — mostra e deixa editar os dados do utilizador.
// FRAGMENTO limpo (a moldura — navbar e rodapé — vem do header/footer, igual ao dashboard).
// Recebe $user do AuthController. Uso o $avatarSrc que o header já preparou.
$u = $user ?? [];
$fotoPerfil = $avatarSrc ?? (BASE_URL . 'uploads/logosite.png');
?>

<div class="container">
    <div class="page-header">
        <span class="eyebrow"><i class="fas fa-user"></i> Conta</span>
        <h1 class="page-title">O meu perfil</h1>
        <p class="page-sub">Atualiza os teus dados pessoais.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>
    <?php if (!empty($flash) && ($flash['type'] ?? '') === 'success'): ?>
        <div class="alert alert-ok"><i class="fas fa-circle-check"></i><span><?php echo htmlspecialchars($flash['message']); ?></span></div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns: 300px 1fr; gap:1.5rem; align-items:start;">
        <!-- Cartão da foto -->
        <div class="card">
            <div class="card__body text-center">
                <img src="<?php echo $fotoPerfil; ?>" alt="Foto de perfil"
                     style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid var(--blue-soft-2);margin:0 auto 1rem;">
                <h3 style="margin:0;font-size:1.15rem;font-weight:700;color:#fff;"><?php echo htmlspecialchars($u['nome'] ?? 'Utilizador'); ?></h3>
                <p class="muted" style="margin:0.25rem 0 0;font-size:0.9rem;"><?php echo htmlspecialchars(ucfirst(strtolower($u['tipo'] ?? 'Aluno'))); ?></p>
            </div>
        </div>

        <!-- Cartão do formulário -->
        <div class="card">
            <div class="card__body">
                <form method="post" action="<?php echo BASE_URL; ?>auth/updateProfile" enctype="multipart/form-data">
                    <div class="grid" style="grid-template-columns:1fr 1fr;gap:0 1rem;">
                        <div class="field" style="grid-column:1 / -1;">
                            <label for="nome">Nome completo</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($u['nome'] ?? ''); ?>" required>
                        </div>
                        <div class="field" style="grid-column:1 / -1;">
                            <label for="email">Email (não editável)</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>" disabled style="opacity:0.6;">
                        </div>
                        <div class="field">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($u['telefone'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="nasc">Data de nascimento</label>
                            <input type="date" id="nasc" name="data_nascimento" value="<?php echo htmlspecialchars($u['data_nascimento'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="turma">Turma / Ano</label>
                            <input type="text" id="turma" name="turma" value="<?php echo htmlspecialchars($u['turma'] ?? ''); ?>" placeholder="Ex: 12ºA">
                        </div>
                        <div class="field">
                            <label for="linkedin">LinkedIn</label>
                            <input type="url" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($u['linkedin'] ?? ''); ?>" placeholder="https://...">
                        </div>
                        <div class="field" style="grid-column:1 / -1;">
                            <label>Mudar foto de perfil</label>
                            <label class="btn btn-outline" for="foto" style="cursor:pointer;">
                                <i class="fas fa-cloud-arrow-up"></i> <span id="fotoLabel">Escolher imagem (JPG, PNG)</span>
                            </label>
                            <input type="file" id="foto" name="foto_perfil" accept="image/*" style="display:none"
                                   onchange="document.getElementById('fotoLabel').textContent = this.files[0] ? this.files[0].name : 'Escolher imagem (JPG, PNG)'">
                        </div>
                    </div>
                    <div style="margin-top:1rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Guardar alterações</button>
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>dashboard/index">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Segurança: alteração de palavra-passe por email -->
    <div class="card" style="margin-top:1.5rem;">
        <div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:0.9rem;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(59,130,246,0.15);color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <div style="font-weight:700;color:#fff;">Palavra-passe</div>
                    <p class="muted" style="margin:0.15rem 0 0;font-size:0.85rem;">Por segurança, a alteração é feita por email: enviamos-te um link para definires uma nova.</p>
                </div>
            </div>
            <form method="post" action="<?php echo BASE_URL; ?>auth/pedirNovaPassword">
                <button type="submit" class="btn btn-outline"><i class="fas fa-paper-plane"></i> Alterar por email</button>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 720px) {
        .container .grid[style*="300px"] { grid-template-columns: 1fr !important; }
    }
</style>
