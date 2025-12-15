<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN (CRUCIAL)
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

// 1. OBTÉM OS IDs NECESSÁRIOS
$sessao_id = intval($_GET['id'] ?? 0);

if ($sessao_id === 0) {
    // Se não houver ID, redireciona para a lista de filmes
    header("Location: filmes.php");
    exit;
}

$pdo = Banco::conectar();

// 2. BUSCAR FILME ID RELACIONADO (Para saber para onde redirecionar no final)
$sql_filme_id = "SELECT filme_id, ativo FROM tb_sessoes WHERE id = ?";
$stmt_filme_id = $pdo->prepare($sql_filme_id);
$stmt_filme_id->execute([$sessao_id]);
$sessao = $stmt_filme_id->fetch(PDO::FETCH_ASSOC);

if (!$sessao) {
    // Se a sessão não for encontrada
    header("Location: filmes.php");
    exit;
}

$filme_id = $sessao['filme_id'];
$status_atual = $sessao['ativo'];

// 3. ALTERAR O STATUS (Ativa se inativo, ou inativa se ativo)
// Ação é toggle (alternar)
$novo_status = $status_atual ? 0 : 1; 

try {
    $sql_update = "UPDATE tb_sessoes SET ativo = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$novo_status, $sessao_id]);
    
} catch (PDOException $e) {
    // Em caso de erro de banco, exibe a mensagem de erro.
    echo "<h1>Erro ao atualizar status da sessão</h1>";
    echo "<p>Detalhes do erro: " . $e->getMessage() . "</p>";
    exit;
}

Banco::desconectar();

// 4. REDIRECIONA DE VOLTA para a tela de gerenciamento de sessões
header("Location: cad_sessao.php?filme_id={$filme_id}");
exit;
?>