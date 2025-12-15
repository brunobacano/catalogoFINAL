<?php
session_start();

// Se o usuário não estiver logado, redireciona para login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CatalogoFlix - Principal</title>
    <link rel="icon" href="./img/logo2.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold text-warning" href="index.php">🎬 CatalogoFlix </a>

            <div class="text-light">
                <span class="me-3">👋 Olá, <strong><?= $_SESSION['usuario_email']; ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <h2 class="text-center mb-4 text-warning">Gestão do Negócio</h2>

      
            <div class="col-md-4">
                <a href="cad_filme.php" class="card-link">
                    <div class="custom-card text-center border border-warning">
                        <h5 class="card-title text-warning">➕ Cadastrar Novo Filme</h5>
                        <p class="card-text text-light">Adicione títulos, sinopses e detalhes ao catálogo.</p>
                    </div>
                </a>
            </div>

        </div>

    </main>

    

    <script src="script.js"></script>
</body>
</html>