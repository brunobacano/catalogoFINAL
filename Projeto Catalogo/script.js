document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const cadastroForm = document.getElementById("cadastroForm");
  const cartBtn = document.getElementById("cart-btn");
  const cartPanel = document.getElementById("cart-panel");
  const closeCart = document.getElementById("close-cart");

  // Recupera usuários já cadastrados
  let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

  // --- CADASTRO ---
  if (cadastroForm) {
    cadastroForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const inputs = cadastroForm.querySelectorAll("input");
      const nome = inputs[0].value.trim();
      const email = inputs[1].value.trim();
      const usuario = inputs[2].value.trim();
      const senha = inputs[3].value.trim();

      // Verifica se já existe usuário ou e-mail
      if (usuarios.some(u => u.usuario === usuario)) {
        alert("Esse usuário já existe!");
        return;
      }
      if (usuarios.some(u => u.email === email)) {
        alert("Esse e-mail já foi usado!");
        return;
      }

      // Salva novo usuário
      usuarios.push({ nome, email, usuario, senha });
      localStorage.setItem("usuarios", JSON.stringify(usuarios));

      alert("Cadastro realizado com sucesso!");
      window.location.href = "login.php";
    });
  }

  // --- LOGIN ---
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const usuarioInput = document.getElementById("usuario").value.trim();
      const senhaInput = document.getElementById("senha").value.trim();

      const user = usuarios.find(
        u =>
          (u.usuario === usuarioInput || u.email === usuarioInput) &&
          u.senha === senhaInput
      );

      if (user) {
        alert(`Bem-vindo, ${user.nome}!`);
        localStorage.setItem("usuarioLogado", JSON.stringify(user));
        window.location.href = "index.php";
      } else {
        alert("Usuário ou senha incorretos!");
      }
    });
  }

  // --- Carrinho deslizante ---
  if (cartBtn && cartPanel && closeCart) {
    cartBtn.addEventListener("click", () => {
      cartPanel.classList.toggle("open");
    });

    closeCart.addEventListener("click", () => {
      cartPanel.classList.remove("open");
    });
  }
});
  // =========================
  // SEÇÃO DE FILMES
  // =========================

 const filmes = [
  { titulo: "Vingadores: Ultimato", preco: 20, imagem: "https://image.tmdb.org/t/p/w500/ulzhLuWrPK07P1YkdWQLZnQh1JL.jpg" },
  { titulo: "Homem-Aranha: Sem Volta Para Casa", preco: 18, imagem: "https://ingresso-a.akamaihd.net/prd/img/movie/homem-aranha-sem-volta-para-casa-a-versao-ainda-mais-divertida/fe3e9588-2fa8-43ca-a207-ce16ee204afd.jpg" },
  { titulo: "Batman: O Cavaleiro das Trevas", preco: 15, imagem: "https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg" },
  { titulo: "Pantera Negra", preco: 16, imagem: "https://image.tmdb.org/t/p/w500/uxzzxijgPIY7slzFvMotPv8wjKA.jpg" },
  { titulo: "Doutor Estranho", preco: 17, imagem: "https://image.tmdb.org/t/p/w500/uGBVj3bEbCoZbDjjl9wTxcygko1.jpg" },
  { titulo: "Homem de Ferro", preco: 14, imagem: "https://image.tmdb.org/t/p/w500/78lPtwv72eTNqFW9COBYI0dWDJa.jpg" },
  
  { titulo: "Thor: Ragnarok", preco: 17, imagem: "https://image.tmdb.org/t/p/w500/rzRwTcFvttcN1ZpX2xv4j3tSdJu.jpg" },
  { titulo: "Guardiões da Galáxia", preco: 16, imagem: "https://image.tmdb.org/t/p/w500/r7vmZjiyZw9rpJMQJdXpjgiCOk9.jpg" },
  
  { titulo: "Capitã Marvel", preco: 15, imagem: "https://image.tmdb.org/t/p/w500/AtsgWhDnHTq68L0lLsUrCnM7TjG.jpg" },
  { titulo: "Viúva Negra", preco: 18, imagem: "https://image.tmdb.org/t/p/w500/qAZ0pzat24kLdO3o8ejmbLxyOac.jpg" },
  { titulo: "Homem de Ferro 3", preco: 16, imagem: "https://image.tmdb.org/t/p/w500/qhPtAc1TKbMPqNvcdXSOn9Bn7hZ.jpg" },
  { titulo: "Vingadores: Guerra Infinita", preco: 20, imagem: "https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg" },
];


  const lista = document.getElementById("lista-filmes");

  if (lista) {
    filmes.forEach((filme) => {
      const card = document.createElement("div");
      card.classList.add("col-md-3", "mb-4");
      card.innerHTML = `
        <div class="card h-100 bg-dark text-white border-warning">
          <img src="${filme.imagem}" class="card-img-top" alt="${filme.titulo}">
          <div class="card-body text-center">
            <h5 class="card-title">${filme.titulo}</h5>
            <p class="card-text text-warning">R$ ${filme.preco},00</p>
            <button class="btn btn-warning btn-add">Adicionar ao Carrinho</button>
          </div>
        </div>
      `;
      lista.appendChild(card);
    });
  }

