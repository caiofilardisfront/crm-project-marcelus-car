<?php
// 1. Inicia as configurações globais [3]
require_once 'config/config.php';

// 2. A TRAVA INVERSA: Se o usuário JÁ estiver logado, não precisa ver o login, manda pro Dashboard!
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Marcelus Car</title>
    <!-- Estilos Premium Dark [4] -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="bg-dark text-light d-flex align-items-center justify-content-center vh-100" style="background-color: var(--bg-base) !important;">
    
    <div class="card border-secondary shadow-lg p-4 p-sm-5" style="width: 100%; max-width: 420px; background-color: var(--bg-surface) !important;">
        
        <div class="text-center mb-4">
            <div class="bg-primary rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-car-front-fill fs-2 text-white"></i>
            </div>
            <h3 class="fw-bold text-light mb-1">Marcelus Car</h3>
            <p class="text-muted small">Acesso Restrito ao CRM</p>
        </div>

        <!-- Formulário ligado diretamente ao seu auth.js [2] -->
        <form id="form-login">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase">E-mail</label>
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" class="form-control bg-dark text-light border-secondary border-start-0" placeholder="vendedor@marcelus.com" required style="box-shadow: none;">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold text-uppercase">Senha</label>
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" class="form-control bg-dark text-light border-secondary border-start-0" placeholder="••••••••" required style="box-shadow: none;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center">
                Entrar no Sistema <i class="bi bi-arrow-right-short ms-2 fs-5"></i>
            </button>
        </form>
        
    </div>

    <!-- Scripts do Sistema -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/auth.js"></script>
</body>
</html>

<?php include 'includes/footer.php'; ?>