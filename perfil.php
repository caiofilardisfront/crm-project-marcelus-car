<?php
// 1. AS CHAVES DA CASA: Trazemos as configurações do sistema
require_once 'config/config.php';
require_once 'config/database.php';

// 2. O LEÃO DE CHÁCARA: Se o cara não estiver logado, manda ele pro login (index.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// 3. BUSCANDO OS DADOS: Pegamos o ID da sessão e pedimos ao banco o nome e e-mail dele
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Importamos o Topo do HTML (A tag <head> com o Bootstrap e CSS) -->
<?php include 'includes/header.php'; ?>

<div class="d-flex vh-100 overflow-hidden w-100">
    
    <!-- Importamos o nosso Menu Lateral Escuro -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="d-flex flex-column flex-grow-1 overflow-auto w-100">
        
        <!-- A BARRA SUPERIOR (Navbar idêntica à do Dashboard) -->
        <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-4 py-3 sticky-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3 border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="mb-0 fw-bold d-none d-sm-block text-light">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i>Minha Conta
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
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a></li>
                </ul>
            </div>
        </nav>

        <!-- O CONTEÚDO DA PÁGINA (A Vitrine) -->
        <div class="container-fluid p-4">
            
            <div class="mb-4">
                <h4 class="fw-bold text-light mb-1">Perfil do Usuário</h4>
                <p class="text-muted small">Mantenha seus dados atualizados e altere a sua senha.</p>
            </div>

            <!-- O Card Premium Dark que segura o nosso formulário -->
            <div class="card border-secondary shadow-lg" style="max-width: 600px;">
                <div class="card-header bg-transparent border-secondary p-3">
                    <h5 class="m-0 fw-bold text-light d-flex align-items-center">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>Dados Cadastrais
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    
                    <!-- Demos um ID para o formulário. O JavaScript (nossa próxima tarefa) vai "escutar" ele! -->
                    <form id="form-perfil">
                        
                        <!-- E-MAIL (Bloqueado/Readonly) -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">E-mail de Acesso</label>
                            <input type="email" class="form-control bg-dark text-muted border-secondary" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly disabled style="cursor: not-allowed;">
                            <div class="form-text text-secondary" style="font-size: 0.75rem;">O e-mail não pode ser alterado por questões de segurança.</div>
                        </div>

                        <!-- NOME COMPLETO (Pode editar, já vem preenchido) -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nome Completo *</label>
                            <!-- O PHP "cospe" o nome do cara dentro do atributo 'value' -->
                            <input type="text" name="name" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($usuario['name']); ?>" required>
                        </div>

                        <!-- NOVA SENHA (Pode editar, mas vem vazio) -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nova Senha</label>
                            <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Digite apenas se quiser alterar a senha atual">
                            <div class="form-text text-secondary" style="font-size: 0.75rem;">Deixe em branco para manter a sua senha atual.</div>
                        </div>

                        <hr class="border-secondary mb-4">

                        <!-- BOTÃO DE SALVAR -->
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center">
                            <i class="bi bi-save me-2 fs-5"></i> Salvar Alterações
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fechamos as Tags do HTML e chamamos as bibliotecas Jquery/Bootstrap -->
<?php include 'includes/footer.php'; ?>

<!-- O arquivo JavaScript que criaremos na próxima e última tarefa! -->
<script src="<?php echo BASE_URL; ?>assets/js/perfil.js"></script>