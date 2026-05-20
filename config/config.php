<?php
// Inicia a sessão para que o sistema possa identificar o usuário logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define a URL base do projeto para facilitar links e inclusão de arquivos
// IMPORTANTE: Ajuste o caminho se a sua pasta for diferente
define('BASE_URL', 'http://localhost/crm-php-test/crm-marcelus-car/');

// Opcional: Configurar o fuso horário para os logs saírem com a hora certa
date_default_timezone_set('America/Sao_Paulo');

// Para teste (você pode apagar essa linha depois de verificar no navegador)
// echo "Configurações carregadas! A URL base é: " . BASE_URL;