<?php
// 1. Importações da arquitetura base
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho de API JSON
header('Content-Type: application/json');

// 3. Trava de Segurança: Bloqueia acesso de usuários não logados
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Captura o filtro de tempo passado via URL (default: 'monthly')
$periodo = $_GET['periodo'] ?? 'monthly';

// 5. Busca os dados brutos usando o motor que você acabou de criar no repositório
$dadosBrutos = getPerformanceChartData($pdo, $periodo);

// 6. Inteligência de API (Preparando o terreno para o Chart.js)
// Vamos separar as colunas em 3 arrays distintos
$labels = [];
$totalLeads = [];
$totalVendas = [];

// Varre os resultados do banco e separa nas gavetas corretas
// 6. Inteligência de API 
$labels = [];
$totalLeads = [];
$totalVendas = [];
$totalPerdidos = []; // NOVO ARRAY

foreach ($dadosBrutos as $linha) {
    $dataCrua = $linha['data_agrupada'];
    if ($periodo === 'yearly') {
        $dataFormatada = date('m/Y', strtotime($dataCrua . '-01'));
    } else {
        $dataFormatada = date('d/m/Y', strtotime($dataCrua));
    }

    $labels[] = $dataFormatada;
    $totalLeads[] = (int) $linha['total_leads'];
    $totalVendas[] = (int) $linha['total_vendas'];
    $totalPerdidos[] = (int) $linha['total_perdidos']; // ALIMENTANDO O NOVO ARRAY
}

// 7. Entrega o pacote pronto
echo json_encode([
    'status' => 'success',
    'data' => [
        'labels' => $labels,
        'leads' => $totalLeads,
        'vendas' => $totalVendas,
        'perdidos' => $totalPerdidos // ENVIANDO PARA O JS
    ]
]);