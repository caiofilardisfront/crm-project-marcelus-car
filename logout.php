<?php
// 1. Traz as configurações globais (agora estamos na raiz, então entra direto na pasta config)
require_once 'config/config.php';

// 2. Limpa todas as variáveis armazenadas na sessão atual
session_unset();

// 3. Destrói a sessão no servidor (Tritura o "crachá" fantasma do usuário)
session_destroy();

// 4. Redireciona o usuário de volta para a porta da frente (Login)
header("Location: " . BASE_URL . "index.php");
exit;