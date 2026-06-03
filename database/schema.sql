CREATE DATABASE IF NOT EXISTS semana_enfermagem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE semana_enfermagem;

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_reference VARCHAR(64) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'BRL',
    payment_status VARCHAR(30) NOT NULL DEFAULT 'checkout_iniciado',
    payment_id VARCHAR(64) NULL,
    payment_date DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_registrations_created_at (created_at),
    INDEX idx_registrations_payment_status (payment_status)
);
