<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conexao.php';
require_once '../includes/auth.php';

// Garante que o usuário está logado
if (!usuarioEstaLogado()) {
    header("Location: ../pages/login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_produto = (int)($_GET['id_produto'] ?? 0);

if ($id_produto <= 0) {
    header("Location: ../index.php");
    exit();
}

// Verifica se já existe nos favoritos
$sql_verifica = "SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_produto = ?";
$stmt = $conexao->prepare($sql_verifica);
$stmt->bind_param("ii", $id_usuario, $id_produto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    // Se já existe, remove dos favoritos
    $stmt->close();
    $sql_remove = "DELETE FROM favoritos WHERE id_usuario = ? AND id_produto = ?";
    $stmt_rem = $conexao->prepare($sql_remove);
    $stmt_rem->bind_param("ii", $id_usuario, $id_produto);
    $stmt_rem->execute();
    $stmt_rem->close();
} else {
    // Se não existe, adiciona aos favoritos
    $stmt->close();
    $sql_insere = "INSERT INTO favoritos (id_usuario, id_produto, data_favorito) VALUES (?, ?, NOW())";
    $stmt_ins = $conexao->prepare($sql_insere);
    $stmt_ins->bind_param("ii", $id_usuario, $id_produto);
    $stmt_ins->execute();
    $stmt_ins->close();
}

// Retorna para a página de detalhes do produto de onde veio
$referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
header("Location: " . $referer);
exit();
?>