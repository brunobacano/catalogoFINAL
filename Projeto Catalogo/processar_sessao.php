<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN (CRUCIAL)
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

// 1. OBTÉM OS PARÂMETROS
$sessao_id = intval($_GET['id'] ?? 0);
$acao = $_GET['acao'] ?? ''; // Pode ser 'toggle' ou 'excluir'

if ($sessao_id === 0 || !in_array($acao, ['toggle', 'excluir'])) {
    header("Location: filmes.php"); // Ação ou ID inválido
    exit;
}

$pdo = Banco::conectar();

// 2. BUSCAR FILME ID (Para redirecionar corretamente no final)
$sql_filme_id = "SELECT filme_id, ativo FROM tb_sessoes WHERE id = ?";
$stmt_filme_id = $pdo->prepare($sql_filme_id);
$stmt_filme_id->execute([$sessao_id]);
$sessao = $stmt_filme_id->fetch(PDO::FETCH_ASSOC);

if (!$sessao) {
    // Se a sessão não for encontrada, redireciona para a lista de filmes.
    header("Location: filmes.php");
    exit;
}

$filme_id = $sessao['filme_id'];
$status_atual = $sessao['ativo'];

// 3. EXECUÇÃO DA AÇÃO
try {
    if ($acao === 'toggle') {
        // Ação: Desativar/Ativar (Soft Delete)
        $novo_status = $status_atual ? 0 : 1; 
        $sql_update = "UPDATE tb_sessoes SET ativo = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$novo_status, $sessao_id]);
        
    } elseif ($acao === 'excluir') {
        // Ação: Exclusão Permanente (Hard Delete)
        $sql_delete = "DELETE FROM tb_sessoes WHERE id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$sessao_id]);
    }
    
} catch (PDOException $e) {
    echo "<h1>Erro ao processar sessão</h1>";
    echo "<p>Detalhes do erro: " . $e->getMessage() . "</p>";
    exit;
}

Banco::desconectar();

// 4. REDIRECIONA DE VOLTA para a tela de gerenciamento
header("Location: cad_sessao.php?filme_id={$filme_id}");
exit;
?>