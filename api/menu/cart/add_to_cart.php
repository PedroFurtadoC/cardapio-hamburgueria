<?php

// api/menu/cart/add_to_cart.php

session_start();

require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'message' => '', 'items' => [], 'total' => 0];

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lê os dados recebidos no corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    $itemId = $data['item_id'] ?? null;
    $quantidade = $data['quantidade'] ?? 1;
    $observacao = $data['observacao'] ?? null;

    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        $response['message'] = 'Usuário não autenticado.';
        echo json_encode($response);
        exit();
    }

    // Valida os dados recebidos
    if (!$itemId || $quantidade < 1) {
        $response['message'] = 'Dados inválidos. Verifique o item e a quantidade.';
        echo json_encode($response);
        exit();
    }

    // Valida a observação
    if ($observacao !== null && strlen($observacao) > 100) {
        $response['message'] = 'A observação é muito longa. Máximo de 100 caracteres.';
        echo json_encode($response);
        exit();
    }

    try {
        // Verifica se o item existe e está disponível
        $itemCheckStmt = $pdo->prepare("SELECT id, preco FROM tb_item WHERE id = :item_id AND disponivel = 1");
        $itemCheckStmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $itemCheckStmt->execute();
        $itemData = $itemCheckStmt->fetch(PDO::FETCH_ASSOC);

        if (!$itemData) {
            $response['message'] = 'Item inválido ou indisponível.';
            echo json_encode($response);
            exit();
        }

        // Verifica se o item já está no carrinho
        $cartCheckStmt = $pdo->prepare("
            SELECT id, quantidade, observacao 
            FROM tb_itens_carrinho 
            WHERE usuario_id = :usuario_id AND item_id = :item_id
        ");
        $cartCheckStmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
        $cartCheckStmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $cartCheckStmt->execute();
        $cartItem = $cartCheckStmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Atualiza a quantidade e observação do item existente
            $newQuantity = $cartItem['quantidade'] + $quantidade;
            $updateStmt = $pdo->prepare("
                UPDATE tb_itens_carrinho 
                SET quantidade = :quantidade, observacao = :observacao 
                WHERE id = :cart_item_id
            ");
            $updateStmt->bindParam(':quantidade', $newQuantity, PDO::PARAM_INT);
            $updateStmt->bindParam(':observacao', $observacao, PDO::PARAM_STR);
            $updateStmt->bindParam(':cart_item_id', $cartItem['id'], PDO::PARAM_INT);
            $updateStmt->execute();
        } else {
            // Adiciona o item ao carrinho com a observação
            $insertStmt = $pdo->prepare("
                INSERT INTO tb_itens_carrinho (usuario_id, item_id, quantidade, observacao) 
                VALUES (:usuario_id, :item_id, :quantidade, :observacao)
            ");
            $insertStmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $insertStmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
            $insertStmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
            $insertStmt->bindParam(':observacao', $observacao, PDO::PARAM_STR);
            $insertStmt->execute();
        }

        // Recarrega os itens do carrinho para atualizar a interface
        $cartFetchStmt = $pdo->prepare("
            SELECT 
                c.item_id, 
                i.nome, 
                i.preco, 
                c.quantidade, 
                c.observacao,
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
                'observacao' => htmlspecialchars($item['observacao'], ENT_QUOTES, 'UTF-8'),
                'subtotal' => number_format((float) $item['subtotal'], 2, '.', ''),
            ];
            $total += (float) $item['subtotal'];
        }

        $response['total'] = number_format($total, 2, '.', '');
        $response['success'] = true;
        $response['message'] = 'Item adicionado ao carrinho com sucesso.';
    } catch (PDOException $e) {
        error_log('Erro ao adicionar item ao carrinho: ' . $e->getMessage());
        $response['message'] = 'Erro ao adicionar item ao carrinho.';
    }
} else {
    $response['message'] = 'Método não permitido.';
}

echo json_encode($response);
