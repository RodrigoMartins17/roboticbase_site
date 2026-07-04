<?php
// FORMULÁRIO para reservar uma sala. FRAGMENTO (moldura vem do header/footer).
// Recebo $salas (lista) e $salaSelecionada (sala pré-escolhida, se vier do inventário).
// Ao submeter vai para RequisicaoSalaController::criar.
$salas = $salas ?? [];
$salaSelecionada = $salaSelecionada ?? 0;
?>

<div class="container" style="max-width:720px;">
    <div class="page-header">
        <span class="eyebrow"><i class="fas fa-calendar-plus"></i> Nova reserva</span>
        <h1 class="page-title">Reservar sala</h1>
        <p class="page-sub">Escolhe a sala e o período que precisas.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>

    <div class="card">
        <div class="card__body">
            <form method="post" action="<?php echo BASE_URL; ?>requisicaoSala/criar">
                <div class="field">
                    <label for="sala">Sala</label>
                    <select id="sala" name="sala_id" required>
                        <option value="">— Escolhe uma sala —</option>
                        <?php foreach ($salas as $s):
                            $nome = ($s['bloco'] ?? '') . ($s['andar'] ?? '') . '.' . ($s['numero'] ?? '');
                            if (!empty($s['descricao'])) $nome .= ' — ' . $s['descricao'];
                        ?>
                            <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$salaSelecionada === (int)$s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nome); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid" style="grid-template-columns:1fr 1fr;gap:0 1rem;">
                    <div class="field">
                        <label for="inicio">Início</label>
                        <input type="datetime-local" id="inicio" name="data_inicio" required>
                    </div>
                    <div class="field">
                        <label for="fim">Fim</label>
                        <input type="datetime-local" id="fim" name="data_fim" required
                               oninput="this.setCustomValidity(document.getElementById('inicio').value && this.value <= document.getElementById('inicio').value ? 'O fim tem de ser depois do início.' : '')">
                    </div>
                </div>
                <div class="field">
                    <label for="obs">Observações (opcional)</label>
                    <textarea id="obs" name="observacao" rows="3" placeholder="Para que atividade é a sala?"></textarea>
                </div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar pedido</button>
                    <a class="btn btn-outline" href="<?php echo BASE_URL; ?>sala/index">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
