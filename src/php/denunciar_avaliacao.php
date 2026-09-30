<?php
// Ativa a exibição de erros temporariamente para testes
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/conexao.php';

$redirect_padrao = "../pages/painel.php";
$url_retorno = $_SERVER['HTTP_REFERER'] ?? $redirect_padrao;

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    die("ERRO DE DEBUG: Utilizador não está logado na sessão. ID do usuário ausente.");
}

// 2. Pega o ID da avaliação via GET ou POST
$id_avaliacao = isset($_GET['id_avaliacao']) ? (int)$_GET['id_avaliacao'] : (isset($_POST['id_avaliacao']) ? (int)$_POST['id_avaliacao'] : 0);

if ($id_avaliacao <= 0) {
    die("ERRO DE DEBUG: ID da avaliação inválido ou não recebido. Valor: " . var_export($id_avaliacao, true));
}

$id_usuario = (int) $_SESSION['id_usuario'];

// 3. Verifica se já denunciou
$stmt = $conexao->prepare("SELECT id_denuncia FROM avaliacao_denuncias WHERE id_avaliacao = ? AND id_usuario = ?");
if (!$stmt) {
    die("ERRO NO PREPARE (SELECT): " . $conexao->error);
}
$stmt->bind_param("ii", $id_avaliacao, $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows == 0) {
    // Insere a denúncia
    $stmt_ins = $conexao->prepare("INSERT INTO avaliacao_denuncias (id_avaliacao, id_usuario, motivo) VALUES (?, ?, 'Conteúdo inadequado')");
    if (!$stmt_ins) {
        die("ERRO NO PREPARE (INSERT): " . $conexao->error);
    }
    $stmt_ins->bind_param("ii", $id_avaliacao, $id_usuario);
    if (!$stmt_ins->execute()) {
        die("ERRO AO EXECUTAR INSERT: " . $stmt_ins->error);
    }
    $stmt_ins->close();

    // Atualiza o status
    $stmt_up = $conexao->prepare("UPDATE avaliacoes SET status = 'denunciado' WHERE id_avaliacao = ?");
    if (!$stmt_up) {
        die("ERRO NO PREPARE (UPDATE): " . $conexao->error);
    }
    $stmt_up->bind_param("i", $id_avaliacao);
    if (!$stmt_up->execute()) {
        die("ERRO AO EXECUTAR UPDATE: " . $stmt_up->error);
    }
    $stmt_up->close();
} else {
    echo "AVISO DE DEBUG: Este utilizador já tinha denunciado esta avaliação anteriormente.";
    exit();
}

// Se deu tudo certo, redireciona de volta
header("Location: " . $url_retorno);
exit();
?>