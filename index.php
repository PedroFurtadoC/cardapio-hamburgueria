<?php
// Iniciar sessão para verificar autenticação, se necessário
session_start();

// Verificar se o usuário está logado
if (isset($_SESSION['user_id'])) {
    // Redirecionar para o menu principal (cardápio) se estiver logado
    header("Location: html/menu.html");
    exit();
} else {
    // Se o usuário não estiver logado, redirecionar para a página de login
    header("Location: html/login.html");
    exit();
}
