<?php
// ADICIONE ESTA LINHA:
ob_start();

session_start();
require_once "banco.php";

// 🔒 1. VERIFICAÇÃO DE SEGURANÇA (APENAS ADMIN)
if (empty($_SESSION['usuario_id']) || empty($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 2. PEGAR O ID DO FILME DA URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: filmes.php");
    exit;
}

$filme_id = intval($_GET['id']);

$pdo = Banco::conectar();

// 3. EXECUTAR O SOFT DELETE
$sql = "UPDATE tb_filmes SET ativo = 0 WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$filme_id]);
Banco::desconectar();

// 4. REDIRECIONAR AGORA (o buffer deve garantir que funcione)
header("Location: filmes.php");

// ADICIONE ESTAS DUAS LINHAS:
ob_end_flush(); 
exit;