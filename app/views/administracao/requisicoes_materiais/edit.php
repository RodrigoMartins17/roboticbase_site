<?php
require_once __DIR__ . '/../../../config/config.php';
$r = $requisicao ?? [];
$id = (int)($r['id'] ?? 0);
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
    <h4 class="mb-0 fw-bold" style="color: #1e293b;">Editar Requisição #<?php echo $id; ?></h4>
    <a href="<?php echo BASE_URL; ?>admin/reqMaterialView/<?php echo $id; ?>" class="btn-clean-secondary">Voltar</a>
</div>

<div class="clean-card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo BASE_URL; ?>admin/reqMaterialUpdate/<?php echo $id; ?>" id="formRequisicaoEdit">
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label">Utilizador</label>
                <select class="form-select clean-input" disabled>
                    <option><?php echo htmlspecialchars($r['utilizador_nome'] ?? ''); ?></option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Exemplar</label>
                <select class="form-select clean-input" disabled>
                    <option>
                        <?php echo htmlspecialchars($r['material_designacao'] ?? ''); ?> (<?php echo htmlspecialchars($r['num_referencia'] ?? ''); ?>)
                    </option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label">Estado do Pedido</label>
                <select name="estado_pedido" class="form-select clean-input">
                    <option value="PENDENTE" <?php echo ($r['estado_pedido'] ?? '') === 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="ACEITE" <?php echo ($r['estado_pedido'] ?? '') === 'ACEITE' ? 'selected' : ''; ?>>Aceite</option>
                    <option value="EM_USO" <?php echo ($r['estado_pedido'] ?? '') === 'EM_USO' ? 'selected' : ''; ?>>Em uso</option>
                    <option value="REJEITADO" <?php echo ($r['estado_pedido'] ?? '') === 'REJEITADO' ? 'selected' : ''; ?>>Rejeitado</option>
                    <option value="CONCLUIDO" <?php echo ($r['estado_pedido'] ?? '') === 'CONCLUIDO' ? 'selected' : ''; ?>>Devolvido</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Estado da Devolução</label>
                <select name="estado_devolucao" class="form-select clean-input">
                    <option value="OK" <?php echo ($r['estado_devolucao'] ?? '') === 'OK' ? 'selected' : ''; ?>>OK (Em bom estado)</option>
                    <option value="DANIFICADO" <?php echo ($r['estado_devolucao'] ?? '') === 'DANIFICADO' ? 'selected' : ''; ?>>DANIFICADO</option>
                    <option value="PERDIDO" <?php echo ($r['estado_devolucao'] ?? '') === 'PERDIDO' ? 'selected' : ''; ?>>PERDIDO</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Estado de Entrega</label>
                <select name="estado_entrega" class="form-select clean-input">
                    <option value="0" <?php echo (int)($r['estado_entrega'] ?? 0) === 0 ? 'selected' : ''; ?>>Pendente (Não levantado)</option>
                    <option value="1" <?php echo (int)($r['estado_entrega'] ?? 0) === 1 ? 'selected' : ''; ?>>Levantado (Entregue)</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="clean-label">Observação</label>
            <textarea name="observacao" class="form-control clean-input" rows="2"><?php echo htmlspecialchars($r['observacao'] ?? ''); ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label">Entrega (Levantamento) <span class="text-danger">*</span></label>
                <input type="datetime-local" name="data_levantamento" id="data_levantamento" class="form-control clean-input" required 
                       value="<?php echo !empty($r['data_levantamento']) ? date('Y-m-d\TH:i', strtotime($r['data_levantamento'])) : ''; ?>">
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label">Devolução <span class="text-danger">*</span></label>
                <input type="datetime-local" name="data_devolucao" id="data_devolucao" class="form-control clean-input" required 
                       value="<?php echo !empty($r['data_devolucao']) ? date('Y-m-d\TH:i', strtotime($r['data_devolucao'])) : ''; ?>">
            </div>
        </div>

        <button type="submit" class="btn-clean-primary">Guardar Alterações <i class="fas fa-save ms-2"></i></button>
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
    if (inputLev.value) checkAvailability();

    inputLev.addEventListener('change', function() {
        if (inputLev.value) {
            inputDev.min = inputLev.value;
        }
    });
});
</script>