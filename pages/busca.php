<?php
require_once '../src/includes/header.php';

$busca = trim($_GET['busca'] ?? '');
$ordem = $_GET['ordem'] ?? 'novos';

$sql = "SELECT p.*, 
        COALESCE(avg_table.media_estrelas, 0) AS media_estrelas,
        COALESCE(avg_table.total_avaliacoes, 0) AS total_avaliacoes
        FROM produtos p
        LEFT JOIN (
            SELECT id_produto, AVG(estrelas) AS media_estrelas, COUNT(id_avaliacao) AS total_avaliacoes
            FROM avaliacoes
            WHERE status = 'ativo'
            GROUP BY id_produto
        ) avg_table ON p.id_produto = avg_table.id_produto
        WHERE (p.id_produto_pai IS NULL OR p.id_produto = p.id_produto_pai)";

$params = [];
$tipos  = "";

if (!empty($busca)) {
    $sql .= " AND (p.nome_produto LIKE ? OR p.autor_produto LIKE ? OR p.categoria_produto LIKE ?)";
    $paramBusca = "%" . $busca . "%";
    $params[]   = $paramBusca;
    $params[]   = $paramBusca;
    $params[]   = $paramBusca;
    $tipos     .= "sss";
}

switch ($ordem) {
    case 'preco_asc':
        $sql .= " ORDER BY p.preco_produto ASC";
        break;
    case 'preco_desc':
        $sql .= " ORDER BY p.preco_produto DESC";
        break;
    case 'avaliados':
        $sql .= " ORDER BY media_estrelas DESC";
        break;
    default:
        $sql .= " ORDER BY p.id_produto DESC";
        break;
}

$stmt = $conexao->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="catalog-container my-5">
    <div class="catalog-header-flex">
        <h2>
            <?= !empty($busca) ? 'Resultados para "' . htmlspecialchars($busca) . '"' : 'Todos os Livros' ?>
        </h2>

        <form method="GET" action="busca.php" class="search-filter-form">
            <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">

            <label for="ordem" class="filter-label">Ordenar por:</label>
            <select id="ordem" name="ordem" class="filter-select js-auto-submit">
                <option value="novos" <?= $ordem === 'novos' ? 'selected' : '' ?>>Mais recentes</option>
                <option value="preco_asc" <?= $ordem === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
                <option value="preco_desc" <?= $ordem === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
                <option value="avaliados" <?= $ordem === 'avaliados' ? 'selected' : '' ?>>Melhor avaliados</option>
            </select>
        </form>
    </div>

    <div class="books-grid">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while ($livro = $resultado->fetch_assoc()): ?>
                <div class="book-card">
                    <img src="../<?= htmlspecialchars($livro['img_produto']) ?>" alt="Capa de <?= htmlspecialchars($livro['nome_produto']) ?>" class="book-cover">

                    <div class="book-info">
                        <span class="category-badge"><?= htmlspecialchars($livro['categoria_produto']) ?></span>
                        <h3><?= htmlspecialchars($livro['nome_produto']) ?></h3>
                        <p class="author">Por <?= htmlspecialchars($livro['autor_produto']) ?></p>

                        <div class="rating-display">
                            <span class="stars">★ <?= number_format($livro['media_estrelas'], 1) ?></span>
                            <span class="rating-count">(<?= $livro['total_avaliacoes'] ?>)</span>
                        </div>

                        <div class="book-footer">
                            
                            <a href="livro.php?id=<?= $livro['id_produto'] ?>" class="btn-details">Ver Livro</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">Nenhum livro encontrado para esta busca.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
require_once '../src/includes/footer.php';
?>