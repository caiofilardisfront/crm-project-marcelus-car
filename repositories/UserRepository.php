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

/**
 * Atualiza os dados de perfil do usuário (Nome e opcionalmente a Senha)
 * @param PDO $pdo Instância da conexão
 * @param int $id ID do usuário logado
 * @param string $name Novo nome digitado
 * @param string|null $password_hash Nova senha criptografada (se houver)
 * @return bool Retorna true se atualizou, false em caso de falha
 */
function updateUserProfile($pdo, $id, $name, $password_hash = null) {
    try {
        // Se a senha foi enviada, atualiza o nome e a senha
        if ($password_hash) {
            $sql = "UPDATE users SET name = :name, password_hash = :password_hash WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
        } else {
            // Se a senha NÃO foi enviada, atualiza apenas o nome
            $sql = "UPDATE users SET name = :name WHERE id = :id";
            $stmt = $pdo->prepare($sql);
        }
        
        // Binds comuns para ambos os cenários
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Erro em updateUserProfile: " . $e->getMessage());
        return false;
    }
}