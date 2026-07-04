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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes do Exemplar</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/exemplarEdit/<?php echo (int)($exemplar['id'] ?? 0); ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <a href="<?php echo BASE_URL; ?>admin/exemplarDelete/<?php echo (int)$exemplar['id']; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                <i class="fas fa-trash-alt me-2"></i>Eliminar</a>
                <a href="<?php echo BASE_URL; ?>admin/exemplaresPorMaterial/<?php echo (int)$exemplar['id_material']; ?>" class="btn-clean-outline">
                    Voltar
                </a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5 pb-4 border-bottom" style="border-color: #e2e8f0 !important;">
                <div style="width: 120px; height: 120px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #2563eb; font-size: 3rem; border: 1px solid #e2e8f0;">
                    <i class="fas fa-barcode"></i>
                </div>
                
                <h2 style="color: #0f172a; font-family: monospace; font-weight: 800; margin-bottom: 12px; letter-spacing: 1px;">
                    <?php echo htmlspecialchars($exemplar['num_referencia'] ?? ''); ?>
                </h2>
                
                <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                    <span style="background-color: #e0e7ff; color: #2563eb; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                        ID Interno: <?php echo (int)($exemplar['id'] ?? 0); ?>
                    </span>
                    
                    <?php 
                        $est = $exemplar['estado'] ?? '';
                        $bg = '#f1f5f9'; $color = '#475569'; $border = '#e2e8f0';
                        if ($est === 'DISPONIVEL') { $bg = '#dcfce7'; $color = '#166534'; $border = '#bbf7d0'; }
                        elseif ($est === 'EMPRESTADO') { $bg = '#e0f2fe'; $color = '#075985'; $border = '#bae6fd'; }
                        elseif ($est === 'DANIFICADO') { $bg = '#fef3c7'; $color = '#92400e'; $border = '#fde68a'; }
                        elseif ($est === 'PERDIDO') { $bg = '#fee2e2'; $color = '#991b1b'; $border = '#fecaca'; }
                    ?>
                    <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid <?php echo $border; ?>;">
                        <?php echo htmlspecialchars($est); ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-5">
                <h6 style="font-weight: 700; color: #334155; margin-bottom: 16px;"><i class="fas fa-link text-primary me-2"></i>Associações do Exemplar</h6>
                <table class="table-clean w-100" style="border: 1px solid #e2e8f0; border-radius: 12px; border-collapse: separate; overflow: hidden;">
                    <tbody>
                        <tr>
                            <th style="width: 200px; border-top: none;">Material Associado</th>
                            <td style="border-top: none;">
                                <a href="<?php echo BASE_URL; ?>admin/materialView/<?php echo (int)$exemplar['id_material']; ?>" style="color: #2563eb; font-weight: 600; text-decoration: none; transition: 0.2s;">
                                    <i class="fas fa-box me-2"></i><?php echo htmlspecialchars($material['designacao'] ?? 'Ver Material'); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Sala (Localização)</th>
                            <td>
                                <?php if(!empty($exemplar['nome_sala'])): ?>
                                    <span style="font-weight: 600; color: #0f172a;"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($exemplar['nome_sala']); ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-style: italic;"><i class="fas fa-map-marker-alt me-2"></i>Nenhuma sala associada.</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <h6 style="font-weight: 700; color: #334155; margin-bottom: 16px;"><i class="fas fa-clipboard-list text-primary me-2"></i>Observações</h6>
                <?php if (!empty($exemplar['observacao'])): ?>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 24px; border-radius: 12px; color: #475569; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($exemplar['observacao'])); ?>
                    </div>
                <?php else: ?>
                    <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 24px; border-radius: 12px; text-align: center; color: #64748b; font-style: italic;">
                        Sem observações registadas.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>