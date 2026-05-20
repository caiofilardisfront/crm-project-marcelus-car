<?php
// 1. Configurações e Segurança base
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'repositories/LeadRepository.php';

// Trava: só acessa se estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>

<!-- 2. Cabeçalho Global (Traz nosso CSS e fontes) -->
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">

    <!-- 3. Menu Lateral -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">

        <!-- 4. Navbar Superior -->
        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-4 py-3 sticky-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-sm-block text-light">
                    <i class="bi bi-calendar-check-fill text-primary me-2"></i>Agenda de Retornos
                </h5>
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
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary mt-2">
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </nav>

        <!-- 5. Conteúdo Principal: A Lista de Follow-up -->
        <div class="container-fluid p-4">
            <div class="mb-4">
                <h4 class="fw-bold text-light mb-1">Meus Retornos</h4>
                <p class="text-muted small">Clientes que necessitam de contato urgente ou que estão agendados.</p>
            </div>

            <!-- Tabela Focada em Agenda -->
            <div class="card border-secondary shadow-lg">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead class="bg-dark">
                                <tr>
                                    <th class="border-secondary py-3 ps-4 text-muted small text-uppercase">Cliente</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Contato</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Agendado Para</th>
                                    <th class="border-secondary py-3 text-muted small text-uppercase">Status</th>
                                    <th class="border-secondary py-3 pe-4 text-end text-muted small text-uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-agenda">
                                <!-- O JavaScript vai injetar os leads atrasados aqui -->
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Buscando compromissos agendados...
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



<!-- O pulo do gato! Incluímos o modal da ficha do cliente para funcionar nesta tela também -->
<?php include 'includes/modals/lead_details_modal.php'; ?>



<!-- 6. Rodapé Global (Importa Bootstrap, jQuery e as Máscaras) -->
<?php include 'includes/footer.php'; ?>

<!-- Chamada do motor de inteligência que faremos na próxima tarefa! -->
 <script src="<?php echo BASE_URL; ?>assets/js/agenda.js"></script>