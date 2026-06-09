<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/OriginRepository.php';

header('Content-Type: application/json');

// Trava 1: Usuário Logado?
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// Trava 2: É uma requisição POST?
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// Higieniza o dado recebido do formulário
$name = trim($_POST['name'] ?? '');

// Validação de regra de negócio
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'O nome da origem é obrigatório.']);
    exit;
}

// Chama a função de inserir no banco que você criou na Tarefa 2
$sucesso = adicionarOrigem($pdo, $name);

if ($sucesso) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Nova origem cadastrada com sucesso!'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno ao salvar no banco de dados.'
    ]);
}