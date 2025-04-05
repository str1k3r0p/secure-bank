-- Banking DVWA Project Database Schema

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `bdv_statements`;
DROP TABLE IF EXISTS `bdv_transactions`;
DROP TABLE IF EXISTS `bdv_accounts`;
DROP TABLE IF EXISTS `bdv_security_levels`;
DROP TABLE IF EXISTS `bdv_users`;

-- Create users table
CREATE TABLE `bdv_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create accounts table
CREATE TABLE `bdv_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bdv_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bdv_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create transactions table
CREATE TABLE `bdv_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `transaction_type` varchar(20) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text,
  `reference` varchar(50) NOT NULL,
  `to_account_id` int(11) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `to_account_id` (`to_account_id`),
  CONSTRAINT `bdv_transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `bdv_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bdv_transactions_ibfk_2` FOREIGN KEY (`to_account_id`) REFERENCES `bdv_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create statements table
CREATE TABLE `bdv_statements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `statement_date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL,
  `closing_balance` decimal(15,2) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `bdv_statements_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `bdv_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create security levels table
CREATE TABLE `bdv_security_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vulnerability` varchar(50) NOT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'low',
  `description` text,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vulnerability` (`vulnerability`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bdv_security_levels_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bdv_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;