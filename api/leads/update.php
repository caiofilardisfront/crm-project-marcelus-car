<?php
// 1. Importações da arquitetura base
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho JSON
header('Content-Type: application/json');

// 3. Travas de Segurança (Proteção de Sessão e Método)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// 4. Coleta e sanitização dos dados
$dados = [
    'lead_id' => (int) ($_POST['lead_id'] ?? 0),
    'customer_name' => trim($_POST['customer_name'] ?? ''),
    'customer_phone' => trim($_POST['customer_phone'] ?? ''),
    'customer_email' => trim($_POST['customer_email'] ?? ''),
    'origin_id' => (int) ($_POST['origin_id'] ?? 0),
    'vehicle_id'       => !empty($_POST['vehicle_id']) ? (int) $_POST['vehicle_id'] : null, 
    'vehicle_interest' => trim($_POST['vehicle_interest'] ?? '')
];

$user_id = $_SESSION['user_id'];

// 5. Validação de Regra de Negócio Básica
if (empty($dados['lead_id']) || empty($dados['customer_name']) || empty($dados['customer_phone']) || empty($dados['origin_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Preencha os campos obrigatórios (*).']);
    exit;
}

// 6. Bloco Transacional Seguro
try {
    $pdo->beginTransaction();
    
    // Atualiza os dados principais
    atualizarLead($pdo, $dados);
    
    // UX Premium: Audita a alteração injetando um log na timeline
    insertLog($pdo, $dados['lead_id'], $user_id, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.');
    
    // Confirma as transações
    $pdo->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Dados do lead atualizados com sucesso!']);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
