<?php
// MANTEMOS O ob_start() AQUI para garantir que o redirecionamento da exclusão funcione
ob_start(); 
session_start(); 

require_once "banco.php";

$isAdmin = isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin';

$pdo = Banco::conectar();
$sql = "SELECT id, titulo, sinopse, imagem, preco FROM tb_filmes WHERE ativo = 1 ORDER BY id DESC";
$filmes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Filmes - Totem</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand text-warning" href="filmes.php">
        <i class="fas fa-video"></i> CatalogoFlix
    </a>
    <div class="ml-auto">
        <?php if ($isAdmin || isset($_SESSION['usuario_id'])): ?>
            <a href="logout.php" class="btn btn-warning my-2 my-sm-0">Sair</a>
        <?php else: ?>
            <a href="cad_usuario.php" class="btn btn-outline-light my-2 my-sm-0 mr-2">
                Cadastrar
            </a>
            <a href="login.php" class="btn btn-warning my-2 my-sm-0">
                Entrar
            </a>
        <?php endif; ?>
    </div>
</nav>

<div class="container my-5">
    <h2 class="stark text-center mb-4">Selecione o Filme</h2>

    <?php if ($isAdmin): ?>
        <div class="mb-4 text-center">
            <a href="cad_filme.php" class="btn btn-warning btn-lg">
                <i class="fas fa-plus-circle"></i> Cadastrar Novo Filme
            </a>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($filmes)): ?>
            <div class="col-12 text-center text-light">
                <p>Nenhum filme em cartaz no momento.</p>
            </div>
        <?php endif; ?>
        
        <?php foreach ($filmes as $filme): ?>
            <div class="col-md-4 mb-4">
                <a href="sessao.php?filme_id=<?php echo $filme['id']; ?>" class="card-link">
                    <div class="card h-100 shadow">
                        <img src="<?php echo htmlspecialchars($filme['imagem']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($filme['titulo']); ?>" 
                             style="height: 300px; object-fit: cover;">
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($filme['titulo']); ?>
                            </h5>
                            
                            <p class="card-text text-light small">
                                <?php echo htmlspecialchars(substr($filme['sinopse'], 0, 80)) . '...'; ?>
                            </p>
                        </div>

                        <?php if ($isAdmin): ?>
                        <div class="card-footer bg-dark border-top border-secondary text-center">
                            <a href="editar_filme.php?id=<?php echo $filme['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="excluir_filme.php?id=<?php echo $filme['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja desativar este filme?');">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
<?php ob_end_flush(); ?>