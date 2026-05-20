<?php

/**
 * ========================================================
 * BUSCAR HISTÓRICO DE INTERAÇÕES (LOGS)
 * ========================================================
 * @param PDO $pdo Instância da conexão
 * @param int $lead_id ID do lead
 * @return array Lista de interações ou array vazio
 */
function getInteractionsByLeadId($pdo, $lead_id) {
    try {
        // Seleciona as anotações e ordena da mais recente para a mais antiga
        $sql = "SELECT * FROM lead_interactions WHERE lead_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        
        // Executa com segurança contra SQL Injection
        $stmt->execute([$lead_id]);
        
        // Retorna todas as linhas como um array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro em getInteractionsByLeadId: " . $e->getMessage());
        return []; // Retorna vazio em caso de erro para não quebrar o Front-end
    }
}