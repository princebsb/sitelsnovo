-- ============================================
-- LS GLOBAL TECNOLOGIA - Sistema de Orçamento
-- Execute este SQL no phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS lsglobal_orcamento
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lsglobal_orcamento;

CREATE TABLE IF NOT EXISTS leads_orcamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    whatsapp VARCHAR(30) NOT NULL,
    tipo_projeto VARCHAR(50) NOT NULL,
    descricao_original TEXT NOT NULL,
    descricao_melhorada TEXT,
    funcionalidades JSON,
    integracoes JSON,
    sugestoes JSON,
    complexidade VARCHAR(20) DEFAULT 'media',
    horas_estimadas INT DEFAULT 0,
    valor_min DECIMAL(10,2) DEFAULT 0.00,
    valor_max DECIMAL(10,2) DEFAULT 0.00,
    prazo_min INT DEFAULT 0,
    prazo_max INT DEFAULT 0,
    status ENUM('novo', 'analisado', 'contatado', 'convertido') DEFAULT 'novo',
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
