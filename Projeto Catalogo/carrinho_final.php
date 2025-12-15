<?php
session_start();
require_once "banco.php";

// ----------------------------------------------------
// 1. GARANTE QUE O CARRINHO ESTÁ ACESSÍVEL E COMPLETO
// ----------------------------------------------------

// Função auxiliar (copiada do seu script de processamento para garantir que funcione aqui)
if (!function_exists('recalcular_total')) {
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
    }
}

// Recalcula para garantia
recalcular_total();

// Verifica se o carrinho tem ingressos (o mínimo para ir ao checkout)
if (empty($_SESSION['carrinho']['ingressos'])) {
    header("Location: filmes.php?erro=sem_ingresso");
    exit;
}

$carrinho = $_SESSION['carrinho'];
$ingressos = $carrinho['ingressos'] ?? [];
$alimentos = $carrinho['alimentos'] ?? [];
$total_final = $carrinho['total'] ?? 0.00;

// 🔒 VERIFICAÇÃO DE LOGIN
$usuario_logado = isset($_SESSION['usuario_id']);
$btn_finalizar_text = $usuario_logado ? 'Finalizar Compra' : 'Fazer Login para Pagar';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Revisão do Pedido - CatalogoFlix</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .stark { color: #ffc107; }
        .resumo-card {
            background-color: #2a2a2a;
        }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container my-5">
    <h2 class="stark text-center mb-5 display-4"><i class="fas fa-receipt"></i> Revisão do Pedido</h2>

    <div class="card resumo-card p-4 shadow-lg mx-auto" style="max-width: 800px;">
        
        <h4 class="text-warning border-bottom pb-2 mb-3">1. Detalhes da Sessão</h4>
        
        <?php foreach ($ingressos as $ing): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong class="text-light display-5"><?php echo htmlspecialchars($ing['filme_titulo']); ?></strong>
                    <p class="small mb-0 text-white-50">
                        <?php echo htmlspecialchars($ing['quantidade']); ?>x Ingresso(s)
                    </p>
                    <p class="small mb-0">
                        Sala <?php echo htmlspecialchars($ing['sala']); ?> | 
                        Data: <?php echo date('d/m/Y', strtotime($ing['data_sessao'])); ?> às 
                        <?php echo date('H:i', strtotime($ing['horario'])); ?>
                    </p>
                </div>
                <span class="text-success font-weight-bold display-5">
                    R$ <?php echo number_format($ing['subtotal'], 2, ',', '.'); ?>
                </span>
            </div>
        <?php endforeach; ?>

        <h4 class="text-warning border-bottom pb-2 mt-4 mb-3">2. Alimentos</h4>
        
        <?php if (!empty($alimentos)): ?>
            <?php foreach ($alimentos as $ali): ?>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-light">
                        <?php echo htmlspecialchars($ali['quantidade']); ?>x <?php echo htmlspecialchars($ali['nome']); ?>
                    </span>
                    <span class="text-success">
                        R$ <?php echo number_format($ali['subtotal'], 2, ',', '.'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-white-50">Nenhum Alimento Adicionado.</p>
        <?php endif; ?>

        <hr class="bg-warning mt-4 mb-3">

        <div class="d-flex justify-content-between align-items-center">
            <h3 class="stark mb-0">TOTAL A PAGAR:</h3>
            <h3 class="stark mb-0">R$ <?php echo number_format($total_final, 2, ',', '.'); ?></h3>
        </div>
        
        <div class="row mt-4">
            <div class="col-6">
                <a href="alimentos.php" class="btn btn-outline-light btn-lg btn-block">
                    <i class="fas fa-undo"></i> Alterar Alimentos
                </a>
            </div>
            <div class="col-6">
                <?php if ($usuario_logado): ?>
                    <a href="finalizar_compra.php" class="btn btn-success btn-lg btn-block">
                        <?php echo $btn_finalizar_text; ?> <i class="fas fa-check-circle"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=carrinho_final.php" class="btn btn-warning btn-lg btn-block">
                        <?php echo $btn_finalizar_text; ?> <i class="fas fa-sign-in-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>