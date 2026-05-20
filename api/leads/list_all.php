<?php
// 1. Importações base
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

header('Content-Type: application/json');

// 2. Segurança: Bloqueia não logados
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 3. CAPTURA DO FILTRO: Pega o status da URL, se não vier nada, usa 'all' como padrão
$status = $_GET['status'] ?? 'all';

// 4. Executa a busca passando o filtro para o repositório
$leads = getAllLeads($pdo, $status);

// 5. Retorna o JSON (Note que agora a função getAllLeads já recebe o parâmetro)
echo json_encode($leads);