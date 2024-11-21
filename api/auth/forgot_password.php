<?php

// api/auth/forgot_password.php

require_once '../../includes/db.php'; 

// Inicializa variáveis para feedback
$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Verifica se o e-mail existe no banco de dados
    $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Gera o link de redefinição de senha
        $resetLink = "http://localhost/cardapio-hamburgueria/api/auth/reset_password.php?email=" . urlencode($email);

        // Configuração do e-mail (MailHog)
        $subject = "Redefinição de Senha - Royale Burger";
        $message = "Olá, clique no link abaixo para redefinir sua senha:\n\n$resetLink";
        $headers = "From: no-reply@royaleburger.com";

        // Envia o e-mail e define mensagem de sucesso
        if (mail($email, $subject, $message, $headers)) {
            $statusMessage = '<div class="text-success text-center mb-3">E-mail de redefinição enviado com sucesso!</div>';
        } else {
            $statusMessage = '<div class="text-danger text-center mb-3">Erro ao enviar o e-mail. Tente novamente mais tarde.</div>';
        }
    } else {
        $statusMessage = '<div class="text-danger text-center mb-3">E-mail não encontrado.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu a Senha - Royale Burger</title>

    <!-- Fontes e estilos principais -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos globais e específicos para autenticação -->
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/global-styles.css">
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/auth-styles.css?v=1.1">
</head>

<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <img src="/cardapio-hamburgueria/images/logo.png" alt="Royale Burger Logo" class="logo-image mb-4">

            <!-- Título -->
            <h2>Recuperar Senha</h2>

            <!-- Mensagem de feedback -->
            <?php echo $statusMessage; ?>

            <!-- Formulário -->
            <form action="/cardapio-hamburgueria/api/auth/forgot_password.php" method="POST">
                <div class="form-group mb-4">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="Digite seu e-mail" required>
                </div>

                <!-- Botão de envio -->
                <button type="submit" class="btn-submit">Enviar Link de Recuperação</button>
            </form>

            <!-- Link para login -->
            <div class="auth-link mt-4">
                <a href="/cardapio-hamburgueria/html/auth/login.html">Voltar ao Login</a>
            </div>
        </div>
    </div>
</body>
</html>
