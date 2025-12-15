<?php
session_start();
require_once "banco.php";

// Protege contra acesso direto se não houver um carrinho iniciado (deve ter comprado ingresso primeiro)
if (!isset($_SESSION['carrinho']['ingressos']) || empty($_SESSION['carrinho']['ingressos'])) {
    header("Location: filmes.php");
    exit;
}

$pdo = Banco::conectar();

// 1. BUSCAR ALIMENTOS ATIVOS
$sql_alimentos = "SELECT id, nome, descricao, preco, imagem FROM tb_alimentos WHERE ativo = 1 ORDER BY nome ASC"; 
$stmt_alimentos = $pdo->prepare($sql_alimentos);
$stmt_alimentos->execute();
$alimentos = $stmt_alimentos->fetchAll(PDO::FETCH_ASSOC);

Banco::desconectar();

// Função para exibir a quantidade atual no carrinho (para o cliente)
function get_quantidade_carrinho($alimento_id) {
    $key = 'ALIMENTO_' . $alimento_id;
    return $_SESSION['carrinho']['alimentos'][$key]['quantidade'] ?? 0;
}

// Recalcula o total do carrinho
function recalcular_total() {
    $total = 0.00;
    
    if (isset($_SESSION['carrinho']['ingressos'])) {
        foreach ($_SESSION['carrinho']['ingressos'] as $item) {
            $total += $item['subtotal'];
        }
    }
    
    if (isset($_SESSION['carrinho']['alimentos'])) {
        foreach ($_SESSION['carrinho']['alimentos'] as $item) {
            $total += $item['subtotal'];
        }
    }
    
    $_SESSION['carrinho']['total'] = $total;
    return number_format($_SESSION['carrinho']['total'], 2, ',', '.');
}

$total_carrinho = recalcular_total();

// Variáveis de controle de limite e Admin
$LIMITE_MAXIMO = 10;
$isAdmin = isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alimentos - CatalogoFlix</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .card-img-top {
            height: 250px; 
            object-fit: cover;
        }
        .card {
            background-color: #2a2a2a;
            color: #fff;
            border: 1px solid #444;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .text-success {
            color: #ffc107 !important;
        }
        .stark {
            color: #ffc107;
        }
        .item-count {
            display: inline-block;
            width: 30px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-dark">

    <nav class="navbar navbar-dark bg-dark border-bottom border-warning">
        <div class="container-fluid">
            <a class="navbar-brand text-warning" href="filmes.php"><i class="fas fa-video"></i> CatalogoFlix</a>
            <span class="navbar-text text-light">Total Atual: R$ <?php echo $total_carrinho; ?></span> 
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="stark text-center mb-4">
            <i class="fas fa-popcorn"></i> Alimentos
            
            <?php if ($isAdmin): ?>
                <a href="cad_alimentos.php" class="btn btn-info btn-sm ml-3">
                    <i class="fas fa-cog"></i> Gerenciar Produtos
                </a>
            <?php endif; ?>
        </h2> 
        
        <div class="row">
            <?php if (empty($alimentos)): ?>
                <div class="col-12 text-center text-light">
                    <p>Nenhum produto de alimentos disponível no momento.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($alimentos as $alimento): 
                $quantidade_atual = get_quantidade_carrinho($alimento['id']);
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-lg h-100">
                        <img src="<?php echo htmlspecialchars($alimento['imagem']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($alimento['nome']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-light"><?php echo htmlspecialchars($alimento['nome']); ?></h5>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars($alimento['descricao']); ?></p>
                            
                            <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                <span class="fs-1 fw-bold text-success">R$ <?php echo number_format($alimento['preco'], 2, ',', '.'); ?></span>
                                
                                <div class="btn-group" role="group">
                                    
                                    <form action="carrinho.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="acao" value="remover_alimento">
                                        <input type="hidden" name="alimento_id" value="<?php echo $alimento['id']; ?>">
                                        <input type="hidden" name="redirect_url" value="alimentos.php"> 
                                        <button type="submit" class="btn btn-outline-warning" 
                                                <?php echo $quantidade_atual === 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </form>

                                    <span class="item-count text-warning">
                                        <?php echo $quantidade_atual; ?>
                                    </span>
                                    
                                    <form action="carrinho.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="acao" value="adicionar_alimento">
                                        <input type="hidden" name="alimento_id" value="<?php echo $alimento['id']; ?>">
                                        <input type="hidden" name="redirect_url" value="alimentos.php"> 
                                        <button type="submit" class="btn btn-warning" 
                                                <?php echo $quantidade_atual >= $LIMITE_MAXIMO ? 'disabled' : ''; ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fixed-bottom text-center mb-4">
            <a href="carrinho_final.php" class="btn btn-warning btn-lg px-5 py-2" style="font-size: 1.4rem;">
                <i class="fas fa-shopping-cart"></i> Continuar (R$ <?php echo $total_carrinho; ?>)
            </a>
            <a href="carrinho_final.php" class="btn btn-outline-light btn-lg px-4 py-2 ml-3" style="font-size: 1.4rem;">
                <i class="fas fa-forward"></i> Pular Alimentos
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>