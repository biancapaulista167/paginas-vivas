<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/includes/header.php';
require_once '../src/includes/auth.php';

if (!usuarioEstaLogado()) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];
$is_admin = isset($_SESSION['tipo_usuario']) && ($_SESSION['tipo_usuario'] === 'admin' || $_SESSION['tipo_usuario'] === 'moderador');

// Ações do painel administrativo de moderação de avaliações
if ($is_admin && isset($_GET['acao'], $_GET['id_av'])) {
    $id_av = (int) $_GET['id_av'];
    if ($_GET['acao'] === 'aprovar') {
        $stmt_m = $conexao->prepare("UPDATE avaliacoes SET status = 'ativo' WHERE id_avaliacao = ?");
        $stmt_m->bind_param("i", $id_av);
        $stmt_m->execute();
        $stmt_m->close();
    } elseif ($_GET['acao'] === 'excluir') {
        $stmt_m = $conexao->prepare("DELETE FROM avaliacoes WHERE id_avaliacao = ?");
        $stmt_m->bind_param("i", $id_av);
        $stmt_m->execute();
        $stmt_m->close();
    }
    header("Location: painel.php");
    exit();
}

// Buscar dados do usuário
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nome_exibicao = !empty($usuario['nome']) ? $usuario['nome'] : 'Usuário';
$primeiro_nome = explode(' ', trim($nome_exibicao))[0];

// Buscar Favoritos do usuário
$sql_favoritos = "SELECT p.*, fav.data_favoritado 
                  FROM favoritos fav 
                  INNER JOIN produtos p ON fav.id_produto = p.id_produto 
                  WHERE fav.id_usuario = ? 
                  ORDER BY fav.data_favoritado DESC";
$stmt_fav = $conexao->prepare($sql_favoritos);
$stmt_fav->bind_param("i", $id_usuario);
$stmt_fav->execute();
$res_favoritos = $stmt_fav->get_result();
$favoritos = [];
while ($row = $res_favoritos->fetch_assoc()) {
    $favoritos[] = $row;
}
$stmt_fav->close();

// Buscar Pedidos / Compras do usuário
$sql_compras = "SELECT p.id_pedido, p.valor_total, p.status_pedido, p.data_pedido, 
                       GROUP_CONCAT(CONCAT(prod.nome_produto, ' (x', ip.quantidade, ')') SEPARATOR ', ') AS livros_comprados
                FROM pedidos p
                INNER JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido
                INNER JOIN produtos prod ON ip.id_produto = prod.id_produto
                WHERE p.id_usuario = ?
                GROUP BY p.id_pedido
                ORDER BY p.data_pedido DESC";
$stmt_compras = $conexao->prepare($sql_compras);
$stmt_compras->bind_param("i", $id_usuario);
$stmt_compras->execute();
$res_compras = $stmt_compras->get_result();
$total_pedidos = $res_compras ? $res_compras->num_rows : 0;

// Mapeia o status do pedido para a classe visual do selo
function classeStatusPedido(?string $status): string
{
    $chave = strtolower(trim((string) $status));
    return match ($chave) {
        'pendente' => 'status-badge--pendente',
        'processando' => 'status-badge--processando',
        'enviado' => 'status-badge--enviado',
        'entregue' => 'status-badge--entregue',
        'cancelado' => 'status-badge--cancelado',
        default => 'status-badge--outro',
    };
}
?>

