<?php
session_start();
require_once "banco.php";

// Configuração do limite
$LIMITE_MAXIMO_ALIMENTO = 10;
$mensagem_erro = '';

// 1. FUNÇÃO AUXILIAR PARA RECALCULAR O TOTAL
function recalcular_total() {
    $total = 0.00;
    
    // Soma ingressos
    if (isset($_SESSION['carrinho']['ingressos'])) {
        foreach ($_SESSION['carrinho']['ingressos'] as $item) {
            $total += $item['subtotal'];
        }
    }
    
    // Soma alimentos
    if (isset($_SESSION['carrinho']['alimentos'])) {
        foreach ($_SESSION['carrinho']['alimentos'] as $item) {
            $total += $item['subtotal'];
        }
    }
    
    $_SESSION['carrinho']['total'] = $total;
}


// 2. INICIALIZA O CARRINHO SE AINDA NÃO EXISTIR
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [
        'ingressos' => [],
        'alimentos' => [],
        'total' => 0.00
    ];
}

// 3. OBTÉM OS PARÂMETROS E O REDIRECIONAMENTO
$acao = $_POST['acao'] ?? '';
// CRÍTICO: Captura a URL de retorno. Se não vier do formulário, assume que é o checkout final.
$redirect_url = $_POST['redirect_url'] ?? 'carrinho_final.php'; 

$pdo = Banco::conectar();

// 4. PROCESSAMENTO DAS AÇÕES
switch ($acao) {
    
    // Ação acionada pelo clique na SESSÃO (vindo de sessao.php)
    case 'adicionar_sessao':
        $sessao_id = intval($_POST['sessao_id'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 1); 

        if ($sessao_id > 0 && $quantidade > 0) {
            
            $sql = "SELECT s.id, s.horario, s.data, s.preco, s.sala, f.titulo
                    FROM tb_sessoes s
                    JOIN tb_filmes f ON s.filme_id = f.id
                    WHERE s.id = ? AND s.ativo = TRUE";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sessao_id]);
            $sessao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sessao) {
                $item_key = 'SESSAO_' . $sessao_id;
                $preco_unitario = (float)$sessao['preco'];
                
                // Regra: Limpa ingressos anteriores (apenas uma sessão por compra)
                $_SESSION['carrinho']['ingressos'] = [];
                
                // Adiciona o novo ingresso
                $_SESSION['carrinho']['ingressos'][$item_key] = [
                    'id' => $sessao_id,
                    'filme_titulo' => $sessao['titulo'],
                    'data_sessao' => $sessao['data'],
                    'horario' => $sessao['horario'],
                    'sala' => $sessao['sala'],
                    'preco_unitario' => $preco_unitario,
                    'quantidade' => $quantidade,
                    'subtotal' => $quantidade * $preco_unitario
                ];
                
                // Redireciona para a próxima etapa: Seleção de Alimentos (SEMPRE)
                recalcular_total();
                header("Location: alimentos.php");
                exit;
                
            } else {
                $mensagem_erro = "Sessão inválida.";
            }
        }
        break; 


    // Ação acionada pelo botão + (vindo de alimentos.php)
    case 'adicionar_alimento':
        $alimento_id = filter_input(INPUT_POST, 'alimento_id', FILTER_VALIDATE_INT);
        if ($alimento_id) {
            
            // 1. Buscar dados do alimento 
            $sql = "SELECT id, nome, preco FROM tb_alimentos WHERE id = ? AND ativo = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$alimento_id]);
            $alimento = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alimento) {
                $key = 'ALIMENTO_' . $alimento['id'];
                
                // 2. Verificar limite
                $quantidade_atual = $_SESSION['carrinho']['alimentos'][$key]['quantidade'] ?? 0;
                
                if ($quantidade_atual < $LIMITE_MAXIMO_ALIMENTO) {
                    
                    // 3. Adicionar item e atualizar subtotal
                    $nova_quantidade = $quantidade_atual + 1;
                    $preco_unitario = (float)$alimento['preco'];
                    
                    $_SESSION['carrinho']['alimentos'][$key] = [
                        'id' => $alimento['id'],
                        'nome' => $alimento['nome'],
                        'preco_unitario' => $preco_unitario,
                        'quantidade' => $nova_quantidade,
                        'subtotal' => $nova_quantidade * $preco_unitario
                    ];
                }
            }
        }
        recalcular_total();
        // CRÍTICO: Redireciona para a URL passada pelo formulário (alimentos.php)
        header("Location: " . $redirect_url);
        exit;
        
    // Ação acionada pelo botão - (vindo de alimentos.php)
    case 'remover_alimento':
        $alimento_id = filter_input(INPUT_POST, 'alimento_id', FILTER_VALIDATE_INT);
        if ($alimento_id) {
            $key = 'ALIMENTO_' . $alimento_id;
            
            if (isset($_SESSION['carrinho']['alimentos'][$key])) {
                $item = $_SESSION['carrinho']['alimentos'][$key];
                
                if ($item['quantidade'] > 1) {
                    // Diminuir quantidade e atualizar subtotal
                    $nova_quantidade = $item['quantidade'] - 1;
                    $preco_unitario = $item['preco_unitario'];
                    
                    $_SESSION['carrinho']['alimentos'][$key]['quantidade'] = $nova_quantidade;
                    $_SESSION['carrinho']['alimentos'][$key]['subtotal'] = $nova_quantidade * $preco_unitario;
                } else {
                    // Remover item se a quantidade for 1
                    unset($_SESSION['carrinho']['alimentos'][$key]);
                }
            }
        }
        recalcular_total();
        // CRÍTICO: Redireciona para a URL passada pelo formulário (alimentos.php)
        header("Location: " . $redirect_url);
        exit;
        
    default:
        // Ação desconhecida
        break;
}

Banco::desconectar();

// 5. TRATAMENTO DE ERROS E ACESSOS DIRETOS
if (!empty($mensagem_erro)) {
    $_SESSION['erro_carrinho'] = $mensagem_erro;
    header("Location: filmes.php");
    exit;
}

// Se o código chegar até aqui, é porque não houve uma ação POST que nos redirecionou.
// Recalcula o total (garantia) e redireciona para a tela final de resumo, se houver ingressos.
recalcular_total();
if (empty($_SESSION['carrinho']['ingressos'])) {
    header("Location: filmes.php");
} else {
    // Redirecionamento para a tela final (caso o usuário pule os alimentos, ou ação default)
    header("Location: carrinho_final.php");
}
exit;
?>