<?php

// api\menu\cart\update_cart_item.php

session_start();
require_once '../../../includes/db.php';
header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'message' => '', 'items' => [], 'total' => 0];

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $itemId = isset($data['item_id']) ? (int) $data['item_id'] : 0;
        $novaQuantidade = isset($data['quantidade']) ? (int) $data['quantidade'] : null;

        // Valida o ID do item e a quantidade
        if ($itemId <= 0 || $novaQuantidade === null || $novaQuantidade < 1 || $novaQuantidade > 15) {
            $response['message'] = 'Quantidade inválida ou ID do item incorreto.';
            echo json_encode($response);
            exit();
        }

        // Atualiza a quantidade do item no carrinho
        $stmt = $pdo->prepare("
            UPDATE tb_itens_carrinho 
            SET quantidade = :quantidade 
            WHERE usuario_id = :usuario_id AND item_id = :item_id
        ");
        $stmt->bindParam(':quantidade', $novaQuantidade, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $response['message'] = 'Item não encontrado no carrinho.';
            echo json_encode($response);
            exit();
        }

        // Retorna os detalhes atualizados do carrinho
        $cartStmt = $pdo->prepare("
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
        $cartStmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $cartStmt->execute();
        $items = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcula o total do carrinho
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
        $response['message'] = 'Quantidade atualizada com sucesso.';
    } catch (PDOException $e) {
        // Registra o erro no log e retorna uma mensagem genérica
        error_log('Erro ao atualizar item no carrinho: ' . $e->getMessage());
        $response['message'] = 'Erro ao atualizar o item.';
    }
} else {
    $response['message'] = 'Método não permitido.';
}

echo json_encode($response);
?>
