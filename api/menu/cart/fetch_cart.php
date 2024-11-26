<?php

// api/menu/cart/fetch_cart.php

session_start();

require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'items' => [], 'total' => 0];

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

$usuarioId = $_SESSION['usuario_id'];

try {
    // Busca os itens do carrinho do usuário, incluindo a observação
    $stmt = $pdo->prepare("
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
    $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;

    foreach ($items as $item) {
        // Adiciona os itens formatados à resposta, incluindo a observação
        $response['items'][] = [
            'item_id' => (int)$item['item_id'],
            'nome' => htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'),
            'preco' => number_format((float)$item['preco'], 2, '.', ''),
            'quantidade' => (int)$item['quantidade'],
            'observacao' => htmlspecialchars($item['observacao'] ?? '', ENT_QUOTES, 'UTF-8'),
            'subtotal' => number_format((float)$item['subtotal'], 2, '.', ''),
        ];
        $total += (float)$item['subtotal'];
    }

    // Adiciona informações gerais ao carrinho
    $response['mesa'] = htmlspecialchars($_SESSION['mesa'] ?? 'Não definida', ENT_QUOTES, 'UTF-8');
    $response['total'] = number_format($total, 2, '.', '');
    $response['success'] = true;
    $response['message'] = 'Carrinho carregado com sucesso.';
} catch (PDOException $e) {
    // Registra erros no log e retorna mensagem genérica
    error_log('Erro ao buscar o carrinho: ' . $e->getMessage());
    $response['message'] = 'Erro ao carregar o carrinho.';
}

echo json_encode($response);
