<style>
    .clean-card { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); }
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Eliminar Evento</h4>
        </div>

        <div class="clean-card p-5 text-center" style="border-top: 6px solid #ef4444;">
            <div style="width: 80px; height: 80px; background-color: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto; color: #ef4444; font-size: 2.2rem;">
                <i class="fas fa-trash-alt"></i>
            </div>
            
            <h3 style="color: #0f172a; font-weight: 800; margin-bottom: 16px;">Confirmar Eliminação</h3>
            <p style="color: #475569; font-size: 1.05rem; margin-bottom: 35px;">
                Deseja eliminar o evento <strong style="color: #0f172a; background-color: #f1f5f9; padding: 4px 10px; border-radius: 6px;"><?php echo htmlspecialchars($evento['titulo'] ?? ''); ?></strong>?
            </p>
            
            <form method="POST" action="<?php echo BASE_URL; ?>admin/eventoDestroy/<?php echo (int)$evento['id']; ?>" class="d-flex justify-content-center gap-3">
                <a href="<?php echo BASE_URL; ?>admin/eventos" class="btn btn-outline-secondary px-4 fw-bold" style="border-radius: 10px;">Cancelar</a>
                <button type="submit" class="btn btn-danger px-4 fw-bold" style="border-radius: 10px;">
                    <i class="fas fa-trash-alt me-2"></i>Sim, Eliminar
                </button>
            </form>
        </div>
    </div>
</div>