// js\cart\cartCount.js

// Atualiza o número de itens no carrinho.
function atualizarCarrinho() {
  fetch("/cardapio-hamburgueria/api/menu/cart/cart_count.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(
          "Erro ao carregar o carrinho. Status: " + response.status
        );
      }
      return response.json();
    })
    .then((data) => {
      const cartCountElement = document.getElementById("cart-count");
      if (cartCountElement) {
        cartCountElement.textContent = data.count || 0;
      }
    })
    .catch((error) => {
      console.error("Erro ao atualizar a contagem do carrinho:", error);
      const cartCountElement = document.getElementById("cart-count");
      if (cartCountElement) {
        cartCountElement.textContent = "0";
      }
    });
}

// Inicializa a contagem do carrinho ao carregar a página.
document.addEventListener("DOMContentLoaded", atualizarCarrinho);
