<?php

// api/auth/register.php

require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    try {
        // Verifica se o e-mail já está cadastrado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_usuario WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $emailExistente = $stmt->fetchColumn();

        if ($emailExistente > 0) {
            header('Location: /cardapio-hamburgueria/html/auth/register.html?error=email_exists');
            exit();
        }

        // Criptografa a senha
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        // Insere o novo usuário no banco de dados
        $insertStmt = $pdo->prepare("INSERT INTO tb_usuario (nome, email, senha) VALUES (:nome, :email, :senha)");
        $insertStmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $insertStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $insertStmt->bindParam(':senha', $senhaHash, PDO::PARAM_STR);
        $insertStmt->execute();

        // Redireciona para a página de login com sucesso
        header('Location: /cardapio-hamburgueria/html/auth/login.html?success=registered');
        exit();
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar usuário: " . $e->getMessage());
        header('Location: /cardapio-hamburgueria/html/auth/register.html?error=server_error');
        exit();
    }
}
?>
