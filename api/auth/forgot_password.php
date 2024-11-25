<?php

// api/auth/forgot_password.php

require_once '../../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $statusMessage = '';

    // Verifica se o e-mail fornecido está cadastrado no sistema
    $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Cria o link para redefinir a senha, anexando o e-mail como parâmetro
        $resetLink = "http://localhost/cardapio-hamburgueria/html/auth/reset_password.html?email=" . urlencode($email);

        // Configura e envia o e-mail de redefinição usando MailHog
        $subject = "Redefinição de Senha - Royale Burger";
        $message = "Olá, clique no link abaixo para redefinir sua senha:\n\n$resetLink";
        $headers = "From: no-reply@royaleburger.com";

        // Define a mensagem de status com base no envio do e-mail
        if (mail($email, $subject, $message, $headers)) {
            $statusMessage = 'E-mail de redefinição enviado com sucesso!';
        } else {
            $statusMessage = 'Erro ao enviar o e-mail. Tente novamente mais tarde.';
        }
    } else {
        $statusMessage = 'E-mail não encontrado.';
    }

    // Redireciona para a página de "Esqueceu a Senha" com o status (sucesso ou erro)
    header("Location: /cardapio-hamburgueria/html/auth/forgot_password.html?status=" . urlencode($statusMessage));
    exit();
}
?>
