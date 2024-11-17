<?php
// includes/session.php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para a página de login se o usuário não estiver autenticado
    header('Location: /cardapio-hamburgueria/html/auth/login.html');
    exit();
}
?>
