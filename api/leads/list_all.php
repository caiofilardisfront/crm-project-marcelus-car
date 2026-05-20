<?php
// 1. Importa os arquivos necessários (voltando duas pastas: api -> leads -> raiz)
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Define o cabeçalho para garantir que o navegador entenda que é um JSON puro
header('Content-Type: application/json');

// 3. Segurança Nível Sênior: Bloqueia quem tentar acessar a API sem estar logado
if (!isset($_SESSION['user_id'])) {
    // Retornamos um erro 403 (Acesso Proibido) e encerramos a execução
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Executa a função que você criou para buscar os dados no banco
$leads = getAllLeads($pdo);

// 5. Converte o array do PHP para o formato JSON e imprime na tela
echo json_encode($leads);