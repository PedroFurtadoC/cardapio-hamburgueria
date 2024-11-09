<?php
// Definição das variáveis de conexão com o banco de dados
$host = 'localhost';
$user = 'root';
$password = ''; // Deixe vazio se você não configurou uma senha para o usuário root
$dbname = 'royale_burger';

// Tente estabelecer uma conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configura a conexão para usar UTF-8
    $pdo->exec("set names utf8mb4");
} catch (PDOException $e) {
    // Exibe uma mensagem de erro em caso de falha
    echo "Erro de conexão com o banco de dados: " . $e->getMessage();
    exit();
}
?>
