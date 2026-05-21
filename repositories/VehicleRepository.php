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
        $sql = "INSERT INTO vehicles (brand, model, manufacture_year, model_year, mileage, price, image_path) 
            VALUES (:brand, :model, :manufacture_year, :model_year, :mileage, :price, :image_path)";
                
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute([
        ':brand' => $dados['brand'],
        ':model' => $dados['model'],
        ':manufacture_year' => $dados['manufacture_year'],
        ':model_year' => $dados['model_year'],
        ':mileage' => $dados['mileage'],
        ':price' => $dados['price'],
        ':image_path' => $dados['image_path'] // <-- O novo valor entra no banco aqui
    ]);
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
