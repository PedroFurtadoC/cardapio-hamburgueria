<?php

// api/menu/cart/remove_from_cart.php

session_start();

require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'message' => '', 'items' => [], 'total' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lê os dados recebidos no corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    $itemId = $data['item_id'] ?? null;

    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        $response['message'] = 'Usuário não autenticado.';
        echo json_encode($response);
        exit();
    }

    // Valida o ID do item
    if (!$itemId || !is_numeric($itemId)) {
        $response['message'] = 'ID do item inválido ou não fornecido.';
        echo json_encode($response);
        exit();
    }

    try {
        // Remove o item do carrinho
        $stmt = $pdo->prepare("
            DELETE FROM tb_itens_carrinho 
            WHERE usuario_id = :usuario_id AND item_id = :item_id
        ");
        $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Atualiza os itens restantes no carrinho
            $cartFetchStmt = $pdo->prepare("
                SELECT 
                    c.item_id, 
                    i.nome, 
                    i.preco, 
                    c.quantidade, 
                    (i.preco * c.quantidade) AS subtotal
                FROM tb_itens_carrinho c
                JOIN tb_item i ON c.item_id = i.id
                WHERE c.usuario_id = :usuario_id
            ");
            $cartFetchStmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $cartFetchStmt->execute();
            $items = $cartFetchStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcula o total atualizado
            $total = 0;
            foreach ($items as $item) {
                $response['items'][] = [
                    'item_id' => (int) $item['item_id'],
                    'nome' => htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'),
                    'preco' => number_format((float) $item['preco'], 2, '.', ''),
                    'quantidade' => (int) $item['quantidade'],
                    'subtotal' => number_format((float) $item['subtotal'], 2, '.', ''),
                ];
                $total += (float) $item['subtotal'];
            }

            $response['total'] = number_format($total, 2, '.', '');
            $response['success'] = true;
            $response['message'] = 'Item removido do carrinho com sucesso.';
        } else {
            $response['message'] = 'Item não encontrado no carrinho.';
        }
    } catch (PDOException $e) {
        // Log de erros para depuração
        error_log('Erro ao remover item do carrinho: ' . $e->getMessage());
        $response['message'] = 'Erro ao processar a remoção do item. Tente novamente mais tarde.';
    }
} else {
    $response['message'] = 'Método não permitido.';
}

echo json_encode($response);
?>
