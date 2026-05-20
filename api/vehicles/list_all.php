<?php
// 1. Importações da nossa arquitetura (voltando duas pastas)
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/VehicleRepository.php';

// 2. Avisamos ao navegador que a resposta é estritamente JSON
header('Content-Type: application/json');

// 3. Segurança Sênior: Bloqueia acesso não autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Executa a função do nosso repositório
$veiculos = listarVeiculosDisponiveis($pdo);

// 5. Devolvemos a bandeja pronta para o Front-end
if ($veiculos !== false) {
    echo json_encode([
        'status' => 'success',
        'data' => $veiculos
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar o estoque.']);
}