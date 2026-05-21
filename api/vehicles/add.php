<?php
// 1. Importações da arquitetura
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/VehicleRepository.php';

// 2. Cabeçalho JSON
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

// 4. Capturando e limpando os dados
$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$manufacture_year = (int) ($_POST['manufacture_year'] ?? 0);
$model_year = (int) ($_POST['model_year'] ?? 0);
$mileage = (int) ($_POST['mileage'] ?? 0);

$price_raw = $_POST['price'] ?? '0';
$price_clean = str_replace('.', '', $price_raw);
$price_clean = str_replace(',', '.', $price_clean);
$price = (float) $price_clean;

// ==========================================
// MÓDULO DE UPLOAD DE IMAGEM
// ==========================================
$uploadDir = '../../uploads/vehicles/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$imagePath = null; // Valor padrão se o cliente não enviar foto

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        // Gera um nome único (Ex: car_169000_1a2b3c.jpg)
        $newFileName = 'car_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Caminho que o banco vai salvar para o Frontend ler depois
            $imagePath = 'uploads/vehicles/' . $newFileName;
        }
    }
}
// ==========================================

// 5. Validação de Regra de Negócio
if (empty($brand) || empty($model) || empty($manufacture_year) || empty($price)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Preencha os campos obrigatórios.']);
    exit;
}

// 6. Monta o pacote de dados para o Repositório
$dadosVeiculo = [
    'brand' => $brand,
    'model' => $model,
    'manufacture_year' => $manufacture_year,
    'model_year' => $model_year,
    'mileage' => $mileage,
    'price' => $price,
    'image_path' => $imagePath // <-- Injetando a imagem aqui!
];

// 7. Salva no banco de dados
$sucesso = adicionarVeiculo($pdo, $dadosVeiculo);

if ($sucesso) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Veículo adicionado ao estoque com sucesso!'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar veículo.']);
}