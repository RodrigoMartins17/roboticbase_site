<?php
// REQUISIÇÕES DE MATERIAL — lista limpa com dois separadores (Pendentes / Histórico).
// FRAGMENTO (moldura vem do header/footer, igual ao dashboard). Recebo $pendentes e $historico.
// Nota: aqui é o lado do UTILIZADOR — só mostra os pedidos dele e nunca botões de
// administração (aceitar/rejeitar/entregar/devolver). Essas ações são só no painel admin.

// Pequena ajuda para a etiqueta de estado (classe de cor + texto).
function badgeEstadoMat($est) {
    switch ($est) {
        case 'ACEITE':    return ['badge-blue',  'Aceite'];
        case 'EM_USO':    return ['badge-amber', 'Em uso'];
        case 'CONCLUIDO': return ['badge-green', 'Concluído'];
        case 'REJEITADO': return ['badge-red',   'Rejeitado'];
        default:          return ['badge-gray',  'Pendente'];
    }
}
$totalPend = count($pendentes ?? []);
$totalHist = count($historico ?? []);
?>

<div class="container">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;">
        <div>
            <span class="eyebrow"><i class="fas fa-clipboard-list"></i> Os meus pedidos</span>
            <h1 class="page-title">Requisições de material</h1>
        </div>
        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>material/index"><i class="fas fa-arrow-left"></i> Ir ao inventário</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>

    <!-- Separadores -->
    <div style="display:flex;gap:0.5rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem;">
        <button class="tab-btn active" onclick="mudarTab('pendentes', this)">Pendentes <span class="badge badge-gray"><?php echo $totalPend; ?></span></button>
        <button class="tab-btn" onclick="mudarTab('historico', this)">Histórico <span class="badge badge-gray"><?php echo $totalHist; ?></span></button>
    </div>

    <!-- PENDENTES -->
    <div id="tab-pendentes">
        <?php if (empty($pendentes)): ?>
            <div class="empty"><i class="fas fa-inbox"></i><p>Não tens pedidos pendentes.</p></div>
        <?php else: foreach ($pendentes as $p): ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="card__body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <div style="font-weight:700;color:#fff;"><?php echo htmlspecialchars($p['material_designacao'] ?? 'Material'); ?></div>
                        <div class="muted" style="font-size:0.83rem;">Ref. <?php echo htmlspecialchars($p['num_referencia'] ?? '—'); ?> · <?php echo htmlspecialchars($p['data_pedido'] ?? ''); ?></div>
                    </div>
                    <span class="badge badge-gray"><i class="fas fa-clock"></i> Pendente</span>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoMaterial/detalhe/<?php echo (int)$p['id']; ?>"><i class="fas fa-location-arrow"></i> Acompanhar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- HISTÓRICO -->
    <div id="tab-historico" style="display:none;">
        <?php if (empty($historico)): ?>
            <div class="empty"><i class="fas fa-folder-open"></i><p>O histórico está vazio.</p></div>
        <?php else: foreach ($historico as $h):
            [$bc, $bt] = badgeEstadoMat($h['estado_pedido'] ?? '');
            $est = $h['estado_pedido'] ?? '';
        ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="card__body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <div style="font-weight:700;color:#fff;"><?php echo htmlspecialchars($h['material_designacao'] ?? 'Material'); ?></div>
                        <div class="muted" style="font-size:0.83rem;">Ref. <?php echo htmlspecialchars($h['num_referencia'] ?? '—'); ?></div>
                    </div>
                    <span class="badge <?php echo $bc; ?>"><?php echo $bt; ?></span>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoMaterial/detalhe/<?php echo (int)$h['id']; ?>"><i class="fas fa-location-arrow"></i> Acompanhar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<style>
    .tab-btn { background:none;border:none;color:var(--text-muted);font-family:var(--font);font-weight:600;font-size:0.92rem;padding:0.7rem 1rem;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px; }
    .tab-btn:hover { color:#fff; }
    .tab-btn.active { color:var(--blue);border-bottom-color:var(--blue); }
</style>
<script>
    function mudarTab(qual, btn) {
        document.getElementById('tab-pendentes').style.display = (qual === 'pendentes') ? '' : 'none';
        document.getElementById('tab-historico').style.display = (qual === 'historico') ? '' : 'none';
        document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
    }
</script>
