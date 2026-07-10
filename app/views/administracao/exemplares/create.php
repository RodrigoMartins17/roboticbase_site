<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Novo Exemplar</h5>
    <?php if (!empty($materialPre)): ?>
        <a href="<?php echo BASE_URL; ?>admin/exemplaresPorMaterial/<?php echo (int)$materialPre['id']; ?>" class="btn btn-outline-secondary">Voltar</a>
    <?php else: ?>
        <a href="<?php echo BASE_URL; ?>admin/exemplares" class="btn btn-outline-secondary">Voltar</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo BASE_URL; ?>admin/exemplarStore">
            <div class="mb-4">
                <label class="form-label fw-bold">Material Associado</label>
                <select name="id_material" class="form-select" required>
                    <option value="">— Selecionar Material —</option>
                    <?php foreach ($materiais as $m): ?>
                        <option value="<?php echo (int)$m['id']; ?>" <?php echo (!empty($materialPre) && (int)$materialPre['id'] === (int)$m['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['designacao'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nº Referência</label>
                    <input type="text" name="num_referencia" class="form-control" placeholder="Ex: REF-001 (Vazio para auto)">
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="DISPONIVEL">Disponível</option>
                        <option value="INDISPONIVEL">Indisponível (não requisitável)</option>
                        <option value="EMPRESTADO">Emprestado</option>
                        <option value="DANIFICADO">Danificado</option>
                        <option value="MANUTENCAO">Em Manutenção</option>
                        <option value="PERDIDO">Perdido</option>
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label class="form-label fw-bold">Sala (Localização)</label>
                    <select name="id_sala" class="form-select">
                        <option value="0">-- Sem Sala --</option>
                        <?php if(!empty($salas)): ?>
                            <?php foreach($salas as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['bloco'] . $s['andar'] . "." . $s['numero']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Observações</label>
                <textarea name="observacao" class="form-control" rows="3" placeholder="Detalhes opcionais sobre o estado do material..."></textarea>
            </div>
            
            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar Exemplar</button>
            </div>
        </form>
    </div>
</div>