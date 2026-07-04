<?php
$r = $requisicao ?? [];
$id = (int)($r['id'] ?? 0);
$estado = $r['estado_sala'] ?? '';
$salaLabel = ($r['bloco'] ?? '') . ($r['andar'] ?? '') . '.' . str_pad($r['sala_numero'] ?? '', 2, '0', STR_PAD_LEFT);
$salaLabel = $salaLabel !== '.' ? $salaLabel : '—';

// Função auxiliar para cores do estado (mesmo padrão)
$getBadgeStyle = function ($st) {
    if ($st === 'PENDENTE') return ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fde68a'];
    if ($st === 'ACEITE') return ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'];
    if ($st === 'EM_USO') return ['bg' => '#e0f2fe', 'text' => '#075985', 'border' => '#bae6fd'];
    if ($st === 'REJEITADO') return ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca'];
    if ($st === 'CONCLUIDO') return ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'];
    return ['bg' => '#ffffff', 'text' => '#000000', 'border' => '#e2e8f0'];
};
$style = $getBadgeStyle($estado);
// Etiquetas amigáveis (o valor guardado na BD mantém-se igual).
$estadoLabels = ['PENDENTE' => 'Pendente', 'ACEITE' => 'Aceite', 'EM_USO' => 'Em uso', 'REJEITADO' => 'Rejeitado', 'CONCLUIDO' => 'Devolvido'];
$estadoLabel = $estadoLabels[$estado] ?? $estado;
?>

<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 10px 24px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; }
    .btn-clean-outline:hover { background-color: #f1f5f9; color: #0f172a; }
    
    .info-label { font-weight: 700; color: #64748b; margin-bottom: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px 20px; border-radius: 12px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    
    .btn-workflow { border-radius: 10px; font-weight: 600; padding: 12px 28px; transition: all 0.3s; display: inline-flex; align-items: center; text-decoration: none; }
    .btn-workflow:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Detalhes da Requisição</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/reqSalaEdit/<?php echo $id; ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <a href="<?php echo BASE_URL; ?>admin/reqSalaDelete/<?php echo $id;?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                <i class="fas fa-trash-alt me-2"></i>Eliminar</a>
                <a href="<?php echo BASE_URL; ?>admin/requisicoesSalas" class="btn-clean-outline">
                    Voltar
                </a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-5">
            <div class="text-center mb-5 pb-4 border-bottom" style="border-color: #e2e8f0 !important;">
                <div style="width: 140px; height: 140px; background-color: #f8fafc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #2563eb; font-size: 3.5rem; border: 1px solid #e2e8f0;">
                    <i class="fas fa-door-open"></i>
                </div>
                
                <h2 style="color: #0f172a; font-family: monospace; font-weight: 800; margin-bottom: 12px; letter-spacing: 1px;">
                    REQ-#<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?>
                </h2>
                
                <div class="d-flex justify-content-center mt-3">
                    <span style="background-color: <?php echo $style['bg']; ?>; color: <?php echo $style['text']; ?>; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid <?php echo $style['border']; ?>; letter-spacing: 0.5px;">
                        ESTADO: <?php echo htmlspecialchars($estadoLabel); ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-2">
                <h6 style="font-weight: 700; color: #334155; margin-bottom: 16px;"><i class="fas fa-info-circle text-primary me-2"></i>Informação do Pedido</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <h6 class="info-label">Utilizador</h6>
                            <span style="font-weight: 700; color: #1e293b; font-size: 1.05rem;"><?php echo htmlspecialchars($r['utilizador_nome'] ?? '—'); ?></span>
                            <span style="color: #64748b; font-size: 0.9rem; margin-top: 2px;"><?php echo htmlspecialchars($r['utilizador_email'] ?? '—'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <h6 class="info-label">Sala Requisitada</h6>
                            <span style="font-weight: 700; color: #1e293b; font-size: 1.05rem;"><?php echo htmlspecialchars($salaLabel); ?></span>
                            <span style="color: #2563eb; font-family: monospace; font-weight: 600; font-size: 0.9rem; margin-top: 2px;">Espaço</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <h6 class="info-label">Data e Hora de Início</h6>
                            <span style="font-weight: 600; color: #334155; font-size: 1.05rem;">
                                <?php echo !empty($r['data_inicio']) && ($ts = strtotime($r['data_inicio'])) !== false ? date('d/m/Y \à\s H:i', $ts) : '—'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <h6 class="info-label">Data e Hora de Fim</h6>
                            <span style="font-weight: 600; color: #334155; font-size: 1.05rem;">
                                <?php echo !empty($r['data_fim']) && ($ts = strtotime($r['data_fim'])) !== false ? date('d/m/Y \à\s H:i', $ts) : '—'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($r['observacao'])): ?>
                    <div class="mb-4">
                        <h6 style="font-weight: 700; color: #334155; margin-bottom: 12px;"><i class="fas fa-comment-dots text-primary me-2"></i>Observações</h6>
                        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; color: #475569; line-height: 1.6; font-style: italic;">
                            <?php echo nl2br(htmlspecialchars($r['observacao'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (in_array($estado, ['PENDENTE', 'ACEITE', 'EM_USO'])): ?>
                <div class="d-flex flex-wrap justify-content-center gap-3 pt-4 mt-4 border-top" style="border-color: #e2e8f0 !important;">

                    <?php if ($estado === 'PENDENTE'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/reqSalaAceitar/<?php echo $id; ?>" class="btn-workflow" style="background-color: #10b981; color: white; border: 1px solid #059669;" onclick="return confirm('Confirmar aprovação deste pedido de sala?');">
                            <i class="fas fa-check-circle me-2"></i>Aprovar Pedido
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/reqSalaRejeitar/<?php echo $id; ?>" class="btn-workflow" style="background-color: #ef4444; color: white; border: 1px solid #dc2626;" onclick="return confirm('Rejeitar esta requisição?');">
                            <i class="fas fa-times-circle me-2"></i>Rejeitar Pedido
                        </a>
                    <?php elseif ($estado === 'ACEITE'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/reqSalaEntregar/<?php echo $id; ?>" class="btn-workflow" style="background-color: #0ea5e9; color: white; border: 1px solid #0284c7;" onclick="return confirm('Marcar sala como entregue?');">
                            <i class="fas fa-door-open me-2"></i>Marcar entregue à turma
                        </a>
                    <?php elseif ($estado === 'EM_USO'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/reqSalaDevolver/<?php echo $id; ?>" class="btn-workflow" style="background-color: #16a34a; color: white; border: 1px solid #15803d;">
                            <i class="fas fa-undo-alt me-2"></i>Marcar devolvido ao clube
                        </a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>
    </div>
</div>