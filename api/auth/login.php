<?php
// 1. Puxamos as configurações, a conexão com o banco e a nossa função de busca
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/UserRepository.php';

// 2. Avisamos ao navegador que a resposta será exclusivamente em JSON
header('Content-Type: application/json');

// 3. Capturamos o que o JavaScript enviou (usamos '??' para evitar erros se vier vazio)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 4. Validação básica de segurança
if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos.']);
    exit;
}

// 5. Usamos a função que você criou para buscar o usuário
$user = findUserByEmail($pdo, $email);

if ($user) {
    
    // --- ROTINA DE ATUALIZAÇÃO DA SENHA PROVISÓRIA ---
    // Se a senha no banco for a provisória que criamos via SQL, nós a transformamos em um Hash seguro agora mesmo.
    if ($user['password_hash'] === 'senha_provisoria_123' && $password === 'senha_provisoria_123') {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $stmt->bindValue(':hash', $newHash);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();
        
        // Atualizamos a variável para a validação real passar logo abaixo
        $user['password_hash'] = $newHash; 
    }
    // --------------------------------------------------

    // 6. Validação real (Compara a senha digitada com a criptografia do banco)
    if (password_verify($password, $user['password_hash'])) {
        
        // 7. Login Sucesso! Salvamos quem é o usuário na Sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode(['status' => 'success']);
        exit;
    }
}

// 8. Se chegou até aqui, ou o e-mail não existe ou a senha está errada
// DICA DE SEGURANÇA: Nunca diga "E-mail não existe", diga "E-mail ou senha incorretos" para não dar dicas a hackers.
echo json_encode(['status' => 'error', 'message' => 'E-mail ou senha incorretos.']);