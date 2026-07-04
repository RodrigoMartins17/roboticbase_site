<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;}
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); color: white; }
    
    .table-clean th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; padding: 16px; border-bottom: 2px solid #e2e8f0; }
    .table-clean td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; font-weight: 500; }
    .table-clean tbody tr:hover { background-color: #f8fafc; }
    
    .action-icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.85rem; transition: 0.2s; color: white; margin-left: 4px; text-decoration: none;}
    .action-stock { background: #8b5cf6; } .action-stock:hover { background: #7c3aed; transform: scale(1.05); color: white;}
    .action-view { background: #3b82f6; } .action-view:hover { background: #2563eb; transform: scale(1.05); color: white;}
    .action-edit { background: #f59e0b; } .action-edit:hover { background: #d97706; transform: scale(1.05); color: white;}
    .action-delete { background: #ef4444; } .action-delete:hover { background: #dc2626; transform: scale(1.05); color: white;}
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-boxes-stacked text-primary me-2"></i>Gerir Materiais</h4>
    
    <a href="<?php echo BASE_URL; ?>admin/materialCreate" class="btn-clean-primary">
        <i class="fas fa-plus me-2"></i> Novo Material
    </a>
</div>

<?php
// Barra de filtros partilhada por todas as listas da administração
// (o formulário em si está em app/views/administracao/_filtros.php).
$filtrosAccao = 'admin/materiais';
$filtrosPlaceholder = 'Pesquisar por designação…';
$filtrosSelects = []; // esta lista só tem pesquisa por texto
include __DIR__ . '/../_filtros.php';
?>
<div class="clean-card">
    <div class="table-responsive">
        <?php if (empty($materiais)): ?>
            <div class="text-center py-5" style="color: #94a3b8;">
                <i class="fas fa-boxes-stacked mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                <h5>Nenhum material registado.</h5>
            </div>
        <?php else: ?>
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Designação do Material</th>
                    <th class="text-end pe-4" style="width: 200px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materiais as $m): ?>
                <tr>
                    <td class="ps-4"><?php echo htmlspecialchars($m['id']); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($m['imagem_src'])): ?>
                                <img src="<?php echo htmlspecialchars($m['imagem_src']); ?>" style="width: 36px; height: 36px; border-radius: 8px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 36px; height: 36px; border-radius: 8px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-box"></i></div>
                            <?php endif; ?>
                            <strong><?php echo htmlspecialchars($m['designacao']); ?></strong>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo BASE_URL; ?>admin/exemplaresPorMaterial/<?php echo (int)$m['id']; ?>" class="action-icon-btn action-stock" title="Ver Stock"><i class="fas fa-cubes"></i></a>
                        <a href="<?php echo BASE_URL; ?>admin/materialView/<?php echo (int)$m['id']; ?>" class="action-icon-btn action-view" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                        <a href="<?php echo BASE_URL; ?>admin/materialEdit/<?php echo (int)$m['id']; ?>" class="action-icon-btn action-edit" title="Editar"><i class="fas fa-pen"></i></a>
                        <a href="<?php echo BASE_URL; ?>admin/materialDelete/<?php echo (int)$m['id']; ?>" class="action-icon-btn action-delete" title="Apagar"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php
// Botões das páginas (partilhados — ver app/views/administracao/_paginacao.php).
include __DIR__ . '/../_paginacao.php';
?>
