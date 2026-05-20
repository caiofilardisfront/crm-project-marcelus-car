<?php
// 1. Importações da arquitetura base
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Cabeçalho JSON
header('Content-Type: application/json');

// 3. Trava de Segurança
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Busca os KPIs usando o motor do repositório
$kpis = getLeadsStats($pdo);

// Verifica se a busca não falhou
if ($kpis !== false) {
    // 5. Inteligência de Negócio: Somamos os status ativos para o Card único do Dashboard
    $kpis['negociacao_ativa'] = $kpis['total_in_progress'] + $kpis['total_proposal'];

    // 6. Retorna o pacote pronto
    echo json_encode([
        'status' => 'success',
        'data' => $kpis
    ]);
} else {
    // Tratativa Sênior de Erro
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar as métricas.']);
}