<?php
require_once '../../includes/db.php';

$email = '';
$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['email'])) {
        $email = htmlspecialchars($_GET['email']);
    } else {
        die('E-mail não fornecido!');
    }

    if (isset($_GET['status'])) {
        switch ($_GET['status']) {
            case 'mismatch':
                $statusMessage = '<div class="text-danger text-center mb-3">As senhas não coincidem!</div>';
                break;
            case 'email_not_found':
                $statusMessage = '<div class="text-danger text-center mb-3">E-mail não encontrado no sistema!</div>';
                break;
            case 'server_error':
                $statusMessage = '<div class="text-danger text-center mb-3">Erro no servidor. Tente novamente mais tarde.</div>';
                break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar-senha'];

    if ($senha !== $confirmarSenha) {
        header("Location: /cardapio-hamburgueria/api/auth/reset_password.php?email=" . urlencode($email) . "&status=mismatch");
        exit();
    }

    try {
        $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE tb_usuario SET senha = :senha WHERE email = :email");
        $stmt->bindParam(':senha', $hashedSenha, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: /cardapio-hamburgueria/html/auth/login.html?status=password_reset_success");
        } else {
            header("Location: /cardapio-hamburgueria/api/auth/reset_password.php?email=" . urlencode($email) . "&status=email_not_found");
        }
        exit();
    } catch (PDOException $e) {
        error_log("Erro ao redefinir senha: " . $e->getMessage());
        header("Location: /cardapio-hamburgueria/api/auth/reset_password.php?email=" . urlencode($email) . "&status=server_error");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Royale Burger</title>

    <!-- Fontes e estilos principais -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos globais e específicos -->
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/global-styles.css">
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/auth-styles.css?v=1.1">
</head>

<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <img src="/cardapio-hamburgueria/images/logo.png" alt="Royale Burger Logo" class="logo-image mb-4">
            <h2>Redefinir Senha</h2>

            <!-- Mensagem de status -->
            <?php echo $statusMessage; ?>

            <!-- Formulário -->
            <form action="/cardapio-hamburgueria/api/auth/reset_password.php" method="POST">
                <input type="hidden" name="email" value="<?php echo $email; ?>">

                <div class="form-group mb-4">
                    <label for="senha" class="form-label">Nova Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control"
                        placeholder="Digite sua nova senha" required>
                </div>
                <div class="form-group mb-4">
                    <label for="confirmar-senha" class="form-label">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar-senha" name="confirmar-senha" class="form-control"
                        placeholder="Confirme sua nova senha" required>
                </div>

                <!-- Botão de envio -->
                <button type="submit" class="btn-submit">Redefinir Senha</button>
            </form>
        </div>
    </div>
</body>

</html>