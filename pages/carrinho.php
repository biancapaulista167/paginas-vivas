<?php
require_once '../src/includes/header.php';

// Inicializa o carrinho na sessão se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$itens_carrinho = [];
$subtotal_geral = 0;

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
    $sql = "SELECT id_produto, nome_produto, preco_produto, img_produto, tipo_produto, quantidade_produto FROM produtos WHERE id_produto IN ($ids)";
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($produto = $resultado->fetch_assoc()) {
            $id = $produto['id_produto'];
            $qtd_solicitada = $_SESSION['carrinho'][$id] ?? 0;
            $preco = (float) $produto['preco_produto'];
            $total_item = $preco * $qtd_solicitada;
            $subtotal_geral += $total_item;

            $produto['quantidade_carrinho'] = $qtd_solicitada;
            $produto['total_item'] = $total_item;
            $itens_carrinho[] = $produto;
        }
    }
}
?>

<div class="cart-container">
    <h2>Seu Carrinho de Compras 🛒</h2>

    <?php if (!empty($itens_carrinho)): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Formato</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_carrinho as $item): ?>
                    <tr id="produto-row-<?= $item['id_produto'] ?>">
                        <td>
                            <div class="cart-product-cell">
                                <img src="../<?= htmlspecialchars($item['img_produto']) ?>" alt="<?= htmlspecialchars($item['nome_produto']) ?>">
                                <div>
                                    <strong><?= htmlspecialchars($item['nome_produto']) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><span class="category-badge"><?= htmlspecialchars($item['tipo_produto']) ?></span></td>
                        <td>R$ <?= number_format($item['preco_produto'], 2, ',', '.') ?></td>
                        <td>
                            <div class="qty-input-group">
                                <button type="button" class="qty-btn js-qtd" data-id="<?= $item['id_produto'] ?>" data-delta="-1">-</button>
                                <span class="qty-display" id="qtd-<?= $item['id_produto'] ?>"><?= $item['quantidade_carrinho'] ?></span>
                                <button type="button" class="qty-btn js-qtd" data-id="<?= $item['id_produto'] ?>" data-delta="1">+</button>
                            </div>
                        </td>
                        <td id="subtotal-<?= $item['id_produto'] ?>">R$ <?= number_format($item['total_item'], 2, ',', '.') ?></td>
                        <td>
                            <button type="button" class="btn-remove js-remover-item" data-id="<?= $item['id_produto'] ?>">Remover</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div>
                <h3>Total Geral: <span id="cart-total-geral" class="cart-total-value">R$ <?= number_format($subtotal_geral, 2, ',', '.') ?></span></h3>
            </div>
            <div class="checkout-actions">
                <a href="../index.php" class="btn-continue">Continuar Comprando</a>
                <a href="checkout.php" class="btn-checkout">Finalizar Compra ➔</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-empty-box">
            <p class="cart-empty-text">Seu carrinho está vazio.</p>
            <a href="../index.php" class="btn-checkout">Explorar Catálogo</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../src/includes/footer.php';
?>