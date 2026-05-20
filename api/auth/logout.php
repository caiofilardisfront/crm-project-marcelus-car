<?php
// 1. Volta duas pastas para encontrar a config
require_once '../../config/config.php';

// 2. Limpa e destrói
session_unset();
session_destroy();

// 3. Redireciona
header("Location: " . BASE_URL . "index.php");
exit;