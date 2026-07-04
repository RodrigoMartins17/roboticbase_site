<?php
require_once __DIR__ . '/../../../config/config.php';
?>

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
    max-width: 700px;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-header {
    margin-bottom: 35px;
    border-bottom: 1px solid var(--tech-border);
    padding-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.form-header h2 {
    margin: 0;
    color: #fff;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.025em;
}

.form-header i {
    color: var(--tech-accent);
    font-size: 1.5rem;
    background: rgba(59, 130, 246, 0.1);
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.form-group {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--tech-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-group input, 
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    background: #0f172a;
    border: 1px solid var(--tech-border);
    border-radius: 12px;
    color: #fff;
    outline: none;
    transition: all 0.2s;
    font-size: 0.95rem;
    color-scheme: dark;
}

.form-group input:focus, 
.form-group textarea:focus,
.form-group select:focus {
    border-color: var(--tech-accent);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 576px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 40px;
    padding-top: 25px;
    border-top: 1px solid var(--tech-border);
}

.btn-tech {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--tech-accent);
    color: white;
}

.btn-primary:hover {
    background: var(--tech-accent-hover);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -10px rgba(59, 130, 246, 0.5);
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
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<div class="form-wrapper">
    <div class="form-card">
        
        <div class="form-header">
            <i class="fas fa-calendar-plus"></i>
            <h2>Nova Requisição de Sala</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo BASE_URL; ?>admin/reqSalaStore" id="formSala">
            <div class="form-row">
                <div class="form-group">
                    <label>Utilizador</label>
                    <select name="id_utilizador" required>
                        <option value="">— Selecionar —</option>
                        <?php foreach ($utilizadores ?? [] as $u): ?>
                            <option value="<?php echo (int)$u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sala</label>
                    <select name="id_sala" required>
                        <option value="">— Selecionar —</option>
                        <?php foreach ($salas ?? [] as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars(trim($s['bloco']." ".$s['andar']." ".$s['numero'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Início da Reserva</label>
                    <input type="datetime-local" name="data_inicio" id="data_inicio" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                <div class="form-group">
                    <label>Fim da Reserva</label>
                    <input type="datetime-local" name="data_fim" id="data_fim" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Observação</label>
                <textarea name="observacao" rows="2" placeholder="Opcional..."></textarea>
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>admin/requisicoesSalas" class="btn-tech btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-tech btn-primary">
                    <i class="fas fa-check"></i> Confirmar Requisição
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');

    inputInicio.addEventListener('change', function() {
        if (inputInicio.value) {
            inputFim.min = inputInicio.value;
            if (inputFim.value && inputFim.value < inputInicio.value) {
                inputFim.value = inputInicio.value;
            }
        }
    });
});
</script>