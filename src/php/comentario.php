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
$id_produto = (int)($_POST['id_produto'] ?? 0);
$estrelas = (int)($_POST['estrelas'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

// Validações básicas
if ($id_produto <= 0 || $estrelas < 1 || $estrelas > 5 || empty($comentario)) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
    header("Location: " . $referer . (strpos($referer, '?') !== false ? '&' : '?') . "erro=dados_invalidos");
    exit();
}

// Insere a avaliação na tabela 'avaliacoes'
// Definimos o status como 'ativo' por padrão para já exibir na página
$sql = "INSERT INTO avaliacoes (id_usuario, id_produto, estrelas, comentario, status, data_avaliacao) VALUES (?, ?, ?, ?, 'ativo', NOW())";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("iiis", $id_usuario, $id_produto, $estrelas, $comentario);

if ($stmt->execute()) {
    $stmt->close();
    $referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
    header("Location: " . $referer . (strpos($referer, '?') !== false ? '&' : '?') . "sucesso_avaliacao=1");
    exit();
} else {
    $stmt->close();
    $referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
    header("Location: " . $referer . (strpos($referer, '?') !== false ? '&' : '?') . "erro=falha_insercao");
    exit();
}
?>