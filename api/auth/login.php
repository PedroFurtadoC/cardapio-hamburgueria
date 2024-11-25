<?php

// api/auth/login.php

session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $mesa = $_POST['mesa'];

    try {
        // Verifica se o usuário existe no banco de dados
        $stmt = $pdo->prepare("SELECT id, nome, senha FROM tb_usuario WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Insere o login na tabela de logins
            $loginStmt = $pdo->prepare("
                INSERT INTO tb_login (usuario_id, mesa, status) 
                VALUES (:usuario_id, :mesa, 'ativo')
            ");
            $loginStmt->bindParam(':usuario_id', $usuario['id'], PDO::PARAM_INT);
            $loginStmt->bindParam(':mesa', $mesa, PDO::PARAM_INT);
            $loginStmt->execute();

            // Define variáveis de sessão para gerenciar o login
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['mesa'] = $mesa;
            $_SESSION['login_id'] = $pdo->lastInsertId();

            // Redireciona para o menu principal
            header('Location: /cardapio-hamburgueria/html/menu/index.html');
            exit();
        } else {
            // Redireciona para o login com erro de credenciais
            header('Location: /cardapio-hamburgueria/html/auth/login.html?error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        // Redireciona para o login com erro genérico
        header('Location: /cardapio-hamburgueria/html/auth/login.html?error=server_error');
        exit();
    }
}
?>
