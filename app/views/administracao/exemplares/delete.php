<?php require_once __DIR__ . '/../../../config/config.php'; ?>
<style>
:root {
    --tech-accent: #3b82f6;
    --tech-accent-hover: #2563eb;
    --tech-bg: #0f172a;
    --tech-card-bg: #1e293b;
    --tech-border: #334155;
    --tech-text: #f1f5f9;
    --tech-muted: #94a3b8;
    --tech-error: #ef4444;
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.6);
}

/* OVERRIDE LAYOUT DEFAULTS */
body, html, #page-content-wrapper, .main-container, .wrapper, .main-panel, #main-wrapper {
    background-color: var(--tech-bg) !important;
    color: var(--tech-text);
}

.container-fluid > .d-flex.border-bottom {
    border-bottom: 1px solid var(--tech-border) !important;
    background-color: var(--tech-bg) !important;
    margin-top: 0 !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}

.form-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 120px);
    padding: 40px 20px;
}

.form-card {
    background: var(--tech-card-bg);
    padding: 40px;
    border-radius: 24px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--tech-border);
    width: 100%;
    max-width: 600px;
    animation: fadeIn 0.6s ease-out;
    text-align: center;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.delete-icon {
    width: 80px;
    height: 80px;
    background: rgba(239, 68, 68, 0.1);
    color: var(--tech-error);
    font-size: 2.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    margin: 0 auto 24px auto;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.form-card h3 {
    color: #fff;
    font-weight: 800;
    margin-bottom: 16px;
    letter-spacing: -0.025em;
}

.form-card p {
    color: var(--tech-muted);
    font-size: 1.05rem;
    margin-bottom: 35px;
    line-height: 1.6;
}

.item-highlight {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
    padding: 4px 12px;
    border-radius: 8px;
    border: 1px solid var(--tech-border);
    font-weight: 700;
    font-family: monospace;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.btn-tech {
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-danger {
    background: var(--tech-error);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -10px rgba(239, 68, 68, 0.5);
}

.btn-cancel {
    background: var(--tech-border);
    color: var(--tech-text);
}

.btn-cancel:hover {
    background: #475569;
    color: #fff;
}

.error-msg {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    border: 1px solid rgba(239, 68, 68, 0.2);
    text-align: left;
}
</style>

<div class="form-wrapper">
    <div class="form-card" style="border-top: 6px solid var(--tech-error);">
        
        <div class="delete-icon">
            <i class="fas fa-barcode"></i>
        </div>

        <h3>Confirmar Eliminação</h3>

        <?php if (!empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <p>
            Está prestes a eliminar permanentemente o exemplar <span class="item-highlight"><?php echo htmlspecialchars($exemplar['num_referencia'] ?? ''); ?></span>.
            Esta ação não pode ser desfeita.
        </p>
        
        <form id="deleteFormExemplar" method="POST" action="<?php echo BASE_URL; ?>admin/exemplarDestroy/<?php echo (int)$exemplar['id']; ?>">
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>admin/exemplares" class="btn-tech btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-tech btn-danger" id="btnDeleteExemplar">
                    <i class="fas fa-trash-alt"></i> Sim, Eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formDeleteExemplar = document.getElementById('deleteFormExemplar');
    if(formDeleteExemplar) {
        formDeleteExemplar.addEventListener('submit', function() {
            const btn = document.getElementById('btnDeleteExemplar');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>A apagar...';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.7';
        });
    }
});
</script>
