<?php
require_once 'src/includes/header.php';

// Captura termos enviados pela busca do header
$busca = trim($_GET['busca'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

// Consulta utilizando subquery para calcular as avaliações e evitar erros de agrupamento com p.*
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
$tipos = "";

if (!empty($busca)) {
    $sql .= " AND (p.nome_produto LIKE ? OR p.autor_produto LIKE ?)";
    $paramBusca = "%" . $busca . "%";
    $params[] = $paramBusca;
    $params[] = $paramBusca;
    $tipos .= "ss";
}

if (!empty($categoria)) {
    $sql .= " AND p.categoria_produto LIKE ?";
    $params[] = "%" . $categoria . "%";
    $tipos .= "s";
}

$sql .= " ORDER BY p.id_produto DESC";

$stmt = $conexao->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="catalog-container">
    <h2>Nosso Catálogo 📚</h2>

    <div class="books-grid">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while ($livro = $resultado->fetch_assoc()): ?>
                <div class="book-card">
                    <img src="<?= htmlspecialchars($livro['img_produto']) ?>" alt="Capa de <?= htmlspecialchars($livro['nome_produto']) ?>" class="book-cover">
                    
                    <div class="book-info">
                        <span class="category-badge"><?= htmlspecialchars($livro['categoria_produto']) ?></span>
                        <h3><?= htmlspecialchars($livro['nome_produto']) ?></h3>
                        <p class="author">Por <?= htmlspecialchars($livro['autor_produto']) ?></p>
                        
                        <div class="rating-display">
                            <span class="stars">★ <?= number_format($livro['media_estrelas'], 1) ?></span>
                            <span class="rating-count">(<?= $livro['total_avaliacoes'] ?>)</span>
                        </div>

                        <div class="book-footer">
                            
                            <a href="pages/livro.php?id=<?= $livro['id_produto'] ?>" class="btn-details">Ver Livro</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">Nenhum livro encontrado no momento.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
require_once 'src/includes/footer.php'; 
?>