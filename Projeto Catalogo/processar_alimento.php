<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

// 1. OBTÉM OS PARÂMETROS
$alimento_id = intval($_GET['id'] ?? 0);
$acao = $_GET['acao'] ?? ''; // Pode ser 'toggle' ou 'excluir'

if ($alimento_id === 0 || !in_array($acao, ['toggle', 'excluir'])) {
    // Ação ou ID inválido
    header("Location: cad_alimentos.php");
    exit;
}

$pdo = Banco::conectar();

// 2. EXECUÇÃO DA AÇÃO
try {
    if ($acao === 'toggle') {
        // Ação: Desativar/Ativar (Soft Delete)
        
        // Busca o status atual
        $sql_status = "SELECT ativo FROM tb_alimentos WHERE id = ?";
        $stmt_status = $pdo->prepare($sql_status);
        $stmt_status->execute([$alimento_id]);
        $alimento = $stmt_status->fetch(PDO::FETCH_ASSOC);

        if ($alimento) {
            $status_atual = $alimento['ativo'];
            $novo_status = $status_atual ? 0 : 1; 

            $sql_update = "UPDATE tb_alimentos SET ativo = ? WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$novo_status, $alimento_id]);
        }
        
    } elseif ($acao === 'excluir') {
        // Ação: Exclusão Permanente (Hard Delete)
        $sql_delete = "DELETE FROM tb_alimentos WHERE id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$alimento_id]);
    }
    
} catch (PDOException $e) {
    echo "<h1>Erro ao processar alimento</h1>";
    echo "<p>Detalhes do erro: " . $e->getMessage() . "</p>";
    Banco::desconectar();
    exit;
}

Banco::desconectar();

// 3. REDIRECIONA DE VOLTA para a tela de gerenciamento
header("Location: cad_alimentos.php");
exit;
?>