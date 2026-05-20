<?php 
// 1. Garantia extra: Se a página que chamou o header esquecer do config, ele carrega aqui.
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// 2. PROTEÇÃO DE ROTAS (A Tarefa atual)
// Descobre em qual página o usuário está no momento
$pagina_atual = basename($_SERVER['PHP_SELF']);

// Se NÃO houver sessão E a página atual NÃO for o index.php (login), bloqueia o acesso!
if (!isset($_SESSION['user_id']) && $pagina_atual !== 'index.php') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcelus Car | CRM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="bg-dark text-light">