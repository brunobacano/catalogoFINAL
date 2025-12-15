<?php
session_start();
include "banco.php";

$pdo = Banco::conectar();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_SPECIAL_CHARS);

    // 1. Modificar a query para selecionar a coluna 'role'
    $stmt = $pdo->prepare("SELECT id, email, pass, role FROM tb_user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // AVISO: A condição "$senha === $usuario['pass']" permite login sem hash, 
        // o que não é seguro e deve ser removido após garantir que todas as senhas estejam hasheadas.
// --- Dentro do login.php ---

if (password_verify($senha, $usuario['pass'])) {
    // Login bem-sucedido
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_role'] = $usuario['role'];
    
    // 1. VERIFICA SE EXISTE UM PARÂMETRO DE REDIRECIONAMENTO NA URL
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $pagina_destino = $_GET['redirect'];
        header("Location: " . $pagina_destino);
        exit();
    }
    
    // 2. LÓGICA DE REDIRECIONAMENTO PADRÃO (Se não houver 'redirect')
    if($_SESSION['usuario_role'] == "admin") {
        header('Location: filmes.php');
    } else {
        header('Location: index.php');
    }
    exit();
} else {
    $erro = "Senha incorreta.";
}

    } else {
        $erro = "Usuário não encontrado.";
    }

}
// Desconexão do banco, se necessário (dependendo de como Banco::desconectar() está implementado)
// Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CatalogoFlix</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="loregister-page">
    <div class="container-auth">
        <div class="left-panel">
            <img src="img/logo.png" alt="CatalogoFlix Logo">
        </div>

        <div class="right-side">
            <img src="img/logo2.png" alt="CatalogoFlix Logo" class="mobile-logo">
            <h2>Login</h2>

            <form action="login.php" method="post">
                <input type="text" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required> 
                <button type="submit">Entrar</button>
            </form>

            <?php if (!empty($erro)): ?>
                <p style="color: red;"><?= $erro ?></p>
            <?php endif; ?>

            <p>Crie uma conta <a href="cad_usuario.php" style="color: #ddaf18;">Cadastrar</a></p>
            <a href="index.php" class="home-btn">🏠 Home</a>
        </div>
    </div>
</body>
</html>