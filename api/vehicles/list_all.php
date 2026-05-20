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
        $sql = "SELECT id, brand, model, manufacture_year, price, status 
                FROM vehicles 
                WHERE status = 'available' 
                ORDER BY brand ASC, model ASC";
    } else {
        $sql = "SELECT id, brand, model, manufacture_year, price, status 
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