<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

// Lógica de processamento do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo'] ?? '');
    $sinopse = trim($_POST['sinopse'] ?? '');
    $imagem = trim($_POST['imagem'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0); // Adicionando preco para a tabela de filmes

    if (!empty($titulo) && $preco > 0) {
        $pdo = Banco::conectar();
        
        // Insere o novo filme no banco de dados
        $sql = "INSERT INTO tb_filmes (titulo, sinopse, imagem, preco) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([$titulo, $sinopse, $imagem, $preco]);
            Banco::desconectar();
            
            // Redireciona com mensagem de sucesso
            header("Location: filmes.php?msg=filme_adicionado");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar o filme: " . $e->getMessage();
            Banco::desconectar();
        }
    } else {
        $erro = "Todos os campos obrigatórios (Título e Preço) devem ser preenchidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro de Filme - CatalogoFlix</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .stark { color: #ffc107; }
        .card-cadastro {
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>
<body class="bg-dark text-light">

    <div class="container">
        <div class="card card-cadastro bg-secondary p-4 mx-auto shadow-lg">
            <h2 class="stark text-center mb-4"><i class="fas fa-film"></i> Cadastro de Novo Filme</h2>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger" role="alert"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form action="cad_filme.php" method="POST">
                
                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required 
                           value="<?php echo htmlspecialchars($titulo ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="sinopse">Sinopse / Resumo do Filme:</label>
                    <textarea class="form-control" id="sinopse" name="sinopse" rows="4"><?php echo htmlspecialchars($sinopse ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem (URL):</label>
                    <input type="url" class="form-control" id="imagem" name="imagem" 
                           value="<?php echo htmlspecialchars($imagem ?? ''); ?>">
                    <small class="form-text text-muted">Use o link direto para a imagem do poster.</small>
                </div>
                
                
                <button type="submit" class="btn btn-warning btn-lg btn-block mt-4">
                    <i class="fas fa-plus-circle"></i> Cadastrar Filme
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="filmes.php" class="text-white-50 small">
                    ← Voltar para Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>