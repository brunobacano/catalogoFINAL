<?php
session_start();
require_once "banco.php";

// 🔒 1. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?redirect=carrinho_final.php");
    exit;
}

// 🛒 2. VERIFICAÇÃO DO CARRINHO
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho']['ingressos'])) {
    header("Location: filmes.php?erro=carrinho_vazio");
    exit;
}

$carrinho = $_SESSION['carrinho'];
$usuario_id = $_SESSION['usuario_id'];
$total_pedido = $carrinho['total'] ?? 0.00; 

$pdo = Banco::conectar();

try {
    $pdo->beginTransaction();

    // 3. REGISTRA O PEDIDO PRINCIPAL
    // data_pedido é definida corretamente como DATETIME no banco
    $sql_pedido = "INSERT INTO tb_pedidos (usuario_id, data_pedido, valor_total, status) 
                   VALUES (?, NOW(), ?, 'Pago')";
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([$usuario_id, $total_pedido]);
    
    // Obtém o ID do pedido
    $pedido_id = $pdo->lastInsertId();

    // 4. PREPARA INSERÇÃO DE ITENS
    $sql_item = "INSERT INTO tb_itens_pedido (pedido_id, tipo, item_id, quantidade, preco_unitario, subtotal) 
                 VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_item = $pdo->prepare($sql_item);

    // 5. REGISTRA ITENS DO PEDIDO (Ingressos)
    // Usamos $item_key para o ID, resolvendo o erro da linha 48.
    foreach ($carrinho['ingressos'] as $item_key => $item) {
        
        // CORREÇÃO: Pega o ID da sessão da CHAVE do array (ex: [3] => {...})
        $sessao_id = $item_key; 
        
        // O banco de dados requer que item_id seja NOT NULL.
        // Se a correção acima funcionar, o erro 1048 será resolvido.

        $stmt_item->execute([
            $pedido_id, 
            'ingresso', 
            $sessao_id, // <--- ID CORRIGIDO AQUI
            $item['quantidade'], 
            $item['preco_unitario'], 
            $item['subtotal']
        ]);
    }
    
    // 6. REGISTRA ITENS DO PEDIDO (Alimentos)
    // Mantemos $item['id'] para alimentos, pois não houve erro reportado nesta seção.
    if (!empty($carrinho['alimentos'])) {
        foreach ($carrinho['alimentos'] as $item_key => $item) {
            
            $alimento_id = $item['id']; 

            $stmt_item->execute([
                $pedido_id, 
                'alimento', 
                $alimento_id, 
                $item['quantidade'], 
                $item['preco_unitario'], 
                $item['subtotal']
            ]);
        }
    }

    $pdo->commit();
    
    // 7. LIMPA O CARRINHO APÓS O SUCESSO
    unset($_SESSION['carrinho']);

    // 8. REDIRECIONA PARA A PÁGINA DE SUCESSO
    header("Location: sucesso_compra.php?pedido=" . $pedido_id);
    exit;

} catch (PDOException $e) {
    // Restaura o bloco de erro original para a produção
    $pdo->rollBack();
    header("Location: filmes.php?erro=falha_pedido");
    exit;
}

Banco::desconectar();
?>