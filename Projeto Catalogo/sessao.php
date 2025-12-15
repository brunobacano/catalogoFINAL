<?php
// Ativa o buffer de saída para evitar problemas de header
ob_start(); 

session_start();
require_once "banco.php";

// ----------------------------------------------------
// VERIFICAÇÕES INICIAIS
// ----------------------------------------------------
$isAdmin = isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin';

if (!isset($_GET['filme_id']) || empty($_GET['filme_id'])) {
    header("Location: filmes.php");
    ob_end_flush();
    exit;
}

$filme_id = intval($_GET['filme_id']);

$pdo = Banco::conectar();

// 2. BUSCAR DETALHES DO FILME
$sql_filme = "SELECT id, titulo, imagem, sinopse FROM tb_filmes WHERE id = ?"; 
$stmt_filme = $pdo->prepare($sql_filme);
$stmt_filme->execute([$filme_id]);
$filme = $stmt_filme->fetch(PDO::FETCH_ASSOC);

if (!$filme) {
    header("Location: filmes.php");
    ob_end_flush();
    exit;
}

// 3. BUSCAR SESSÕES ATIVAS, FUTURAS E CORRIGIDAS (AQUI ESTÁ O FOCO)
try {
    // CORREÇÃO: Usando 'ativo = 1' e 'DATE('now')' e GARANTINDO O PONTO E VÍRGULA
    $sql_sessoes = "SELECT id, horario, data, preco, sala FROM tb_sessoes 
                    WHERE filme_id = ? AND ativo = 1 AND data >= DATE('now')
                    ORDER BY data ASC, horario ASC"; 
                    
    $stmt_sessoes = $pdo->prepare($sql_sessoes);
    $stmt_sessoes->execute([$filme_id]);
    $sessoes = $stmt_sessoes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, apenas retorna um array vazio
    $sessoes = []; 
    // Para debug: echo "Erro de Banco de Dados: " . $e->getMessage();
}

Banco::desconectar();

// ----------------------------------------------------
// FUNÇÕES AUXILIARES
// ----------------------------------------------------
function formatar_data($data) {
    // Formato de data mais limpo (DD/MM)
    return date('d/m', strtotime($data)); 
}
function formatar_hora($hora) {
    return date('H:i', strtotime($hora));
}

// Agrupa sessões por data
$sessoes_por_data = [];
foreach ($sessoes as $sessao) {
    $data_chave = formatar_data($sessao['data']);
    if (!isset($sessoes_por_data[$data_chave])) {
        $sessoes_por_data[$data_chave] = [];
    }
    $sessoes_por_data[$data_chave][] = $sessao;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sessões para <?php echo htmlspecialchars($filme['titulo']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .sessao-bloco {
            background-color: #1c1c1c; 
            border: 2px solid #333;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            margin-right: 10px; 
            margin-bottom: 10px;
            flex-grow: 1; 
            min-width: 150px; 
        }
        .sessao-bloco:hover {
            border-color: #ffc107; 
            background-color: #2a2a2a;
        }
        .sessao-time {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffc107; 
        }
        .sessao-preco {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .sessao-sala-data {
            font-size: 0.8rem;
            color: #ccc;
        }
        .sessao-container {
            max-height: 75vh; 
            overflow-y: auto; 
            padding-right: 15px;
        }
        .sessao-row {
            display: flex;
            flex-wrap: wrap; 
            margin-bottom: 20px;
        }
        .filme-compacto {
            background-color: #2a2a2a;
            border: 1px solid #444;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            margin-bottom: 30px;
            align-items: flex-start;
        }
        .filme-compacto img {
            width: 100px; 
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .filme-compacto-info {
            flex-grow: 1;
        }
    </style>
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
            <a href="cad_usuario.php" class="btn btn-outline-light my-2 my-sm-0 mr-2">Cadastrar</a>
            <a href="login.php" class="btn btn-warning my-2 my-sm-0">Entrar</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container my-4">
    <h2 class="stark text-center mb-4">Escolha a Sessão</h2>

    <div class="filme-compacto shadow">
        <img src="<?php echo htmlspecialchars($filme['imagem']); ?>" 
             alt="<?php echo htmlspecialchars($filme['titulo']); ?>">
        <div class="filme-compacto-info">
            <h4 class="card-title text-warning mb-1"><?php echo htmlspecialchars($filme['titulo']); ?></h4>
            <p class="text-light small mb-2"><?php echo htmlspecialchars(substr($filme['sinopse'], 0, 150)) . '...'; ?></p>
            <?php if ($isAdmin): ?>
                <a href="cad_sessao.php?filme_id=<?php echo $filme_id; ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar-plus"></i> Gerenciar Sessões
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="sessao-container">
        <?php if (empty($sessoes)): ?>
            <div class="text-center text-light">
                <p>Não há sessões programadas para este filme.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($sessoes_por_data as $data_sessao => $sessoes_do_dia): ?>
            <h4 class="text-light mt-3 mb-2 border-bottom border-warning pb-1">
                <i class="fas fa-calendar-alt"></i> Data: <?php echo $data_sessao; ?>
            </h4>

            <div class="sessao-row">
                <?php foreach ($sessoes_do_dia as $sessao): ?>
                    <form action="processar_carrinho.php" method="POST" class="sessao-bloco shadow-sm" style="flex-basis: 22%;">
                        <input type="hidden" name="acao" value="adicionar_ingresso">
                        <input type="hidden" name="sessao_id" value="<?php echo $sessao['id']; ?>">
                        
                        <div class="text-left">
                            <div class="sessao-time">
                                <?php echo formatar_hora($sessao['horario']); ?>
                            </div>
                            <div class="sessao-sala-data">
                                Sala: <?php echo htmlspecialchars($sessao['sala']); ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="sessao-preco text-warning">
                                R$ <?php echo number_format($sessao['preco'], 2, ',', '.'); ?>
                            </span>
                        </div>

                        <button type="submit" style="display: none;"></button>
                    </form>
                    
                    <script>
                        // Garante que o clique no bloco envie o formulário
                        document.querySelectorAll('.sessao-bloco').forEach(bloco => {
                            bloco.addEventListener('click', function() {
                                this.submit();
                            });
                        });
                    </script>

                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="filmes.php" class="btn btn-outline-warning btn-lg">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
<?php ob_end_flush(); ?>