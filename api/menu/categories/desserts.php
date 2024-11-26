<?php

// api/menu/categories/desserts.php

require_once '../../../includes/db.php';
session_start();

$categoriaId = 3; // ID fixo da categoria "Sobremesas"

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    die("<h1 class='text-danger text-center mt-5'>Você precisa estar logado para acessar esta página.</h1>");
}

try {
    // Busca o nome da categoria
    $stmtCategoria = $pdo->prepare("SELECT nome FROM tb_categoria WHERE id = :categoria_id");
    $stmtCategoria->bindParam(':categoria_id', $categoriaId, PDO::PARAM_INT);
    $stmtCategoria->execute();
    $categoria = $stmtCategoria->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        die("<h1 class='text-danger text-center mt-5'>Categoria não encontrada.</h1>");
    }

    // Busca os itens da categoria
    $stmtItens = $pdo->prepare("
        SELECT 
            id AS itemId,
            nome, 
            descricao AS descricaoCompleta, 
            descricao_detalhada,
            preco,
            COALESCE(imagem, 'default-item.jpg') AS imagem
        FROM tb_item
        WHERE categoria_id = :categoria_id AND disponivel = 1
        ORDER BY nome
    ");
    $stmtItens->bindParam(':categoria_id', $categoriaId, PDO::PARAM_INT);
    $stmtItens->execute();
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<h1 class='text-danger text-center mt-5'>Erro ao buscar dados: " . htmlspecialchars($e->getMessage()) . "</h1>");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobremesas - Royale Burger</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/global-styles.css" />
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/header.css" />
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/categories.css" />
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/components/back-button.css" />
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/components/cart-button.css" />
    <link rel="stylesheet" href="/cardapio-hamburgueria/css/responsive.css" />
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-logo">
            <a href="/cardapio-hamburgueria/html/menu/index.html">
                <img src="/cardapio-hamburgueria/images/logo.png" alt="Royale Burger Logo" class="logo-image">
            </a>
        </div>
        <div class="header-info">
            <span id="mesa-info">Mesa: <?php echo htmlspecialchars($_SESSION['mesa'] ?? 'Não definida'); ?></span>
        </div>
    </header>

    <!-- Contêiner do botão "Voltar" -->
    <div id="back-button-container"></div>

    <!-- Carrinho -->
    <div class="cart-button-container">
      <a href="/cardapio-hamburgueria/html/menu/cart.html" class="cart-button">
        <img
          src="/cardapio-hamburgueria/images/cart-icon.png"
          alt="Carrinho"
          width="24"
          height="24"
        />
        <span>Carrinho</span>
        <span id="cart-count" class="cart-count">0</span>
      </a>
    </div>

    <!-- Conteúdo Principal -->
    <div class="container my-5">
        <h1 class="text-center text-warning mb-4"><?php echo htmlspecialchars($categoria['nome']); ?></h1>
        <div class="row">
            <?php if (empty($itens)): ?>
                <p class="text-center text-warning">Nenhum item encontrado nesta categoria.</p>
            <?php else: ?>
                <?php foreach ($itens as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card item-card">
                            <img src="/cardapio-hamburgueria/images/categories/<?php echo htmlspecialchars($item['imagem']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['nome']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['nome']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($item['descricaoCompleta']); ?></p>
                                <?php if (!empty($item['descricao_detalhada'])): ?>
                                    <div class="more-info" style="display: none;">
                                        <p class="detailed-description">
                                            <?php echo htmlspecialchars($item['descricao_detalhada']); ?>
                                        </p>
                                    </div>
                                    <button class="btn btn-warning show-more-btn">Mostrar Mais</button>
                                <?php endif; ?>
                                <p class="card-text text-success mt-3">
                                    <strong>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong>
                                </p>
                                <button 
                                    class="btn btn-warning add-to-cart" 
                                    data-item-id="<?php echo $item['itemId']; ?>" 
                                    data-item-name="<?php echo htmlspecialchars($item['nome']); ?>" 
                                    data-item-price="<?php echo $item['preco']; ?>">
                                    Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Adicionar ao Carrinho
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', async () => {
                const itemId = button.getAttribute('data-item-id');
                const quantidade = 1;

                try {
                    const response = await fetch('/cardapio-hamburgueria/api/menu/cart/add_to_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ item_id: itemId, quantidade }),
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('Item adicionado ao carrinho com sucesso!');
                        // Atualiza a página para refletir a nova contagem do carrinho
                        window.location.reload();
                    } else {
                        alert(result.message || 'Erro ao adicionar item.');
                    }
                } catch (error) {
                    console.error('Erro ao adicionar ao carrinho:', error);
                    alert('Erro ao adicionar ao carrinho.');
                }
            });
        });
    </script>
    <script src="/cardapio-hamburgueria/js/backButton.js"></script>
    <script src="/cardapio-hamburgueria/js/showMore.js"></script>
    <script src="/cardapio-hamburgueria/js/cart/cartCount.js"></script>
</body>
</html>
