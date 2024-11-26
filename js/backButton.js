// js\backButton.js

document.addEventListener("DOMContentLoaded", function () {
  const backButtonContainer = document.getElementById("back-button-container");

  if (backButtonContainer) {
    // Ao clicar, executa a lógica
    backButtonContainer.innerHTML = `
      <button class="back-button" onclick="handleBackButton()">
        <img src="/cardapio-hamburgueria/images/back.png" alt="Voltar" class="back-icon" />
        Voltar
      </button>
    `;
  }
});

// Lógica do botão Voltar
function handleBackButton() {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    window.location.href = "/cardapio-hamburgueria/html/menu/index.html";
  }
}
