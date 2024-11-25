// js\auth\login.js

document.addEventListener("DOMContentLoaded", function () {
  const intervaloMesa = document.getElementById("intervalo-mesa");
  const mesaContainer = document.getElementById("mesa-container");
  const mesaSelect = document.getElementById("mesa");
  const errorMessage = document.getElementById("error-message");

  // Manipula a exibição das mesas com base no intervalo selecionado
  intervaloMesa.addEventListener("change", function () {
    const selectedInterval = intervaloMesa.value;
    mesaSelect.innerHTML = ""; // Limpa as opções anteriores

    let start = 0;
    let end = 0;

    if (selectedInterval === "1-5") {
      start = 1;
      end = 5;
    } else if (selectedInterval === "6-10") {
      start = 6;
      end = 10;
    } else if (selectedInterval === "11-15") {
      start = 11;
      end = 15;
    } else if (selectedInterval === "16-20") {
      start = 16;
      end = 20;
    }

    for (let i = start; i <= end; i++) {
      const option = document.createElement("option");
      option.value = i;
      option.textContent = `Mesa ${i}`;
      mesaSelect.appendChild(option);
    }

    mesaContainer.style.display = "block";
  });

  // Exibe a mensagem de erro, se presente na URL
  const urlParams = new URLSearchParams(window.location.search);
  if (
    urlParams.has("error") &&
    urlParams.get("error") === "invalid_credentials"
  ) {
    errorMessage.style.display = "block";
  }
});
