<?php

/**
 * ========================================================
 * BUSCAR VEÍCULOS DISPONÍVEIS
 * ========================================================
 * @param PDO $pdo Instância da conexão
 * @return array Lista de carros no estoque
 */
function listarVeiculosDisponiveis($pdo) {
    try {
        // Buscamos apenas os veículos com status 'available' (disponíveis para venda)
        // Como deve ficar:
        $sql = "SELECT * FROM vehicles ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro em listarVeiculosDisponiveis: " . $e->getMessage());
        return [];
    }
}

/**
 * ========================================================
 * ADICIONAR NOVO VEÍCULO AO ESTOQUE
 * ========================================================
 * @param PDO $pdo Instância da conexão
 * @param array $dados Array associativo com os dados do carro
 * @return bool True se inseriu com sucesso, False se falhou
 */
function adicionarVeiculo($pdo, $dados) {
    try {
        // Query de inserção usando âncoras (:param) para segurança
        $sql = "INSERT INTO vehicles 
                (brand, model, manufacture_year, model_year, mileage, price, status) 
                VALUES 
                (:brand, :model, :manufacture_year, :model_year, :mileage, :price, 'available')";
                
        $stmt = $pdo->prepare($sql);
        
        // Fazendo o Bind (ligação) dos dados de forma segura
        $stmt->bindValue(':brand', $dados['brand']);
        $stmt->bindValue(':model', $dados['model']);
        $stmt->bindValue(':manufacture_year', $dados['manufacture_year'], PDO::PARAM_INT);
        $stmt->bindValue(':model_year', $dados['model_year'], PDO::PARAM_INT);
        $stmt->bindValue(':mileage', $dados['mileage'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':price', $dados['price']); // Vem como decimal (ex: 95000.00)
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro em adicionarVeiculo: " . $e->getMessage());
        return false;
    }
}

/**
 * ATUALIZAR STATUS DO VEÍCULO (DISPONÍVEL/RESERVADO/VENDIDO)
 * @param PDO $pdo
 * @param int $vehicle_id
 * @param string $status
 * @return bool
 */
function atualizarStatusVeiculo($pdo, $vehicle_id, $status) {
    try {
        $sql = "UPDATE vehicles SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $vehicle_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro em atualizarStatusVeiculo: " . $e->getMessage());
        return false;
    }
}
