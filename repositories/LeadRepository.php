<?php

/**
 * Retorna a quantidade total de leads cadastrados no banco de dados.
 * (Sua função countLeads atual continua aqui intacta)
 */
function countLeads($pdo)
{
    try {
        $sql = "SELECT COUNT(*) FROM leads";
        $stmt = $pdo->query($sql);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro em countLeads: " . $e->getMessage());
        return 0;
    }
}

/**
 * ========================================================
 * NOVA TAREFA: Função para buscar todos os leads
 * ========================================================
 * Retorna todos os leads cadastrados, ordenados do mais recente para o mais antigo.
 * @param PDO $pdo Instância da conexão com o banco
 * @return array Lista de leads (array associativo)
 */
function getAllLeads($pdo)
{
    try {
        // Adicionamos o LEFT JOIN com a tabela de veículos para buscar Marca e Modelo
        $sql = "SELECT leads.*, users.name AS seller_name, vehicles.brand, vehicles.model
                FROM leads 
                LEFT JOIN users ON leads.user_id = users.id 
                LEFT JOIN vehicles ON leads.vehicle_id = vehicles.id
                ORDER BY leads.created_at DESC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro em getAllLeads: " . $e->getMessage());
        return [];
    }
}

/**
 * ========================================================
 * Função para registrar um Log (Interação) no histórico do Lead
 * ========================================================
 * @param PDO $pdo Instância da conexão com o banco
 * @param int $lead_id ID do lead que está recebendo o log
 * @param int $user_id ID do usuário (vendedor/admin) que fez a ação
 * @param string $type Tipo de interação (ex: 'note', 'status_change', 'call')
 * @param string $content Conteúdo ou descrição do log
 * @return bool Retorna true se sucesso, false se der erro
 */
function insertLog($pdo, $lead_id, $user_id, $type, $content)
{
    try {
        // A query de inserção. Note o uso dos "dois pontos" (:param) - isso cria as "âncoras" de segurança
        $sql = "INSERT INTO lead_interactions (lead_id, user_id, type, content) 
                VALUES (:lead_id, :user_id, :type, :content)";

        $stmt = $pdo->prepare($sql);

        // Conectando os valores às âncoras com segurança
        $stmt->bindValue(':lead_id', $lead_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);

        // Executa a query e retorna true/false
        return $stmt->execute();
    } catch (PDOException $e) {
        // Se der erro (ex: tabela não existe ou nome de coluna errado), salva no log do PHP silenciosamente
        error_log("Erro em insertLog: " . $e->getMessage());
        return false;
    }
}

/**
 * ========================================================
 * Função para atualizar o status de um Lead
 * ========================================================
 * @param PDO $pdo Instância da conexão com o banco
 * @param int $lead_id ID do lead que será atualizado
 * @param string $status O novo status (ex: 'in_progress', 'won', 'lost')
 * @return bool Retorna true se sucesso, false se der erro
 */
function updateStatus($pdo, $lead_id, $status)
{
    try {
        // Query de atualização com âncoras de segurança (:status e :id)
        $sql = "UPDATE leads SET status = :status WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        // Fazendo o "bind" (ligação) dos valores com as âncoras
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $lead_id, PDO::PARAM_INT);

        // Executa a query e retorna true em caso de sucesso
        return $stmt->execute();
    } catch (PDOException $e) {
        // Loga o erro silenciosamente para o administrador
        error_log("Erro em updateStatus: " . $e->getMessage());
        return false;
    }
}

/**
 * ========================================================
 * BUSCAR UM ÚNICO LEAD POR ID
 * ========================================================
 * @param PDO $pdo Instância da conexão
 * @param int $id O ID do lead que queremos buscar
 * @return array|false Retorna o array do lead ou false se não achar
 */
