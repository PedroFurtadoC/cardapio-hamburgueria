<?php

// api/auth/logout.php

session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido. Use POST.';
    echo json_encode($response);
    exit();
}

// Verifica se a sessão está ativa
if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Por favor, chame o atendente. Sua sessão expirou.';
    $response['expired'] = true;
    echo json_encode($response);
    session_destroy();
    exit();
}

// Recupera dados enviados
$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? null;

if (!$password) {
    $response['message'] = 'Senha não fornecida.';
    echo json_encode($response);
    exit();
}

try {
    // Verifica a senha do usuário
    $stmt = $pdo->prepare("SELECT senha FROM tb_usuario WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['senha'])) {
        // Atualiza o status do login na tabela
        $stmtUpdate = $pdo->prepare("UPDATE tb_login SET status = 'encerrado', data_logout = NOW() WHERE id = :login_id");
        $stmtUpdate->bindParam(':login_id', $_SESSION['login_id'], PDO::PARAM_INT);
        $stmtUpdate->execute();

        // Finaliza a sessão
        session_unset();
        session_destroy();

        $response['success'] = true;
        $response['message'] = 'Logout realizado com sucesso.';
    } else {
        $response['message'] = 'Senha incorreta.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro ao realizar o logout: ' . $e->getMessage();
}

echo json_encode($response);
