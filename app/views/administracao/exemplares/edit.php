<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Editar Exemplar</h5>
    <a href="<?php echo BASE_URL; ?>admin/exemplaresPorMaterial/<?php echo (int)$exemplar['id_material']; ?>" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo BASE_URL; ?>admin/exemplarUpdate/<?php echo (int)$exemplar['id']; ?>">
            <div class="mb-4">
                <label class="form-label fw-bold">Material Associado</label>
                <select name="id_material" class="form-select" required>
                    <?php foreach ($materiais as $m): ?>
                        <option value="<?php echo (int)$m['id']; ?>" <?php echo (int)$exemplar['id_material'] === (int)$m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['designacao'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nº Referência</label>
                    <input type="text" name="num_referencia" class="form-control" value="<?php echo htmlspecialchars($exemplar['num_referencia'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <?php foreach (['DISPONIVEL','EMPRESTADO','DANIFICADO','MANUTENCAO','PERDIDO','INDISPONIVEL'] as $est): ?>
                            <option value="<?php echo $est; ?>" <?php echo ($exemplar['estado'] ?? '') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label class="form-label fw-bold">Sala (Localização)</label>
                    <select name="id_sala" class="form-select">
                        <option value="0">-- Sem Sala --</option>
                            <?php if(!empty($salas)): ?>
                                <?php foreach($salas as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo (isset($exemplar['id_sala']) && $exemplar['id_sala'] == $s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['bloco'] . $s['andar'] . "." . $s['numero']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Observações</label>
                <textarea name="observacao" class="form-control" rows="3"><?php echo htmlspecialchars($exemplar['observacao'] ?? ''); ?></textarea>
            </div>
            
            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 fw-bold">Atualizar Exemplar</button>
            </div>
        </form>
    </div>
</div>