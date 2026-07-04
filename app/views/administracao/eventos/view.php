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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes do Evento</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/eventoEdit/<?php echo (int)$evento['id']; ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <a href="<?php echo BASE_URL; ?>admin/eventoDelete/<?php echo (int)$evento['id']; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                <i class="fas fa-trash-alt me-2"></i>Eliminar</a>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/eventos" class="btn-clean-outline">Voltar</a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5">
                <?php if(!empty($evento['imagem_src'])): ?>
                    <img src="<?php echo htmlspecialchars($evento['imagem_src']); ?>" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 20px; border: 4px solid #f8fafc; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px;">
                <?php else: ?>
                    <div style="width: 140px; height: 140px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #94a3b8; font-size: 3rem; border: 1px solid #e2e8f0;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                <?php endif; ?>
                
                <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 12px;"><?php echo htmlspecialchars($evento['titulo'] ?? ''); ?></h2>
                
                <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                    <span style="background-color: #e0e7ff; color: #4338ca; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                        Registo ID: <?php echo (int)($evento['id'] ?? 0); ?>
                    </span>
                    <span style="background-color: #f8fafc; color: #475569; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0;">
                        Ordem: #<?php echo htmlspecialchars($evento['ordem'] ?? 0); ?>
                    </span>
                    
                    <?php if(isset($evento['ativo']) && $evento['ativo'] == 1): ?>
                        <span style="background-color: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid #bbf7d0;">ATIVO</span>
                    <?php else: ?>
                        <span style="background-color: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid #cbd5e1;">INATIVO</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="table-clean w-100">
                <tbody>
                    <tr>
                        <th style="width: 160px; border-top: 1px solid #e2e8f0;">Título</th>
                        <td style="border-top: 1px solid #f1f5f9; font-weight: 700; color: #0f172a;">
                            <?php echo htmlspecialchars($evento['titulo'] ?? ''); ?>
                        </td>
                    </tr>
                    <?php if (!empty($evento['url'])): ?>
                    <tr>
                        <th>Link / Website</th>
                        <td>
                            <a href="<?php echo htmlspecialchars($evento['url']); ?>" target="_blank" style="color: #2563eb; font-weight: 600; text-decoration: none;">
                                <i class="fas fa-external-link-alt me-2"></i>Link Externo
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th style="vertical-align: top;">Descrição</th>
                        <td style="line-height: 1.6; color: #475569;">
                            <?php echo (!empty($evento['descricao'])) ? nl2br(htmlspecialchars($evento['descricao'])) : '<em>Nenhuma descrição fornecida.</em>'; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>