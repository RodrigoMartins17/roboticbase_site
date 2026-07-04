<?php
// PÁGINA DE ACOMPANHAMENTO de um pedido de material (fluxo tipo CTT), agora clara.
// É um FRAGMENTO (a moldura vem do header/footer). Recebe o pedido em $req e,
// conforme o estado, marca cada passo como concluído, atual, por fazer ou recusado.
$isStaff = Auth::isAdmin() || Auth::isResponsavel() || Auth::isProf();

$estado = $req['estado_pedido'] ?? 'PENDENTE';
$devol  = $req['estado_devolucao'] ?? null;

// Função para formatar as datas de forma bonita (ou devolver null se não houver).
$fmt = function ($d) {
    if (empty($d) || $d === '0000-00-00 00:00:00') return null;
    $t = strtotime($d);
    return $t ? date('d/m/Y \à\s H:i', $t) : null;
};

// A ordem dos estados ajuda-me a saber que passos já estão concluídos.
$ordem = ['PENDENTE' => 0, 'ACEITE' => 1, 'EM_USO' => 2, 'CONCLUIDO' => 3];
$pos = $ordem[$estado] ?? 0;
$rejeitado = ($estado === 'REJEITADO');
$devolLabel = ['OK' => 'Devolvido em bom estado', 'DANIFICADO' => 'Devolvido danificado', 'PERDIDO' => 'Material dado como perdido'];

// Monto a lista de passos da linha do tempo.
$steps = [];
$steps[] = ['fa-paper-plane', 'Pedido registado', $fmt($req['data_pedido'] ?? null), 'O teu pedido deu entrada no sistema.', 'done'];

if ($rejeitado) {
    $steps[] = ['fa-xmark', 'Pedido recusado', null, 'O clube não aprovou este pedido.', 'rejected'];
} else {
    $steps[] = ['fa-clipboard-check', 'Aceite pelo clube', $pos >= 1 ? $fmt($req['data_levantamento'] ?? null) : null,
        $pos >= 1 ? 'Pedido aprovado. Levantamento agendado.' : 'A aguardar decisão do responsável do clube.',
        $pos >= 1 ? 'done' : 'current'];
    $steps[] = ['fa-hand-holding-hand', 'Entregue ao aluno', $pos >= 2 ? $fmt($req['data_levantamento'] ?? null) : null,
        $pos >= 2 ? 'Material levantado pelo requisitante.' : ($pos === 1 ? 'Pronto para levantamento.' : 'Aguarda entrega.'),
        $pos >= 2 ? 'done' : ($pos === 1 ? 'current' : 'todo')];
    $steps[] = ['fa-rotate-left', 'Devolvido ao clube', $pos >= 3 ? $fmt($req['data_devolucao'] ?? null) : null,
        $pos >= 3 ? ($devolLabel[$devol] ?? 'Fluxo concluído.') : ($pos === 2 ? 'Em utilização. Devolução prevista para ' . ($fmt($req['data_devolucao'] ?? null) ?? 'data a definir') . '.' : 'Aguarda devolução.'),
        $pos >= 3 ? 'done' : ($pos === 2 ? 'current' : 'todo')];
}

// Banner grande com o estado atual (cor + ícone + texto).
$banner = [
    'PENDENTE'  => ['wait', 'fa-hourglass-half', 'Pendente', 'A aguardar decisão do clube.'],
    'ACEITE'    => ['live', 'fa-clipboard-check', 'Aceite', 'Aprovado — pronto para levantamento.'],
    'EM_USO'    => ['live', 'fa-hand-holding-hand', 'Entregue ao aluno', 'Material em utilização.'],
    'CONCLUIDO' => ['ok', 'fa-circle-check', 'Concluído', $devolLabel[$devol] ?? 'Devolvido ao clube.'],
    'REJEITADO' => ['bad', 'fa-circle-xmark', 'Recusado', 'O pedido não foi aprovado.'],
];
$b = $banner[$estado] ?? $banner['PENDENTE'];
?>

<div class="track-wrap fade-in">
    <a class="track-back" href="<?php echo BASE_URL; ?>requisicaoMaterial/index"><i class="fas fa-arrow-left"></i> Voltar às minhas requisições</a>

    <span class="track-ref"># REQ-M<?php echo str_pad((string)($req['id'] ?? 0), 4, '0', STR_PAD_LEFT); ?></span>
    <h1 class="page-title"><?php echo htmlspecialchars($req['material_designacao'] ?? 'Material'); ?></h1>
    <p class="page-sub"><i class="fas fa-barcode"></i> Exemplar <?php echo htmlspecialchars($req['num_referencia'] ?? '—'); ?></p>

    <div class="track-grid">
        <!-- Linha do tempo -->
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

        <!-- Detalhes -->
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
                <div class="info-row"><span class="lbl">Levantamento <?php echo $pos >= 2 ? 'efetuado' : 'previsto'; ?></span><span class="val"><?php echo htmlspecialchars($fmt($req['data_levantamento'] ?? null) ?? 'A definir'); ?></span></div>
                <div class="info-row"><span class="lbl">Devolução <?php echo $pos >= 3 ? 'efetuada' : 'prevista'; ?></span><span class="val"><?php echo htmlspecialchars($fmt($req['data_devolucao'] ?? null) ?? 'A definir'); ?></span></div>
                <?php if (!empty($req['observacao'])): ?>
                    <div class="info-div"></div>
                    <div class="info-row"><span class="lbl">Observação</span><span class="val" style="font-weight:400;"><?php echo nl2br(htmlspecialchars($req['observacao'])); ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
