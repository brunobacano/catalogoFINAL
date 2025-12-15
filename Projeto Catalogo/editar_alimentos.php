<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

$alimento_id = intval($_GET['id'] ?? 0);

if ($alimento_id === 0) {
    header("Location: cad_alimentos.php");
    exit;
}

$pdo = Banco::conectar();

// Busca os dados do alimento pelo ID
$sql = "SELECT id, nome, descricao, preco, imagem FROM tb_alimentos WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$alimento_id]);
$alimento = $stmt->fetch(PDO::FETCH_ASSOC);

Banco::desconectar();

if (!$alimento) {
    header("Location: cad_alimentos.php?erro=nao_encontrado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Alimento - <?php echo htmlspecialchars($alimento['nome']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .stark { color: #ffc107; }
        .card-edicao {
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>
<body class="bg-dark text-light">

    <div class="container">
        <div class="card card-edicao bg-secondary p-4 mx-auto shadow-lg">
            <h2 class="stark text-center mb-4"><i class="fas fa-edit"></i> Editar Alimento</h2>
            
            <form action="processar_edicao.php" method="POST">
                <input type="hidden" name="acao" value="editar_alimento">
                <input type="hidden" name="alimento_id" value="<?php echo htmlspecialchars($alimento['id']); ?>">
                
                <div class="form-group">
                    <label for="nome">Nome do Produto:</label>
                    <input type="text" class="form-control" id="nome" name="nome" required 
                           value="<?php echo htmlspecialchars($alimento['nome']); ?>">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($alimento['descricao']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" required min="0.01" 
                           value="<?php echo htmlspecialchars($alimento['preco']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem (URL):</label>
                    <input type="url" class="form-control" id="imagem" name="imagem" 
                           value="<?php echo htmlspecialchars($alimento['imagem']); ?>">
                    <small class="form-text text-muted">Link direto para a imagem do produto.</small>
                </div>
                
                <button type="submit" class="btn btn-warning btn-lg btn-block mt-4">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="cad_alimentos.php" class="text-white-50 small">
                    ← Voltar para Gerenciamento de Alimentos
                </a>
            </div>
        </div>
    </div>
    
</body>
</html>