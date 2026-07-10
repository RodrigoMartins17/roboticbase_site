<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .form-control-clean { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; width: 100%; outline: none; }
    .form-control-clean:focus { background-color: #ffffff; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; text-decoration: none; display: inline-block; }
    .btn-clean-outline:hover { background-color: #f1f5f9; color: #0f172a; }
</style>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Editar Sala</h4>
            <a href="<?php echo BASE_URL; ?>admin/salas" class="btn-clean-outline">Voltar à lista</a>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <?php if (!empty($error)): ?>
                <div style="background-color: #fef2f2; color: #ef4444; padding: 12px 16px; border-radius: 10px; margin-bottom: 24px; border: 1px solid #fee2e2;">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="editForm" method="post" action="<?php echo BASE_URL; ?>admin/salaUpdate/<?php echo (int)$sala['id']; ?>" enctype="multipart/form-data">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Bloco (Máx: G) *</label>
                        <input type="text" name="bloco" class="form-control-clean" required pattern="[A-Ga-g]" maxlength="1" oninput="this.value = this.value.toUpperCase()" value="<?php echo htmlspecialchars($sala['bloco'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Número (2 Dígitos) *</label>
                        <input type="text" name="numero" class="form-control-clean" required pattern="\d{2}" maxlength="2" value="<?php echo htmlspecialchars($sala['numero'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Andar (Máx: 2) *</label>
                        <input type="number" name="andar" class="form-control-clean" required min="0" max="2" value="<?php echo (int)($sala['andar'] ?? 0); ?>">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Capacidade (Máx: 200) *</label>
                        <input type="number" name="capacidade" class="form-control-clean" required min="1" max="200" value="<?php echo (int)($sala['capacidade'] ?? 0); ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Estado *</label>
                    <select name="estado" class="form-control-clean" required>
                        <?php $sEstado = $sala['estado'] ?? 'DISPONIVEL'; ?>
                        <option value="DISPONIVEL" <?php echo $sEstado === 'DISPONIVEL' ? 'selected' : ''; ?>>Disponível</option>
                        <option value="MANUTENCAO" <?php echo $sEstado === 'MANUTENCAO' ? 'selected' : ''; ?>>Em Manutenção</option>
                        <option value="DANIFICADA" <?php echo $sEstado === 'DANIFICADA' ? 'selected' : ''; ?>>Danificada</option>
                        <option value="OCUPADA" <?php echo $sEstado === 'OCUPADA' ? 'selected' : ''; ?>>Ocupada</option>
                        <option value="INDISPONIVEL" <?php echo $sEstado === 'INDISPONIVEL' ? 'selected' : ''; ?>>Indisponível (não requisitável)</option>
                    </select>
                </div>


                <div class="mb-4">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Fotografia da Sala (Opcional)</label>
                    <!-- A foto é comprimida no servidor e guardada na base de dados. -->
                    <input type="file" name="imagem" accept="image/*" class="form-control-clean">
                </div>
                <div class="mb-5">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Descrição</label>
                    <textarea name="descricao" class="form-control-clean" rows="3"><?php echo htmlspecialchars($sala['descricao'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-3 border-top pt-4" style="border-color: #e2e8f0 !important;">
                    <button type="submit" class="btn-clean-primary" id="submitEditBtn">
                        <i class="fas fa-save me-2"></i>Atualizar Sala
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('editForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitEditBtn');
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>A atualizar...';
        btn.style.opacity = '0.8';
        btn.style.pointerEvents = 'none';
    });
</script>