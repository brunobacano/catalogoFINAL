<?php

// ATIVAR A EXIBIÇÃO DE ERROS (PARA DEBUG)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "banco.php";

// 🔒 Somente admin pode acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 1️⃣ Pegar o ID do filme que o admin quer editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: filmes.php");
    exit;
}

$filme_id = intval($_GET['id']); // converte para inteiro para segurança

$pdo = Banco::conectar();

// 2️⃣ Se o formulário foi enviado, atualizar o filme
if (isset($_POST['salvar'])) {
    $titulo  = $_POST['titulo'];
    $sinopse = $_POST['sinopse'];
    $imagem  = $_POST['imagem'];
    $preco   = $_POST['preco'];
    $ativo   = isset($_POST['ativo']) ? 1 : 0;

    $sql = "UPDATE tb_filmes
            SET titulo = ?, sinopse = ?, imagem = ?, preco = ?, ativo = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $sinopse, $imagem, $preco, $ativo, $filme_id]);

    header("Location: filmes.php"); // redireciona de volta para a lista
    exit;
}

// 3️⃣ Buscar os dados atuais do filme para preencher o formulário
$stmt = $pdo->prepare("SELECT * FROM tb_filmes WHERE id = ?");
$stmt->execute([$filme_id]);
$filme = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar o filme, redireciona
if (!$filme) {
    header("Location: filmes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Filme - CatalogoFlix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container mt-5">
    <h2 class="mb-4 text-warning">Editar Filme</h2>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($filme['titulo']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Sinopse</label>
            <textarea name="sinopse" class="form-control" required><?= htmlspecialchars($filme['sinopse']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagem (URL)</label>
            <input type="text" name="imagem" class="form-control" value="<?= htmlspecialchars($filme['imagem']) ?>" required>
        </div>


        <div class="mb-3 form-check">
            <input type="checkbox" name="ativo" class="form-check-input" <?= $filme['ativo'] ? 'checked' : '' ?>>
            <label class="form-check-label">Ativo (visível para usuários)</label>
        </div>

        <button type="submit" name="salvar" class="btn btn-warning">Salvar Alterações</button>
        <a href="filmes.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>

</body>
</html>
