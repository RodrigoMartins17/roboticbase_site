<?php
// REQUISIÇÕES DE SALA — lista limpa com Pendentes / Histórico.
// FRAGMENTO (moldura vem do header/footer). Recebo $pendentes e $historico.
// Lado do UTILIZADOR: só os pedidos dele e sem botões de administração. As
// decisões (aceitar/rejeitar/dar acesso/finalizar) são só no painel admin.

function badgeEstadoSala($est) {
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
            <span class="eyebrow"><i class="fas fa-door-open"></i> Reservas de sala</span>
            <h1 class="page-title">Requisições de sala</h1>
        </div>
        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>sala/index"><i class="fas fa-arrow-left"></i> Ver salas</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
    <?php endif; ?>

    <div style="display:flex;gap:0.5rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem;">
        <button class="tab-btn active" onclick="mudarTab('pendentes', this)">Pendentes <span class="badge badge-gray"><?php echo $totalPend; ?></span></button>
        <button class="tab-btn" onclick="mudarTab('historico', this)">Histórico <span class="badge badge-gray"><?php echo $totalHist; ?></span></button>
    </div>

    <?php
    // Função para desenhar o nome da sala (bloco + número).
    $nomeSala = function($r) { return ($r['bloco'] ?? '') . ($r['andar'] ?? '') . '.' . ($r['sala_numero'] ?? ''); };
    ?>

    <!-- PENDENTES -->
    <div id="tab-pendentes">
        <?php if (empty($pendentes)): ?>
            <div class="empty"><i class="fas fa-inbox"></i><p>Não há reservas pendentes.</p></div>
        <?php else: foreach ($pendentes as $p): ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="card__body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <div style="font-weight:700;color:#fff;"><?php echo htmlspecialchars($nomeSala($p)); ?></div>
                        <div class="muted" style="font-size:0.83rem;"><?php echo htmlspecialchars($p['utilizador_nome'] ?? ''); ?> · <?php echo htmlspecialchars($p['data_inicio'] ?? ''); ?></div>
                    </div>
                    <span class="badge badge-gray"><i class="fas fa-clock"></i> Pendente</span>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoSala/detalhe/<?php echo (int)$p['id']; ?>"><i class="fas fa-location-arrow"></i> Acompanhar</a>
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
            [$bc, $bt] = badgeEstadoSala($h['estado_sala'] ?? '');
            $est = $h['estado_sala'] ?? '';
        ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="card__body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <div style="font-weight:700;color:#fff;"><?php echo htmlspecialchars($nomeSala($h)); ?></div>
                        <div class="muted" style="font-size:0.83rem;"><?php echo htmlspecialchars($h['utilizador_nome'] ?? ''); ?></div>
                    </div>
                    <span class="badge <?php echo $bc; ?>"><?php echo $bt; ?></span>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoSala/detalhe/<?php echo (int)$h['id']; ?>"><i class="fas fa-location-arrow"></i> Acompanhar</a>
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
