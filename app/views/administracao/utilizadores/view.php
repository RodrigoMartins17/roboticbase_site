<?php
$u = $utilizador ?? [];
$id = (int)($u['id'] ?? 0);
$tipoUser = strtoupper($u['tipo'] ?? 'ALUNO');

// Cores para o tipo de utilizador
$bgTipo = '#f1f5f9'; $colorTipo = '#475569';
if ($tipoUser === 'ADMIN') { $bgTipo = '#fee2e2'; $colorTipo = '#991b1b'; }
elseif (in_array($tipoUser, ['PROFESSOR', 'RESPONSAVEL'])) { $bgTipo = '#e0e7ff'; $colorTipo = '#3730a3'; }
elseif ($tipoUser === 'ALUNO') { $bgTipo = '#dcfce7'; $colorTipo = '#166534'; }

// Função para cores do estado das requisições (igual às outras vistas)
$getBadgeStyle = function ($st) {
    $st = strtoupper($st);
    if (in_array($st, ['PENDENTE', 'AGUARDA'])) return ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fde68a'];
    if (in_array($st, ['ACEITE', 'APROVADO', 'CONCLUIDO', 'ENTREGUE'])) return ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'];
    if (in_array($st, ['REJEITADO', 'RECUSADO', 'CANCELADO'])) return ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca'];
    return ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'];
};
?>

<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .btn-clean-outline { background-color: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 10px 24px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; }
    .btn-clean-outline:hover { background-color: #f1f5f9; color: #0f172a; }
    
    .table-clean { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-clean th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; color: #64748b; font-weight: 700; padding: 16px; border-bottom: 2px solid #e2e8f0; text-align: left; letter-spacing: 0.5px; }
    .table-clean td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .table-clean tbody tr:hover { background-color: #f8fafc; }
    
    .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px 20px; border-radius: 12px; height: 100%; }
    .info-label { font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 4px; display: block; letter-spacing: 0.5px;}
    .info-value { font-size: 1.05rem; color: #1e293b; font-weight: 600; margin-bottom: 0; }
</style>

<div class="row justify-content-center">
    <div class="col-md-9">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Perfil do Utilizador</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>admin/utilizadorEdit/<?php echo (int)$utilizador['id']; ?>" class="btn-clean-outline" style="background-color: #fffbeb; color: #d97706; border-color: #fde68a;">
                    <i class="fas fa-pen me-2"></i>Editar
                </a>
                <?php if ($id != ($_SESSION['user']['id'] ?? 0)): ?>
                    <a href="<?php echo BASE_URL; ?>admin/utilizadorDelete/<?php echo $id; ?>" class="btn-clean-outline" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;" title="Apagar">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar</a>  
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>admin/utilizadores" class="btn-clean-outline">
                    Voltar
                </a>
            </div>
        </div>

        <div class="clean-card p-4 p-md-5 mb-4">
            <?php 
                require_once __DIR__ . '/../../../models/Utilizador.php'; 
                $utilizadorModel = new Utilizador(); 
            ?>
            
            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4 mb-5 pb-4 border-bottom" style="border-color: #e2e8f0 !important;">
                <img src="<?php echo $utilizadorModel->getAvatarUrl($u); ?>" alt="Avatar" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #f8fafc; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <div class="text-center text-sm-start mt-2 mt-sm-0">
                    <h3 style="color: #0f172a; font-weight: 800; margin-bottom: 6px;"><?php echo htmlspecialchars($u['nome'] ?? 'Sem Nome'); ?></h3>
                    <p style="color: #64748b; font-size: 1.05rem; margin-bottom: 12px;"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($u['email'] ?? '—'); ?></p>
                    
                    <span style="background-color: <?php echo $bgTipo; ?>; color: <?php echo $colorTipo; ?>; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(0,0,0,0.05); letter-spacing: 0.5px;">
                        <?php echo $tipoUser; ?>
                    </span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="info-box">
                        <span class="info-label"><i class="fas fa-id-badge me-2"></i>Registo ID</span>
                        <p class="info-value">#<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>
                
                <?php if (!empty($u['telefone'])): ?>
                <div class="col-6 col-md-4">
                    <div class="info-box">
                        <span class="info-label"><i class="fas fa-phone me-2"></i>Telefone</span>
                        <p class="info-value"><?php echo htmlspecialchars($u['telefone']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($u['turma'])): ?>
                <div class="col-6 col-md-4">
                    <div class="info-box">
                        <span class="info-label"><i class="fas fa-users me-2"></i>Turma</span>
                        <p class="info-value"><?php echo htmlspecialchars($u['turma']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($u['data_nascimento'])): ?>
                <div class="col-6 col-md-4">
                    <div class="info-box">
                        <span class="info-label"><i class="fas fa-calendar-alt me-2"></i>Nascimento</span>
                        <p class="info-value"><?php echo date('d/m/Y', strtotime($u['data_nascimento'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($u['linkedin'])): ?>
                <div class="col-12 col-md-8">
                    <div class="info-box">
                        <span class="info-label"><i class="fab fa-linkedin me-2 text-primary"></i>LinkedIn</span>
                        <a href="<?php echo htmlspecialchars($u['linkedin']); ?>" target="_blank" style="font-weight: 600; color: #2563eb; text-decoration: none; word-break: break-all; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($u['linkedin']); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>