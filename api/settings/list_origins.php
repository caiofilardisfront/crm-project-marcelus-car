<?php
// 1. Trazemos as configurações e o motor de banco de dados
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/OriginRepository.php';

// 2. Avisamos ao navegador que vamos conversar em formato JSON
header('Content-Type: application/json');

// 3. Trava de segurança: só quem está logado pode ver as origens
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit;
}

// 4. Pede para a cozinha (Repositório) a lista de origens
$origens = listarOrigens($pdo);

// 5. Empacota e entrega para o Front-end
echo json_encode([
    'status' => 'success',
    'data' => $origens
]);