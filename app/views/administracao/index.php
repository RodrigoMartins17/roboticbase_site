<?php
// --- Lógica de Datas para o Calendário Semanal (Apenas Lógica Visual) ---
$hoje = date('Y-m-d');
// A semana a mostrar vem do Controller (permite navegar entre semanas). Se não
// vier nada, calculo a semana atual como antes.
$offsetSemana = $offsetSemana ?? 0;
if (!empty($inicioSemanaCal)) {
    $inicioSemana = $inicioSemanaCal;
} else {
    $diaSemana = date('N'); // 1 (Seg) a 7 (Dom)
    $inicioSemana = date('Y-m-d', strtotime('-' . ($diaSemana - 1) . ' days'));
}

$diasSemana = [];
$nomesDiasAbrev = ['SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB', 'DOM'];

for ($i = 0; $i < 7; $i++) {
    $dataDia = date('Y-m-d', strtotime($inicioSemana . " +$i days"));
    $diasSemana[] = [
        'data' => $dataDia,
        'dia_numero' => date('d', strtotime($dataDia)),
        'nome_dia' => $nomesDiasAbrev[$i],
        'is_hoje' => $dataDia === $hoje
    ];
}

// Agrupar eventos do Calendário (A variável $eventosBrutos já vem do Controller)
$agendaGrid = [];
$eventosGrelha = $eventosBrutos ?? []; 
foreach ($eventosGrelha as $ev) {
    $horaBloco = (int)substr($ev['hora'], 0, 2);
    $agendaGrid[$ev['data']][$horaBloco][] = $ev;
}

// Horário de funcionamento do calendário
$horaInicio = 8;
$horaFim = 19; 
?>

