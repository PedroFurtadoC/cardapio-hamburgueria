// js\showMore.js

// Adiciona funcionalidade para alternar entre mostrar e esconder informações adicionais
document.querySelectorAll(".show-more-btn").forEach((button) => {
  button.addEventListener("click", () => {
    // Obtém o elemento de informações adicionais associado ao botão
    const moreInfo = button.previousElementSibling;

    // Alterna a exibição das informações e o texto do botão
    if (moreInfo.style.display === "block") {
      moreInfo.style.display = "none";
      button.textContent = "Mostrar Mais";
    } else {
      moreInfo.style.display = "block";
      button.textContent = "Mostrar Menos";
    }
  });
});
