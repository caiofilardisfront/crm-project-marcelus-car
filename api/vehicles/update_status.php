<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/VehicleRepository.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

$vehicle_id = (int) ($_POST['vehicle_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($vehicle_id <= 0 || empty($status)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    exit;
}

if (atualizarStatusVeiculo($pdo, $vehicle_id, $status)) {
    echo json_encode(['status' => 'success', 'message' => 'Status do veículo atualizado!']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco de dados.']);
}