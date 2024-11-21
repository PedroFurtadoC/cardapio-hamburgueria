<?php

// api/auth/login.php

session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $mesa = $_POST['mesa'];

    try {
        // Verifica se o usuário existe
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

            // Recupera o ID do login recém-criado
            $loginId = $pdo->lastInsertId();

            // Define as variáveis de sessão necessárias para o logout
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['mesa'] = $mesa; // Define a mesa selecionada
            $_SESSION['login_id'] = $loginId;

            // Redireciona para o menu principal
            header('Location: /cardapio-hamburgueria/html/menu/index.html');
            exit();
        } else {
            // Redireciona de volta ao login com mensagem de erro
            header('Location: /cardapio-hamburgueria/html/auth/login.html?error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        header('Location: /cardapio-hamburgueria/html/auth/login.html?error=server_error');
        exit();
    }
}
