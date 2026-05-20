<?php
// 1. Importações da arquitetura base
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho de API JSON
header('Content-Type: application/json');

// 3. Trava de Segurança
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Executa a nossa nova função super otimizada
$leads = getLeadsAgenda($pdo);

// 5. Devolve para o JavaScript
echo json_encode([
    'status' => 'success', 
    'data' => $leads
]);