// js\auth\reset_password.js

document.addEventListener("DOMContentLoaded", function () {
  // Obtém os parâmetros da URL
  const params = new URLSearchParams(window.location.search);
  const email = params.get("email");
  const status = params.get("status");

  // Preenche o campo oculto com o e-mail
  if (email) {
    document.getElementById("email").value = decodeURIComponent(email);
  }

  // Exibe a mensagem de status, se houver
  if (status) {
    const statusDiv = document.getElementById("status-message");

    let message = "";
    let className = "text-center mb-3 ";

    switch (status) {
      case "mismatch":
        message = "As senhas não coincidem!";
        className += "text-danger";
        break;
      case "email_not_found":
        message = "E-mail não encontrado no sistema!";
        className += "text-danger";
        break;
      case "server_error":
        message = "Erro no servidor. Tente novamente mais tarde.";
        className += "text-danger";
        break;
      case "password_reset_success":
        message = "Senha redefinida com sucesso! Faça login novamente.";
        className += "text-success";
        break;
    }

    statusDiv.textContent = message;
    statusDiv.className = className;
  }
});
