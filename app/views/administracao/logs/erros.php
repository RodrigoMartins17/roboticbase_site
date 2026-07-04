<?php
// LOGS DE ERROS — erros vindos da base de dados (validações com SIGNAL nas
// procedures/triggers) e exceções não tratadas.
$erros = $erros ?? [];
$totalAcessos = $totalAcessos ?? 0;
?>
<style>
    .clean-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); overflow: hidden; }
    .log-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
    .log-tab { padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid #e2e8f0; color: #475569; background: #fff; display: inline-flex; align-items: center; gap: 8px; }
    .log-tab.active { background: #d93025; color: #fff; border-color: #d93025; }
    .log-tab .cnt { background: rgba(0,0,0,0.08); padding: 1px 8px; border-radius: 20px; font-size: 0.78rem; }
    .log-tab.active .cnt { background: rgba(255,255,255,0.25); }
    .table-clean th { background: #f8fafc; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 14px 16px; border-bottom: 2px solid #e2e8f0; }
    .table-clean td { padding: 13px 16px; vertical-align: top; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    .table-clean tbody tr:hover { background: #fff5f5; }
    .code-chip { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 6px; font-family: monospace; font-size: 0.78rem; font-weight: 700; }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-triangle-exclamation text-danger me-2"></i>Logs de Erros</h4>
    <span style="color:#64748b;font-size:0.85rem;"><?php echo count($erros); ?> erros registados</span>
</div>

<div class="log-tabs">
    <a class="log-tab" href="<?php echo BASE_URL; ?>admin/logs"><i class="fas fa-list"></i> Acessos &amp; Auditoria <span class="cnt"><?php echo (int)$totalAcessos; ?></span></a>
    <a class="log-tab active" href="<?php echo BASE_URL; ?>admin/logsErros"><i class="fas fa-triangle-exclamation"></i> Erros <span class="cnt"><?php echo count($erros); ?></span></a>
</div>

<?php
// Barra de filtros partilhada por todas as listas da administração
// (o formulário em si está em app/views/administracao/_filtros.php).
$filtrosAccao = 'admin/logsErros';
$filtrosPlaceholder = 'Pesquisar na mensagem ou origem…';
$filtrosSelects = ['origem' => ['label' => 'Origem', 'opcoes' => $origensErro ?? []]];
include __DIR__ . '/../_filtros.php';
?>
<div class="clean-card">
    <div class="table-responsive">
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 160px;" class="ps-4">Data / Hora</th>
                    <th style="width: 230px;">Origem</th>
                    <th style="width: 120px;">Código</th>
                    <th>Mensagem</th>
                    <th style="width: 150px;">Utilizador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($erros)): ?>
                    <tr><td colspan="5"><div class="text-center py-5" style="color:#94a3b8;"><i class="fas fa-circle-check mb-3" style="font-size:3rem;opacity:0.5;color:#22c55e;"></i><h5>Sem erros registados. Tudo em ordem!</h5></div></td></tr>
                <?php else: foreach ($erros as $e): ?>
                    <tr>
                        <td class="ps-4"><?php echo date('d/m/Y H:i', strtotime($e['data_hora'] ?? 'now')); ?></td>
                        <td><span style="font-family:monospace;font-size:0.82rem;color:#334155;"><?php echo htmlspecialchars($e['origem'] ?? ''); ?></span></td>
                        <td>
                            <?php if (!empty($e['sqlstate']) || !empty($e['codigo'])): ?>
                                <span class="code-chip"><?php echo htmlspecialchars(trim(($e['sqlstate'] ?? '') . ' ' . ($e['codigo'] ?? ''))); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#7f1d1d;"><?php echo htmlspecialchars($e['mensagem'] ?? ''); ?></td>
                        <td><?php echo !empty($e['utilizador_nome']) ? htmlspecialchars($e['utilizador_nome']) : '<span class="text-muted">—</span>'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Botões das páginas (partilhados — ver app/views/administracao/_paginacao.php).
include __DIR__ . '/../_paginacao.php';
?>
