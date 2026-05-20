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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// 4. Captura e higieniza os dados vindos do modal
$lead_id = (int) ($_POST['lead_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
// TAREFA 2: Capturamos a data que veio do formulário
$next_contact_at = trim($_POST['next_contact_at'] ?? ''); 
$user_id = $_SESSION['user_id'];

// 5. Validação da regra de negócio
if (empty($lead_id) || empty($content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A anotação não pode estar vazia.']);
    exit;
}

// 6. Usa a nossa função poderosa de Log para salvar a anotação principal no banco
$sucesso = insertLog($pdo, $lead_id, $user_id, 'note', $content);

// 7. TAREFA 2: Lógica de Agendamento Inteligente
if ($sucesso && !empty($next_contact_at)) {
    // Converte de YYYY-MM-DDTHH:MM (HTML5) para YYYY-MM-DD HH:MM:SS (MySQL)
    $data_mysql = date('Y-m-d H:i:s', strtotime($next_contact_at));
    
    // Salva a data na ficha do lead
    atualizarDataRetorno($pdo, $lead_id, $data_mysql);
    
    // UX Sênior: Grava um histórico automático na timeline para que todos saibam do agendamento
    $dataFormatada = date('d/m/Y \à\s H:i', strtotime($next_contact_at));
    insertLog($pdo, $lead_id, $user_id, 'note', "🕒 <strong>Retorno agendado para:</strong> {$dataFormatada}");
}

// 8. Resposta ao JavaScript
if ($sucesso) {
    echo json_encode(['status' => 'success', 'message' => 'Anotação adicionada ao histórico.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar a anotação.']);
}