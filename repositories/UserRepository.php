<?php

/**
 * Função para buscar um usuário pelo e-mail
 * @param PDO $pdo Instância da conexão com o banco
 * @param string $email E-mail digitado no login
 * @return array|false Retorna os dados do usuário ou falso se não encontrar
 */
function findUserByEmail($pdo, $email) {
    try {
        // 1. Preparamos a consulta (Prevenção total contra SQL Injection)
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        
        // 2. Vinculamos o valor de forma segura
        $stmt->bindValue(':email', $email);
        
        // 3. Executamos
        $stmt->execute();
        
        // 4. Retornamos o resultado como um array associativo
        return $stmt->fetch(); 
        
    } catch (PDOException $e) {
        // Log de erro básico se algo der errado na consulta
        error_log("Erro em findUserByEmail: " . $e->getMessage());
        return false;
    }
}