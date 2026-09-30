<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conexao.php';
require_once '../includes/auth.php';

if (!usuarioEstaLogado() || empty($_SESSION['carrinho'])) {
    header("Location: ../../index.php");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$forma_pagamento = $_POST['forma_pagamento'] ?? '';
$parcelas = (int)($_POST['parcelas'] ?? 1);

if (!in_array($forma_pagamento, ['pix', 'boleto', 'cartao'])) {
    header("Location: ../../pages/checkout.php?erro=pagamento_invalido");
    exit();
}

$conexao->begin_transaction();

try {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
    $sql = "SELECT id_produto, preco_produto, tipo_produto, quantidade_produto, nome_produto FROM produtos WHERE id_produto IN ($ids)";
    $resultado = $conexao->query($sql);

    $subtotal = 0;
    $itens_validos = [];

    while ($p = $resultado->fetch_assoc()) {
        $id = $p['id_produto'];
        $qtd_solicitada = $_SESSION['carrinho'][$id];
        
        if ($p['tipo_produto'] === 'Livro Físico' && $p['quantidade_produto'] < $qtd_solicitada) {
            throw new Exception("Estoque insuficiente para o livro físico: " . $p['nome_produto']);
        }

        $subtotal += $p['preco_produto'] * $qtd_solicitada;
        $itens_validos[] = [
            'id_produto' => $id,
            'preco' => $p['preco_produto'],
            'quantidade' => $qtd_solicitada,
            'tipo' => $p['tipo_produto']
        ];
    }

    $valor_total = $subtotal;
    if ($forma_pagamento === 'pix') {
        $valor_total = $subtotal * 0.90;
    } elseif ($forma_pagamento === 'cartao' && $parcelas > 3) {
        $percentualAcres = ($parcelas - 3) * 0.015;
        $valor_total = $subtotal * (1 + $percentualAcres);
    }

    $status_pedido = ($forma_pagamento === 'pix') ? 'aprovado' : 'pendente';

    $stmt_ped = $conexao->prepare("INSERT INTO pedidos (id_usuario, valor_total, forma_pagamento, parcelas, status_pedido, data_pedido) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt_ped->bind_param("idsis", $id_usuario, $valor_total, $forma_pagamento, $parcelas, $status_pedido);
    $stmt_ped->execute();
    $id_pedido = $stmt_ped->insert_id;
    $stmt_ped->close();

    foreach ($itens_validos as $item) {
        $stmt_item = $conexao->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $id_pedido, $item['id_produto'], $item['quantidade'], $item['preco']);
        $stmt_item->execute();
        $stmt_item->close();

        if ($item['tipo'] === 'Livro Físico') {
            $stmt_est = $conexao->prepare("UPDATE produtos SET quantidade_produto = quantidade_produto - ? WHERE id_produto = ?");
            $stmt_est->bind_param("ii", $item['quantidade'], $item['id_produto']);
            $stmt_est->execute();
            $stmt_est->close();
        }
    }

    $_SESSION['carrinho'] = [];
    $conexao->commit();

    header("Location: ../../pages/painel.php?sucesso=1");
    exit();

} catch (Exception $e) {
    $conexao->rollback();
    header("Location: ../../pages/checkout.php?erro=" . urlencode($e->getMessage()));
    exit();
}
?>