<?php

// api/menu/cart/cart_count.php

session_start();

require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'count' => 0, 'message' => ''];

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

try {
    // Consulta para contar os itens do carrinho do usuário
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(quantidade), 0) AS total 
        FROM tb_itens_carrinho 
        WHERE usuario_id = :usuario_id
    ");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Adiciona o total ao campo de resposta
    $response['count'] = (int) $result['total'];
    $response['success'] = true;
    $response['message'] = 'Contagem realizada com sucesso.';
} catch (PDOException $e) {
    // Registra o erro no log e retorna uma mensagem genérica
    error_log('Erro ao contar itens do carrinho: ' . $e->getMessage());
    $response['message'] = 'Erro ao contar itens do carrinho. Tente novamente mais tarde.';
}

echo json_encode($response);
