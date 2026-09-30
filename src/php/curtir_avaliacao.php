<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conexao.php';
require_once '../includes/auth.php';

if (!usuarioEstaLogado()) {
    header("Location: ../pages/login.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_avaliacao = (int)($_GET['id_avaliacao'] ?? 0);

if ($id_avaliacao > 0) {
    // Verifica se já curtiu
    $stmt = $conexao->prepare("SELECT id_curtida FROM avaliacao_curtidas WHERE id_avaliacao = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_avaliacao, $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows > 0) {
        // Se já curtiu, descurte (remove)
        $stmt_del = $conexao->prepare("DELETE FROM avaliacao_curtidas WHERE id_avaliacao = ? AND id_usuario = ?");
        $stmt_del->bind_param("ii", $id_avaliacao, $id_usuario);
        $stmt_del->execute();
        $stmt_del->close();
    } else {
        // Se não curtiu, adiciona a curtida
        $stmt_ins = $conexao->prepare("INSERT INTO avaliacao_curtidas (id_avaliacao, id_usuario) VALUES (?, ?)");
        $stmt_ins->bind_param("ii", $id_avaliacao, $id_usuario);
        $stmt_ins->execute();
        $stmt_ins->close();
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
header("Location: " . $referer);
exit();
?>