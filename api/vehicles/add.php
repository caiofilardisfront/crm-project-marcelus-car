<?php
// 1. Importações da arquitetura
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/VehicleRepository.php';

// 2. Cabeçalho JSON
header('Content-Type: application/json');

// 3. Trava de Segurança: Usuário logado e requisição POST
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
$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$manufacture_year = (int) ($_POST['manufacture_year'] ?? 0);
$model_year = (int) ($_POST['model_year'] ?? 0);
$mileage = (int) ($_POST['mileage'] ?? 0);

// Limpeza Premium do campo de Moeda (Ex: "95.000,00" -> "95000.00")
$price_raw = $_POST['price'] ?? '0';
$price_clean = str_replace('.', '', $price_raw); // Remove os pontos de milhar
$price_clean = str_replace(',', '.', $price_clean); // Troca a vírgula dos centavos por ponto
$price = (float) $price_clean;

// 5. Validação de Regra de Negócio (Campos obrigatórios)
if (empty($brand) || empty($model) || empty($manufacture_year) || empty($price)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Preencha os campos obrigatórios (Marca, Modelo, Ano e Preço).']);
    exit;
}

// 6. Monta o pacote de dados para o Repositório
$dadosVeiculo = [
    'brand' => $brand,
    'model' => $model,
    'manufacture_year' => $manufacture_year,
    'model_year' => $model_year,
    'mileage' => $mileage,
    'price' => $price
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