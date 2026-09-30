<?php
require_once '../src/includes/header.php';
require_once '../src/includes/auth.php';

// Exige que o usuário esteja logado para acessar o checkout
if (!usuarioEstaLogado()) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];

// 1. Busca os dados do carrinho e verifica se há algum produto físico
$ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
$sql = "SELECT id_produto, nome_produto, preco_produto, tipo_produto FROM produtos WHERE id_produto IN ($ids)";
$resultado = $conexao->query($sql);

$itens_checkout = [];
$subtotal = 0;
$possui_fisico = false;

if ($resultado) {
    while ($p = $resultado->fetch_assoc()) {
        $id = $p['id_produto'];
        $qtd = $_SESSION['carrinho'][$id] ?? 0;
        $total_item = $p['preco_produto'] * $qtd;
        $subtotal += $total_item;

        if ($p['tipo_produto'] === 'Livro Físico') {
            $possui_fisico = true;
        }

        $p['quantidade'] = $qtd;
        $p['total_item'] = $total_item;
        $itens_checkout[] = $p;
    }
}

// 2. Se houver produto físico, busca o endereço do usuário
$endereco_usuario = null;
if ($possui_fisico) {
    $stmt_end = $conexao->prepare("SELECT rua, numero, bairro, cidade, estado, cep FROM usuarios WHERE id_usuario = ?");
    $stmt_end->bind_param("i", $id_usuario);
    $stmt_end->execute();
    $endereco_usuario = $stmt_end->get_result()->fetch_assoc();
    $stmt_end->close();
}
?>

<div class="checkout-container">
    <h2>Finalizar Pedido 📦</h2>

    <form action="../src/php/processar_pedido.php" method="POST" id="form-checkout" data-subtotal="<?= $subtotal ?>">
        <div class="checkout-grid">
            <div>
                <!-- Seção de Endereço (Apenas se houver Livro Físico) -->
                <?php if ($possui_fisico): ?>
                    <div class="checkout-card">
                        <h3>Endereço de Entrega 📍</h3>
                        <?php if (!empty($endereco_usuario['rua'])): ?>
                            <p class="address-text">
                                <strong><?= htmlspecialchars($endereco_usuario['rua']) ?>, <?= htmlspecialchars($endereco_usuario['numero']) ?></strong><br>
                                <?= htmlspecialchars($endereco_usuario['bairro']) ?> - <?= htmlspecialchars($endereco_usuario['cidade']) ?> / <?= htmlspecialchars($endereco_usuario['estado']) ?><br>
                                CEP: <?= htmlspecialchars($endereco_usuario['cep']) ?>
                            </p>
                            <a href="perfil.php" class="address-link">Alterar endereço no perfil</a>
                        <?php else: ?>
                            <div class="address-warning">
                                Você possui livros físicos no carrinho, mas ainda não cadastrou um endereço de entrega.
                            </div>
                            <a href="perfil.php" class="btn-details">Cadastrar Endereço Agora</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Seção de Opções de Pagamento -->
                <div class="checkout-card">
                    <h3>Forma de Pagamento 💳</h3>

                    <label class="payment-option">
                        <input type="radio" name="forma_pagamento" value="pix" required>
                        <div>
                            <strong>PIX</strong> <span class="payment-tag-success">(10% de desconto - Aprovação imediata)</span>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="forma_pagamento" value="boleto">
                        <div>
                            <strong>Boleto Bancário</strong> <span class="payment-tag-muted">(Sem desconto ou acréscimo)</span>
                        </div>
                    </label>

                    <label class="payment-option payment-option--stacked">
                        <div class="payment-option-row">
                            <input type="radio" name="forma_pagamento" value="cartao" id="radio-cartao">
                            <div>
                                <strong>Cartão de Crédito</strong> <span class="payment-tag-muted">(Simulação Acadêmica - Sem dados sensíveis)</span>
                            </div>
                        </div>
                        <div id="container-parcelas" class="installments-box is-hidden">
                            <label class="installments-label" for="select-parcelas">Parcelamento:</label>
                            <select name="parcelas" id="select-parcelas" class="installments-select">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?>x <?= $i > 3 ? '(Com juros de acréscimo)' : '(Sem juros)' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Resumo Lateral -->
            <div>
                <div class="checkout-card">
                    <h3>Resumo da Compra</h3>
                    <div class="summary-line">
                        <span>Subtotal (<?= array_sum($_SESSION['carrinho']) ?> itens)</span>
                        <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>
                    <div class="summary-line discount is-hidden" id="linha-desconto">
                        <span>Desconto PIX (10%)</span>
                        <span id="valor-desconto">- R$ 0,00</span>
                    </div>
                    <div class="summary-line interest is-hidden" id="linha-juros">
                        <span>Acréscimo Parcelamento</span>
                        <span id="valor-juros">+ R$ 0,00</span>
                    </div>
                    <div class="summary-line total">
                        <span>Total Final</span>
                        <span id="valor-total-final">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>

                    <button type="submit" class="btn-finalizar">Finalizar Pedido</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
require_once '../src/includes/footer.php';
?>