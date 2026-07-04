<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .form-control-clean { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px; font-size: 0.95rem; width: 100%; outline: none; transition: 0.3s; }
    .form-control-clean:focus { background-color: #ffffff; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
    .btn-clean-primary { background-color: #2563eb; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 24px; transition: 0.3s; }
    .btn-clean-primary:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2); }
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Editar Evento</h4>
            <a href="<?php echo BASE_URL; ?>admin/eventos" class="btn btn-outline-secondary" style="border-radius: 10px;">Voltar</a>
        </div>

        <div class="clean-card p-4 p-md-5">
            <form id="editForm" method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>admin/eventoUpdate/<?php echo (int)$evento['id']; ?>">
                <div class="mb-4">
                    <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Título do Evento</label>
                    <input type="text" name="titulo" class="form-control-clean" required value="<?php echo htmlspecialchars($evento['titulo'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Descrição</label>
                    <textarea name="descricao" class="form-control-clean" rows="4"><?php echo htmlspecialchars($evento['descricao'] ?? ''); ?></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">URL / Link</label>
                        <input type="url" name="url" class="form-control-clean" value="<?php echo htmlspecialchars($evento['url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Ordem no Site</label>
                        <input type="number" name="ordem" class="form-control-clean" min="0" value="<?php echo htmlspecialchars($evento['ordem'] ?? 0); ?>">
                    </div>
                </div>

                <div class="row mb-4 align-items-end pt-3 border-top" style="border-color: #e2e8f0 !important;">
                    <div class="col-md-3">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Imagem Atual</label>
                        <?php if (!empty($evento['imagem_src'])): ?>
                            <img src="<?php echo htmlspecialchars($evento['imagem_src']); ?>" style="height: 80px; width: 80px; object-fit: cover; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <?php else: ?>
                            <div style="height: 80px; width: 80px; border-radius: 12px; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; color: #94a3b8; border: 1px dashed #cbd5e1; font-size: 0.8rem;">Sem foto</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9 mt-3 mt-md-0">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Substituir Imagem</label>
                        <input type="file" name="imagem_url" class="form-control-clean" accept="image/*" style="padding: 9px 16px;">
                    </div>
                </div>

                <div class="mb-4 pt-2">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo" value="1" <?php echo ($evento['ativo'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label fs-6 ms-2 mt-1" for="ativoSwitch" style="font-weight: 600; color: #475569;">Visível no Site (Ativo)</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 border-top pt-4">
                    <button type="submit" class="btn-clean-primary">
                        <i class="fas fa-save me-2"></i>Atualizar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>