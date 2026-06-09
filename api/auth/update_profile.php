<?php
// 1. O KIT DE FERRAMENTAS: Trazemos as configurações e o nosso "motor" de usuários [2, 4, 6]
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../repositories/UserRepository.php';

// 2. O FORMATO DE RESPOSTA: Avisamos ao navegador que vamos conversar em formato JSON [7]
header('Content-Type: application/json');

// 3. O SEGURANÇA NA PORTA: Verificamos se quem está acessando realmente fez login [8]
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado. Faça login.']);
    exit;
}

// 4. RECEBENDO A ENCOMENDA: Pegamos o que o usuário digitou no formulário
// Usamos o trim() para cortar espaços em branco que o usuário digitou sem querer no começo ou fim
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

// DICA DE SÊNIOR: Nós NUNCA pegamos o ID do usuário do formulário (alguém poderia hackear e mudar o ID de outro).
// Nós pegamos o ID diretamente da nossa "memória segura" (a Sessão) de quando ele fez o login [5]!
$id = $_SESSION['user_id']; 

// 5. VALIDAÇÃO SIMPLES: Não podemos deixar o usuário salvar um nome vazio, certo?
if (empty($name)) {
    http_response_code(400); // 400 significa "Pedido Ruim"
    echo json_encode(['status' => 'error', 'message' => 'O nome não pode ficar em branco.']);
    exit;
}

// 6. A MÁGICA DA SENHA: Vamos ver se ele digitou alguma senha nova
$password_hash = null; // Começamos assumindo que ele não quer trocar a senha

if (!empty($password)) {
    // Se ele digitou algo na senha, nós usamos a função nativa do PHP para criptografar.
    // Ela transforma "123456" em algo como "$2y$10$U.T7..." [9].
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

// 7. CHAMANDO A COZINHA: Enviamos os dados para a função que você construiu na Tarefa 1.1 [10]
$sucesso = updateUserProfile($pdo, $id, $name, $password_hash);

if ($sucesso) {
    // 8. O TRUQUE DE MESTRE (Atualizar a Interface)
    // Se a gente não atualizar a Sessão, o nome antigo do usuário vai continuar aparecendo no topo 
    // do painel até ele fazer login de novo. Atualizando aqui, a mudança é em tempo real [5]!
    $_SESSION['user_name'] = $name;

    // Avisamos para a tela que deu tudo certo!
    echo json_encode([
        'status' => 'success', 
        'message' => 'Perfil atualizado com sucesso!'
    ]);
} else {
    // Se o banco de dados falhar por algum motivo...
    http_response_code(500); // 500 significa "Erro interno do servidor"
    echo json_encode([
        'status' => 'error', 
        'message' => 'Erro no banco de dados ao atualizar o perfil.'
    ]);
}