function getLeadById($pdo, $id)
{
    try {
        // Usamos o '?' como um "lugar reservado" (placeholder)
        $sql = "SELECT leads.*, vehicles.brand, vehicles.model, vehicles.manufacture_year, vehicles.price 
        FROM leads LEFT JOIN vehicles ON leads.vehicle_id = vehicles.id WHERE leads.id = ?";

        // PREPARE: Prepara a consulta antes de inserir os dados do usuário.
        // Isso é o que blinda o sistema contra ataques de SQL Injection!
        $stmt = $pdo->prepare($sql);

        // EXECUTE: Injeta o ID real no lugar do '?' com total segurança
        $stmt->execute([$id]);

        // Usamos apenas fetch() em vez de fetchAll() porque queremos apenas 1 linha
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro em getLeadById: " . $e->getMessage());
        return false;
    }
}

/**
 * ========================================================
 * ADICIONAR NOVO LEAD (ENTRADA MANUAL)
 * ========================================================
 */
function adicionarLead($pdo, $dados)
{
    try {
        $sql = "INSERT INTO leads 
                (user_id, vehicle_id, origin_id, customer_name, customer_phone, customer_email, status, temperature) 
                VALUES 
                (:user_id, :vehicle_id, :origin_id, :customer_name, :customer_phone, :customer_email, 'new', 'warm')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int) $dados['user_id'], PDO::PARAM_INT);
        // Tratamento para salvar NULL se nenhum carro for selecionado
        $stmt->bindValue(':vehicle_id', !empty($dados['vehicle_id']) ? (int) $dados['vehicle_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':origin_id', (int) $dados['origin_id'], PDO::PARAM_INT);
        $stmt->bindValue(':customer_name', $dados['customer_name']);
        $stmt->bindValue(':customer_phone', $dados['customer_phone']);

        $email = empty($dados['customer_email']) ? null : $dados['customer_email'];
        $stmt->bindValue(':customer_email', $email, PDO::PARAM_STR);

        $stmt->execute();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        throw new Exception("Erro do MySQL: " . $e->getMessage());
    }
}

/**
 * ========================================================
 * ATUALIZAR DATA DE PRÓXIMO CONTATO (AGENDA)
 * ========================================================
 * @param PDO $pdo
 * @param int $lead_id
 * @param string $data_retorno (Formato YYYY-MM-DD HH:MM:SS)
 * @return bool
 */
