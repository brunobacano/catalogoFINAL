<?php
session_start();
require_once "banco.php";

// Garante que o carrinho existe na sessão
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [
        'ingressos' => [],
        'alimentos' => [],
        'total' => 0.00
    ];
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$sessao_id = intval($_POST['sessao_id'] ?? $_GET['sessao_id'] ?? 0);
$quantidade = intval($_POST['quantidade'] ?? 1); // Assume 1 ingresso se não especificado

// Verifica se há uma ação válida
if ($acao === 'adicionar_ingresso' && $sessao_id > 0 && $quantidade > 0) {
    
    $pdo = Banco::conectar();
    
    // 1. Buscar detalhes da Sessão e do Filme
    $sql = "SELECT s.id AS sessao_id, s.horario, s.data, s.preco, s.sala, 
                   f.titulo AS filme_titulo
            FROM tb_sessoes s
            JOIN tb_filmes f ON s.filme_id = f.id
            WHERE s.id = ? AND s.ativo = 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessao_id]);
    $dados_sessao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    Banco::desconectar();

    if ($dados_sessao) {
        
        $item_chave = $dados_sessao['sessao_id'];
        $preco_unitario = $dados_sessao['preco'];
        $subtotal = $preco_unitario * $quantidade;

        // Se o ingresso já estiver no carrinho, apenas atualiza a quantidade (ou substitui, dependendo da sua lógica)
        // Neste caso, vamos substituir o ingresso anterior por este novo para simplificar o fluxo de 1 sessão por vez:
        
        // Limpa ingressos antigos (se houver, para simplificar o fluxo de compra)
        $_SESSION['carrinho']['ingressos'] = [];
        
        // Adiciona o novo ingresso
        $_SESSION['carrinho']['ingressos'][$item_chave] = [
            'sessao_id'      => $dados_sessao['sessao_id'],
            'filme_titulo'   => $dados_sessao['filme_titulo'],
            'data_sessao'    => $dados_sessao['data'],
            'horario'        => $dados_sessao['horario'],
            'sala'           => $dados_sessao['sala'],
            'preco_unitario' => $preco_unitario,
            'quantidade'     => $quantidade,
            'subtotal'       => $subtotal
        ];
        
        // 2. Recalcular o Total Final
        $_SESSION['carrinho']['total'] = calcular_total_carrinho();
        
        // 3. Redirecionar para a próxima etapa: Escolha de Alimentos
        header("Location: alimentos.php");
        exit;

    } else {
        // Sessão não encontrada ou inativa
        header("Location: filmes.php?erro=sessao_invalida");
        exit;
    }
} else {
    // Ação ou dados inválidos, redireciona para a lista de filmes
    header("Location: filmes.php");
    exit;
}

// ----------------------------------------------------------------------------------
// FUNÇÃO DE CÁLCULO (Deve ser colocada no seu arquivo funcoes.php ou aqui mesmo)
// ----------------------------------------------------------------------------------
function calcular_total_carrinho() {
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
    return $total;
}
?>