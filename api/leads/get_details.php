<?php
// 1. Importações (Mesmo padrão do list_all.php)
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho JSON
header('Content-Type: application/json');

// 3. Segurança Nível Sênior: Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Validação: O ID foi enviado na URL? (Ex: get.php?id=5)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // 400 = Bad Request (Requisição malformada)
    echo json_encode(['status' => 'error', 'message' => 'O ID do lead não foi informado.']);
    exit;
}

// 5. Limpeza de dados: Garante que o ID será tratado como um número inteiro
$id = (int) $_GET['id'];

// 6. Busca no banco de dados
$lead = getLeadById($pdo, $id);

// 7. Resposta da API
if ($lead) {
    // Se achou o lead, devolve os dados com sucesso
    echo json_encode(['status' => 'success', 'data' => $lead]);
} else {
    // Se retornou false (não achou o ID no banco)
    http_response_code(404); // 404 = Not Found
    echo json_encode(['status' => 'error', 'message' => 'Lead não encontrado no sistema.']);
}