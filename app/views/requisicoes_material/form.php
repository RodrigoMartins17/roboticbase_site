<?php
// FORMULÁRIO para requisitar material. FRAGMENTO (moldura vem do header/footer).
// Recebo $material (se veio de um material específico), $itens (exemplares
// disponíveis) e $todosMateriais. Ao submeter vai para RequisicaoMaterialController::criar.
$itens = $itens ?? [];
?>

<div class="container" style="max-width:720px;">
    <div class="page-header">
        <span class="eyebrow"><i class="fas fa-plus"></i> Novo pedido</span>
        <h1 class="page-title">Requisitar material</h1>
        <?php if (!empty($material)): ?>
            <p class="page-sub"><?php echo htmlspecialchars($material['designacao'] ?? ''); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>

    <div class="card">
        <div class="card__body">
            <?php if (empty($itens)): ?>
                <div class="empty" style="border:none;padding:2rem 0;"><i class="fas fa-box-open"></i><p>Não há exemplares disponíveis para requisitar.</p></div>
            <?php else: ?>
                <form method="post" action="<?php echo BASE_URL; ?>requisicaoMaterial/criar">
                    <div class="field">
                        <label for="item">Exemplar a requisitar</label>
                        <select id="item" name="material_item_id" required>
                            <option value="">— Escolhe um exemplar —</option>
                            <?php foreach ($itens as $it): ?>
                                <option value="<?php echo (int)$it['id']; ?>">
                                    <?php echo htmlspecialchars(($it['material_designacao'] ?? $material['designacao'] ?? 'Material') . ' · Ref. ' . ($it['num_referencia'] ?? $it['id'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid" style="grid-template-columns:1fr 1fr;gap:0 1rem;">
                        <div class="field">
                            <label for="lev">Levantamento</label>
                            <input type="datetime-local" id="lev" name="data_levantamento" required>
                        </div>
                        <div class="field">
                            <label for="dev">Devolução prevista</label>
                            <input type="datetime-local" id="dev" name="data_devolucao" required
                                   oninput="this.setCustomValidity(document.getElementById('lev').value && this.value <= document.getElementById('lev').value ? 'A devolução tem de ser depois do levantamento.' : '')">
                        </div>
                    </div>
                    <div class="field">
                        <label for="obs">Observações (opcional)</label>
                        <textarea id="obs" name="observacao" rows="3" placeholder="Para que precisas do material?"></textarea>
                    </div>
                    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:0.5rem;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar pedido</button>
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>material/index">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
