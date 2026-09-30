<?php
require_once '../src/includes/header.php';

$id_produto = (int) ($_GET['id'] ?? 0);

if ($id_produto <= 0) {
    header("Location: ../index.php");
    exit();
}

// 1. Busca o produto principal e todas as variações associadas
$sql_livros = "SELECT p.*, 
                COALESCE(AVG(a.estrelas), 0) AS media_estrelas,
                COUNT(a.id_avaliacao) AS total_avaliacoes
              FROM produtos p
              LEFT JOIN avaliacoes a ON p.id_produto = a.id_produto AND a.status = 'ativo'
              WHERE p.id_produto = ? OR p.id_produto_pai = ? OR p.id_produto = (SELECT COALESCE(id_produto_pai, id_produto) FROM produtos WHERE id_produto = ?)
              GROUP BY p.id_produto";

$stmt = $conexao->prepare($sql_livros);
$stmt->bind_param("iii", $id_produto, $id_produto, $id_produto);
$stmt->execute();
$resultado_versoes = $stmt->get_result();
$stmt->close();

$versoes = [];
while ($row = $resultado_versoes->fetch_assoc()) {
    $versoes[] = $row;
}

if (empty($versoes)) {
    echo "<div class='catalog-container'><p class='empty-message'>Livro não encontrado.</p></div>";
    require_once '../src/includes/footer.php';
    exit();
}

$livro_principal = $versoes[0];

// 2. Verificar se o usuário logado favoritou esta obra
$favoritado = false;
$id_usuario_atual = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

if ($id_usuario_atual > 0) {
    $sql_fav = "SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_produto = ?";
    $stmt_fav = $conexao->prepare($sql_fav);
    $stmt_fav->bind_param("ii", $id_usuario_atual, $livro_principal['id_produto']);
    $stmt_fav->execute();
    $favoritado = $stmt_fav->get_result()->num_rows > 0;
    $stmt_fav->close();
}

// 3. Buscar avaliações ativas e denunciadas (para exibir o aviso de moderação)
$sql_avaliacoes = "SELECT a.*, u.nome, 
                    (SELECT COUNT(*) FROM avaliacao_curtidas c WHERE c.id_avaliacao = a.id_avaliacao) AS curtidas,
                    (SELECT COUNT(*) FROM avaliacao_curtidas c WHERE c.id_avaliacao = a.id_avaliacao AND c.id_usuario = ?) AS ja_curtiu
                   FROM avaliacoes a
                   INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                   WHERE a.id_produto = ? AND a.status IN ('ativo', 'denunciado')
                   ORDER BY a.data_avaliacao DESC";

$stmt_rev = $conexao->prepare($sql_avaliacoes);
$stmt_rev->bind_param("ii", $id_usuario_atual, $livro_principal['id_produto']);
$stmt_rev->execute();
$avaliacoes = $stmt_rev->get_result();
?>

<!-- Container do Toast -->
<div id="toast-notification" class="custom-toast"></div>

