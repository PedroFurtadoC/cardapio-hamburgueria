<?php

// api/auth/reset_password.php

require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida o formulário enviado e verifica se as senhas coincidem
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar-senha'];

    if ($senha !== $confirmarSenha) {
        // Redireciona para a página de redefinição com status de erro se as senhas forem diferentes
        header("Location: /cardapio-hamburgueria/html/auth/reset_password.html?email=" . urlencode($email) . "&status=mismatch");
        exit();
    }

    try {
        // Protege a nova senha usando criptografia padrão
        $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);

        // Tenta atualizar a senha do usuário no banco de dados
        $stmt = $pdo->prepare("UPDATE tb_usuario SET senha = :senha WHERE email = :email");
        $stmt->bindParam(':senha', $hashedSenha, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Caso a atualização seja bem-sucedida, redireciona para o login com status de sucesso
            header("Location: /cardapio-hamburgueria/html/auth/login.html?status=password_reset_success");
        } else {
            // Redireciona para a página de redefinição com mensagem de e-mail não encontrado
            header("Location: /cardapio-hamburgueria/html/auth/reset_password.html?email=" . urlencode($email) . "&status=email_not_found");
        }
        exit();
    } catch (PDOException $e) {
        // Em caso de erro no banco de dados, registra no log e redireciona para a página de redefinição com mensagem de erro
        error_log("Erro ao redefinir senha: " . $e->getMessage());
        header("Location: /cardapio-hamburgueria/html/auth/reset_password.html?email=" . urlencode($email) . "&status=server_error");
        exit();
    }
}
?>
