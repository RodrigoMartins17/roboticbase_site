<style>
    /* Estética Clean Design */
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); color: white; }
    
    /* Tabela */
    .table-clean th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 16px; border-bottom: 2px solid #e2e8f0; }
    .table-clean td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-weight: 500; }
    .table-clean tbody tr:hover { background-color: #f8fafc; }
    
    /* Botões de Ação */
    .action-icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.85rem; transition: 0.2s; color: white; margin-left: 4px; text-decoration: none;}
    .action-stock { background: #8b5cf6; } .action-stock:hover { background: #7c3aed; transform: scale(1.05); color: white;}
    .action-view { background: #3b82f6; } .action-view:hover { background: #2563eb; transform: scale(1.05); color: white;}
    .action-edit { background: #f59e0b; } .action-edit:hover { background: #d97706; transform: scale(1.05); color: white;}
    .action-delete { background: #ef4444; } .action-delete:hover { background: #dc2626; transform: scale(1.05); color: white;}
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-clipboard-list text-primary me-2"></i>Requisições de Materiais</h4>
    
    <a href="<?php echo BASE_URL; ?>admin/reqMaterialCreate" class="btn-clean-primary">
        <i class="fas fa-plus me-2"></i> Nova Requisição
    </a>
</div>

<?php
$renderTableRows = function ($items) {
    if (empty($items)) {
        echo '<tr><td colspan="6"><div class="text-center py-5" style="color: #94a3b8;"><i class="fas fa-inbox mb-3" style="font-size: 3rem; opacity: 0.5;"></i><h5>Nenhum registo encontrado.</h5></div></td></tr>';
        return;
    }
    foreach ($items as $r) {
        $id = (int)($r['id'] ?? 0);
        $st = $r['estado_pedido'] ?? '';
        
        $badgeClass = '';
        if ($st === 'PENDENTE') $badgeClass = 'bg-warning text-dark';
        elseif ($st === 'ACEITE') $badgeClass = 'bg-success';
        elseif ($st === 'EM_USO') $badgeClass = 'bg-info text-dark';
        elseif ($st === 'REJEITADO') $badgeClass = 'bg-danger';
        elseif ($st === 'CONCLUIDO') $badgeClass = 'bg-secondary';
        else $badgeClass = 'bg-secondary';

        // Etiquetas "bonitas" para mostrar ao utilizador (o valor na BD continua igual).
        $labels = ['PENDENTE' => 'Pendente', 'ACEITE' => 'Aceite', 'EM_USO' => 'Em uso', 'REJEITADO' => 'Rejeitado', 'CONCLUIDO' => 'Devolvido'];
        $stLabel = $labels[$st] ?? $st;

        $lev = !empty($r['data_levantamento']) ? date('d/m/Y H:i', strtotime($r['data_levantamento'])) : '—';
        $dev = !empty($r['data_devolucao']) ? date('d/m/Y H:i', strtotime($r['data_devolucao'])) : '—';
        ?>
        <tr>
            <td class="ps-4"><?php echo $id; ?></td>
            <td>
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-box"></i></div>
                    <div>
                        <strong><?php echo htmlspecialchars($r['material_designacao'] ?? ''); ?> (<?php echo htmlspecialchars($r['num_referencia'] ?? ''); ?>)</strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($r['utilizador_nome'] ?? ''); ?></small>
                    </div>
                </div>
            </td>
            <td><?php echo $lev; ?></td>
            <td><?php echo $dev; ?></td>
            <td>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($stLabel); ?></span>
            </td>
            <td class="text-end pe-4 text-nowrap">
                <a href="<?php echo BASE_URL; ?>admin/reqMaterialView/<?php echo $id; ?>" class="action-icon-btn action-view" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                <?php if ($st === 'PENDENTE'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/reqMaterialAceitar/<?php echo $id; ?>" class="action-icon-btn" style="background-color: #10b981;" onclick="return confirm('Aceitar requisição?');"><i class="fas fa-check"></i></a>
                    <a href="<?php echo BASE_URL; ?>admin/reqMaterialRejeitar/<?php echo $id; ?>" class="action-icon-btn action-delete" onclick="return confirm('Rejeitar requisição?');"><i class="fas fa-times"></i></a>
                <?php endif; ?>
                <?php if ($st === 'ACEITE'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/reqMaterialEntregar/<?php echo $id; ?>" class="action-icon-btn" style="background-color: #0ea5e9;" title="Marcar como entregue ao aluno" onclick="return confirm('Marcar como entregue ao aluno?');"><i class="fas fa-box-open"></i></a>
                <?php endif; ?>
                <?php if ($st === 'EM_USO'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/reqMaterialDevolver/<?php echo $id; ?>" class="action-icon-btn" style="background-color: #16a34a;" title="Marcar como devolvido ao clube"><i class="fas fa-undo"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>admin/reqMaterialEdit/<?php echo $id; ?>" class="action-icon-btn action-edit" title="Editar"><i class="fas fa-pen"></i></a>
                <a href="<?php echo BASE_URL; ?>admin/reqMaterialDelete/<?php echo $id; ?>" class="action-icon-btn action-delete" title="Apagar"><i class="fas fa-trash-alt"></i></a>
            </td>
        </tr>
        <?php
    }
};
?>


<div class="clean-card mb-4">
    <div class="px-4 py-3 border-bottom" style="background-color: #f8fafc;">
        <h6 class="mb-0 fw-bold" style="color: #334155;"><i class="fas fa-clock text-warning me-2"></i> Por Aceitar <span class="badge bg-warning text-dark ms-2"><?php echo count($pendentes ?? []); ?></span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Material / Utilizador</th>
                    <th>Levantamento</th>
                    <th>Devolução</th>
                    <th>Estado</th>
                    <th class="text-end pe-4 text-nowrap" style="width: 240px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $renderTableRows($pendentes ?? []); ?>
            </tbody>
        </table>
    </div>
</div>

<div class="clean-card mb-4">
    <div class="px-4 py-3 border-bottom" style="background-color: #f8fafc;">
        <h6 class="mb-0 fw-bold" style="color: #334155;"><i class="fas fa-box-open text-success me-2"></i> A Entregar / Em Uso <span class="badge bg-success ms-2"><?php echo count($aceites ?? []); ?></span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Material / Utilizador</th>
                    <th>Levantamento</th>
                    <th>Devolução</th>
                    <th>Estado</th>
                    <th class="text-end pe-4 text-nowrap" style="width: 240px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $renderTableRows($aceites ?? []); ?>
            </tbody>
        </table>
    </div>
</div>

<div class="clean-card mb-4">
    <div class="px-4 py-3 border-bottom" style="background-color: #f8fafc;">
        <h6 class="mb-0 fw-bold" style="color: #334155;"><i class="fas fa-archive text-secondary me-2"></i> Histórico <span class="badge bg-secondary ms-2"><?php echo count($historico ?? []); ?></span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Material / Utilizador</th>
                    <th>Levantamento</th>
                    <th>Devolução</th>
                    <th>Estado</th>
                    <th class="text-end pe-4 text-nowrap" style="width: 240px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $renderTableRows($historico ?? []); ?>
            </tbody>
        </table>
    </div>
</div>