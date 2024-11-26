<?php

// api/menu/cart/checkout.php

session_start();

require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        $response['message'] = 'Usuário não autenticado.';
        echo json_encode($response);
        exit();
    }

    $usuarioId = $_SESSION['usuario_id'];
    $mesa = $_SESSION['mesa'] ?? null;

    // Verifica se o número da mesa está definido
    if (!$mesa) {
        $response['message'] = 'Número da mesa não definido.';
        echo json_encode($response);
        exit();
    }

    try {
        // Valida se o carrinho está vazio
        $stmt = $pdo->prepare("
            SELECT 
                c.item_id, 
                i.nome, 
                c.quantidade, 
                c.observacao, 
                i.preco, 
                (c.quantidade * i.preco) AS subtotal 
            FROM tb_itens_carrinho c
            JOIN tb_item i ON c.item_id = i.id
            WHERE c.usuario_id = :usuario_id
        ");
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($carrinho)) {
            $response['message'] = 'O carrinho está vazio.';
            echo json_encode($response);
            exit();
        }

        // Calcula o total do pedido
        $total = array_reduce($carrinho, function ($carry, $item) {
            return $carry + $item['subtotal'];
        }, 0);

        // Cria o pedido principal
        $pedidoStmt = $pdo->prepare("
            INSERT INTO tb_pedido (usuario_id, mesa, total, status) 
            VALUES (:usuario_id, :mesa, :total, 'pendente')
        ");
        $pedidoStmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $pedidoStmt->bindParam(':mesa', $mesa, PDO::PARAM_INT);
        $pedidoStmt->bindParam(':total', $total, PDO::PARAM_STR);
        $pedidoStmt->execute();

        $pedidoId = $pdo->lastInsertId();

        // Insere os itens do pedido, incluindo observações
        $itensStmt = $pdo->prepare("
            INSERT INTO tb_pedido_item (pedido_id, item_id, quantidade, preco_unitario, observacao) 
            VALUES (:pedido_id, :item_id, :quantidade, :preco_unitario, :observacao)
        ");

        foreach ($carrinho as $item) {
            $itensStmt->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
            $itensStmt->bindParam(':item_id', $item['item_id'], PDO::PARAM_INT);
            $itensStmt->bindParam(':quantidade', $item['quantidade'], PDO::PARAM_INT);
            $itensStmt->bindParam(':preco_unitario', $item['preco'], PDO::PARAM_STR);
            $itensStmt->bindValue(':observacao', $item['observacao'] ?? null, PDO::PARAM_STR);
            $itensStmt->execute();
        }

        // Limpa o carrinho do usuário
        $clearCartStmt = $pdo->prepare("DELETE FROM tb_itens_carrinho WHERE usuario_id = :usuario_id");
        $clearCartStmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $clearCartStmt->execute();

        // Retorna resposta de sucesso
        $response['success'] = true;
        $response['message'] = 'Pedido confirmado com sucesso.';
        $response['pedido_id'] = $pedidoId;
        $response['mesa'] = htmlspecialchars($mesa, ENT_QUOTES, 'UTF-8');
        $response['items'] = $carrinho;
    } catch (PDOException $e) {
        // Registra o erro no log e retorna uma mensagem genérica
        error_log('Erro ao confirmar pedido: ' . $e->getMessage());
        $response['message'] = 'Erro ao confirmar pedido. Por favor, tente novamente.';
    }
} else {
    // Utiliza somente o método POST
    $response['message'] = 'Método não permitido.';
}

echo json_encode($response);
?>