<div class="catalog-container my-5">
    <div class="book-detail-wrapper">
        <div class="book-detail-cover">
            <img src="../<?= htmlspecialchars($livro_principal['img_produto']) ?>"
                 alt="Capa de <?= htmlspecialchars($livro_principal['nome_produto']) ?>">
        </div>

        <div class="book-detail-info">
            <span class="category-badge"><?= htmlspecialchars($livro_principal['categoria_produto']) ?></span>
            <h1 class="book-detail-title"><?= htmlspecialchars($livro_principal['nome_produto']) ?></h1>
            <p class="author book-detail-author">Por <strong><?= htmlspecialchars($livro_principal['autor_produto']) ?></strong></p>

            <div class="rating-display book-detail-rating">
                <span class="stars">★ <?= number_format($livro_principal['media_estrelas'], 1) ?></span>
                <span class="rating-count">(<?= $livro_principal['total_avaliacoes'] ?> avaliações)</span>
            </div>

            <ul class="book-meta-list">
                <li><strong>Editora:</strong> <?= htmlspecialchars($livro_principal['editora_produto']) ?></li>
                <li><strong>Páginas:</strong> <?= $livro_principal['paginas_produto'] ?></li>
                <li><strong>Idioma:</strong> <?= htmlspecialchars($livro_principal['idioma_produto']) ?></li>
                <li><strong>Classificação:</strong> <?= htmlspecialchars($livro_principal['classificacao_indicativa_produto']) ?></li>
            </ul>

            <!-- Seleção de Formatos -->
            <form id="form-carrinho" action="../src/php/carrinho_acoes.php" method="POST">
                <input type="hidden" name="acao" value="adicionar">

                <div class="format-selector">
                    <label class="format-selector-title">Escolha o formato:</label>
                    <div class="format-options">
                        <?php foreach ($versoes as $v): ?>
                            <label class="format-option-label">
                                <input type="radio" name="id_produto" value="<?= $v['id_produto'] ?>" required <?= ($v['id_produto'] == $livro_principal['id_produto']) ? 'checked' : '' ?>>
                                <div><strong><?= htmlspecialchars($v['tipo_produto']) ?></strong></div>
                                <div class="format-price">R$ <?= number_format($v['preco_produto'], 2, ',', '.') ?></div>

                                <!-- Aviso de estoque baixo (< 5) -->
                                <?php if ($v['tipo_produto'] === 'Livro Físico'): ?>
                                    <?php if ($v['quantidade_produto'] > 0 && $v['quantidade_produto'] < 5): ?>
                                        <div class="stock-warning">Restam apenas <?= $v['quantidade_produto'] ?> unidades!</div>
                                    <?php elseif ($v['quantidade_produto'] == 0): ?>
                                        <div class="stock-empty">Esgotado</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="book-actions">
                    <button type="submit" class="btn-details btn-details-lg">
                        Adicionar ao Carrinho 🛒
                    </button>

                    <?php if ($id_usuario_atual > 0): ?>
                        <a href="../src/php/favoritar.php?id_produto=<?= $livro_principal['id_produto'] ?>"
                           class="btn-details btn-fav<?= $favoritado ? ' is-favoritado' : '' ?>">
                            <?= $favoritado ? '♥ Favoritado' : '♡ Favoritar' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <hr class="divider">

    <!-- Sinopse -->
    <section class="description-section">
        <h3>Sinopse</h3>
        <p class="description-text"><?= nl2br(htmlspecialchars($livro_principal['descricao_produto'])) ?></p>
    </section>

    <hr class="divider">

    <!-- Seção de Avaliações -->
    <section class="reviews-section">
        <h3>Avaliações dos Leitores ⭐</h3>

        <?php if ($id_usuario_atual > 0): ?>
            <form action="../src/php/comentario.php" method="POST" class="review-form">
                <input type="hidden" name="id_produto" value="<?= $livro_principal['id_produto'] ?>">

                <div class="review-form-block">
                    <label class="review-form-label">Sua Nota:</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="estrelas" value="5" required /><label for="star5" title="5 estrelas">★</label>
                        <input type="radio" id="star4" name="estrelas" value="4" /><label for="star4" title="4 estrelas">★</label>
                        <input type="radio" id="star3" name="estrelas" value="3" /><label for="star3" title="3 estrelas">★</label>
                        <input type="radio" id="star2" name="estrelas" value="2" /><label for="star2" title="2 estrelas">★</label>
                        <input type="radio" id="star1" name="estrelas" value="1" /><label for="star1" title="1 estrela">★</label>
                    </div>
                </div>

                <div class="review-form-block">
                    <textarea name="comentario" rows="3" placeholder="Escreva sua opinião sobre a obra..." required class="review-textarea"></textarea>
                </div>

                <button type="submit" class="btn-details">Enviar Avaliação</button>
            </form>
        <?php else: ?>
            <p class="review-login-note"><a href="login.php">Faça login</a> para deixar sua avaliação.</p>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if ($avaliacoes && $avaliacoes->num_rows > 0): ?>
                <?php while ($rev = $avaliacoes->fetch_assoc()): ?>
                    <div class="review-card">
                        <?php if ($rev['status'] === 'denunciado'): ?>
                            <!-- Mensagem quando o comentário está sob avaliação -->
                            <p class="review-moderation-note">
                                ⚠️ Este comentário está sob avaliação de um moderador.
                            </p>
                        <?php else: ?>
                            <div class="review-header">
                                <strong><?= htmlspecialchars($rev['nome']) ?></strong>
                                <span class="review-stars"><?= str_repeat('★', $rev['estrelas']) ?><?= str_repeat('☆', 5 - $rev['estrelas']) ?></span>
                            </div>
                            <p class="review-text"><?= htmlspecialchars($rev['comentario']) ?></p>

                            <div class="review-footer">
                                <small class="review-date"><?= date('d/m/Y H:i', strtotime($rev['data_avaliacao'])) ?></small>

                                <?php if ($id_usuario_atual > 0): ?>
                                    <?php $ja_curtiu = (bool) $rev['ja_curtiu']; ?>
                                    <div class="review-actions">
                                        <a href="../src/php/curtir_avaliacao.php?id_avaliacao=<?= $rev['id_avaliacao'] ?>"
                                           class="btn-like<?= $ja_curtiu ? ' is-ativo' : '' ?>">
                                            👍 Curtir (<?= $rev['curtidas'] ?>)
                                        </a>

                                        <a href="../src/php/denunciar_avaliacao.php?id_avaliacao=<?= $rev['id_avaliacao'] ?>"
                                           class="btn-report"
                                           data-confirm="Deseja realmente denunciar esta avaliação?">
                                            🚩 Denunciar
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <small class="review-likes-count">Curtidas: <?= $rev['curtidas'] ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-message">Ainda não há avaliações para esta obra. Seja o primeiro a avaliar!</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
if (isset($stmt_rev)) {
    $stmt_rev->close();
}
require_once '../src/includes/footer.php';
?>