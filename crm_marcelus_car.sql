-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23/05/2026 às 16:48
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `crm_marcelus_car`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `leads`
--

CREATE TABLE `leads` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `vehicle_id` int(10) UNSIGNED DEFAULT NULL,
  `origin_id` int(10) UNSIGNED NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `vehicle_interest` varchar(255) DEFAULT NULL,
  `vehicles_interest` varchar(255) DEFAULT NULL,
  `customer_document` varchar(20) DEFAULT NULL,
  `status` enum('new','in_progress','proposal_sent','won','lost') DEFAULT 'new',
  `next_contact_at` datetime DEFAULT NULL,
  `temperature` enum('cold','warm','hot') DEFAULT 'warm',
  `utm_source` varchar(100) DEFAULT NULL,
  `utm_medium` varchar(100) DEFAULT NULL,
  `utm_campaign` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `leads`
--

INSERT INTO `leads` (`id`, `user_id`, `vehicle_id`, `origin_id`, `customer_name`, `customer_phone`, `customer_email`, `vehicle_interest`, `vehicles_interest`, `customer_document`, `status`, `next_contact_at`, `temperature`, `utm_source`, `utm_medium`, `utm_campaign`, `created_at`, `updated_at`) VALUES
(3, 1, NULL, 1, 'João Silva Teste', '(11) 98888-7777', 'joao.teste@email.com', '', NULL, NULL, 'proposal_sent', NULL, 'warm', NULL, NULL, NULL, '2026-05-16 05:04:19', '2026-05-19 23:26:26'),
(5, 1, NULL, 3, 'Caio Santos', '(21) 99072-4131', 'caiosantosshake@hotmail.com', 'BMW 320i 2022', NULL, NULL, 'lost', '2026-05-30 14:42:00', 'warm', NULL, NULL, NULL, '2026-05-16 20:04:03', '2026-05-20 18:42:28'),
(8, 1, NULL, 2, 'Aidel Jones', '(21) 99072-4131', 'caiosantosshake@hotmail.com', 'Toyota Corolla XEI', NULL, NULL, 'proposal_sent', '2026-05-21 13:47:00', 'warm', NULL, NULL, NULL, '2026-05-16 20:40:04', '2026-05-21 00:23:57'),
(18, 1, NULL, 1, 'Cliente Teste Jan', '(21) 99999-1111', NULL, NULL, NULL, NULL, 'lost', NULL, 'warm', NULL, NULL, NULL, '2026-01-15 13:00:00', '2026-05-20 18:43:08'),
(19, 1, NULL, 1, 'Cliente Teste Fev', '(21) 99999-2222', NULL, NULL, NULL, NULL, 'won', NULL, 'warm', NULL, NULL, NULL, '2026-02-20 17:00:00', '2026-05-20 18:43:06'),
(20, 1, NULL, 1, 'Cliente Teste Mar', '(21) 99999-3333', NULL, '', NULL, NULL, 'won', NULL, 'warm', NULL, NULL, NULL, '2026-03-10 14:00:00', '2026-05-20 18:43:18'),
(21, 1, NULL, 1, 'Cliente Teste Abr', '(21) 99999-4444', NULL, '', NULL, NULL, 'lost', NULL, 'warm', NULL, NULL, NULL, '2026-04-05 19:00:00', '2026-05-21 13:11:21'),
(22, 1, NULL, 1, 'Lead de 3 dias atrás', '(21) 98888-1111', NULL, NULL, NULL, NULL, 'lost', NULL, 'warm', NULL, NULL, NULL, '2026-05-17 04:18:09', '2026-05-20 18:43:00'),
(23, 1, NULL, 1, 'Venda de 2 dias atrás', '(21) 98888-2222', NULL, NULL, NULL, NULL, 'new', '2026-05-29 14:41:00', 'warm', NULL, NULL, NULL, '2026-05-18 04:18:09', '2026-05-21 13:11:17'),
(26, 1, 12, 4, 'Tati', '(21) 99999-9999', 'tati@gmail.com', NULL, NULL, NULL, 'proposal_sent', '2026-05-23 21:26:00', 'warm', NULL, NULL, NULL, '2026-05-21 00:25:51', '2026-05-21 00:26:24'),
(27, 1, 11, 1, 'caio SANTOS', '(21) 99072-4131', 'caiosantosshake@hotmail.com', NULL, NULL, NULL, 'in_progress', '2026-05-22 10:24:00', 'warm', NULL, NULL, NULL, '2026-05-21 13:22:22', '2026-05-21 13:24:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lead_interactions`
--

CREATE TABLE `lead_interactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `lead_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('note','status_change','whatsapp','call','system') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lead_interactions`
--

INSERT INTO `lead_interactions` (`id`, `lead_id`, `user_id`, `type`, `content`, `created_at`) VALUES
(1, 3, 1, 'status_change', 'Status alterado para: proposal_sent', '2026-05-16 05:15:02'),
(4, 5, 1, 'note', 'Lead cadastrado manualmente no sistema.', '2026-05-16 20:04:03'),
(5, 5, 1, 'note', 'Liguei para o cliente, ele vem ver o carro amanhã às 14h', '2026-05-16 20:19:06'),
(6, 5, 1, 'note', 'teste', '2026-05-16 20:24:37'),
(7, 8, 1, 'note', 'Lead cadastrado manualmente no sistema.', '2026-05-16 20:40:04'),
(8, 8, 1, 'note', 'O cliente demonstrou interesse no veículo: <strong>Corolla XEI 2020</strong>', '2026-05-16 20:40:04'),
(9, 8, 1, 'note', 'Liguei para o cliente e ele pediu para retornar amanhã', '2026-05-16 20:44:05'),
(10, 8, 1, 'note', 'Cliente atendeu e pediu para ligar amanhã', '2026-05-18 19:38:54'),
(11, 8, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 21/05/2026 às 16:38', '2026-05-18 19:38:54'),
(12, 5, 1, 'note', 'Cliente atendeu e pediu para ligar amanhã', '2026-05-18 19:41:47'),
(13, 5, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 28/04/2026 às 16:41', '2026-05-18 19:41:47'),
(16, 8, 1, 'note', 'Cliente pediu para retornar!', '2026-05-19 16:48:16'),
(17, 8, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 21/05/2026 às 13:47', '2026-05-19 16:48:16'),
(18, 5, 1, 'note', 'Ligar depois', '2026-05-19 16:49:23'),
(19, 5, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 17/05/2026 às 13:48', '2026-05-19 16:49:23'),
(20, 5, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 20:46:10'),
(21, 8, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 22:18:55'),
(23, 8, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 23:07:50'),
(24, 5, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 23:08:01'),
(25, 3, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 23:26:26'),
(26, 5, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-19 23:56:03'),
(28, 5, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-20 00:04:28'),
(29, 8, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 00:35:22'),
(30, 5, 1, 'status_change', 'Status alterado para: Proposta Enviada', '2026-05-20 00:37:32'),
(31, 8, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 02:12:07'),
(32, 5, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-20 02:12:13'),
(33, 5, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 02:12:22'),
(34, 8, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-20 02:12:30'),
(35, 8, 1, 'status_change', 'Status alterado para: Proposta Enviada', '2026-05-20 02:12:32'),
(36, 8, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 02:12:38'),
(37, 8, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-20 02:12:41'),
(38, 5, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 02:12:46'),
(41, 23, 1, 'note', 'Visita', '2026-05-20 17:41:42'),
(42, 23, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 29/05/2026 às 14:41', '2026-05-20 17:41:42'),
(43, 5, 1, 'note', 'Teste', '2026-05-20 17:42:29'),
(44, 5, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 30/05/2026 às 14:42', '2026-05-20 17:42:29'),
(45, 8, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 17:42:58'),
(46, 5, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 17:43:00'),
(47, 23, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-20 17:48:07'),
(48, 23, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-20 18:42:26'),
(49, 5, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-20 18:42:28'),
(51, 22, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-20 18:43:00'),
(53, 19, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 18:43:06'),
(54, 18, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-20 18:43:08'),
(55, 21, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-20 18:43:13'),
(56, 20, 1, 'note', '✏️ Os dados cadastrais do cliente foram atualizados.', '2026-05-20 18:43:18'),
(59, 21, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 18:45:28'),
(60, 8, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 18:45:31'),
(62, 21, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-20 20:34:05'),
(63, 21, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 20:34:08'),
(64, 21, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-20 20:34:10'),
(65, 21, 1, 'status_change', 'Status alterado para: Novo', '2026-05-20 20:34:12'),
(66, 8, 1, 'status_change', 'Status alterado para: Proposta Enviada', '2026-05-21 00:23:57'),
(67, 26, 1, 'note', 'Lead cadastrado manualmente no sistema.', '2026-05-21 00:25:51'),
(68, 26, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-21 00:26:01'),
(69, 26, 1, 'status_change', 'Status alterado para: Proposta Enviada', '2026-05-21 00:26:03'),
(70, 26, 1, 'note', 'Tati, pediu para ligar pra ela', '2026-05-21 00:26:24'),
(71, 26, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 23/05/2026 às 21:26', '2026-05-21 00:26:24'),
(72, 23, 1, 'status_change', 'Status alterado para: Novo', '2026-05-21 13:11:17'),
(73, 21, 1, 'status_change', 'Status alterado para: Perdido', '2026-05-21 13:11:21'),
(74, 27, 1, 'note', 'Lead cadastrado manualmente no sistema.', '2026-05-21 13:22:22'),
(75, 27, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-21 13:22:32'),
(76, 27, 1, 'status_change', 'Status alterado para: Proposta Enviada', '2026-05-21 13:22:35'),
(77, 27, 1, 'status_change', 'Status alterado para: Vendido', '2026-05-21 13:22:37'),
(78, 27, 1, 'status_change', 'Status alterado para: Em Negociação', '2026-05-21 13:22:42'),
(79, 27, 1, 'note', 'MArquie vista amanhã', '2026-05-21 13:24:06'),
(80, 27, 1, 'note', '🕒 <strong>Retorno agendado para:</strong> 22/05/2026 às 10:24', '2026-05-21 13:24:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lead_origins`
--

CREATE TABLE `lead_origins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lead_origins`
--

INSERT INTO `lead_origins` (`id`, `name`, `is_active`) VALUES
(1, 'Site / Teste', 1),
(2, 'WhatsApp', 1),
(3, 'Instagram', 1),
(4, 'Site', 1),
(5, 'Indicação', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','seller') DEFAULT 'seller',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Caio Filardis', 'admin@marceluscar.com.br', '$2y$10$b7N8qOJ7/0u3y.p/AxkFwuHwDIp5HgSJsV/gkz6HmMG.6yOZ9mFrK', 'admin', '2026-05-16 02:48:43', '2026-05-16 03:19:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacture_year` year(4) NOT NULL,
  `model_year` year(4) NOT NULL,
  `mileage` int(10) UNSIGNED DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `license_plate` varchar(10) DEFAULT NULL,
  `status` enum('available','reserved','sold') DEFAULT 'available',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `manufacture_year`, `model_year`, `mileage`, `price`, `license_plate`, `status`, `image_path`, `created_at`) VALUES
(11, 'FIAT', 'ARGO DRIVE 1.0 6V FLEX MANUAL', '2020', '2021', 35000, 32000.00, NULL, 'available', 'uploads/vehicles/car_1779307983_6a0e15cf5f882.jpg', '2026-05-20 20:13:03'),
(12, 'Chevrolet', 'Onix 1.0 Turbo LTZ', '2022', '2023', 45000, 53590.36, NULL, 'available', 'uploads/vehicles/car_1779308080_6a0e1630381d5.jpg', '2026-05-20 20:14:40'),
(13, 'Jeep', 'Compass Longitude T270 (Turbo Flex)', '2019', '2020', 120000, 93790.00, NULL, 'available', 'uploads/vehicles/car_1779308189_6a0e169dc10a9.jpg', '2026-05-20 20:16:29'),
(14, 'Hyundai', 'Creta 1.6 Pulse Plus', '2018', '2019', 25000, 69000.00, NULL, 'available', 'uploads/vehicles/car_1779308265_6a0e16e919e1d.jpg', '2026-05-20 20:17:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `origin_id` (`origin_id`),
  ADD KEY `idx_lead_status` (`status`);

--
-- Índices de tabela `lead_interactions`
--
ALTER TABLE `lead_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `lead_origins`
--
ALTER TABLE `lead_origins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `idx_vehicle_status` (`status`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `lead_interactions`
--
ALTER TABLE `lead_interactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de tabela `lead_origins`
--
ALTER TABLE `lead_origins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_ibfk_3` FOREIGN KEY (`origin_id`) REFERENCES `lead_origins` (`id`);

--
-- Restrições para tabelas `lead_interactions`
--
ALTER TABLE `lead_interactions`
  ADD CONSTRAINT `lead_interactions_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_interactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
