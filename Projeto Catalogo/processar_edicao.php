<?php
session_start();
require_once "banco.php";

// 🔒 VERIFICAÇÃO DE ADMIN
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
    header("Location: filmes.php");
    exit;
}

$acao = $_POST['acao'] ?? '';
$pdo = Banco::conectar();

switch ($acao) {
    case 'editar_sessao':
        $sessao_id = intval($_POST['sessao_id'] ?? 0);
        $filme_id = intval($_POST['filme_id'] ?? 0);
        $sala = intval($_POST['sala'] ?? 0);
        $data = $_POST['data'] ?? '';
        $horario = $_POST['horario'] ?? '';
        $preco = floatval($_POST['preco'] ?? 0);

        if ($sessao_id > 0 && $filme_id > 0 && $sala > 0 && !empty($data) && !empty($horario)) {
            try {
                $sql = "UPDATE tb_sessoes SET 
                            filme_id = ?, 
                            sala = ?, 
                            data = ?, 
                            horario = ?, 
                            preco = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$filme_id, $sala, $data, $horario, $preco, $sessao_id]);
                
                // CORREÇÃO APLICADA AQUI: Redireciona para cad_sessao.php
                header("Location: cad_sessao.php?msg=sucesso_edicao");
                exit;

            } catch (PDOException $e) {
                echo "<h1>Erro ao editar Sessão</h1>";
                echo "<p>Detalhes do erro: " . $e->getMessage() . "</p>";
                Banco::desconectar();
                exit;
            }
        }
        break;
        
    case 'editar_alimento':
        $alimento_id = intval($_POST['alimento_id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $imagem = trim($_POST['imagem'] ?? '');

        if ($alimento_id > 0 && !empty($nome) && $preco > 0) {
            try {
                $sql = "UPDATE tb_alimentos SET 
                            nome = ?, 
                            descricao = ?, 
                            preco = ?, 
                            imagem = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $preco, $imagem, $alimento_id]);
                
                // Redirecionamento de Alimento
                header("Location: cad_alimentos.php?msg=sucesso_edicao");
                exit;

            } catch (PDOException $e) {
                echo "<h1>Erro ao editar Alimento</h1>";
                echo "<p>Detalhes do erro: " . $e->getMessage() . "</p>";
                Banco::desconectar();
                exit;
            }
        }
        break;
}

Banco::desconectar();
// Se a ação não for reconhecida, volta para a tela de filmes
header("Location: filmes.php");
exit;
?>