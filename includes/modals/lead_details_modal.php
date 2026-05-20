<div class="modal fade" id="modal-lead" tabindex="-1" aria-labelledby="modalLeadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-secondary shadow-lg text-light">

            <div class="modal-header border-bottom border-secondary p-4">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalLeadLabel">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-person-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <span id="modal-nome-cliente" class="d-block fs-4 mb-1">Nome do Cliente</span>
                        <span class="d-block text-muted fw-normal" style="font-size: 0.85rem;" id="modal-data-criacao">
                            <i class="bi bi-calendar3 me-1"></i> Cadastrado em: --/--/----
                        </span>
                    </div>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-2" style="letter-spacing: 0.5px;">Contato do Cliente</h6>
                        <div class="card bg-transparent border-secondary p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-secondary bg-opacity-25 p-2 rounded me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-envelope text-primary fs-5"></i>
                                </div>
                                <div class="text-truncate">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">E-mail Principal</small>
                                    <span class="fw-semibold text-light text-break" id="modal-email-cliente">email@exemplo.com</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-25 p-2 rounded me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-telephone text-primary fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Telefone / WhatsApp</small>
                                    <span class="fw-semibold text-light" id="modal-telefone-cliente">(00) 00000-0000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-2" style="letter-spacing: 0.5px;">Detalhes do Negócio</h6>
                        <div class="card bg-transparent border-secondary p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-secondary bg-opacity-25 p-2 rounded me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-car-front text-primary fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Veículo de Interesse</small>
                                    <span class="fw-semibold text-light" id="modal-veiculo-interesse">A definir</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-25 p-2 rounded me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-tag text-primary fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Status Atual</small>
                                    <div id="modal-status-badge" class="mt-1">
                                        <span class="badge bg-secondary">Novo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="text-uppercase text-secondary fw-bold small mb-2" style="letter-spacing: 0.5px;">Observações Internas</h6>

                    <!-- NOVO: Formulário de Nova Anotação com Agendamento -->
                    <form id="form-add-note" class="mb-3">
                        <!-- Input invisível que o JS vai usar para saber de qual lead é essa anotação -->
                        <input type="hidden" name="lead_id" id="modal-note-lead-id">

                        <!-- TAREFA 1: Campo de Agendamento de Retorno -->
                        <div class="mb-2">
                            <label class="text-muted small fw-bold mb-1"><i class="bi bi-calendar-plus text-primary me-1"></i> Agendar Retorno (Opcional)</label>
                            <input type="datetime-local" name="next_contact_at" id="modal-note-date" class="form-control form-control-sm bg-dark text-light border-secondary" style="max-width: 250px; color-scheme: dark;">
                        </div>

                        <!-- Campo de Mensagem e Botão -->
                        <div class="input-group shadow-sm">
                            <textarea name="content" id="modal-note-content" class="form-control bg-dark text-light border-secondary" placeholder="Digite uma nova anotação e, se quiser, agende um retorno acima..." rows="2" required style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary d-flex align-items-center px-4" id="btn-save-note">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Container onde o JS injeta a timeline (Adicionamos barra de rolagem e altura máxima para não quebrar a tela) -->
                    <div class="p-3 border border-secondary rounded text-muted" id="modal-observacoes" style="font-size: 0.9rem; background-color: #121212 !important; min-height: 60px; max-height: 350px; overflow-y: auto;">
                        <!-- O loading premium foi movido para o dashboard.js, mas o container permanece aqui -->
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary p-3 bg-dark">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success fw-bold d-flex align-items-center" id="btn-whatsapp-lead">
                    <i class="bi bi-whatsapp me-2"></i> Iniciar Conversa
                </button>
            </div>

        </div>
    </div>
</div>