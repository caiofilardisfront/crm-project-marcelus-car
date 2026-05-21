<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// 1. Captura o filtro enviado pelo Front-end (padrão é 'all')
$statusFilter = $_GET['status'] ?? 'all';

try {
    // 2. Monta a Query dinamicamente com base no filtro
    if ($statusFilter === 'available') {
        // ADICIONADO: model_year, mileage, license_plate e image_path
        $sql = "SELECT id, brand, model, manufacture_year, model_year, mileage, price, license_plate, status, image_path 
                FROM vehicles 
                WHERE status = 'available' 
                ORDER BY brand ASC, model ASC";
    } else {
        // ADICIONADO: license_plate e image_path
        $sql = "SELECT id, brand, model, manufacture_year, model_year, mileage, price, license_plate, status, image_path 
                FROM vehicles 
                ORDER BY brand ASC, model ASC";
    }

    $stmt = $pdo->query($sql);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $vehicles
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao buscar veículos: ' . $e->getMessage()
    ]);
}