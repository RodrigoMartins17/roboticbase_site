<?php
// PÁGINA DE ACOMPANHAMENTO de uma reserva de sala (fluxo tipo CTT), estilo claro.
// FRAGMENTO: a moldura vem do header/footer. Passos adaptados a salas:
// pedido -> aceite -> sala disponibilizada (check-in) -> reserva concluída (check-out).
$isStaff = Auth::isAdmin() || Auth::isResponsavel() || Auth::isProf();

$estado = $req['estado_sala'] ?? 'PENDENTE';
$devol  = $req['estado_devolucao'] ?? null;

$fmt = function ($d) {
    if (empty($d) || $d === '0000-00-00 00:00:00') return null;
    $t = strtotime($d);
    return $t ? date('d/m/Y \à\s H:i', $t) : null;
};

$ordem = ['PENDENTE' => 0, 'ACEITE' => 1, 'EM_USO' => 2, 'CONCLUIDO' => 3];
$pos = $ordem[$estado] ?? 0;
$rejeitado = ($estado === 'REJEITADO');
$devolLabel = ['NORMAL' => 'Sala devolvida em ordem', 'OK' => 'Sala devolvida em ordem', 'DANIFICADA' => 'Sala devolvida com danos', 'DESARRUMADA_SUJA' => 'Sala devolvida suja/desarrumada'];
$salaNome = ($req['bloco'] ?? '') . ($req['andar'] ?? '') . '.' . ($req['sala_numero'] ?? '');

$steps = [];
$steps[] = ['fa-paper-plane', 'Pedido registado', $fmt($req['data'] ?? null), 'A tua reserva deu entrada no sistema.', 'done'];

if ($rejeitado) {
    $steps[] = ['fa-xmark', 'Pedido recusado', null, 'O clube não aprovou esta reserva.', 'rejected'];
} else {
    $steps[] = ['fa-clipboard-check', 'Aceite pelo clube', null,
        $pos >= 1 ? 'Reserva aprovada e confirmada.' : 'A aguardar decisão do responsável do clube.',
        $pos >= 1 ? 'done' : 'current'];
    $steps[] = ['fa-door-open', 'Sala disponibilizada', $pos >= 2 ? $fmt($req['data_inicio'] ?? null) : null,
        $pos >= 2 ? 'Check-in efetuado — sala em utilização.' : ($pos === 1 ? 'Início previsto para ' . ($fmt($req['data_inicio'] ?? null) ?? 'data a definir') . '.' : 'Aguarda início.'),
        $pos >= 2 ? 'done' : ($pos === 1 ? 'current' : 'todo')];
    $steps[] = ['fa-flag-checkered', 'Reserva concluída', $pos >= 3 ? $fmt($req['data_fim'] ?? null) : null,
        $pos >= 3 ? ($devolLabel[$devol] ?? 'Check-out efetuado.') : ($pos === 2 ? 'Em utilização. Fim previsto para ' . ($fmt($req['data_fim'] ?? null) ?? 'data a definir') . '.' : 'Aguarda conclusão.'),
        $pos >= 3 ? 'done' : ($pos === 2 ? 'current' : 'todo')];
}

$banner = [
    'PENDENTE'  => ['wait', 'fa-hourglass-half', 'Pendente', 'A aguardar decisão do clube.'],
    'ACEITE'    => ['live', 'fa-clipboard-check', 'Aceite', 'Reserva confirmada.'],
    'EM_USO'    => ['live', 'fa-door-open', 'Em utilização', 'Sala em utilização (check-in efetuado).'],
    'CONCLUIDO' => ['ok', 'fa-circle-check', 'Concluído', $devolLabel[$devol] ?? 'Reserva terminada.'],
    'REJEITADO' => ['bad', 'fa-circle-xmark', 'Recusado', 'O pedido não foi aprovado.'],
];
$b = $banner[$estado] ?? $banner['PENDENTE'];
?>

<div class="track-wrap fade-in">
    <a class="track-back" href="<?php echo BASE_URL; ?>requisicaoSala/index"><i class="fas fa-arrow-left"></i> Voltar às minhas requisições</a>

    <span class="track-ref"># REQ-S<?php echo str_pad((string)($req['id'] ?? 0), 4, '0', STR_PAD_LEFT); ?></span>
    <h1 class="page-title"><?php echo htmlspecialchars($salaNome); ?></h1>
    <p class="page-sub"><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars(($req['sala_descricao'] ?? '') ?: ('Andar ' . ($req['andar'] ?? '—'))); ?></p>

    <div class="track-grid">
        <div class="track-panel">
            <h3>Acompanhamento do pedido</h3>
            <div class="track">
                <?php foreach ($steps as $s): ?>
                    <div class="track-step <?php echo $s[4]; ?>">
                        <div class="track-ic"><i class="fas <?php echo $s[0]; ?>"></i></div>
                        <div class="track-b">
                            <h4><?php echo htmlspecialchars($s[1]); ?></h4>
                            <?php if (!empty($s[2])): ?><div class="track-t"><i class="far fa-clock"></i> <?php echo htmlspecialchars($s[2]); ?></div><?php endif; ?>
                            <?php if (!empty($s[3])): ?><div class="track-d"><?php echo htmlspecialchars($s[3]); ?></div><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="track-panel">
            <h3>Detalhes</h3>
            <div class="tsb <?php echo $b[0]; ?>">
                <div class="tsb-ic"><i class="fas <?php echo $b[1]; ?>"></i></div>
                <div><strong><?php echo htmlspecialchars($b[2]); ?></strong><span><?php echo htmlspecialchars($b[3]); ?></span></div>
            </div>
            <div class="info-list">
                <div class="info-row"><span class="lbl">Requisitante</span><span class="val"><i class="fas fa-user" style="color:var(--blue)"></i> <?php echo htmlspecialchars($req['utilizador_nome'] ?? '—'); ?></span></div>
                <?php if ($isStaff && !empty($req['utilizador_email'])): ?>
                    <div class="info-row"><span class="lbl">Email</span><span class="val"><?php echo htmlspecialchars($req['utilizador_email']); ?></span></div>
                <?php endif; ?>
                <div class="info-div"></div>
                <div class="info-row"><span class="lbl">Início <?php echo $pos >= 2 ? 'efetivo' : 'previsto'; ?></span><span class="val"><?php echo htmlspecialchars($fmt($req['data_inicio'] ?? null) ?? 'A definir'); ?></span></div>
                <div class="info-row"><span class="lbl">Fim <?php echo $pos >= 3 ? 'efetivo' : 'previsto'; ?></span><span class="val"><?php echo htmlspecialchars($fmt($req['data_fim'] ?? null) ?? 'A definir'); ?></span></div>
                <?php if (!empty($req['observacao'])): ?>
                    <div class="info-div"></div>
                    <div class="info-row"><span class="lbl">Observação</span><span class="val" style="font-weight:400;"><?php echo nl2br(htmlspecialchars($req['observacao'])); ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
