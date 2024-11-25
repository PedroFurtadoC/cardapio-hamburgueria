// js\auth\forgot_password.js

document.addEventListener("DOMContentLoaded", function () {
  // Obtém os parâmetros da URL
  const params = new URLSearchParams(window.location.search);
  const statusMessage = params.get("status");

  if (statusMessage) {
    // Seleciona o elemento onde a mensagem será exibida
    const statusDiv = document.getElementById("status-message");

    // Decodifica e insere a mensagem
    statusDiv.textContent = decodeURIComponent(statusMessage);
    statusDiv.classList.add("text-center", "mb-3");

    // Define a cor do texto com base na mensagem
    if (statusMessage.includes("sucesso")) {
      statusDiv.classList.add("text-success");
    } else {
      statusDiv.classList.add("text-danger");
    }
  }
});
