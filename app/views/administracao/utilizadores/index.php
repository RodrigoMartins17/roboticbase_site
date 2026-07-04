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
    
    /* Badge Tipo Utilizador */
    .badge-tipo { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(0,0,0,0.05); text-transform: uppercase; letter-spacing: 0.5px; }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <h4 class="mb-0 fw-bold" style="color: #1e293b;"><i class="fas fa-users text-primary me-2"></i>Gerir Utilizadores</h4>
    <a href="<?php echo BASE_URL; ?>admin/utilizadorCreate" class="btn-clean-primary">
        <i class="fas fa-plus me-2"></i> Novo Utilizador
    </a>
</div>

<?php
// Barra de filtros partilhada por todas as listas da administração
// (o formulário em si está em app/views/administracao/_filtros.php).
$filtrosAccao = 'admin/utilizadores';
$filtrosPlaceholder = 'Pesquisar por nome ou email…';
$filtrosSelects = ['tipo' => ['label' => 'Tipo', 'opcoes' => $tiposUser ?? []]];
include __DIR__ . '/../_filtros.php';
?>
<div class="clean-card">
    <?php require_once __DIR__ . '/../../../models/Utilizador.php'; $utilizadorModel = new Utilizador(); ?>
    <div class="table-responsive">
        <?php if (empty($users)): ?>
            <div class="text-center py-5" style="color: #94a3b8;">
                <i class="fas fa-users mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                <h5>Nenhum utilizador registado.</h5>
            </div>
        <?php else: ?>
        <table class="table table-borderless table-clean mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;" class="ps-4">ID</th>
                    <th>Nome / Utilizador</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th class="text-end pe-4" style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php 
                        $tipo = $u['tipo'] ?? '';
                        $bg = '#f1f5f9'; $color = '#475569';
                        if ($tipo === 'ADMIN') { $bg = '#fee2e2'; $color = '#991b1b'; }
                        elseif ($tipo === 'PROFESSOR' || $tipo === 'RESPONSAVEL') { $bg = '#e0e7ff'; $color = '#3730a3'; }
                        elseif ($tipo === 'ALUNO') { $bg = '#dcfce7'; $color = '#166534'; }
                    ?>
                    <tr>
                        <td class="ps-4" style="color: #64748b; font-weight: 600;"><?php echo htmlspecialchars($u['id']); ?></td>
                        <td class="td-nome">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <strong><?php echo htmlspecialchars($u['nome'] ?? ''); ?></strong>
                            </div>
                        </td>
                        <td class="td-email"><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                        <td>
                        <span class="badge-tipo" style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                                <?php echo htmlspecialchars($tipo); ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="<?php echo BASE_URL; ?>admin/utilizadorView/<?php echo (int)$u['id']; ?>" class="action-icon-btn action-view" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo BASE_URL; ?>admin/utilizadorEdit/<?php echo (int)$u['id']; ?>" class="action-icon-btn action-edit" title="Editar"><i class="fas fa-pen"></i></a>
                            <?php if (($u['id'] ?? 0) != ($_SESSION['user']['id'] ?? 0)): ?>
                                <a href="<?php echo BASE_URL; ?>admin/utilizadorDelete/<?php echo (int)$u['id']; ?>" class="action-icon-btn action-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                            <?php endif; ?>
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
