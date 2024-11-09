<?php
// Inclui a conexão com o banco de dados
include_once 'db.php';

// Conexão com o MySQL
try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criação do banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS royale_burger CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Banco de dados 'royale_burger' criado com sucesso.<br>";

    // Seleciona o banco de dados
    $pdo->exec("USE royale_burger");

    // Criação da tabela de usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS tb_usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'tb_usuario' criada com sucesso.<br>";

    // Criação da tabela de pedidos
    $pdo->exec("CREATE TABLE IF NOT EXISTS tb_pedido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'pendente',
        FOREIGN KEY (usuario_id) REFERENCES tb_usuario(id)
    )");
    echo "Tabela 'tb_pedido' criada com sucesso.<br>";

    // Criação da tabela de itens do cardápio
    $pdo->exec("CREATE TABLE IF NOT EXISTS tb_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10, 2) NOT NULL,
        categoria VARCHAR(50)
    )");
    echo "Tabela 'tb_item' criada com sucesso.<br>";

    // Criação da tabela de itens do pedido
    $pdo->exec("CREATE TABLE IF NOT EXISTS tb_pedido_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        item_id INT NOT NULL,
        quantidade INT DEFAULT 1,
        FOREIGN KEY (pedido_id) REFERENCES tb_pedido(id),
        FOREIGN KEY (item_id) REFERENCES tb_item(id)
    )");
    echo "Tabela 'tb_pedido_item' criada com sucesso.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