<style>
/* ===== Calendário — estilo Google Calendar (tema claro) ===== */
.gcal-table { table-layout: fixed; width: 100%; min-width: 900px; border-collapse: collapse; background: #fff; }
.gcal-table td { border-right: 1px solid #e8eaed; border-bottom: 1px solid #e8eaed; }
.gcal-table td:last-child { border-right: none; }
.gcal-table thead th { border-bottom: 1px solid #e8eaed; border-right: 1px solid #eef0f2; padding: 8px 0 10px; background: #fff; vertical-align: top; }
.gcal-table thead th:last-child { border-right: none; }

/* Coluna das horas (à esquerda) */
.gcal-time-col { width: 58px; border-right: none !important; border-bottom: none !important; text-align: right; padding-right: 10px !important; color: #70757a; font-size: 0.68rem; font-weight: 500; vertical-align: top; }
.gcal-time-col span { position: relative; top: -8px; }

/* Célula de cada hora/dia */
.gcal-slot { height: 54px; vertical-align: top; padding: 2px 5px !important; position: relative; }
.gcal-slot:hover { background-color: #f8f9fa; }
.gcal-col-hoje { background-color: #f7fbff; }

/* Cabeçalho de cada dia */
.gcal-dayname { font-size: 0.66rem; font-weight: 600; letter-spacing: .6px; text-transform: uppercase; color: #70757a; }
.gcal-daynum { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; margin-top: 4px; border-radius: 50%; font-size: 1.4rem; font-weight: 400; color: #3c4043; transition: background .15s; }
.gcal-daynum.hoje { background: #1a73e8; color: #fff; font-weight: 500; }
.gcal-th-hoje .gcal-dayname { color: #1a73e8; }

/* Linha vermelha da hora atual (como no Google) */
.gcal-now { position: absolute; left: 0; right: 0; height: 2px; background: #ea4335; z-index: 6; }
.gcal-now::before { content: ''; position: absolute; left: -4px; top: -4px; width: 10px; height: 10px; border-radius: 50%; background: #ea4335; }

/* Eventos */
.gcal-event { display: block; text-decoration: none !important; padding: 3px 8px; border-radius: 6px; margin-bottom: 3px; line-height: 1.25; color: #fff !important; cursor: pointer; overflow: hidden; border: none; box-shadow: 0 1px 2px rgba(60,64,67,.18); transition: box-shadow .15s ease, filter .15s ease; }
.gcal-event:hover { box-shadow: 0 2px 10px rgba(60,64,67,.35); filter: brightness(.96); position: relative; z-index: 10; }
.gcal-ev-line1 { font-size: 0.72rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gcal-ev-line1 .t { font-weight: 700; margin-right: 4px; }
.gcal-ev-line2 { font-size: 0.62rem; opacity: .92; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }

/* Cores planas ao estilo Google */
.gcal-event-entregar  { background: #1a73e8; }  /* azul  — entregar */
.gcal-event-receber   { background: #e8710a; }  /* laranja — receber */
.gcal-event-pedido    { background: #d93025; }  /* vermelho — pedido */
.gcal-event-concluido { background: #188038; }  /* verde — concluído */
.gcal-event.urgente   { box-shadow: 0 0 0 2px #fbbc04, 0 1px 3px rgba(60,64,67,.3); }
</style>

<style>
    /* Estética Clean Design para Tabelas */
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); color: white; }
    
    /* Tabela - MESMO ESTILO da categoria/index.php */
    .table-clean th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 16px; border-bottom: 2px solid #e2e8f0; }
    .table-clean td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-weight: 500; }
    .table-clean tbody tr:hover { background-color: #f8fafc; }
    
    /* Botões de Ação */
    .action-icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.85rem; transition: 0.2s; color: white; margin-left: 4px; }
    .action-view { background: #3b82f6; } .action-view:hover { background: #2563eb; transform: scale(1.05); color: white;}
    .action-edit { background: #f59e0b; } .action-edit:hover { background: #d97706; transform: scale(1.05); color: white;}
    .action-delete { background: #ef4444; } .action-delete:hover { background: #dc2626; transform: scale(1.05); color: white;}
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Materiais</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalMateriais ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-cubes"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Exemplares</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalExemplares ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-tags"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Categorias</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalCategorias ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-door-open"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Salas</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalSalas ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-dark bg-opacity-10 text-dark rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-users"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Utilizadores</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalUtilizadores ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.8rem;">Requisições</p>
                <h3 class="fw-bold mb-0 text-dark"><?= (int)($totalRequisicoes ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
        <?php
            // Intervalo da semana mostrada + navegação (semana anterior / hoje / seguinte).
            $fimSemanaLbl = date('Y-m-d', strtotime($inicioSemana . ' +6 days'));
            $labelSemana = date('d/m', strtotime($inicioSemana)) . ' – ' . date('d/m/Y', strtotime($fimSemanaLbl));
        ?>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>admin/index?semana=<?= (int)$offsetSemana - 1 ?>" class="btn btn-sm btn-outline-secondary" title="Semana anterior"><i class="fas fa-chevron-left"></i></a>
            <a href="<?= BASE_URL ?>admin/index?semana=0" class="btn btn-sm <?= (int)$offsetSemana === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Hoje</a>
            <a href="<?= BASE_URL ?>admin/index?semana=<?= (int)$offsetSemana + 1 ?>" class="btn btn-sm btn-outline-secondary" title="Semana seguinte"><i class="fas fa-chevron-right"></i></a>
            <h6 class="fw-bold mb-0 ms-2" style="font-size: 0.9rem;"><i class="fas fa-calendar-week text-primary me-2"></i><?= $labelSemana ?></h6>
        </div>
        <div class="small d-flex align-items-center gap-1 flex-wrap" style="font-size: 0.72rem;">
                    <span class="badge rounded-pill" style="background-color: #d93025;">Pedido</span>
                    <span class="badge rounded-pill" style="background-color: #1a73e8;">Entregar</span>
                    <span class="badge rounded-pill" style="background-color: #e8710a;">Receber</span>
                    <span class="badge rounded-pill" style="background-color: #188038;">Concluído</span>
                    <span class="badge rounded-pill bg-white text-dark border"><i class="fas fa-exclamation-circle" style="color:#fbbc04;"></i> Urgente</span>
                </div>
    </div>
    
    <div class="card-body p-0 table-responsive" style="scrollbar-width: thin;">
        <table class="table gcal-table mb-0">
            <thead>
                <tr>
                    <th class="gcal-time-col border-0"></th>
                    <?php foreach ($diasSemana as $dia): ?>
                        <th class="text-center <?= $dia['is_hoje'] ? 'gcal-th-hoje' : '' ?>">
                            <div class="gcal-dayname"><?= $dia['nome_dia'] ?></div>
                            <div class="gcal-daynum <?= $dia['is_hoje'] ? 'hoje' : '' ?>"><?= $dia['dia_numero'] ?></div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($h = $horaInicio; $h <= $horaFim; $h++): ?>
                    <tr>
                        <td class="gcal-time-col">
                            <span><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00</span>
                        </td>
                        
                        <?php foreach ($diasSemana as $dia): ?>
                            <td class="gcal-slot <?= $dia['is_hoje'] ? 'gcal-col-hoje' : '' ?>">
                                <?php
                        // Linha vermelha da hora atual (só na coluna de hoje e na hora certa).
                        if ($dia['is_hoje'] && (int)date('G') === $h) {
                            $topPct = round((int)date('i') / 60 * 100, 2);
                            echo "<div class='gcal-now' style='top: {$topPct}%;'></div>";
                        }
                        if (isset($agendaGrid[$dia['data']][$h])) {
                            foreach ($agendaGrid[$dia['data']][$h] as $ev) {
                                switch ($ev['tipo']) {
                                    case 'pedido':
                                        $classeCor = 'gcal-event-pedido';
                                        $iconTipo = 'fa-envelope-open-text';
                                        $labelTipo = 'PED';
                                        break;
                                    case 'entregar':
                                        $classeCor = 'gcal-event-entregar';
                                        $iconTipo = 'fa-box-open';
                                        $labelTipo = 'ENT';
                                        break;
                                    case 'receber':
                                        $classeCor = 'gcal-event-receber';
                                        $iconTipo = 'fa-hand-holding';
                                        $labelTipo = 'REC';
                                        break;
                                    case 'concluido':
                                        $classeCor = 'gcal-event-concluido';
                                        $iconTipo = 'fa-check-double';
                                        $labelTipo = 'FIM';
                                        break;
                                }
                                $classeUrgente = $ev['urgente'] ? 'urgente' : '';
                                
                                $rota = ($ev['tipo_req'] === 'sala') ? 'reqSalaView' : 'reqMaterialView';
                                $linkView = BASE_URL . "admin/{$rota}/" . $ev['id_req'];
                                
                                $entrega = !empty($ev['data_entrega_prevista']) ? date('d/m H:i', strtotime($ev['data_entrega_prevista'])) : '—';
                                $devolucao = !empty($ev['data_devolucao_prevista']) ? date('d/m H:i', strtotime($ev['data_devolucao_prevista'])) : '—';

                                $tt = htmlspecialchars($labelTipo . ' · Req #' . $ev['id_req'] . ' · ' . $ev['hora'] . ' · ' . $ev['titulo'] . ' (' . $ev['user'] . ') · Buscar: ' . $entrega . ' · Devolver: ' . $devolucao, ENT_QUOTES);
                                echo "
                                <a href='{$linkView}' class='gcal-event {$classeCor} {$classeUrgente}' title='{$tt}'>
                                    <div class='gcal-ev-line1'><span class='t'>{$ev['hora']}</span>" . htmlspecialchars($ev['titulo']) . "</div>
                                    <div class='gcal-ev-line2'><i class='fas {$iconTipo} me-1'></i>" . htmlspecialchars($ev['user']) . "</div>
                                </a>";
                            }
                        }
                        ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================================================================
     GRÁFICOS DE REQUISIÇÕES
     Substituem as antigas listas "Pedidos por Aceitar"/"Entregas" (essas
     geriam-se melhor nas páginas próprias). Aqui interessa a visão geral:
     quantas requisições entram por dia e por mês, materiais vs salas.
     Os números vêm prontos do AdminController::index().
     =================================================================== -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-column text-primary me-2"></i> Requisições — últimos 7 dias</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoSemanal" height="230"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-success me-2"></i> Requisições — últimos 6 meses</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoMensal" height="230"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Os dados chegam do PHP já contados (dias/meses, materiais e salas).
const gSemanal = <?= json_encode($graficoSemanal ?? ['labels'=>[], 'materiais'=>[], 'salas'=>[]]) ?>;
const gMensal  = <?= json_encode($graficoMensal ?? ['labels'=>[], 'materiais'=>[], 'salas'=>[]]) ?>;

// Opções comuns aos dois gráficos (eixo Y só com números inteiros).
const opcoes = {
    responsive: true,
    plugins: { legend: { position: 'bottom' } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
};

// Barras lado a lado: azul = materiais, verde = salas.
new Chart(document.getElementById('graficoSemanal'), {
    type: 'bar',
    data: {
        labels: gSemanal.labels,
        datasets: [
            { label: 'Materiais', data: gSemanal.materiais, backgroundColor: '#2563eb', borderRadius: 6 },
            { label: 'Salas',     data: gSemanal.salas,     backgroundColor: '#16a34a', borderRadius: 6 }
        ]
    },
    options: opcoes
});

// Evolução mensal em linhas, para se ver a tendência ao longo do tempo.
new Chart(document.getElementById('graficoMensal'), {
    type: 'line',
    data: {
        labels: gMensal.labels,
        datasets: [
            { label: 'Materiais', data: gMensal.materiais, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.15)', fill: true, tension: .35 },
            { label: 'Salas',     data: gMensal.salas,     borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: .35 }
        ]
    },
    options: opcoes
});
</script>
