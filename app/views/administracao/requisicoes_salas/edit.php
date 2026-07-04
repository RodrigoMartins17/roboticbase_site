<?php 
$r = $requisicao ?? []; 
$id = (int)($r['id'] ?? 0); 
?>
<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); padding: 30px; margin-bottom: 24px;}
    .btn-clean-primary { background-color: #4f46e5; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; width: 100%; cursor: pointer;}
    .btn-clean-primary:hover { background-color: #4338ca; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(79, 70, 229, 0.2); }
    .btn-clean-secondary { background-color: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; text-decoration: none;}
    .btn-clean-secondary:hover { background-color: #e2e8f0; color: #1e293b; }
    
    .clean-input { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px; font-size: 0.95rem; color: #334155; transition: border-color 0.2s;}
    .clean-input:focus { border-color: #4f46e5; outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .clean-label { font-weight: 600; color: #475569; margin-bottom: 8px; font-size: 0.9rem;}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;">Editar Requisição #<?php echo $id; ?></h4>
    <a href="<?php echo BASE_URL; ?>admin/reqSalaView/<?php echo $id; ?>" class="btn-clean-secondary">Voltar</a>
</div>

<div class="clean-card" style="max-width: 800px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="border-radius: 10px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo BASE_URL; ?>admin/reqSalaUpdate/<?php echo $id; ?>">
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Utilizador <span class="text-danger">*</span></label>
                <select name="id_utilizador" class="form-select clean-input" required>
                    <?php foreach ($utilizadores ?? [] as $u): ?>
                        <option value="<?php echo (int)$u['id']; ?>" <?php echo (int)($r['id_utilizador'] ?? 0) === (int)$u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nome'] ?? ''); ?> (<?php echo htmlspecialchars($u['email'] ?? ''); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Sala <span class="text-danger">*</span></label>
                <select name="id_sala" class="form-select clean-input" required>
                    <?php foreach ($salas ?? [] as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo (int)($r['id_sala'] ?? 0) === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim(($s['bloco'] ?? '') . ' ' . ($s['andar'] ?? '') . ' ' . ($s['numero'] ?? ''))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Estado do Pedido</label>
                <select name="estado_sala" class="form-select clean-input">
                    <option value="PENDENTE" <?php echo ($r['estado_sala'] ?? '') === 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="ACEITE" <?php echo ($r['estado_sala'] ?? '') === 'ACEITE' ? 'selected' : ''; ?>>Aceite</option>
                    <option value="EM_USO" <?php echo ($r['estado_sala'] ?? '') === 'EM_USO' ? 'selected' : ''; ?>>Em uso</option>
                    <option value="REJEITADO" <?php echo ($r['estado_sala'] ?? '') === 'REJEITADO' ? 'selected' : ''; ?>>Rejeitado</option>
                    <option value="CONCLUIDO" <?php echo ($r['estado_sala'] ?? '') === 'CONCLUIDO' ? 'selected' : ''; ?>>Devolvido</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Estado da Devolução</label>
                <select name="estado_devolucao" class="form-select clean-input">
                    <option value="NORMAL" <?php echo ($r['estado_devolucao'] ?? '') === 'NORMAL' ? 'selected' : ''; ?>>NORMAL (Limpa)</option>
                    <option value="DESARRUMADA_SUJA" <?php echo ($r['estado_devolucao'] ?? '') === 'DESARRUMADA_SUJA' ? 'selected' : ''; ?>>DESARRUMADA / SUJA</option>
                    <option value="DANIFICADA" <?php echo ($r['estado_devolucao'] ?? '') === 'DANIFICADA' ? 'selected' : ''; ?>>DANIFICADA</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Estado de Entrega (Check-in)</label>
                <select name="estado_entrega" class="form-select clean-input">
                    <option value="0" <?php echo (int)($r['estado_entrega'] ?? 0) === 0 ? 'selected' : ''; ?>>Pendente (Não levantada)</option>
                    <option value="1" <?php echo (int)($r['estado_entrega'] ?? 0) === 1 ? 'selected' : ''; ?>>Levantada (Check-in efetuado)</option>
                </select>
            </div>
        </div>

        <hr style="border-color: #e2e8f0; margin: 20px 0;">
        <h5 class="fw-bold mb-3" style="color: #334155;">Agendamento</h5>

        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Data/hora Início</label>
                <input type="datetime-local" name="data_inicio" class="form-control clean-input" step="60" value="<?php echo !empty($r['data_inicio']) && ($ts = strtotime($r['data_inicio'])) !== false ? date('Y-m-d\TH:i', $ts) : ''; ?>" required>
            </div>
            <div class="col-md-6 mb-4">
                <label class="clean-label d-block">Data/hora Fim</label>
                <input type="datetime-local" name="data_fim" class="form-control clean-input" step="60" value="<?php echo !empty($r['data_fim']) && ($ts = strtotime($r['data_fim'])) !== false ? date('Y-m-d\TH:i', $ts) : ''; ?>" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="clean-label d-block">Observação</label>
            <textarea name="observacao" class="form-control clean-input" rows="2"><?php echo htmlspecialchars($r['observacao'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn-clean-primary mt-2">Guardar Alterações <i class="fas fa-save ms-2"></i></button>
    </form>
</div>
