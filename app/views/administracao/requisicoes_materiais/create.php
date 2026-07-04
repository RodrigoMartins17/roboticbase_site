<?php
require_once __DIR__ . '/../../../config/config.php';
?>

<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); padding: 30px; margin-bottom: 24px;}
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; width: 100%; cursor: pointer;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); }
    .btn-clean-secondary { background-color: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; text-decoration: none;}
    .btn-clean-secondary:hover { background-color: #e2e8f0; color: #1e293b; }
    
    .clean-input { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px; font-size: 0.95rem; color: #334155; transition: border-color 0.2s; width: 100%;}
    .clean-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .clean-label { font-weight: 600; color: #475569; margin-bottom: 8px; font-size: 0.9rem; display: block;}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;">Nova Requisição de Material</h4>
    <a href="<?php echo BASE_URL; ?>admin/requisicoesMateriais" class="btn-clean-secondary">Voltar</a>
</div>

<div class="clean-card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo BASE_URL; ?>admin/reqMaterialStore" id="formRequisicao">
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label">Utilizador <span class="text-danger">*</span></label>
                <select name="id_utilizador" class="form-select clean-input" required>
                    <option value="">— Selecionar —</option>
                    <?php foreach ($utilizadores ?? [] as $u): ?>
                        <option value="<?php echo (int)$u['id']; ?>"><?php echo htmlspecialchars($u['nome'] ?? ''); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Exemplar <span class="text-danger">*</span></label>
                <select name="id_exemplar" id="id_exemplar" class="form-select clean-input" required>
                    <option value="">— Selecionar —</option>
                    <?php foreach ($exemplares ?? [] as $e): ?>
                        <option value="<?php echo (int)$e['id']; ?>"><?php echo htmlspecialchars($e['designacao'] ?? ''); ?> (<?php echo htmlspecialchars($e['num_referencia'] ?? ''); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="clean-label">Observação (Opcional)</label>
            <textarea name="observacao" class="form-control clean-input" rows="2" placeholder="Ex: Necessário para a reunião..."></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label">Entrega (Levantamento) <span class="text-danger">*</span></label>
                <input type="datetime-local" name="data_levantamento" id="data_levantamento" class="form-control clean-input" required min="<?php echo date('Y-m-d\TH:i'); ?>">
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Devolução Prevista <span class="text-danger">*</span></label>
                <input type="datetime-local" name="data_devolucao" id="data_devolucao" class="form-control clean-input" required min="<?php echo date('Y-m-d\TH:i'); ?>">
            </div>
        </div>

        <div id="availability-status" class="mb-4 small" style="display: none; padding: 12px; border-radius: 10px;"></div>

        <button type="submit" class="btn-clean-primary">Concluir Requisição <i class="fas fa-check ms-2"></i></button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputLev = document.getElementById('data_levantamento');
    const inputDev = document.getElementById('data_devolucao');
    const exemplarInput = document.getElementById('id_exemplar');
    const statusDiv = document.getElementById('availability-status');

    function checkAvailability() {
        if (!inputLev.value || !exemplarInput.value) return;
        const data = inputLev.value.split('T')[0];
        const hora = inputLev.value.split('T')[1];

        fetch(`<?php echo BASE_URL; ?>admin/apiHorariosOcupados?id_exemplar=${exemplarInput.value}&data=${data}`)
            .then(res => res.json())
            .then(dados => {
                const ocupados = dados.ocupados || [];
                const isOcupado = ocupados.some(h => h.split(':')[0] === hora.split(':')[0]);

                statusDiv.style.display = 'block';
                if (isOcupado) {
                    statusDiv.style.background = '#fef2f2';
                    statusDiv.style.color = '#dc2626';
                    statusDiv.style.border = '1px solid #fee2e2';
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Aviso: Existem outras requisições para este material neste horário.';
                } else {
                    statusDiv.style.background = '#f0fdf4';
                    statusDiv.style.color = '#16a34a';
                    statusDiv.style.border = '1px solid #dcfce7';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>O horário parece estar livre.';
                }
            });
    }

    inputLev.addEventListener('change', checkAvailability);
    exemplarInput.addEventListener('change', checkAvailability);
    
    inputLev.addEventListener('change', function() {
        if (inputLev.value) {
            inputDev.min = inputLev.value;
            if (inputDev.value && inputDev.value < inputLev.value) {
                inputDev.value = inputLev.value;
            }
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputLev = document.getElementById('data_levantamento');
    const inputDev = document.getElementById('data_devolucao');
    const exemplarInput = document.getElementById('id_exemplar');
    const statusDiv = document.getElementById('availability-status');

    function checkAvailability() {
        if (!inputLev.value || !exemplarInput.value) return;
        const data = inputLev.value.split('T')[0];
        const hora = inputLev.value.split('T')[1];

        fetch(`<?php echo BASE_URL; ?>admin/apiHorariosOcupados?id_exemplar=${exemplarInput.value}&data=${data}`)
            .then(res => res.json())
            .then(dados => {
                const ocupados = dados.ocupados || [];
                const isOcupado = ocupados.some(h => h.split(':')[0] === hora.split(':')[0]);

                statusDiv.style.display = 'block';
                if (isOcupado) {
                    statusDiv.style.background = 'rgba(239, 68, 68, 0.1)';
                    statusDiv.style.color = '#f87171';
                    statusDiv.style.border = '1px solid rgba(239, 68, 68, 0.2)';
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Aviso: Existem outras requisições para este material neste horário.';
                } else {
                    statusDiv.style.background = 'rgba(16, 185, 129, 0.1)';
                    statusDiv.style.color = '#34d399';
                    statusDiv.style.border = '1px solid rgba(16, 185, 129, 0.2)';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>O horário parece estar livre.';
                }
            });
    }

    inputLev.addEventListener('change', checkAvailability);
    exemplarInput.addEventListener('change', checkAvailability);
    
    inputLev.addEventListener('change', function() {
        if (inputLev.value) {
            inputDev.min = inputLev.value;
            if (inputDev.value && inputDev.value < inputLev.value) {
                inputDev.value = inputLev.value;
            }
        }
    });
});
</script>