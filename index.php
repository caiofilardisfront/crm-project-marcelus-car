<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">
        
        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-4 py-3 sticky-top d-flex justify-content-between align-items-center">
            
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-sm-block text-light">Painel de Controle</h5>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    
                    <div class="text-end me-3 d-none d-md-block">
                        <strong class="d-block lh-1"><?php echo $_SESSION['user_name'] ?? 'Usuário'; ?></strong>
                        <small class="text-muted" style="font-size: 0.8rem;"><?php echo ucfirst($_SESSION['user_role'] ?? 'Admin'); ?></small>
                    </div>
                    
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white shadow-sm" style="width: 45px; height: 45px; font-size: 18px; font-weight: bold;">
                        <?php echo substr($_SESSION['user_name'] ?? 'U', 0, 1); ?>
                    </div>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary mt-3" aria-labelledby="userMenu">
                    <li><h6 class="dropdown-header text-muted">Minha Conta</h6></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Meu Perfil</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Preferências</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li>
                        <a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema
                        </a>
                    </li>
                </ul>
            </div>
            
        </nav>

        <div class="container-fluid mt-4 px-4 pb-4">
            <div class="card bg-dark text-light border-secondary p-4">
                <h4>Bem-vindo ao CRM, <?php echo $_SESSION['user_name']; ?>!</h4>
                <p class="text-muted">A estrutura base do sistema está finalizada e com um layout nível Enterprise. O menu lateral e o perfil superior estão funcionando perfeitamente.</p>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>