<div class="catalog-container user-panel my-5">

    <!-- Hero de boas-vindas -->
    <div class="panel-hero">
        <div>
            <h2 class="panel-hero-title">
                Olá, <span class="panel-greeting-name"><?= htmlspecialchars($primeiro_nome) ?></span> 👋
                <?php if ($is_admin): ?>
                    <span class="panel-role-tag"><?= htmlspecialchars(ucfirst($_SESSION['tipo_usuario'])) ?></span>
                <?php endif; ?>
            </h2>
            <p class="panel-hero-subtitle">Aqui está um resumo da sua conta no Páginas Vivas.</p>
        </div>

        <div class="panel-stats">
            <div class="stat-pill">
                <span class="stat-pill-number"><?= $total_pedidos ?></span>
                <span class="stat-pill-label">Pedidos</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-number"><?= count($favoritos) ?></span>
                <span class="stat-pill-label">Favoritos</span>
            </div>
        </div>
    </div>

    <!-- Navegação em Abas -->
    <ul class="nav nav-tabs mb-4" id="painelTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="compras-tab" data-bs-toggle="tab" data-bs-target="#compras" type="button" role="tab">📦 Minhas Compras</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="favoritos-tab" data-bs-toggle="tab" data-bs-target="#favoritos" type="button" role="tab">♥ Meus Favoritos (<?= count($favoritos) ?>)</button>
        </li>
        <?php if ($is_admin): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="moderacao-tab" data-bs-toggle="tab" data-bs-target="#moderacao" type="button" role="tab">🛡️ Moderação</button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="painelTabContent">

        <!-- ABA DE COMPRAS -->
        <div class="tab-pane fade show active" id="compras" role="tabpanel">
            <?php if ($total_pedidos > 0): ?>
                <div class="orders-table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Data</th>
                                <th>Itens</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ped = $res_compras->fetch_assoc()): ?>
                                <tr>
                                    <td class="order-id">#<?= $ped['id_pedido'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($ped['data_pedido'])) ?></td>
                                    <td class="order-books"><?= htmlspecialchars($ped['livros_comprados']) ?></td>
                                    <td class="order-total">R$ <?= number_format($ped['valor_total'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="status-badge <?= classeStatusPedido($ped['status_pedido']) ?>">
                                            <?= htmlspecialchars(ucfirst($ped['status_pedido'] ?? 'Processando')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="panel-empty-state">Você ainda não realizou nenhuma compra na livraria.</div>
            <?php endif; ?>
        </div>

        <!-- ABA DE FAVORITOS -->
        <div class="tab-pane fade" id="favoritos" role="tabpanel">
            <?php if (!empty($favoritos)): ?>
                <div class="favorites-grid">
                    <?php foreach ($favoritos as $fav): ?>
                        <div class="fav-card">
                            <img src="../<?= htmlspecialchars($fav['img_produto']) ?>" class="fav-card-cover" alt="Capa de <?= htmlspecialchars($fav['nome_produto']) ?>">
                            <div class="fav-card-body">
                                <p class="fav-card-title"><?= htmlspecialchars($fav['nome_produto']) ?></p>
                                <p class="fav-card-price">R$ <?= number_format($fav['preco_produto'], 2, ',', '.') ?></p>
                                <a href="livro.php?id=<?= $fav['id_produto'] ?>" class="fav-card-link">Ver Livro</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="panel-empty-state">Você ainda não favoritou nenhum livro.</div>
            <?php endif; ?>
        </div>

        <!-- ABA DE MODERAÇÃO (Caso seja admin/moderador) -->
        <?php if ($is_admin): ?>
        <div class="tab-pane fade" id="moderacao" role="tabpanel">
            <?php
            $sql_denuncias = "SELECT a.*, u.nome, p.nome_produto 
                              FROM avaliacoes a 
                              JOIN usuarios u ON a.id_usuario = u.id_usuario 
                              JOIN produtos p ON a.id_produto = p.id_produto 
                              WHERE a.status = 'denunciado' 
                              ORDER BY a.data_avaliacao DESC";
            $res_denuncias = $conexao->query($sql_denuncias);
            ?>

            <div class="moderation-header">
                <h4>Avaliações denunciadas</h4>
                <span class="moderation-count">(<?= $res_denuncias ? $res_denuncias->num_rows : 0 ?>)</span>
            </div>

            <?php if ($res_denuncias && $res_denuncias->num_rows > 0): ?>
                <div class="moderation-list">
                    <?php while ($d = $res_denuncias->fetch_assoc()): ?>
                        <div class="moderation-item">
                            <div class="moderation-item-header">
                                <span class="moderation-book"><?= htmlspecialchars($d['nome_produto']) ?></span>
                                <span class="moderation-user">denunciado — avaliação de <?= htmlspecialchars($d['nome']) ?></span>
                            </div>
                            <p class="moderation-comment">&ldquo;<?= htmlspecialchars($d['comentario']) ?>&rdquo;</p>
                            <div class="moderation-actions">
                                <a href="painel.php?acao=aprovar&id_av=<?= $d['id_avaliacao'] ?>" class="btn-mod-approve">Aprovar</a>
                                <a href="painel.php?acao=excluir&id_av=<?= $d['id_avaliacao'] ?>" class="btn-mod-delete" data-confirm="Deseja excluir permanentemente este comentário?">Excluir</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="panel-empty-state">Nenhum comentário pendente de moderação no momento. 👍</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../src/includes/footer.php'; ?>