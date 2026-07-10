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
            <h4 class="mb-0 fw-bold" style="color: #1e293b;">Adicionar Evento (Portfólio)</h4>
            <a href="<?php echo BASE_URL; ?>admin/eventos" class="btn btn-outline-secondary" style="border-radius: 10px;">Voltar</a>
        </div>

        <div class="clean-card p-4 p-md-5">
            <form id="createForm" method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>admin/eventoStore">
                <div class="mb-4">
                    <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Título do Evento</label>
                    <input type="text" name="titulo" class="form-control-clean" required placeholder="Ex: Participação no Robótica 2024">
                </div>
                
                <div class="mb-4">
                    <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Descrição</label>
                    <textarea name="descricao" class="form-control-clean" rows="4" placeholder="Detalhes do projeto ou evento..."></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">URL / Link (Opcional)</label>
                        <input type="url" name="url" class="form-control-clean" placeholder="https://...">
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Ordem no Site</label>
                        <input type="number" name="ordem" class="form-control-clean" min="0" value="0">
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-weight: 600; color: #475569; margin-bottom: 8px;">Imagem do Evento (Opcional)</label>
                    <input type="file" name="imagem_url" class="form-control-clean" accept="image/*" style="padding: 9px 16px;">
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo" value="1" checked>
                        <label class="form-check-label fs-6 ms-2 mt-1" for="ativoSwitch" style="font-weight: 600; color: #475569;">Visível no Site (Ativo)</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <!-- Evento afixado: fica sempre no topo da lista de eventos -->
                        <input class="form-check-input" type="checkbox" id="fixadoSwitch" name="fixado" value="1">
                        <label class="form-check-label fs-6 ms-2 mt-1" for="fixadoSwitch" style="font-weight: 600; color: #475569;">Afixar no topo (destaque)</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 border-top pt-4">
                    <button type="submit" class="btn-clean-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Guardar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>