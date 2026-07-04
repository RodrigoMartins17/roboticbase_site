<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .form-control-clean { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; width: 100%; outline: none; }
    .form-control-clean:focus { background-color: #ffffff; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 10px 24px; transition: all 0.3s; text-decoration: none; display: inline-block; }
    .btn-clean-outline:hover { background-color: #f1f5f9; color: #0f172a; }
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Novo Utilizador</h4>
            <a href="<?php echo BASE_URL; ?>admin/utilizadores" class="btn-clean-outline">Voltar à lista</a>
        </div>

        <div class="clean-card p-4 p-md-5">
            <?php if (!empty($error)): ?>
                <div style="background-color: #fef2f2; color: #ef4444; padding: 12px 16px; border-radius: 10px; margin-bottom: 24px; border: 1px solid #fee2e2;">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="createForm" method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>admin/utilizadorStore">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Nome *</label>
                        <input type="text" name="nome" class="form-control-clean" required placeholder="Nome completo">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Email *</label>
                        <input type="email" name="email" class="form-control-clean" required placeholder="email@exemplo.com">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Tipo de Utilizador</label>
                        <select name="tipo" class="form-control-clean">
                            <option value="ALUNO">Aluno</option>
                            <option value="PROFESSOR">Professor</option>
                            <option value="RESPONSAVEL">Responsável</option>
                            <option value="ADMIN">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Telefone</label>
                        <input type="text" name="telefone" class="form-control-clean" maxlength="20" placeholder="Opcional">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Turma</label>
                        <input type="text" name="turma" class="form-control-clean" maxlength="10" placeholder="Ex: 12º A">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="form-control-clean" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">URL do LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control-clean" placeholder="https://linkedin.com/in/...">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Foto de Perfil</label>
                        <input type="file" name="foto_perfil" class="form-control-clean" accept="image/*" style="padding: 9px 16px;">
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Palavra-passe</label>
                    <input type="password" name="palavra_passe" class="form-control-clean" placeholder="Deixe em branco para usar a palavra-passe padrão">
                </div>

                <div class="mb-5">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; background-color: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <input type="checkbox" name="responsavel" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-weight: 600; color: #334155;">É Professor Responsável</span>
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-3 border-top pt-4" style="border-color: #e2e8f0 !important;">
                    <button type="submit" class="btn-clean-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Registar Utilizador
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('createForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>A guardar...';
        btn.style.opacity = '0.8';
        btn.style.pointerEvents = 'none';
    });
</script>