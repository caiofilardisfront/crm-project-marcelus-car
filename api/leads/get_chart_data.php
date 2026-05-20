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
foreach ($dadosBrutos as $linha) {
    // Para deixar o gráfico mais bonito (UX), se o período for 'yearly' (Ex: 2024-05), 
    // a gente inverte para o padrão brasileiro (05/2024). 
    // Se for dia (2024-05-20), invertemos para 20/05/2024.
    $dataCrua = $linha['data_agrupada'];
    if ($periodo === 'yearly') {
        $dataFormatada = date('m/Y', strtotime($dataCrua . '-01'));
    } else {
        $dataFormatada = date('d/m/Y', strtotime($dataCrua));
    }

    $labels[] = $dataFormatada;
    $totalLeads[] = (int) $linha['total_leads'];
    $totalVendas[] = (int) $linha['total_vendas'];
}

// 7. Entrega o pacote pronto e "mastigado" para o Front-end
echo json_encode([
    'status' => 'success',
    'data' => [
        'labels' => $labels,
        'leads' => $totalLeads,
        'vendas' => $totalVendas
    ]
]);