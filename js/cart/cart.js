// js\cart\cart.js

document.addEventListener("DOMContentLoaded", function () {
  const cartItemsContainer = document.getElementById("cart-items");
  const cartTotalElement = document.getElementById("cart-total");
  const confirmButton = document.getElementById("confirm-button");

  // Carrega os itens do carrinho e atualiza a interface.
  async function carregarCarrinho() {
    cartItemsContainer.innerHTML = "<p>Carregando...</p>";

    try {
      const response = await fetch(
        "/cardapio-hamburgueria/api/menu/cart/fetch_cart.php"
      );
      const data = await response.json();

      if (data.success) {
        cartItemsContainer.innerHTML = "";

        if (data.items.length === 0) {
          cartItemsContainer.innerHTML = "<p>Seu carrinho está vazio.</p>";
        } else {
          data.items.forEach((item) => {
            const itemElement = document.createElement("div");
            itemElement.classList.add("cart-item");
            itemElement.innerHTML = `
              <div class="item-info">
                <h4>${item.nome}</h4>
                <p>Preço: R$ ${parseFloat(item.preco).toFixed(2)}</p>
                <div class="quantity-control">
                  <button class="btn-decrease" data-item-id="${
                    item.item_id
                  }">-</button>
                  <input type="number" class="item-quantity" data-item-id="${
                    item.item_id
                  }" 
                      data-preco="${item.preco}" value="${
              item.quantidade
            }" min="1" max="15">
                  <button class="btn-increase" data-item-id="${
                    item.item_id
                  }">+</button>
                </div>
                <p class="subtotal">Subtotal: R$ <span id="subtotal-${
                  item.item_id
                }">
                  ${parseFloat(item.subtotal).toFixed(2)}</span>
                </p>
              </div>
              <textarea class="item-observation" data-item-id="${
                item.item_id
              }" placeholder="Adicione observações aqui...">${
              item.observacao || ""
            }</textarea>
              <button class="remove-button" data-item-id="${
                item.item_id
              }">Remover</button>
            `;
            cartItemsContainer.appendChild(itemElement);
          });

          adicionarEventosQuantidade();
          adicionarEventosRemocao();
          adicionarEventosObservacoes();
        }

        cartTotalElement.textContent = parseFloat(data.total).toFixed(2);
      } else {
        cartItemsContainer.innerHTML = `<p>${
          data.message || "Erro ao carregar o carrinho."
        }</p>`;
      }
    } catch (error) {
      cartItemsContainer.innerHTML =
        "<p>Erro ao carregar o carrinho. Tente novamente.</p>";
      console.error("Erro ao carregar o carrinho:", error);
    }
  }

  /**
   * Atualiza o subtotal do item no frontend.
   */
  function atualizarSubtotal(itemId, quantidade, preco) {
    const subtotalElement = document.getElementById(`subtotal-${itemId}`);
    const subtotal = quantidade * preco;
    subtotalElement.textContent = subtotal.toFixed(2);
  }

  //Recalcula o total do carrinho no frontend.
  function atualizarTotalGlobal() {
    let total = 0;
    document.querySelectorAll(".item-quantity").forEach((input) => {
      const quantidade = parseInt(input.value);
      const preco = parseFloat(input.dataset.preco);
      total += quantidade * preco;
    });
    cartTotalElement.textContent = total.toFixed(2);
  }

  // Atualiza as observações do item no backend.
  async function atualizarObservacao(itemId, observacao) {
    try {
      const response = await fetch(
        "/cardapio-hamburgueria/api/menu/cart/update_observation.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ item_id: itemId, observacao }),
        }
      );

      const result = await response.json();
      if (!result.success) {
        console.error(result.message || "Erro ao salvar a observação.");
      }
    } catch (error) {
      console.error("Erro ao atualizar a observação:", error);
    }
  }

  // Adiciona eventos para o campo de observações.
  function adicionarEventosObservacoes() {
    document.querySelectorAll(".item-observation").forEach((textarea) => {
      textarea.addEventListener("blur", function () {
        const itemId = this.getAttribute("data-item-id");
        const observacao = this.value.trim();
        atualizarObservacao(itemId, observacao);
      });
    });
  }

  // Atualiza o item no carrinho e sincroniza com o backend.
  async function atualizarItemCarrinho(itemId, quantidade = null) {
    try {
      const response = await fetch(
        "/cardapio-hamburgueria/api/menu/cart/update_cart_item.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ item_id: itemId, quantidade }),
        }
      );

      const result = await response.json();
      if (result.success) {
        carregarCarrinho();
      } else {
        console.error(result.message);
      }
    } catch (error) {
      console.error("Erro ao atualizar o item:", error);
    }
  }

  // Adiciona eventos para os botões de aumentar/diminuir quantidade.
  function adicionarEventosQuantidade() {
    document.querySelectorAll(".btn-increase").forEach((button) => {
      button.addEventListener("click", async function () {
        const itemId = this.getAttribute("data-item-id");
        const input = document.querySelector(
          `.item-quantity[data-item-id="${itemId}"]`
        );
        let quantidade = parseInt(input.value);
        const preco = parseFloat(input.dataset.preco);

        if (quantidade < 15) {
          quantidade++;
          input.value = quantidade;

          const sucesso = await atualizarItemCarrinho(itemId, quantidade);
          if (sucesso) {
            atualizarSubtotal(itemId, quantidade, preco);
            atualizarTotalGlobal();
          }
        }
      });
    });

    document.querySelectorAll(".btn-decrease").forEach((button) => {
      button.addEventListener("click", async function () {
        const itemId = this.getAttribute("data-item-id");
        const input = document.querySelector(
          `.item-quantity[data-item-id="${itemId}"]`
        );
        let quantidade = parseInt(input.value);
        const preco = parseFloat(input.dataset.preco);

        if (quantidade > 1) {
          quantidade--;
          input.value = quantidade;

          const sucesso = await atualizarItemCarrinho(itemId, quantidade);
          if (sucesso) {
            atualizarSubtotal(itemId, quantidade, preco);
            atualizarTotalGlobal();
          }
        }
      });
    });
  }

  // Adiciona eventos para os botões de remoção.
  function adicionarEventosRemocao() {
    document.querySelectorAll(".remove-button").forEach((button) => {
      button.addEventListener("click", async function () {
        const itemId = this.getAttribute("data-item-id");

        // Confirmação antes de remover
        const confirmar = confirm(
          "Tem certeza de que deseja remover este item do carrinho?"
        );
        if (confirmar) {
          const sucesso = await removerItemCarrinho(itemId);
          if (sucesso) {
            alert("Item removido com sucesso!");
            carregarCarrinho();
          } else {
            alert("Erro ao remover o item. Tente novamente.");
          }
        }
      });
    });
  }

  // Remove um item do carrinho.
  async function removerItemCarrinho(itemId) {
    try {
      const response = await fetch(
        "/cardapio-hamburgueria/api/menu/cart/remove_from_cart.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ item_id: itemId }),
        }
      );

      const result = await response.json();
      return result.success;
    } catch (error) {
      console.error("Erro ao remover o item do carrinho:", error);
      return false;
    }
  }

  confirmButton.addEventListener("click", async function () {
    try {
      const response = await fetch(
        "/cardapio-hamburgueria/api/menu/cart/checkout.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
        }
      );

      const result = await response.json();
      if (result.success) {
        alert("Pedido confirmado com sucesso!");
        window.location.href =
          "/cardapio-hamburgueria/html/menu/order_summary.html";
      } else {
        alert(result.message || "Erro ao confirmar o pedido.");
      }
    } catch (error) {
      alert("Erro ao confirmar o pedido. Tente novamente.");
      console.error("Erro ao confirmar o pedido:", error);
    }
  });

  carregarCarrinho();
});
