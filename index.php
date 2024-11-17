<?php
// index.php
require_once __DIR__ . '/includes/session.php'; // Valida a autenticação do usuário

// Redireciona o usuário para a página principal do cardápio
header('Location: /cardapio-hamburgueria/html/menu/index.html');
exit();
