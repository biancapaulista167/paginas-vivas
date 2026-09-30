<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/conexao.php');

header('Content-Type: application/json');

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$acao = $_POST['acao'] ?? '';
$id_produto = (int)($_POST['id_produto'] ?? 0);
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));

if ($acao === 'adicionar' && $id_produto > 0) {
    // Consulta o produto, preço, tipo e estoque atual no banco de dados
    $stmt_est = $conexao->prepare("SELECT quantidade_produto, tipo_produto FROM produtos WHERE id_produto = ?");
    $stmt_est->bind_param("i", $id_produto);
    $stmt_est->execute();
    $res_est = $stmt_est->get_result()->fetch_assoc();
    $stmt_est->close();

    if (!$res_est) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit();
    }

    $estoque_disponivel = (int)$res_est['quantidade_produto'];
    $tipo_produto = $res_est['tipo_produto'];

    $quantidade_atual = $_SESSION['carrinho'][$id_produto] ?? 0;
    $nova_quantidade = $quantidade_atual + $quantidade;

    // Se for Livro Físico, valida contra o estoque real. Se for eBook/Audiobook, o estoque é virtual/ilimitado.
    if ($tipo_produto !== 'Livro Físico' || $nova_quantidade <= $estoque_disponivel) {
        $_SESSION['carrinho'][$id_produto] = $nova_quantidade;
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Quantidade indisponível em estoque.']);
        exit();
    }
} elseif ($acao === 'atualizar' && $id_produto > 0) {
    $stmt_est = $conexao->prepare("SELECT quantidade_produto, tipo_produto FROM produtos WHERE id_produto = ?");
    $stmt_est->bind_param("i", $id_produto);
    $stmt_est->execute();
    $res_est = $stmt_est->get_result()->fetch_assoc();
    $stmt_est->close();

    if (!$res_est) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit();
    }

    $estoque_disponivel = (int)$res_est['quantidade_produto'];
    $tipo_produto = $res_est['tipo_produto'];

    if ($tipo_produto !== 'Livro Físico' || $quantidade <= $estoque_disponivel) {
        $_SESSION['carrinho'][$id_produto] = $quantidade;
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Quantidade excede o estoque disponível.']);
        exit();
    }
} elseif ($acao === 'remover' && $id_produto > 0) {
    unset($_SESSION['carrinho'][$id_produto]);
} elseif ($acao === 'limpar') {
    $_SESSION['carrinho'] = [];
}

// Recalcula totais do carrinho
$total_itens = array_sum($_SESSION['carrinho']);
$subtotal = 0;
$itens_detalhados = [];

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    $sql = "SELECT id_produto, nome_produto, preco_produto, img_produto, tipo_produto FROM produtos WHERE id_produto IN ($ids)";
    $res = $conexao->query($sql);
    
    while ($p = $res->fetch_assoc()) {
        $qtd = $_SESSION['carrinho'][$p['id_produto']];
        $total_item = $p['preco_produto'] * $qtd;
        $subtotal += $total_item;
        
        $itens_detalhados[] = [
            'id_produto' => $p['id_produto'],
            'nome_produto' => $p['nome_produto'],
            'tipo_produto' => $p['tipo_produto'],
            'preco' => number_format($p['preco_produto'], 2, ',', '.'),
            'imagem' => $p['img_produto'],
            'quantidade' => $qtd,
            'subtotal' => number_format($total_item, 2, ',', '.')
        ];
    }
}

echo json_encode([
    'sucesso' => true,
    'total_itens' => $total_itens,
    'subtotal' => number_format($subtotal, 2, ',', '.'),
    'itens' => $itens_detalhados
]);
exit();
?>