function atualizarDataRetorno($pdo, $lead_id, $data_retorno)
{
    try {
        // Query simples e direta para atualizar apenas a coluna da agenda
        $sql = "UPDATE leads SET next_contact_at = :data_retorno WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':data_retorno', $data_retorno, PDO::PARAM_STR);
        $stmt->bindValue(':id', $lead_id, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro em atualizarDataRetorno: " . $e->getMessage());
        return false;
    }
}

/**
 * ========================================================
 * BUSCAR LEADS COM CONTATO AGENDADO (AGENDA)
 * ========================================================
 * @param PDO $pdo Instância da conexão com o banco
 * @return array Lista de leads agendados
 */
function getLeadsAgenda($pdo)
{
    try {
        // Também incluímos o JOIN aqui para o vendedor saber qual carro o cliente quer na agenda
        $sql = "SELECT leads.*, users.name AS seller_name, vehicles.brand, vehicles.model
                FROM leads 
                LEFT JOIN users ON leads.user_id = users.id 
                LEFT JOIN vehicles ON leads.vehicle_id = vehicles.id
                WHERE leads.next_contact_at IS NOT NULL 
                AND leads.status NOT IN ('won', 'lost')
                ORDER BY leads.next_contact_at ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro em getLeadsAgenda: " . $e->getMessage());
        return [];
    }
}

/**
 * ========================================================
 * ATUALIZAR DADOS CADASTRAIS DO LEAD (EDIÇÃO)
 * ========================================================
 * @param PDO $pdo Instância do banco
 * @param array $dados Dados vindos do formulário
 * @return bool
 */
function atualizarLead($pdo, $dados)
{
    try {
        $sql = "UPDATE leads 
                SET customer_name = :customer_name, 
                    customer_phone = :customer_phone, 
                    customer_email = :customer_email, 
                    origin_id = :origin_id,
                    vehicle_id = :vehicle_id,
                    vehicle_interest = :vehicle_interest
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':customer_name', $dados['customer_name'], PDO::PARAM_STR);
        $stmt->bindValue(':customer_phone', $dados['customer_phone'], PDO::PARAM_STR);

        $email = empty($dados['customer_email']) ? null : $dados['customer_email'];
        $stmt->bindValue(':customer_email', $email, PDO::PARAM_STR);

        $stmt->bindValue(':vehicle_id', !empty($dados['vehicle_id']) ? (int) $dados['vehicle_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':vehicle_interest', $dados['vehicle_interest'], PDO::PARAM_STR);
        $stmt->bindValue(':origin_id', (int) $dados['origin_id'], PDO::PARAM_INT);
        $stmt->bindValue(':id', (int) $dados['lead_id'], PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        throw new Exception("Erro do MySQL: " . $e->getMessage());
    }
}

/**
 * ========================================================
 * EXCLUIR LEAD E SUAS INTERAÇÕES (INTEGRIDADE TOTAL)
 * ========================================================
 * @param PDO $pdo
 * @param int $lead_id
 * @return bool
 */
function excluirLeadCompleto($pdo, $lead_id)
{
    try {
        // Iniciamos uma transação para garantir que apague TUDO ou NADA
        $pdo->beginTransaction();

        // 1. Apagamos primeiro as interações (logs) para evitar erro de Chave Estrangeira
        $sqlLogs = "DELETE FROM lead_interactions WHERE lead_id = :id";
        $stmtLogs = $pdo->prepare($sqlLogs);
        $stmtLogs->bindValue(':id', $lead_id, PDO::PARAM_INT);
        $stmtLogs->execute();

        // 2. Agora apagamos o Lead principal
        $sqlLead = "DELETE FROM leads WHERE id = :id";
        $stmtLead = $pdo->prepare($sqlLead);
        $stmtLead->bindValue(':id', $lead_id, PDO::PARAM_INT);
        $stmtLead->execute();

        // Se chegou aqui sem erros, confirma as duas exclusões
        return $pdo->commit();
    } catch (PDOException $e) {
        // Se algo deu errado, desfaz as alterações para não corromper o banco
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erro em excluirLeadCompleto: " . $e->getMessage());
        return false;
    }
}

/**
 * ========================================================
 * MOTOR DE ESTATÍSTICAS (KPIs do Dashboard)
 * ========================================================
 * Retorna a contagem total de leads agrupada por status em uma única query.
 * Utiliza "Conditional Aggregation" para máxima performance no banco de dados.
 * * @param PDO $pdo Instância da conexão com o banco
 * @return array Array associativo com os totais já tratados
 */
function getLeadsStats($pdo)
{
    try {
        // A MÁGICA SÊNIOR: Fazemos o banco contar todos os status de uma só vez.
        // Se a condição for verdadeira ele soma 1, senão soma 0.
        $sql = "SELECT 
                    COUNT(*) AS total_leads,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS total_new,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS total_in_progress,
                    SUM(CASE WHEN status = 'proposal_sent' THEN 1 ELSE 0 END) AS total_proposal,
                    SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) AS total_won,
                    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) AS total_lost
                FROM leads";

        $stmt = $pdo->query($sql);
        
        // Pega a linha única de resultados
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Tratamento Sênior: Garante que os valores voltem como inteiros (int), 
        // mesmo se a tabela estiver vazia (null)
        return [
            'total_leads'       => (int) ($resultado['total_leads'] ?? 0),
            'total_new'         => (int) ($resultado['total_new'] ?? 0),
            'total_in_progress' => (int) ($resultado['total_in_progress'] ?? 0),
            'total_proposal'    => (int) ($resultado['total_proposal'] ?? 0),
            'total_won'         => (int) ($resultado['total_won'] ?? 0),
            'total_lost'        => (int) ($resultado['total_lost'] ?? 0)
        ];

    } catch (PDOException $e) {
        // Log silencioso para não expor erros do banco ao cliente
        error_log("Erro em getLeadsStats: " . $e->getMessage());
        
        // Retorna tudo zerado em caso de falha crítica (evita quebrar a API)
        return [
            'total_leads' => 0, 'total_new' => 0, 'total_in_progress' => 0,
            'total_proposal' => 0, 'total_won' => 0, 'total_lost' => 0
        ];
    }
}
