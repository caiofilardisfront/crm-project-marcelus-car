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
                                <h2 class="fw-bold mb-0" id="kpi-total-leads">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                </h2>
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
                                <h2 class="fw-bold mb-0" id="kpi-em-negociacao">
                                    <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                                </h2>
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
                                <h2 class="fw-bold mb-0" id="kpi-fechados">
                                    <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                                </h2>
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
                                <h2 class="fw-bold mb-0" id="kpi-perdidos">
                                    <span class="spinner-border spinner-border-sm text-danger" role="status"></span>
                                </h2>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1);"><i class="bi bi-x-circle text-danger fs-4"></i></div>
                        </div>
                    </div>
                </div>

            </div>

            
            <!-- ========================================================
     TAREFA 1: GRÁFICO DE PERFORMANCE DE VENDAS
======================================================== -->
            <div class="card border-secondary shadow-lg mt-5">

                <!-- Cabeçalho do Gráfico com Filtro de Tempo -->
                <div class="card-header bg-transparent border-secondary p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <h5 class="m-0 fw-bold text-light">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Performance de Vendas
                    </h5>

                    <!-- Seletor de Período (Semanal, Mensal, Anual) -->
                    <select id="filtro-tempo-grafico" class="form-select form-select-sm bg-dark text-light border-secondary shadow-none" style="width: auto; cursor: pointer;">
                        <option value="weekly">Últimos 7 dias</option>
                        <option value="monthly" selected>Últimos 30 dias</option>
                        <option value="yearly">Este Ano</option>
                    </select>
                </div>

                <!-- Corpo do Gráfico -->
                <div class="card-body p-4" style="position: relative; height: 350px; width: 100%;">
                    <!-- A "tela de pintura" do Chart.js -->
                    <canvas id="grafico-performance"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include 'includes/modals/lead_details_modal.php'; ?>
<?php include 'includes/footer.php'; ?>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>