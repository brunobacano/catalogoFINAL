<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

$sessao_id = intval($_GET['id'] ?? 0);

if ($sessao_id === 0) {
    header("Location: cad_sessoes.php");
    exit;
}

$pdo = Banco::conectar();

// 1. BUSCAR DADOS DA SESSÃO E O FILME RELACIONADO
$sql_sessao = "SELECT * FROM tb_sessoes WHERE id = ?";
$stmt_sessao = $pdo->prepare($sql_sessao);
$stmt_sessao->execute([$sessao_id]);
$sessao = $stmt_sessao->fetch(PDO::FETCH_ASSOC);

if (!$sessao) {
    // Sessão não encontrada
    header("Location: cad_sessoes.php");
    exit;
}

// 2. BUSCAR TODOS OS FILMES ATIVOS PARA O DROPDOWN DE SELEÇÃO
$sql_filmes = "SELECT id, titulo FROM tb_filmes WHERE ativo = 1 ORDER BY titulo ASC";
$stmt_filmes = $pdo->query($sql_filmes);
$filmes = $stmt_filmes->fetchAll(PDO::FETCH_ASSOC);

Banco::desconectar();

// Formatação dos dados para exibição no formulário
$data_formatada = date('Y-m-d', strtotime($sessao['data']));
$horario_formatado = date('H:i', strtotime($sessao['horario']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Sessão - <?php echo htmlspecialchars($sessao['data']) . ' ' . htmlspecialchars($sessao['horario']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .stark { color: #ffc107; }
    </style>
</head>
<body class="bg-dark text-light">

    <div class="container mt-5">
        <h2 class="stark text-center mb-4"><i class="fas fa-video"></i> Editar Sessão</h2>
        
        <div class="card bg-secondary p-4 mx-auto" style="max-width: 600px;">
            <form action="processar_edicao.php" method="POST">
                <input type="hidden" name="acao" value="editar_sessao">
                <input type="hidden" name="sessao_id" value="<?php echo $sessao['id']; ?>">

                <div class="form-group">
                    <label for="filme_id">Filme:</label>
                    <select class="form-control" id="filme_id" name="filme_id" required>
                        <option value="">Selecione o Filme</option>
                        <?php foreach ($filmes as $filme): ?>
                            <option value="<?php echo $filme['id']; ?>" 
                                    <?php echo ($filme['id'] == $sessao['filme_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($filme['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sala">Sala:</label>
                    <input type="number" class="form-control" id="sala" name="sala" 
                           value="<?php echo htmlspecialchars($sessao['sala']); ?>" required min="1" max="99">
                </div>

                <div class="form-group">
                    <label for="data">Data:</label>
                    <input type="date" class="form-control" id="data" name="data" 
                           value="<?php echo htmlspecialchars($data_formatada); ?>" required>
                </div>

                <div class="form-group">
                    <label for="horario">Horário:</label>
                    <input type="time" class="form-control" id="horario" name="horario" 
                           value="<?php echo htmlspecialchars($horario_formatado); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" 
                           value="<?php echo htmlspecialchars($sessao['preco']); ?>" required min="0">
                </div>
                
                <button type="submit" class="btn btn-warning btn-block mt-4">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="cad_sessoes.php" class="btn btn-outline-light btn-block mt-2">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </form>
        </div>
    </div>
</body>
</html> 