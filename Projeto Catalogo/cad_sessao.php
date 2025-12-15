<?php
// FORÇA O PHP A MOSTRAR ERROS (APENAS PARA DEBUG!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN (CRUCIAL)
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

// 1. OBTÉM O ID DO FILME DA URL
$filme_id = intval($_GET['filme_id'] ?? 0);

if ($filme_id === 0) {
    // Se não houver ID do filme, redireciona para a lista
    header("Location: filmes.php");
    exit;
}

$pdo = Banco::conectar();

// 2. BUSCA O NOME DO FILME PARA EXIBIÇÃO
$sql_filme = "SELECT titulo FROM tb_filmes WHERE id = ?";
$stmt_filme = $pdo->prepare($sql_filme);
$stmt_filme->execute([$filme_id]);
$filme = $stmt_filme->fetch(PDO::FETCH_ASSOC);

if (!$filme) {
    header("Location: filmes.php");
    exit;
}

$mensagem = '';
$titulo_pagina = "Adicionar Sessão para " . htmlspecialchars($filme['titulo']);

// 3. PROCESSAMENTO DO FORMULÁRIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $horario = $_POST['horario'] ?? '';
    $data = $_POST['data'] ?? '';
    
    // Converte a vírgula para ponto se o usuário usou vírgula
    $preco_str = str_replace(',', '.', $_POST['preco'] ?? '0');
    $preco = filter_var($preco_str, FILTER_VALIDATE_FLOAT);
    
    $sala = $_POST['sala'] ?? '';

    if ($horario && $data && $preco !== false && $sala) {
        
        try {
            // Reabre a conexão se ela foi fechada em uma tentativa anterior de POST
            $pdo = Banco::conectar(); 
            
            $sql_insert = "INSERT INTO tb_sessoes (filme_id, horario, data, preco, sala, ativo) 
                           VALUES (?, ?, ?, ?, ?, 1)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$filme_id, $horario, $data, $preco, $sala]);
            
            $mensagem = "<div class='alert alert-success'>Sessão cadastrada com sucesso! Hora de verificar a lista abaixo.</div>";
            
        } catch (PDOException $e) {
            // Mostra o erro exato do banco de dados
            $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar sessão: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Por favor, preencha todos os campos corretamente.</div>";
    }
}

// 4. BUSCAR SESSÕES EXISTENTES (Sempre busca após o POST para atualizar a lista)
$pdo = Banco::conectar(); // Garante que a conexão está aberta para esta busca
$sql_lista = "SELECT id, horario, data, preco, sala, ativo FROM tb_sessoes 
              WHERE filme_id = ? ORDER BY data ASC, horario ASC";
$stmt_lista = $pdo->prepare($sql_lista);
$stmt_lista->execute([$filme_id]);
$sessoes_existentes = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

Banco::desconectar();
// FIM DA ZONA DE PROCESSAMENTO

// Função auxiliar de formatação
function formatar_data($data) { return date('d/m/Y', strtotime($data)); }
function formatar_hora($hora) { return date('H:i', strtotime($hora)); }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo_pagina); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand text-warning" href="filmes.php">
        <i class="fas fa-video"></i> CatalogoFlix (Admin)
    </a>
    <div class="ml-auto">
        <a href="logout.php" class="btn btn-warning my-2 my-sm-0">Sair</a>
    </div>
</nav>

<div class="container my-5">
    <h2 class="stark text-center mb-4"><?php echo htmlspecialchars($titulo_pagina); ?></h2>
    
    <?php echo $mensagem; ?>

    <div class="card bg-dark text-light p-4 mb-5 shadow">
        <h4 class="card-title text-warning">Nova Sessão</h4>
        <form action="cad_sessao.php?filme_id=<?php echo $filme_id; ?>" method="POST">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="data">Data:</label>
                    <input type="date" id="data" name="data" class="form-control bg-secondary text-light" required>
                </div>
                <div class="col-md-3 form-group">
                    <label for="horario">Horário:</label>
                    <input type="time" id="horario" name="horario" class="form-control bg-secondary text-light" required>
                </div>
                <div class="col-md-3 form-group">
                    <label for="sala">Sala:</label>
                    <input type="text" id="sala" name="sala" class="form-control bg-secondary text-light" required placeholder="Ex: Sala 1, VIP">
                </div>
                <div class="col-md-3 form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" step="0.01" id="preco" name="preco" class="form-control bg-secondary text-light" required min="0.01">
                </div>
            </div>
            <button type="submit" class="btn btn-warning btn-block mt-3">
                <i class="fas fa-plus"></i> Cadastrar Sessão
            </button>
        </form>
    </div>

    <h3 class="stark mb-3">Sessões Ativas / Inativas</h3>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Sala</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessoes_existentes)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhuma sessão cadastrada.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($sessoes_existentes as $sessao): ?>
                    <tr class="<?php echo $sessao['ativo'] ? '' : 'table-secondary'; ?>">
                        <td><?php echo formatar_data($sessao['data']); ?></td>
                        <td><?php echo formatar_hora($sessao['horario']); ?></td>
                        <td><?php echo htmlspecialchars($sessao['sala']); ?></td>
                        <td>R$ <?php echo number_format($sessao['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $sessao['ativo'] ? 'success' : 'danger'; ?>">
                                <?php echo $sessao['ativo'] ? 'Ativa' : 'Inativa'; ?>
                            </span>
                        </td>
                     <td>
                            <a href="editar_sessao.php?id=<?php echo $sessao['id']; ?>" class="btn btn-info btn-sm" title="Editar Sessão">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="processar_sessao.php?acao=toggle&id=<?php echo $sessao['id']; ?>" 
                               class="btn btn-<?php echo $sessao['ativo'] ? 'danger' : 'success'; ?> btn-sm" 
                               title="<?php echo $sessao['ativo'] ? 'Desativar Sessão' : 'Ativar Sessão'; ?>"
                               onclick="return confirm('Deseja <?php echo $sessao['ativo'] ? 'desativar' : 'ativar'; ?> esta sessão?');">
                                <i class="fas fa-<?php echo $sessao['ativo'] ? 'pause-circle' : 'play-circle'; ?>"></i>
                            </a>
                            <a href="processar_sessao.php?acao=excluir&id=<?php echo $sessao['id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               title="Excluir Permanentemente"
                               onclick="return confirm('ATENÇÃO: Deseja EXCLUIR PERMANENTEMENTE esta sessão?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="sessao.php?filme_id=<?php echo $filme_id; ?>" class="btn btn-outline-warning">
            <i class="fas fa-arrow-left"></i> Voltar para Sessões do Filme
        </a>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>