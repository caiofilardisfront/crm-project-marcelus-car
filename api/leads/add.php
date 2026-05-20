<?php
// 1. Importações da arquitetura
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho JSON
header('Content-Type: application/json');

// 3. Trava de Segurança e Validação de Método
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

// 4. Capturando e limpando os dados vindos do Formulário
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$origin_id = (int) ($_POST['origin_id'] ?? 1); // Fallback de segurança para ID 1
$vehicle_interest = trim($_POST['vehicle_interest'] ?? '');
$user_id = $_SESSION['user_id']; // Pega o vendedor que está logado

$vehicle_id = !empty($_POST['vehicle_id']) ? (int) $_POST['vehicle_id'] : null;

// 5. Validação de Regras de Negócio
if (empty($customer_name) || empty($customer_phone)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nome e Telefone são obrigatórios.']);
    exit;
}

// 6. Iniciamos uma Transação (Se algo falhar no meio do caminho, ele desfaz tudo)
try {
    $pdo->beginTransaction();

    $dadosLead = [
        'user_id' => $user_id,
        'origin_id' => $origin_id,
        'vehicle_id' => $vehicle_id, // Agora o ID do veículo vai para o banco
        'customer_name' => $customer_name,
        'customer_phone' => $customer_phone,
        'customer_email' => $customer_email
    ];

    // Salva o Lead e pega o ID gerado pela nossa nova função
    $lead_id = adicionarLead($pdo, $dadosLead);

    if (!$lead_id) {
        throw new Exception("Erro ao salvar o lead no banco de dados.");
    }

    // 7. A MÁGICA: Grava o log automático de criação na timeline
    insertLog($pdo, $lead_id, $user_id, 'note', 'Lead cadastrado manualmente no sistema.');

    // 8. Inteligência Arquitetural: Tratamento do Veículo de Interesse
    // Como a tabela não possui o campo, salvamos essa intenção de compra como histórico rico.
    if (!empty($vehicle_interest)) {
        insertLog($pdo, $lead_id, $user_id, 'note', "O cliente demonstrou interesse no veículo: <strong>" . $vehicle_interest . "</strong>");
    }

    // Confirma as inserções no banco
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Lead cadastrado com sucesso!'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}