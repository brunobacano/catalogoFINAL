<?php
include "banco.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = Banco::conectar();

    // Sanitizar os dados recebidos
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Criptografar a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Verificar se o e-mail ou usuário já existem
    $check = $pdo->prepare("SELECT * FROM tb_user WHERE email = :email OR usuario = :usuario");
    $check->bindParam(":email", $email);
    $check->bindParam(":usuario", $usuario);
    $check->execute();

    if ($check->rowCount() > 0) {
        echo "<script>alert('E-mail ou usuário já cadastrados!');</script>";
    } else {
        // Inserir no banco
        $sql = $pdo->prepare("INSERT INTO tb_user (nome, email, usuario, pass) VALUES (:nome, :email, :usuario, :senha)");
        $sql->bindParam(":nome", $nome);
        $sql->bindParam(":email", $email);
        $sql->bindParam(":usuario", $usuario);
        $sql->bindParam(":senha", $senhaHash);

        if ($sql->execute()) {
            echo "<script>alert('Cadastro realizado com sucesso! Faça login.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar. Tente novamente.');</script>";
        }
    }

    Banco::desconectar();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro - CatalogoFlix</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="./img/logo2.png" type="image/x-icon">
</head>
<body class="loregister-page">
  <div class="container-auth">
    <div class="left-panel">
      <img src="img/logo.png" alt="CatalogoFlix Logo">
    </div>

    <!-- Lado direito -->
    <div class="right-side">
      <img src="img/logo2.png" alt="CatalogoFlix Logo" class="mobile-logo">
        
      <h2>Cadastro</h2>
          <form id="cadastroForm" action="cad_usuario.php" method="POST">
          <input type="text" name="nome" placeholder="Nome completo" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="usuario" placeholder="Usuário" required>
          <input type="password" name="senha" placeholder="Senha" required>
          <button type="submit">Cadastrar</button>
        </form>
      <p>Já tem conta? <a href="login.php" style="color: #ddaf18;">Entrar</a></p>
      <a href="index.php" class="home-btn">🏠 Home</a>
    </div>
  </div>

</body>
</html>
