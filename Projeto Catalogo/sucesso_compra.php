<?php
session_start();
require_once "banco.php";

$pedido_id = intval($_GET['pedido'] ?? 0);

if ($pedido_id === 0) {
    header("Location: filmes.php");
    exit;
}

$pdo = Banco::conectar();

// 1. Buscar os detalhes do pedido
$sql = "SELECT valor_total FROM tb_pedidos WHERE id = ? AND status = 'Pago'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

Banco::desconectar();

if (!$pedido) {
    // Se o pedido não for encontrado ou não estiver 'Pago' (no caso do PIX, pode ser 'Pendente')
    header("Location: filmes.php?erro=pedido_invalido");
    exit;
}

$valor_total = $pedido['valor_total'];

// 2. CONSTRUÇÃO DO CONTEÚDO FICTÍCIO DO QR CODE
// O QR Code será um link que, se lido, informaria a chave PIX, o valor e o nome do recebedor.
// Exemplo Fictício: "PIX: cinemaflix@exemplo.com | Valor: R$ X.XX | Pedido: Y"
$qr_content = "PIX_Ficticio_CatalogoFlix | Valor: R$ " . number_format($valor_total, 2, '.', '') . " | Pedido: " . $pedido_id;

// 3. GERADOR DE QR CODE (API da QuickChart - gratuita e fácil de usar)
// URL: https://quickchart.io/chart?cht=qr&chs=300x300&chl=[CONTEUDO_DO_QR_CODE]
$qr_code_url = "https://quickchart.io/qr?size=300&text=" . urlencode($qr_content);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pagamento Finalizado - CatalogoFlix</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .stark { color: #ffc107; }
        .success-card {
            max-width: 600px;
            background-color: #2a2a2a;
        }
        .qr-box {
            border: 2px solid #555;
            padding: 15px;
            background-color: #fff;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container my-5">
    <div class="card success-card p-4 mx-auto shadow-lg text-center">
        
        <i class="fas fa-check-circle text-success display-1 mb-3"></i>
        <h2 class="stark display-4 mb-2">Pedido Realizado com Sucesso!</h2>
        <p class="lead text-white-50">Seu pedido **#<?php echo $pedido_id; ?>** foi gerado. Aguardando pagamento.</p>
        
        <hr class="bg-warning my-4">

        <h4 class="mb-3">Valor Total: <span class="text-success">R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></span></h4>
        
        <h5 class="mt-4 mb-3"><i class="fas fa-qrcode"></i> Pague via QR Code (PIX Fictício)</h5>
        
        <div class="qr-box mx-auto mb-4">
            <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code de Pagamento" style="width: 250px; height: 250px;">
            
        </div>

        <p class="small text-muted">Este QR Code é apenas para simulação. Se lido, ele contém as informações do pedido.</p>

        <a href="filmes.php" class="btn btn-warning btn-lg mt-3">
            <i class="fas fa-home"></i> Voltar à Tela Inicial
        </a>
    </div>
</div>

</body>
</html>