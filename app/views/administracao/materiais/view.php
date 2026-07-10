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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes do Material</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/materialEdit/<?php echo (int)($material['id'] ?? 0); ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <a href="<?php echo BASE_URL; ?>admin/materialDelete/<?php echo (int)$material['id']; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                <i class="fas fa-trash-alt me-2"></i>Eliminar</a>
                <a href="<?php echo BASE_URL; ?>admin/materiais" class="btn-clean-outline">Voltar</a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5 pb-4 border-bottom" style="border-color: #e2e8f0 !important;">
                <?php if(!empty($material['imagem_src'])): ?>
                    <img src="<?php echo htmlspecialchars($material['imagem_src']); ?>" style="width: 140px; height: 140px; object-fit: contain; border-radius: 24px; border: 4px solid #f8fafc; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px;">
                <?php else: ?>
                    <div style="width: 140px; height: 140px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #94a3b8; font-size: 3rem; border: 1px solid #e2e8f0;">
                        <i class="fas fa-box"></i>
                    </div>
                <?php endif; ?>
                
                <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 12px;"><?php echo htmlspecialchars($material['designacao'] ?? ''); ?></h2>
                
                <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                    <span style="background-color: #e0e7ff; color: #4338ca; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                        ID: <?php echo (int)($material['id'] ?? 0); ?>
                    </span>
                    <span style="background-color: #f8fafc; color: #475569; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0;">
                        <?php echo htmlspecialchars($material['categorias'] ?? 'Sem Categoria'); ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-5">
                <h6 style="font-weight: 700; color: #334155; margin-bottom: 16px;"><i class="fas fa-info-circle text-primary me-2"></i>Descrição Técnica</h6>
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 24px; border-radius: 12px; color: #475569; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($material['descricao'] ?? 'Nenhuma descrição fornecida.')); ?>
                </div>
            </div>
            
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="font-weight: 700; color: #334155; margin-bottom: 0;"><i class="fas fa-cubes text-primary me-2"></i>Exemplares em Stock</h6>
                    <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; border: 1px solid #bbf7d0;">
                        <?php echo count($itens ?? []); ?> Total
                    </span>
                </div>
                
                <?php if (!empty($itens)): ?>
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <table class="table-clean mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Referência</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4" style="width: 100px;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $e): ?>
                                    <?php 
                                        $est = $e['estado'] ?? '';
                                        $bg = '#f1f5f9'; $color = '#475569'; $border = '#e2e8f0';
                                        if ($est === 'DISPONIVEL') { $bg = '#dcfce7'; $color = '#166534'; $border = '#bbf7d0'; }
                                        elseif ($est === 'EMPRESTADO') { $bg = '#e0f2fe'; $color = '#075985'; $border = '#bae6fd'; }
                                        elseif ($est === 'MANUTENCAO') { $bg = '#fef3c7'; $color = '#92400e'; $border = '#fde68a'; }
                                    ?>
                                    <tr>
                                        <td class="ps-4" style="font-family: monospace; font-weight: 700; color: #2563eb; letter-spacing: 0.5px;">
                                            <?php echo htmlspecialchars($e['num_referencia'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; border: 1px solid <?php echo $border; ?>;">
                                                <?php echo htmlspecialchars($est); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="<?php echo BASE_URL; ?>admin/exemplarView/<?php echo $e['id']; ?>" class="btn btn-sm" style="background-color: #f8fafc; color: #2563eb; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; transition: all 0.2s;">
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
                        <i class="fas fa-info-circle me-2"></i>Não existem exemplares registados.
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>admin/exemplaresPorMaterial/<?php echo (int)$material['id']; ?>" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 0.95rem; background-color: #e0e7ff; padding: 10px 24px; border-radius: 10px; display: inline-block; transition: all 0.2s;">
                        <i class="fas fa-list-ul me-2"></i>Gerir Stock deste Material
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>