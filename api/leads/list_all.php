<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

$status = $_GET['status'] ?? 'all';
// 1. Capturamos o que o usuário digitou (se não digitar nada, fica vazio)
$busca = $_GET['search'] ?? ''; 

// 2. Passamos a busca como terceiro parâmetro para a nossa função
$leads = getAllLeads($pdo, $status, $busca);

echo json_encode($leads);