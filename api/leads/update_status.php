<?php
// 1. Importações (voltando 2 pastas para a raiz)
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/LeadRepository.php';

// 2. Define o cabeçalho como JSON
header('Content-Type: application/json');

// 3. Segurança: Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Segurança: Garante que a requisição seja do tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// 5. Recebe os dados via POST
$lead_id = $_POST['lead_id'] ?? null;
$new_status = $_POST['status'] ?? null;

// Pega o ID do usuário logado diretamente da sessão para gravar no Log
$user_id = $_SESSION['user_id']; 

// 6. Validação básica
if (!$lead_id || !$new_status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do lead e novo status são obrigatórios.']);
    exit;
}

try {
    // ==========================================
    // INÍCIO DA TRANSAÇÃO SEGURA
    // ==========================================
    $pdo->beginTransaction();

    // 1ª Ação: Atualiza o status do Lead
    $statusAtualizado = updateStatus($pdo, $lead_id, $new_status);

    if (!$statusAtualizado) {
        throw new Exception("Falha ao atualizar o status no banco de dados.");
    }

    // 2ª Ação: Grava o histórico da alteração
        // Dicionário de tradução para salvar um texto humanizado no banco de dados
        $statusLabels = [
            'new' => 'Novo',
            'in_progress' => 'Em Negociação',
            'proposal_sent' => 'Proposta Enviada',
            'won' => 'Vendido',
            'lost' => 'Perdido'
        ];
        
        // Se existir no dicionário, usa a tradução, senão usa o que vier
        $statusFormatado = $statusLabels[$new_status] ?? $new_status;

        $logGravado = insertLog(
            $pdo,
            $lead_id,
            $user_id,
            'status_change',
            "Status alterado para: " . $statusFormatado
        );

    if (!$logGravado) {
        throw new Exception("Falha ao registrar o log de interação no banco de dados.");
    }

    // Se as duas funções retornaram true, confirmamos tudo no banco de forma definitiva!
    $pdo->commit();

    // Retorna a resposta de sucesso para o JavaScript
    echo json_encode([
        'status' => 'success', 
        'message' => 'Status atualizado e histórico registrado com sucesso!'
    ]);

} catch (Exception $e) {
    // Se QUALQUER uma das duas ações falhar, o banco cancela tudo automaticamente
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}