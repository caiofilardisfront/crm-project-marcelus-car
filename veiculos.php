<?php
// 1. Configurações e Segurança
require_once 'config/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<!-- 2. Cabeçalho Global (CSS) -->
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">
    <!-- 3. Menu Lateral (Sidebar) -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">

        <!-- 4. Navbar Superior -->
        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-4 py-3 sticky-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-sm-block text-light">Módulo de Estoque</h5>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white shadow-sm me-2" style="width: 40px; height: 40px; font-weight: bold;">
                        <?php echo substr($_SESSION['user_name'] ?? 'U', 0, 1); ?>
                    </div>
                    <span class="d-none d-md-inline fw-semibold">
                        <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name'] ?? 'Usuário'))[0]); ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary mt-2" aria-labelledby="userMenu">
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </nav>

        <!-- 5. Conteúdo Principal -->
        <div class="container-fluid p-4">

            <!-- Título e Botão de Ação -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-light mb-1">Veículos Disponíveis</h4>
                    <p class="text-muted small">Gerencie o estoque de carros e relacione-os aos leads.</p>
                </div>
                <!-- Botão que aciona o Modal de Cadastro -->
                <button class="btn btn-primary fw-bold shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modal-add-veiculo">
                    <i class="bi bi-plus-lg me-2"></i>Novo Veículo
                </button>
            </div>

            <!-- Tabela de Veículos -->
            <div class="card border-secondary shadow-lg">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead class="bg-dark">
                                <tr>
                                    <th class="border-secondary py-3 ps-4 text-muted small text-uppercase">Marca / Modelo</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Ano Fab/Mod</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Quilometragem</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Preço (R$)</th>
                                    <th class="border-secondary py-3 pe-4 text-end text-muted small text-uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-veiculos">
                                <!-- O JavaScript (veiculos.js) injetará as linhas aqui -->
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Buscando estoque...
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

<!-- 6. MODAL DE CADASTRO DE VEÍCULO -->
<div class="modal fade" id="modal-add-veiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-secondary shadow-lg text-light">

            <div class="modal-header border-bottom border-secondary p-4">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-car-front-fill fs-4 text-white"></i>
                    </div>
                    Adicionar Veículo ao Estoque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulário -->
            <form id="form-add-veiculo" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Marca *</label>
                            <input type="text" name="brand" class="form-control" placeholder="Ex: Toyota" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Modelo *</label>
                            <input type="text" name="model" class="form-control" placeholder="Ex: Corolla XEI" required>
                        </div>
                        <div class="mb-12">
                            <label for="vehicle_image" class="form-label text-muted small fw-bold">Foto do Veículo (Opcional)</label>
                            <input type="file" class="form-control bg-dark text-light border-secondary" id="vehicle_image" name="image" accept="image/*">
                            <div class="form-text text-muted" style="font-size: 0.75rem;">Formatos aceitos: JPG, PNG, WEBP. Máx: 2MB.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Ano Fab. *</label>
                            <input type="number" name="manufacture_year" class="form-control" placeholder="2020" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Ano Modelo *</label>
                            <input type="number" name="model_year" class="form-control" placeholder="2021" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Km Atual</label>
                            <input type="number" name="mileage" class="form-control" placeholder="35000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Preço (R$) *</label>
                            <input type="text" name="price" class="form-control mask-money" placeholder="0,00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary p-3 bg-dark">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> Salvar Veículo
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- 7. Rodapé Global (Importa Bootstrap e jQuery) -->
<?php include 'includes/footer.php'; ?>

<!-- O script que fará a mágica funcionar será criado na Tarefa 4 -->
<script src="<?php echo BASE_URL; ?>assets/js/veiculos.js"></script>