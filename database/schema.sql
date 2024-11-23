-- database\schema.sql

-- ===========================================
-- Limpando o banco de dados (opcional, use com cautela)
-- ===========================================

-- DROP DATABASE royale_burger; -- Remove o banco de dados inteiro

-- Caso deseje apenas apagar as tabelas:
-- DROP TABLE IF EXISTS tb_pedido_item;
-- DROP TABLE IF EXISTS tb_pedido;
-- DROP TABLE IF EXISTS tb_itens_carrinho;
-- DROP TABLE IF EXISTS tb_login;
-- DROP TABLE IF EXISTS tb_item;
-- DROP TABLE IF EXISTS tb_categoria;
-- DROP TABLE IF EXISTS tb_usuario;

-- ===========================================
-- Criação do banco de dados e estrutura inicial
-- ===========================================

CREATE DATABASE IF NOT EXISTS royale_burger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE royale_burger;

-- ===========================================
-- Tabela de Usuários
-- ===========================================

CREATE TABLE tb_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    role ENUM('funcionario', 'admin') DEFAULT 'funcionario',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- Tabela de Categorias
-- ===========================================

CREATE TABLE tb_categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

-- ===========================================
-- Tabela de Itens
-- ===========================================

CREATE TABLE tb_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES tb_categoria(id) ON DELETE CASCADE
);

-- ===========================================
-- Tabela de Logins
-- ===========================================

CREATE TABLE tb_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mesa INT NOT NULL,
    data_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_logout TIMESTAMP NULL DEFAULT NULL,
    status ENUM('ativo', 'encerrado') DEFAULT 'ativo',
    FOREIGN KEY (usuario_id) REFERENCES tb_usuario(id) ON DELETE CASCADE
);

-- ===========================================
-- Tabela de Itens no Carrinho
-- ===========================================

CREATE TABLE tb_itens_carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    item_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    observacao TEXT DEFAULT NULL
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES tb_item(id) ON DELETE CASCADE
);

-- ===========================================
-- Tabela de Pedidos
-- ===========================================

CREATE TABLE tb_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mesa INT NOT NULL,
    status ENUM('pendente', 'concluido', 'cancelado') DEFAULT 'pendente',
    total DECIMAL(10, 2) NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuario(id) ON DELETE CASCADE
);

-- ===========================================
-- Tabela de Itens de Pedidos
-- ===========================================

CREATE TABLE tb_pedido_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    item_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    preco_total DECIMAL(10, 2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    observacao TEXT DEFAULT NULL
    FOREIGN KEY (pedido_id) REFERENCES tb_pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES tb_item(id) ON DELETE CASCADE
);
