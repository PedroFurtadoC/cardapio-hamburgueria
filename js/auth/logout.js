// js\auth\logout.js

document.addEventListener("DOMContentLoaded", function () {
  const logoutTrigger = document.querySelector("#logout-trigger");

  if (logoutTrigger) {
    logoutTrigger.addEventListener("click", async function (event) {
      event.preventDefault();

      const confirmLogout = confirm("Deseja realmente sair?");
      if (!confirmLogout) return;

      const password = prompt(
        "Por favor, insira sua senha para confirmar o logout:"
      );
      if (!password) {
        alert("Logout cancelado. Nenhuma senha inserida.");
        return;
      }

      try {
        const response = await fetch(
          "/cardapio-hamburgueria/api/auth/logout.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ password }),
          }
        );

        const result = await response.json();

        if (result.success) {
          alert("Logout realizado com sucesso.");
          window.location.href = "/cardapio-hamburgueria/html/auth/login.html";
        } else if (result.expired) {
          alert(result.message);
          window.location.href = "/cardapio-hamburgueria/html/auth/login.html";
        } else {
          alert(result.message || "Erro ao realizar o logout.");
        }
      } catch (error) {
        alert("Erro de comunicação com o servidor. Tente novamente.");
        console.error("Erro:", error);
      }
    });
  }
});
