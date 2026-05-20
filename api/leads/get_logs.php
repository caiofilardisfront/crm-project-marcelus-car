<?php
// 1. Importações (Usando o repositório correto)
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadInteractionRepository.php';

// 2. Cabeçalho de Resposta JSON
header('Content-Type: application/json');

// 3. Trava de Segurança
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Validação do Parâmetro lead_id
if (!isset($_GET['lead_id']) || empty($_GET['lead_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'O ID do lead não foi informado.']);
    exit;
}

// 5. Limpeza do dado
$lead_id = (int) $_GET['lead_id'];

// 6. Busca os logs usando a nossa função do Repositório
$logs = getInteractionsByLeadId($pdo, $lead_id);

// 7. Imprime a resposta de Sucesso
echo json_encode([
    'status' => 'success',
    'data' => $logs
]);