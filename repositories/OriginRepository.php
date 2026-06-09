<?php

/**
 * ========================================================
 * REPOSITÓRIO DE ORIGENS DE LEADS (lead_origins)
 * ========================================================
 * Este arquivo é o motor exclusivo que conversa com a 
 * tabela de origens do banco de dados.
 */

/**
 * 1. Busca todas as origens cadastradas em ordem alfabética
 * @param PDO $pdo Instância da conexão
 * @return array Lista de origens
 */
function listarOrigens($pdo) {
    try {
        // SELECT simples, ordenando pelo nome de A a Z
        $sql = "SELECT * FROM lead_origins ORDER BY name ASC";
        $stmt = $pdo->query($sql);
        
        // Retorna todas as linhas encontradas como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro em listarOrigens: " . $e->getMessage());
        return []; // Retorna vazio para não quebrar a tela em caso de erro
    }
}

/**
 * 2. Adiciona uma nova origem no banco de dados
 * @param PDO $pdo Instância da conexão
 * @param string $name Nome da nova origem (Ex: "Google Ads")
 * @return bool True se salvou, False se deu erro
 */
function adicionarOrigem($pdo, $name) {
    try {
        // Inserimos o nome e já deixamos o is_active como 1 (Ativo) por padrão
        $sql = "INSERT INTO lead_origins (name, is_active) VALUES (:name, 1)";
        $stmt = $pdo->prepare($sql);
        
        // BindValue protege contra Hackers (SQL Injection)
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Erro em adicionarOrigem: " . $e->getMessage());
        return false;
    }
}

/**
 * 3. Alterna o status (Ativo/Inativo) de uma origem existente
 * @param PDO $pdo Instância da conexão
 * @param int $id ID da origem
 * @param int $is_active 1 para Ativar, 0 para Desativar
 * @return bool True se alterou, False se deu erro
 */
function alternarStatusOrigem($pdo, $id, $is_active) {
    try {
        $sql = "UPDATE lead_origins SET is_active = :is_active WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue(':is_active', $is_active, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Erro em alternarStatusOrigem: " . $e->getMessage());
        return false;
    }
}