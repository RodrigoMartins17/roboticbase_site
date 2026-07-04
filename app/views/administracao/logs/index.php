<?php
// LOGS & AUDITORIA — entradas no sistema (login/logout), registos e ações.
$acessos = $acessos ?? [];
$totalErros = $totalErros ?? 0;

// Cor + ícone de cada tipo de evento.
function estiloLog($tipo) {
    switch ($tipo) {
        case 'LOGIN':         return ['#dcfce7', '#166534', 'fa-right-to-bracket', 'Login'];
        case 'LOGOUT':        return ['#f1f5f9', '#475569', 'fa-right-from-bracket', 'Logout'];
        case 'LOGIN_FALHADO': return ['#fef2f2', '#991b1b', 'fa-triangle-exclamation', 'Login falhado'];
        case 'REGISTO':       return ['#e0f2fe', '#075985', 'fa-user-plus', 'Registo'];
        case 'CRIACAO':       return ['#e0e7ff', '#3730a3', 'fa-plus', 'Criação'];
        case 'ALTERACAO':     return ['#fffbeb', '#b45309', 'fa-pen', 'Alteração'];
        case 'ELIMINACAO':    return ['#fef2f2', '#991b1b', 'fa-trash', 'Eliminação'];
        default:              return ['#f1f5f9', '#475569', 'fa-circle-info', 'Ação'];
    }
}
?>
<style>
    .clean-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); overflow: hidden; }
    .log-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
    .log-tab { padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid #e2e8f0; color: #475569; background: #fff; display: inline-flex; align-items: center; gap: 8px; }
    .log-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .log-tab .cnt { background: rgba(0,0,0,0.08); padding: 1px 8px; border-radius: 20px; font-size: 0.78rem; }
    .log-tab.active .cnt { background: rgba(255,255,255,0.25); }
    .table-clean th { background: #f8fafc; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 14px 16px; border-bottom: 2px solid #e2e8f0; }
    .table-clean td { padding: 13px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    .table-clean tbody tr:hover { background: #f8fafc; }
    .log-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-clipboard-check text-primary me-2"></i>Logs &amp; Auditoria</h4>
    <span style="color:#64748b;font-size:0.85rem;"><?php echo count($acessos); ?> registos recentes</span>
</div>

<div class="log-tabs">
    <a class="log-tab active" href="<?php echo BASE_URL; ?>admin/logs"><i class="fas fa-list"></i> Acessos &amp; Auditoria <span class="cnt"><?php echo count($acessos); ?></span></a>
    <a class="log-tab" href="<?php echo BASE_URL; ?>admin/logsErros"><i class="fas fa-triangle-exclamation"></i> Erros <span class="cnt"><?php echo (int)$totalErros; ?></span></a>
</div>

<?php
// Barra de filtros partilhada por todas as listas da administração
// (o formulário em si está em app/views/administracao/_filtros.php).
$filtrosAccao = 'admin/logs';
$filtrosPlaceholder = 'Pesquisar na descrição, nome ou email…';
$filtrosSelects = ['tipo' => ['label' => 'Tipo', 'opcoes' => $tiposLog ?? []]];
include __DIR__ . '/../_filtros.php';
?>
<div class="clean-card">
    <div class="table-responsive">
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 170px;" class="ps-4">Data / Hora</th>
                    <th style="width: 150px;">Tipo</th>
                    <th>Utilizador</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($acessos)): ?>
                    <tr><td colspan="4"><div class="text-center py-5" style="color:#94a3b8;"><i class="fas fa-inbox mb-3" style="font-size:3rem;opacity:0.5;"></i><h5>Sem registos de acesso.</h5></div></td></tr>
                <?php else: foreach ($acessos as $l):
                    [$bg, $cor, $icon, $lbl] = estiloLog($l['tipo'] ?? '');
                ?>
                    <tr>
                        <td class="ps-4"><?php echo date('d/m/Y H:i', strtotime($l['data_hora'] ?? 'now')); ?></td>
                        <td><span class="log-badge" style="background: <?php echo $bg; ?>; color: <?php echo $cor; ?>;"><i class="fas <?php echo $icon; ?>"></i> <?php echo $lbl; ?></span></td>
                        <td>
                            <?php if (!empty($l['utilizador_nome'])): ?>
                                <strong><?php echo htmlspecialchars($l['utilizador_nome']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($l['utilizador_email'] ?? ''); ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($l['descricao'] ?? ''); ?></td>
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
