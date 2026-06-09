<?php
// 1. AS CHAVES DA CASA: Trazemos as configurações do sistema
require_once 'config/config.php';
require_once 'config/database.php';

// 2. O LEÃO DE CHÁCARA: Impede que pessoas sem login entrem digitando o link
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>

<!-- Importamos o Topo do HTML (CSS e Bootstrap) -->
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">
    
    <!-- Importamos o Menu Lateral (Que acabamos de arrumar no Passo 1) -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">
        
        <!-- A BARRA SUPERIOR (Navbar) idêntica à do resto do sistema -->
        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-4 py-3 sticky-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-sm-block text-light">
                    <i class="bi bi-gear-fill text-primary me-2"></i>Configurações Gerais
                </h5>
            </div>
            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center text-white shadow-sm me-2" style="width: 40px; height: 40px; font-weight: bold;">
                        <?php echo substr($_SESSION['user_name'] ?? 'U', 0, 1); ?>
                    </div>
                    <span class="d-none d-md-inline fw-semibold">
                        <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name'] ?? 'Usuário'))[0]); ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary mt-2">
                    <li><a class="dropdown-item text-light py-2" href="<?php echo BASE_URL; ?>perfil.php"><i class="bi bi-person-gear me-2"></i> Minha Conta</a></li>
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </nav>

        <!-- O CONTEÚDO DA PÁGINA (Nosso Card de Origens de Leads) -->
        <div class="container-fluid p-4">
            
            <div class="mb-4">
                <h4 class="fw-bold text-light mb-1">Configurações do CRM</h4>
                <p class="text-muted small">Gerencie as origens de captação e regras de negócio do sistema.</p>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Card Premium Dark -->
                    <div class="card border-secondary shadow-lg mb-4">
                        <div class="card-header bg-transparent border-secondary p-3">
                            <h5 class="m-0 fw-bold text-light d-flex align-items-center">
                                <i class="bi bi-funnel-fill text-primary me-2"></i>Origens de Leads
                            </h5>
                        </div>
                        
                        <div class="card-body p-4">
                            <!-- FORMULÁRIO DE NOVA ORIGEM -->
                            <!-- Demos um ID para ele ("form-add-origin"). O nosso JS vai escutar os cliques nele depois. -->
                            <form id="form-add-origin" class="mb-4">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-9">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nova Origem (Ex: Google Ads, TikTok)</label>
                                        <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required placeholder="Digite o nome da origem...">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center h-100" style="padding: 12px;">
                                            <i class="bi bi-plus-lg me-2"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- TABELA PARA LISTAR AS ORIGENS (Começa vazia, mostrando "Carregando...") -->
                            <div class="table-responsive border border-secondary rounded">
                                <table class="table table-dark table-hover align-middle mb-0">
                                    <thead class="bg-dark">
                                        <tr>
                                            <th class="border-secondary py-3 ps-3 text-muted small text-uppercase">Nome da Origem</th>
                                            <th class="border-secondary py-3 text-muted small text-uppercase text-center">Status</th>
                                            <th class="border-secondary py-3 pe-3 text-end text-muted small text-uppercase">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela-origens">
                                        <!-- O JavaScript (Tarefa 4) vai desenhar as linhas (<tr>) aqui dentro! -->
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary me-2"></div> Carregando origens...
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
    </div>
</div>

<!-- Importamos as bibliotecas Javascript do Bootstrap -->
<?php include 'includes/footer.php'; ?>

<!-- O nosso motor Javascript que vamos criar no final (Tarefa 4) -->
<script src="<?php echo BASE_URL; ?>assets/js/configuracoes.js"></script>