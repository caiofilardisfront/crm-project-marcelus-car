<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'repositories/LeadRepository.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$totalLeads = countLeads($pdo);
?>
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">

    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">

        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3 sticky-top" style="height: 65px;">
            <div class="container-fluid p-0 d-flex align-items-center">

                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <span class="navbar-brand mb-0 h5 fw-bold d-none d-sm-block">Painel de Controle</span>
                </div>

                <div class="d-flex align-items-center ms-auto">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white me-2" style="width: 36px; height: 36px; font-size: 16px; font-weight: bold;">
                                <?php echo substr($_SESSION['user_name'] ?? 'U', 0, 1); ?>
                            </div>
                            <span class="d-none d-md-inline fw-semibold me-1">
                                <?php echo explode(' ', trim($_SESSION['user_name']))[0] ?? 'Usuário'; ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg mt-2 border-secondary" aria-labelledby="dropdownUser">
                            <li>
                                <div class="px-4 py-2"><strong class="d-block"><?php echo $_SESSION['user_name']; ?></strong><small class="text-muted">Administrador</small></div>
                            </li>
                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>
                            <li><a class="dropdown-item text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">

            <div class="mb-4">
                <h4 class="fw-bold text-light mb-1">Visão Geral</h4>
                <p class="text-muted small">Métricas de desempenho do funil de vendas.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0" style="border-bottom: 4px solid var(--brand-primary) !important;">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">Total Leads</h6>
                                <h2 class="fw-bold mb-0"><?php echo $totalLeads; ?></h2>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(37, 99, 235, 0.1);"><i class="bi bi-people text-primary fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0" style="border-bottom: 4px solid #f59e0b !important;">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">Negociação</h6>
                                <h2 class="fw-bold mb-0">12</h2>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1);"><i class="bi bi-chat-dots text-warning fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0" style="border-bottom: 4px solid #10b981 !important;">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">Fechados</h6>
                                <h2 class="fw-bold mb-0">8</h2>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1);"><i class="bi bi-check2-circle text-success fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0" style="border-bottom: 4px solid #ef4444 !important;">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">Perdidos</h6>
                                <h2 class="fw-bold mb-0">3</h2>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1);"><i class="bi bi-x-circle text-danger fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-secondary shadow-lg">
                <div class="card-header bg-transparent border-secondary p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="m-0 fw-bold text-light">Últimos Leads Cadastrados</h5>
                        <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modal-add-lead">
                            <i class="bi bi-plus-lg me-2"></i> Novo Lead
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead class="bg-dark">
                                <tr>
                                    <th class="border-secondary py-3 ps-4 text-muted small text-uppercase">Cliente</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Contato</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Veículo</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Status</th>
                                    <th class="border-secondary py-3 pe-4 text-end text-muted small text-uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-leads">
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        Nenhum lead encontrado no momento.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================
     MODAL DE CADASTRO DE NOVO LEAD (FORMULÁRIO RÁPIDO)
     ======================================================== -->
<div class="modal fade" id="modal-add-lead" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-secondary shadow-lg text-light">

            <div class="modal-header border-bottom border-secondary p-4">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-person-plus-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block fs-5 mb-0">Cadastrar Novo Lead</span>
                        <span class="d-block text-muted fw-normal" style="font-size: 0.85rem;">Preencha os dados básicos para iniciar o atendimento.</span>
                    </div>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulário com IDs e Names corretos para a nossa futura API -->
            <form id="form-add-lead">
                <div class="modal-body p-4">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Nome do Cliente *</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Ex: João da Silva" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Telefone / WhatsApp *</label>
                            <!-- A classe mask-phone já está mapeada no nosso app.js -->
                            <input type="text" name="customer_phone" class="form-control mask-phone" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">E-mail</label>
                            <input type="email" name="customer_email" class="form-control" placeholder="joao@email.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Origem do Contato *</label>
                            <!-- Como o banco exige a chave origin_id, usamos um select básico para o MVP -->
                            <select name="origin_id" class="form-select bg-dark text-light border-secondary" required>
                                <option value="" disabled selected>Selecione...</option>
                                <option value="1">WhatsApp</option>
                                <option value="2">Loja Física</option>
                                <option value="3">Site</option>
                                <option value="4">Indicação</option>
                            </select>
                        </div>


                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Veículo de Interesse (Estoque)</label>
                            <select name="vehicle_id" id="select-veiculos-add" class="form-select bg-dark text-light border-secondary">
                                <option value="">Selecione um veículo disponível...</option>
                                <!-- Populados via AJAX -->
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top border-secondary p-3 bg-dark">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> Salvar Lead
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ========================================================
     MODAL DE EDIÇÃO DO LEAD
======================================================== -->
<div class="modal fade" id="modal-edit-lead" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-secondary shadow-lg text-light">
            <div class="modal-header border-bottom border-secondary p-4">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-pencil-square fs-4 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block fs-5 mb-0">Editar Lead</span>
                        <span class="d-block text-muted fw-normal" style="font-size: 0.85rem;">Atualize os dados de contato ou interesse do cliente.</span>
                    </div>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-edit-lead">
                <!-- O ID oculto é crucial para o banco saber quem atualizar -->
                <input type="hidden" name="lead_id" id="edit_lead_id">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Nome do Cliente *</label>
                            <input type="text" name="customer_name" id="edit_customer_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Telefone / WhatsApp *</label>
                            <input type="text" name="customer_phone" id="edit_customer_phone" class="form-control mask-phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">E-mail</label>
                            <input type="email" name="customer_email" id="edit_customer_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Origem do Contato *</label>
                            <select name="origin_id" id="edit_origin_id" class="form-select bg-dark text-light border-secondary" required>
                                <option value="1">WhatsApp</option>
                                <option value="2">Loja Física</option>
                                <option value="3">Site</option>
                                <option value="4">Indicação</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Veículo Vinculado</label>
                            <select name="vehicle_id" id="select-veiculos-edit" class="form-select bg-dark text-light border-secondary">
                                <option value="">Sem veículo vinculado...</option>
                                <!-- Populados via AJAX -->
                            </select>
                            <input type="hidden" name="vehicle_interest" id="edit_vehicle_interest"> <!-- Mantemos o texto oculto para não quebrar o histórico -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary p-3 bg-dark">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center">
                        <i class="bi bi-save me-2 fs-5"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL DE CONFIRMAÇÃO DE EXCLUSÃO (ESTILO PREMIUM)
======================================================== -->
<div class="modal fade" id="modal-confirm-delete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content bg-dark border-secondary shadow-lg text-light">
            <div class="modal-body p-4 text-center">
                <!-- Ícone de Alerta Vibrante -->
                <div class="bg-danger bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                </div>
                
                <h5 class="fw-bold mb-2">Confirmar Exclusão?</h5>
                <p class="text-muted small mb-4">
                    Esta ação é permanente e removerá o lead e todo o seu histórico de conversas. Não será possível recuperar esses dados.
                </p>

                <!-- Botões de Ação -->
                <div class="d-grid gap-2">
                    <button type="button" id="btn-confirmar-exclusao-real" class="btn btn-danger fw-bold py-2">
                        <i class="bi bi-trash3 me-2"></i> Sim, Excluir Registro
                    </button>
                    <button type="button" class="btn btn-outline-secondary py-2" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/modals/lead_details_modal.php'; ?>
<?php include 'includes/footer.php'; ?>