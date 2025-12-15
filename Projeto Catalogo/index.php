<?php
// Removemos toda a lógica de sessão e login.
// O único objetivo é redirecionar o visitante diretamente para a lista de filmes.

header("Location: filmes.php");
exit;
?>