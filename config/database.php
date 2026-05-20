<?php

$host = '127.0.0.1'; // Usar IP é ligeiramente mais rápido que 'localhost' no Windows
$dbname = 'crm_marcelus_car';
$username = 'root'; // Usuário padrão de ambiente local
$password = ''; // Senha padrão de ambiente local (vazia)

try {
    // 1. Cria a string de conexão (DSN - Data Source Name) e instancia o PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 2. Configura o tratamento de erros para disparar Exceções detalhadas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Configura o PDO para retornar arrays associativos por padrão (facilita muito no futuro
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Linha de teste (Descomente para testar, depois apague ou comente novamente)
    // echo "Conexão com o banco de dados realizada com sucesso!";

} catch (PDOException $e) {
    // Se der qualquer erro de senha, banco inexistente, etc, ele cai aqui e para o sistema
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}