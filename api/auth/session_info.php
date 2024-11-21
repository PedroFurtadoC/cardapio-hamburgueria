<?php

// api\auth\session_info.php

session_start();

// Valida a sessão
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['mesa' => 'Não definida']);
    exit();
}

// Valida a mesa
if (!isset($_SESSION['mesa']) || empty($_SESSION['mesa'])) {
    echo json_encode(['mesa' => 'Não definida']);
    exit();
}

// Retorna a mesa definida
echo json_encode(['mesa' => (int) $_SESSION['mesa']]); // Converte para número para evitar inconsistências
?>
