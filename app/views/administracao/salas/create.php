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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Adicionar Sala</h4>
            <a href="<?php echo BASE_URL; ?>admin/salas" class="btn-clean-outline">Voltar à lista</a>
        </div>

        <div class="clean-card p-4 p-md-5">
            <?php if (!empty($error)): ?>
                <div style="background-color: #fef2f2; color: #ef4444; padding: 12px 16px; border-radius: 10px; margin-bottom: 24px; border: 1px solid #fee2e2;">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="createForm" method="post" action="<?php echo BASE_URL; ?>admin/salaStore">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Bloco (Máx: G) *</label>
                        <input type="text" name="bloco" class="form-control-clean" required pattern="[A-Ga-g]" maxlength="1" oninput="this.value = this.value.toUpperCase()" placeholder="Exemplo: A, B, C...">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Número (2 Dígitos) *</label>
                        <input type="text" name="numero" class="form-control-clean" required pattern="\d{2}" maxlength="2" placeholder="Exemplo: 01, 15...">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Andar (Máx: 2) *</label>
                        <input type="number" name="andar" class="form-control-clean" required min="0" max="2" placeholder="Exemplo: 1">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Capacidade (Máx: 200) *</label>
                        <input type="number" name="capacidade" class="form-control-clean" required min="1" max="200" placeholder="Lotação máxima">
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Estado *</label>
                    <select name="estado" class="form-control-clean" required>
                        <option value="DISPONIVEL">Disponível</option>
                        <option value="MANUTENCAO">Em Manutenção</option>
                        <option value="DANIFICADA">Danificada</option>
                        <option value="OCUPADA">Ocupada</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Descrição (Opcional)</label>
                    <textarea name="descricao" class="form-control-clean" rows="3" placeholder="Informações adicionais sobre a sala..."></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-3 border-top pt-4" style="border-color: #e2e8f0 !important;">
                    <button type="submit" class="btn-clean-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Guardar Sala
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