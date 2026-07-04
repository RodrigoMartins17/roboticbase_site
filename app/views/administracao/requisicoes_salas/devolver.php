<?php
// Formulário para o admin marcar uma sala como DEVOLVIDA ao clube (fica CONCLUIDO).
// Recebe $requisicao. Ao submeter vai para AdminController::reqSalaDevolver (POST).
$r = $requisicao ?? [];
$id = (int)($r['id'] ?? 0);
$bloco = $r['bloco'] ?? '';
$andar = $r['andar'] ?? '';
$numero = str_pad((string)($r['sala_numero'] ?? ''), 2, '0', STR_PAD_LEFT);
$nomeSala = trim($bloco . $andar . '.' . $numero);
?>
<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); padding: 30px; margin-bottom: 24px;}
    .btn-clean-primary { background-color: #16a34a; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: all 0.3s; width: 100%; cursor: pointer;}
    .btn-clean-primary:hover { background-color: #15803d; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(22, 163, 74, 0.2); }
    .btn-clean-secondary { background-color: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; text-decoration: none;}
    .btn-clean-secondary:hover { background-color: #e2e8f0; color: #1e293b; }
    .clean-input { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px; font-size: 0.95rem; color: #334155; transition: border-color 0.2s;}
    .clean-input:focus { border-color: #16a34a; outline: none; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1); }
    .clean-label { font-weight: 600; color: #475569; margin-bottom: 8px; font-size: 0.9rem;}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;">Processar Devolução da Sala</h4>
    <a href="<?php echo BASE_URL; ?>admin/reqSalaView/<?php echo $id; ?>" class="btn-clean-secondary">Cancelar</a>
</div>

<div class="clean-card" style="max-width: 600px; margin: 0 auto; text-align: center;">

    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <i class="fas fa-undo" style="font-size: 2.5rem; color: #16a34a; margin-bottom: 16px;"></i>
        <h5 style="color: #166534; font-weight: 700; margin-bottom: 8px;">Requisição #<?php echo $id; ?></h5>
        <p class="mb-0" style="color: #15803d; font-size: 1.1rem;">
            <strong>Sala <?php echo htmlspecialchars($nomeSala); ?></strong><br>
            <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($r['utilizador_nome'] ?? ''); ?></span>
        </p>
    </div>

    <form method="post" action="<?php echo BASE_URL; ?>admin/reqSalaDevolver/<?php echo $id; ?>" style="text-align: left;">
        <div class="mb-4">
            <label class="clean-label d-block">Estado da Sala na Devolução</label>
            <select name="estado_devolucao" class="form-select clean-input" required>
                <option value="NORMAL">✔️ NORMAL (tudo em ordem)</option>
                <option value="DESARRUMADA_SUJA">⚠️ DESARRUMADA / SUJA</option>
                <option value="DANIFICADA">❌ DANIFICADA (com danos)</option>
            </select>
        </div>

        <button type="submit" class="btn-clean-primary"><i class="fas fa-check-circle me-2"></i> Confirmar Devolução</button>
    </form>
</div>
