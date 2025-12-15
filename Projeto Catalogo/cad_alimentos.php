<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

$mensagem = '';
$pdo = Banco::conectar();

// ----------------------------------------------------
// PROCESSAMENTO DO FORMULÁRIO (CADASTRO)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $imagem = $_POST['imagem'] ?? '';
    $tipo = $_POST['tipo'] ?? 'Outros';
    
    // Processamento do preço
    $preco_str = str_replace(',', '.', $_POST['preco'] ?? '0');
    $preco = filter_var($preco_str, FILTER_VALIDATE_FLOAT);

    if ($nome && $preco !== false) {
        
        try {
            $sql_insert = "INSERT INTO tb_alimentos (nome, descricao, preco, imagem, tipo, ativo) 
                           VALUES (?, ?, ?, ?, ?, 1)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$nome, $descricao, $preco, $imagem, $tipo]);
            
            $mensagem = "<div class='alert alert-success'>Produto '{$nome}' cadastrado com sucesso!</div>";
            
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Por favor, preencha o Nome e o Preço corretamente.</div>";
    }
}

// ----------------------------------------------------
// BUSCAR ALIMENTOS EXISTENTES (PARA A LISTA)
// ----------------------------------------------------
$sql_lista = "SELECT id, nome, preco, tipo, ativo FROM tb_alimentos ORDER BY tipo ASC, nome ASC";
$stmt_lista = $pdo->prepare($sql_lista);
$stmt_lista->execute();
$alimentos_existentes = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Alimentos</title>
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
    <h2 class="stark text-center mb-4"><i class="fas fa-utensils"></i> Gerenciar Alimentos</h2>
    
    <?php echo $mensagem; ?>

    <div class="card bg-dark text-light p-4 mb-5 shadow">
        <h4 class="card-title text-warning">Novo Produto</h4>
        <form action="cad_alimentos.php" method="POST">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control bg-secondary text-light" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" step="0.01" id="preco" name="preco" class="form-control bg-secondary text-light" required min="0.01">
                </div>
                <div class="col-md-4 form-group">
                    <label for="tipo">Tipo:</label>
                    <select id="tipo" name="tipo" class="form-control bg-secondary text-light">
                        <option value="Pipoca">Pipoca</option>
                        <option value="Bebida">Bebida</option>
                        <option value="Combo">Combo</option>
                        <option value="Doce">Doce</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 form-group">
                    <label for="descricao">Descrição:</label>
                    <input type="text" id="descricao" name="descricao" class="form-control bg-secondary text-light">
                </div>
                <div class="col-md-4 form-group">
                    <label for="imagem">URL da Imagem (Opcional):</label>
                    <input type="text" id="imagem" name="imagem" class="form-control bg-secondary text-light">
                </div>
            </div>
            <button type="submit" class="btn btn-warning btn-block mt-3">
                <i class="fas fa-plus"></i> Cadastrar Produto
            </button>
        </form>
    </div>

    <h3 class="stark mb-3">Estoque de Produtos</h3>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alimentos_existentes)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhum produto cadastrado.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($alimentos_existentes as $alimento): ?>
                    <tr class="<?php echo $alimento['ativo'] ? '' : 'table-secondary'; ?>">
                        <td><?php echo $alimento['id']; ?></td>
                        <td><?php echo htmlspecialchars($alimento['nome']); ?></td>
                        <td><?php echo htmlspecialchars($alimento['tipo']); ?></td>
                        <td>R$ <?php echo number_format($alimento['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $alimento['ativo'] ? 'success' : 'danger'; ?>">
                                <?php echo $alimento['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
<td>
                            <a href="editar_alimentos.php?id=<?php echo $alimento['id']; ?>" class="btn btn-info btn-sm" title="Editar Produto">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="processar_alimento.php?acao=toggle&id=<?php echo $alimento['id']; ?>" 
                               class="btn btn-<?php echo $alimento['ativo'] ? 'danger' : 'success'; ?> btn-sm" 
                               title="<?php echo $alimento['ativo'] ? 'Desativar Venda' : 'Ativar Venda'; ?>"
                               onclick="return confirm('Deseja <?php echo $alimento['ativo'] ? 'desativar' : 'ativar'; ?> a venda de <?php echo htmlspecialchars($alimento['nome']); ?>?');">
                                <i class="fas fa-<?php echo $alimento['ativo'] ? 'pause-circle' : 'play-circle'; ?>"></i>
                            </a>
                            <a href="processar_alimento.php?acao=excluir&id=<?php echo $alimento['id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               title="Excluir Permanentemente"
                               onclick="return confirm('ATENÇÃO: Deseja EXCLUIR PERMANENTEMENTE <?php echo htmlspecialchars($alimento['nome']); ?>?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<div class="text-center mt-4">
        <a href="alimentos.php" class="btn btn-outline-warning">
            <i class="fas fa-arrow-left"></i> Fechar Gerenciamento
        </a>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>