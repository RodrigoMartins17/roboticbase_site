<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 10px 24px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; }
    .btn-clean-outline:hover { background-color: #f1f5f9; color: #0f172a; }
    .table-clean { width: 100%; }
    .table-clean th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: #64748b; font-weight: 600; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .table-clean td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; }
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
        
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes da Categoria</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/categoriaEdit/<?php echo (int)($categoria['id'] ?? 0); ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <a href="<?php echo BASE_URL; ?>admin/categoriaDelete/<?php echo (int)$categoria['id']; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar</a>
                <a href="<?php echo BASE_URL; ?>admin/categorias" class="btn-clean-outline">Voltar</a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5 pb-4 border-bottom" style="border-color: #e2e8f0 !important;">
                <?php if(!empty($categoria['imagem_src'])): ?>
                    <img src="<?php echo htmlspecialchars($categoria['imagem_src']); ?>" style="width: 140px; height: 140px; object-fit: cover; border-radius: 24px; border: 4px solid #f8fafc; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px;">
                <?php else: ?>
                    <div style="width: 140px; height: 140px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #94a3b8; font-size: 3rem; border: 1px solid #e2e8f0;">
                        <i class="fas fa-tag"></i>
                    </div>
                <?php endif; ?>
                
                <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 12px;"><?php echo htmlspecialchars($categoria['categoria'] ?? ''); ?></h2>
                
                <div class="d-flex justify-content-center mt-3">
                    <span style="background-color: #e0e7ff; color: #4338ca; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                        Registo ID: <?php echo (int)($categoria['id'] ?? 0); ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-2">
                <h6 style="font-weight: 700; color: #334155; margin-bottom: 16px;"><i class="fas fa-boxes text-primary me-2"></i>Materiais Associados</h6>
                
                <?php if (!empty($materiais)): ?>
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <table class="table-clean mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Designação</th>
                                    <th class="text-end" style="width: 100px;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materiais as $m): ?>
                                    <tr>
                                        <td class="fw-medium text-muted">#<?php echo htmlspecialchars($m['id']); ?></td>
                                        <td style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($m['designacao']); ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?php echo BASE_URL; ?>admin/materialView/<?php echo $m['id']; ?>" class="btn btn-sm" style="background-color: #f8fafc; color: #2563eb; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; transition: all 0.2s;">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 24px; border-radius: 12px; text-align: center; color: #64748b; font-weight: 500;">
                        <i class="fas fa-info-circle me-2"></i>Nenhum material associado a esta categoria.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>