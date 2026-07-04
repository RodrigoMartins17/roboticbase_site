<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
    .btn-danger-clean { background-color: #ef4444; color: white; border: none; border-radius: 10px; font-weight: 600; padding: 12px 28px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25); }
    .btn-danger-clean:hover { background-color: #dc2626; transform: translateY(-2px); color: white;}
    .btn-outline-clean { background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 600; padding: 12px 28px; transition: all 0.3s; text-decoration: none; display: inline-block; }
    .btn-outline-clean:hover { background-color: #f1f5f9; color: #0f172a; }
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Eliminar Categoria</h4>
            <a href="<?php echo BASE_URL; ?>admin/categorias" class="btn-outline-clean" style="padding: 8px 16px;">Voltar</a>
        </div>

        <?php if (!empty($mostrar_botao_forcar)): ?>
            <div class="clean-card p-5 text-center" style="border-top: 6px solid #f59e0b;">
                <div style="width: 80px; height: 80px; background-color: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #f59e0b; font-size: 2.2rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 style="color: #0f172a; font-weight: 800; margin-bottom: 12px;">Aviso de Associação</h4>
                <p style="color: #ef4444; font-weight: 600;"><?php echo htmlspecialchars($erro); ?></p>
                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 30px;">Se continuar, os materiais deixarão de pertencer a esta categoria, mas não serão eliminados da base de dados.</p>
                
                <form id="deleteFormForced" method="POST" action="<?php echo BASE_URL; ?>Categoria/delete/<?php echo (int)$categoria['id']; ?>" class="d-flex justify-content-center gap-3">
                    <input type="hidden" name="forcar" value="1">
                    <a href="<?php echo BASE_URL; ?>Categoria" class="btn-outline-clean">Cancelar</a>
                    <button type="submit" class="btn-danger-clean" id="btnDelete1"><i class="fas fa-trash-alt me-2"></i>Forçar Eliminação</button>
                </form>
            </div>
        <?php else: ?>
            <div class="clean-card p-5 text-center" style="border-top: 6px solid #ef4444;">
                <div style="width: 80px; height: 80px; background-color: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #ef4444; font-size: 2.2rem;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                
                <h3 style="color: #0f172a; font-weight: 800; margin-bottom: 16px;">Tem a certeza absoluta?</h3>
                <p style="color: #475569; font-size: 1.05rem; margin-bottom: 35px; line-height: 1.6;">
                    Está prestes a eliminar permanentemente a categoria <strong style="color: #0f172a; background-color: #f1f5f9; padding: 4px 10px; border-radius: 6px;"><?php echo htmlspecialchars($categoria['categoria'] ?? ''); ?></strong>.
                </p>
                
                <form id="deleteFormNormal" method="POST" action="<?php echo BASE_URL; ?>Categoria/delete/<?php echo (int)$categoria['id']; ?>" class="d-flex justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>Categoria" class="btn-outline-clean">Cancelar</a>
                    <button type="submit" class="btn-danger-clean" id="btnDelete2"><i class="fas fa-trash-alt me-2"></i>Sim, Eliminar</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const formForced = document.getElementById('deleteFormForced');
    if(formForced) {
        formForced.addEventListener('submit', function() {
            const btn = document.getElementById('btnDelete1');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>A apagar...';
            btn.style.pointerEvents = 'none';
        });
    }

    const formNormal = document.getElementById('deleteFormNormal');
    if(formNormal) {
        formNormal.addEventListener('submit', function() {
            const btn = document.getElementById('btnDelete2');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>A apagar...';
            btn.style.pointerEvents = 'none';
        });
    }
</script>