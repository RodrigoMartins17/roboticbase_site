<?php $r = $requisicao ?? []; $id = (int)($r['id'] ?? 0); ?>
<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); padding: 30px; margin-bottom: 24px;}
    .btn-clean-primary { background-color: #0ea5e9; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; width: 100%; cursor: pointer;} /* Cor específica para devolução */
    .btn-clean-primary:hover { background-color: #0284c7; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(14, 165, 233, 0.2); }
    .btn-clean-secondary { background-color: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; text-decoration: none;}
    .btn-clean-secondary:hover { background-color: #e2e8f0; color: #1e293b; }
    
    .clean-input { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px; font-size: 0.95rem; color: #334155; transition: border-color 0.2s;}
    .clean-input:focus { border-color: #0ea5e9; outline: none; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
    .clean-label { font-weight: 600; color: #475569; margin-bottom: 8px; font-size: 0.9rem;}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;">Processar Devolução</h4>
    <a href="<?php echo BASE_URL; ?>admin/reqMaterialView/<?php echo $id; ?>" class="btn-clean-secondary">Cancelar</a>
</div>

<div class="clean-card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    
    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <i class="fas fa-undo" style="font-size: 2.5rem; color: #0ea5e9; margin-bottom: 16px;"></i>
        <h5 style="color: #0369a1; font-weight: 700; margin-bottom: 8px;">Requisição #<?php echo $id; ?></h5>
        <p class="mb-0" style="color: #0284c7; font-size: 1.1rem;">
            <strong><?php echo htmlspecialchars($r['material_designacao'] ?? ''); ?></strong><br>
            <span style="font-family: monospace; font-size: 0.9rem;">(Ref: <?php echo htmlspecialchars($r['num_referencia'] ?? ''); ?>)</span>
        </p>
    </div>

    <form method="post" action="<?php echo BASE_URL; ?>admin/reqMaterialDevolver/<?php echo $id; ?>" style="text-align: left;">
        <div class="mb-4">
            <label class="clean-label d-block">Estado do Material na Devolução</label>
            <select name="estado_devolucao" class="form-select clean-input" required>
                <option value="OK">✔️ OK (em bom estado)</option>
                <option value="DANIFICADO">⚠️ DANIFICADO (com avarias ou danos)</option>
                <option value="PERDIDO">❌ PERDIDO (não devolvido)</option>
            </select>
        </div>
        
        <button type="submit" class="btn-clean-primary"><i class="fas fa-check-circle me-2"></i> Confirmar Devolução</button>
    </form>
</div>