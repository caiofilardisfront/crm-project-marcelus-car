<?php
// 1. Importações da arquitetura base para manter a conexão ativa [2, 3]
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho JSON para comunicação com o AJAX
header('Content-Type: application/json');

// 3. Trava de Segurança Nível 1: Verifica se o usuário está autenticado [4]
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado. Sessão expirada.']);
    exit;
}

// 4. Trava de Segurança Nível 2: Garante que a exclusão só ocorra via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// 5. Coleta e sanitização do ID vindo do Front-end
$lead_id = (int) ($_POST['lead_id'] ?? 0);

// 6. Validação do ID
if ($lead_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do lead inválido para exclusão.']);
    exit;
}

// 7. Ação Definitiva: Chama o motor de limpeza profunda do repositório [1, 5, 6]
// Esta função já cuida de apagar as interações e o lead dentro de uma transação SQL.
$sucesso = excluirLeadCompleto($pdo, $lead_id);

if ($sucesso) {
    // Retorno positivo para o JavaScript disparar o Toast de sucesso
    echo json_encode([
        'status' => 'success', 
        'message' => 'Lead e histórico removidos permanentemente.'
    ]);
} else {
    // Retorno em caso de falha no banco de dados
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Falha crítica ao excluir o registro. Tente novamente mais tarde.'
    ]);
}