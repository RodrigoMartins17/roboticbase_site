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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes da Sala</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/salaEdit/<?php echo (int)$sala['id']; ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;"><i class="fas fa-pen me-2"></i>Editar</a>
                <a href="<?php echo BASE_URL; ?>admin/salaDelete/<?php echo (int)$sala['id']; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                <i class="fas fa-trash-alt me-2"></i>Eliminar</a>                
                <a href="<?php echo BASE_URL; ?>admin/salas" class="btn-clean-outline">Voltar</a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5">
                <div style="width: 140px; height: 140px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #94a3b8; font-size: 3rem; border: 1px solid #e2e8f0;"><i class="fas fa-door-open"></i></div>
                
                <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 8px;">Sala <?php echo htmlspecialchars(($sala['bloco'] ?? '') . ($sala['andar'] ?? '') . '.' . ($sala['numero'] ?? '')); ?></h2>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <span style="background-color: #e0e7ff; color: #4338ca; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">Registo ID: <?php echo (int)($sala['id'] ?? 0); ?></span>
                </div>
            </div>
            
            <table class="table-clean w-100">
                <tbody>
                    <tr>
                        <th style="width: 160px; border-top: 1px solid #e2e8f0;">Bloco</th>
                        <td style="border-top: 1px solid #f1f5f9; font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($sala['bloco'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Andar</th>
                        <td style="font-weight: 700; color: #0f172a;"><?php echo (int)($sala['andar'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <th>Número</th>
                        <td style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($sala['numero'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Capacidade</th>
                        <td>
                            <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; border: 1px solid #bbf7d0;">
                                <?php echo (int)($sala['capacidade'] ?? 0); ?> pessoas
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: top;">Descrição</th>
                        <td style="line-height: 1.6; color: #475569;">
                            <?php echo ($sala['descricao'] ?? '') !== '' ? nl2br(htmlspecialchars($sala['descricao'])) : 'Nenhuma descrição fornecida.'; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>