<?php

// api\menu\cart\update_observation.php

session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

// Inicializa a resposta padrão
$response = ['success' => false, 'message' => ''];

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Lê os dados recebidos
        $data = json_decode(file_get_contents('php://input'), true);
        $itemId = $data['item_id'] ?? null;
        $observacao = $data['observacao'] ?? null;

        // Valida os dados recebidos
        if (!$itemId || !is_numeric($itemId)) {
            $response['message'] = 'ID do item inválido.';
            echo json_encode($response);
            exit();
        }

        if ($observacao !== null && strlen($observacao) > 100) {
            $response['message'] = 'A observação é muito longa. Máximo de 100 caracteres.';
            echo json_encode($response);
            exit();
        }

        $usuarioId = (int) $_SESSION['usuario_id'];

        // Atualiza a observação no banco de dados
        $stmt = $pdo->prepare("
            UPDATE tb_itens_carrinho
            SET observacao = :observacao
            WHERE usuario_id = :usuario_id AND item_id = :item_id
        ");
        $stmt->bindParam(':observacao', $observacao, PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Observação atualizada com sucesso.';
        } else {
            $response['message'] = 'Item não encontrado no carrinho.';
        }
    } catch (PDOException $e) {
        error_log('Erro ao atualizar observação: ' . $e->getMessage());
        $response['message'] = 'Erro ao atualizar observação.';
    }
} else {
    $response['message'] = 'Método não permitido.';
}

echo json_encode($response);
?>
