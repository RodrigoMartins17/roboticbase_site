<style>
    /* Estética Clean Design */
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); color: white; }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center;}
    .btn-clean-outline:hover { background-color: #f8fafc; color: #0f172a; }
    
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
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-cubes text-primary me-2"></i>Gerir Exemplares</h4>
    
    <div class="d-flex gap-2">
        <?php if (!empty($materialFiltro)): ?>
            <a href="<?php echo BASE_URL; ?>admin/materialView/<?php echo (int)$materialFiltro['id']; ?>" class="btn-clean-outline">
                <i class="fas fa-box me-2"></i>Ver Material
            </a>
            <a href="<?php echo BASE_URL; ?>admin/exemplarCreateComMaterial/<?php echo (int)$materialFiltro['id']; ?>" class="btn-clean-primary">
                <i class="fas fa-plus me-2"></i>Novo Exemplar
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>admin/exemplarCreate" class="btn-clean-primary">
                <i class="fas fa-plus me-2"></i>Novo Exemplar
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($materialFiltro)): ?>
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
    <div>
        <span style="color: #64748b; font-weight: 600; font-size: 0.9rem;">A filtrar por Material:</span>
        <strong style="color: #0f172a; margin-left: 8px; font-size: 1.05rem;"><?php echo htmlspecialchars($materialFiltro['designacao'] ?? ''); ?></strong>
    </div>
    <a href="<?php echo BASE_URL; ?>admin/exemplares" style="color: #2563eb; font-weight: 600; font-size: 0.9rem; text-decoration: none;">Ver Todos</a>
</div>
<?php endif; ?>

<div class="clean-card">
    <div class="table-responsive">
        <?php if (empty($exemplares)): ?>
            <div class="text-center py-5" style="color: #94a3b8;">
                <i class="fas fa-box-open mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                <h5>Nenhum exemplar registado.</h5>
            </div>
        <?php else: ?>
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Referência</th>
                    <th>Material</th>
                    <th>Estado</th>
                    <th class="text-end pe-4" style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exemplares as $e): ?>
                <tr>
                    <td class="ps-4"><?php echo htmlspecialchars($e['id']); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-barcode"></i></div>
                            <strong><?php echo htmlspecialchars($e['num_referencia'] ?? ''); ?></strong>
                        </div>
                    </td>
                    <td>
                        <?php if(!empty($materialFiltro)): ?>
                            <?php echo htmlspecialchars($materialFiltro['designacao'] ?? ''); ?>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>admin/materialView/<?php echo (int)$e['id_material']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($e['designacao'] ?? ''); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                            $est = $e['estado'] ?? '';
                            $badgeClass = '';
                            if ($est === 'DISPONIVEL') $badgeClass = 'bg-success text-success border-success';
                            elseif ($est === 'EMPRESTADO') $badgeClass = 'bg-info text-info border-info';
                            elseif ($est === 'DANIFICADO') $badgeClass = 'bg-warning text-warning border-warning';
                            elseif ($est === 'PERDIDO') $badgeClass = 'bg-danger text-danger border-danger';
                            else $badgeClass = 'bg-secondary text-secondary border-secondary';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?> bg-opacity-10 px-3 py-2 rounded-pill border border-opacity-25" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            <?php echo htmlspecialchars($est); ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo BASE_URL; ?>admin/exemplarView/<?php echo (int)$e['id']; ?>" class="action-icon-btn action-view" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                        <a href="<?php echo BASE_URL; ?>admin/exemplarEdit/<?php echo (int)$e['id']; ?>" class="action-icon-btn action-edit" title="Editar"><i class="fas fa-pen"></i></a>
                        <a href="<?php echo BASE_URL; ?>admin/exemplarDelete/<?php echo (int)$e['id']; ?>" class="action-icon-btn action-delete" title="Apagar"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>