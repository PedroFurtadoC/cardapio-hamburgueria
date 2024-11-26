// js\auth\register.js

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("register-form");
  const errorMessage = document.getElementById("error-message");

  form.addEventListener("submit", function (event) {
    const senha = document.getElementById("senha").value;
    const confirmarSenha = document.getElementById("confirmar_senha").value;

    // Verifica se as senhas coincidem
    if (senha !== confirmarSenha) {
      // Impede o envio do formulário
      event.preventDefault();
      errorMessage.textContent = "As senhas não coincidem. Tente novamente.";
      errorMessage.style.display = "block";
      return false;
    }

    // Verifica o comprimento da senha
    if (senha.length < 8) {
      event.preventDefault();
      errorMessage.textContent = "A senha deve ter no mínimo 8 caracteres.";
      errorMessage.style.display = "block";
      return false;
    }

    // Oculta mensagem de erro se tudo estiver correto
    errorMessage.style.display = "none";
    return true;
  });

  // Verifica se há erros passados pela URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has("error")) {
    const error = urlParams.get("error");
    if (error === "email_exists") {
      errorMessage.textContent = "O e-mail fornecido já está cadastrado.";
      errorMessage.style.display = "block";
    } else if (error === "server_error") {
      errorMessage.textContent =
        "Erro no servidor. Por favor, tente novamente.";
      errorMessage.style.display = "block";
    }
  }
});
