<?php
// TAREFA 2.1: Importações Arquiteturais
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// Avisamos ao servidor que sempre vamos devolver um JSON
header('Content-Type: application/json');

// TAREFA 2.2: Trava de Segurança por Token (Acesso Machine-to-Machine)
// No gerenciador da Meta, a URL de envio ficará: 
// https://seudominio.com/api/leads/webhook_meta.php?token=sua_senha_secreta_meta_123
$token_secreto = 'sua_senha_secreta_meta_123';

if (!isset($_GET['token']) || $_GET['token'] !== $token_secreto) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Token inválido.']);
    exit;
}

// TAREFA 2.3: Captura do JSON "Cru" do corpo da requisição
$jsonRecebido = file_get_contents('php://input');
$dadosMeta = json_decode($jsonRecebido, true);

// Trava de segurança: Verifica se a Meta realmente enviou algum dado
if (!$dadosMeta) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload vazio ou JSON inválido.']);
    exit;
}

// TAREFA 2.4: Data Mapping (De: Meta -> Para: Marcelus Car)
// Garantimos que a origem seja 3 (Exemplo: 3 = Tráfego Pago/Site)
$dadosLead = [
    'user_id' => null,           // O lead entra no funil sem dono (esperando primeiro atendimento)
    'vehicle_id' => null,        
    'origin_id' => 3,            // 3 = Site/Tráfego Pago
    'customer_name' => $dadosMeta['nome_completo'] ?? 'Lead Meta Ads',
    'customer_phone' => $dadosMeta['telefone'] ?? '00000000000',
    'customer_email' => $dadosMeta['email'] ?? null,
    'utm_source' => $dadosMeta['utm_source'] ?? 'meta_ads',
    'utm_medium' => $dadosMeta['utm_medium'] ?? 'cpc',
    'utm_campaign' => $dadosMeta['utm_campaign'] ?? 'captacao_padrao',
];

// ========================================================
// BLOCO 3: PERSISTÊNCIA E TIMELINE
// ========================================================

// TAREFA 3.1 e 3.2: Insere o lead e verifica falha
$lead_id = adicionarLead($pdo, $dadosLead);

if (!$lead_id) {
    // Avisa a Meta que houve falha para que ela tente reenviar depois
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar no banco de dados.']);
    exit;
}

// TAREFA 3.3: UX Sênior - Grava um histórico automático na timeline da ficha do lead
insertLog($pdo, $lead_id, null, 'note', '🎯 Lead capturado automaticamente via Webhook do Meta Ads.');

// TAREFA 3.4: Retorna HTTP 200 OK para o robô da Meta encerrar a requisição com sucesso
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Lead recebido e integrado ao funil com sucesso.',
    'lead_id' => $lead_id
]);