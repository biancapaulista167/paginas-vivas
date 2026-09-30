<?php
session_start();
include('../src/config/conexao.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];
$sql = "SELECT tipo_usuario FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario || !in_array($usuario['tipo_usuario'], ['admin', 'moderador'])) {
    header("Location: ../index.php?erro=acesso_negado");
    exit();
}

include('../src/includes/header.php');
?>

<main class="form-page-container">
    <h2>Cadastrar Novo Livro</h2>

    <form action="../src/php/cadastro_livros.php" method="POST" enctype="multipart/form-data" class="book-form">

        <!-- Informações Gerais da Obra -->
        <div class="book-form-group">
            <label><strong>Título do Livro:</strong></label>
            <input type="text" name="nome_produto" placeholder="Ex: Dom Casmurro" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Autor(a):</strong></label>
            <input type="text" name="autor_produto" placeholder="Ex: Machado de Assis" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Editora:</strong></label>
            <input type="text" name="editora_produto" placeholder="Ex: Companhia das Letras" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Número de Páginas:</strong></label>
            <input type="number" name="paginas_produto" placeholder="Ex: 250" required class="book-form-input">
        </div>

        <!-- Lista Completa de Gêneros Literários -->
        <div class="genres-group">
            <label><strong>Gêneros Literários:</strong> (Selecione quantos desejar)</label>
            <div class="genres-box">
                <?php
                $generos = [
                    "Lírico", "Poesia lírica", "Elegia", "Ode", "Cantiga", "Sátira", "Hino",
                    "Épico", "Narrativo", "Conto", "Novela", "Romance", "Epopeia", "Teatro épico",
                    "Crônica", "Fábula", "Dramático", "Comédia", "Tragédia", "Tragicomédia", "Farsa",
                    "Melodrama", "Pantomima", "Teatro de máscaras", "Teatro de improvisação", "Comédia musical",
                    "Fantasia", "Alta fantasia", "Baixa fantasia", "Fantasia épica", "Fantasia urbana", "Fantasia sombria",
                    "Ficção científica", "Distopia", "Space opera", "Cyberpunk", "Ação e aventura",
                    "Ficção policial", "Mistério", "Horror", "Terror", "Horror gótico", "Horror cósmico",
                    "Terror sobrenatural", "Terror psicológico", "Thriller", "Suspense", "Ficção histórica",
                    "Romance YA", "Romance paranormal", "Romance histórico", "Ficção feminina", "Chick lit",
                    "LGBTQ+", "Ficção contemporânea", "Realismo mágico", "Graphic novel", "Young Adult (YA)",
                    "New Adult (NA)", "Infantil (ficção)", "Memórias", "Autobiografia", "Biografia",
                    "Gastronomia", "Arte e fotografia", "Autoajuda", "História", "Viagem", "Crimes reais",
                    "Humor", "Ensaios", "Guias", "Como fazer", "Religião", "Espiritualidade", "Humanidades",
                    "Ciências sociais", "Paternidade", "Família", "Tecnologia", "Ciência", "Infantil (não ficção)",
                    "Nonsense", "Paródia", "Técnico", "Poesia de cordel", "Histórias em quadrinhos", "Mangá", "Fanfictions"
                ];

                foreach ($generos as $genero):
                ?>
                    <label class="genre-item">
                        <input type="checkbox" name="categorias[]" value="<?= htmlspecialchars($genero) ?>">
                        <span><?= htmlspecialchars($genero) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <input type="text" name="nova_categoria" placeholder="Ou digite outra categoria/gênero personalizado..." class="new-genre-input">
        </div>

        <div class="book-form-group">
            <label><strong>Idioma:</strong></label>
            <input type="text" name="idioma_produto" value="Português" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Classificação Indicativa:</strong></label>
            <input type="text" name="classificacao_indicativa_produto" placeholder="Ex: Livre / +16" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Data de Lançamento:</strong></label>
            <input type="date" name="lancamento_produto" required class="book-form-input">
        </div>
        <div class="book-form-group">
            <label><strong>Sinopse:</strong></label>
            <textarea name="descricao_produto" rows="4" placeholder="Resumo da obra..." required class="book-form-textarea"></textarea>
        </div>
        <div class="book-form-group">
            <label><strong>Capa do Livro (Imagem):</strong></label>
            <input type="file" name="img_produto" required class="book-form-input">
        </div>

        <hr class="form-divider">
        <h3>Formatos, Preços e Estoques</h3>
        <p class="form-hint">Marque as caixas dos formatos disponíveis para esta obra:</p>

        <!-- Versão eBook -->
        <div class="format-box">
            <label class="format-box-toggle">
                <input type="checkbox" name="tem_ebook" id="check_ebook" value="1" checked data-toggle-formato="ebook"> <strong>eBook (Digital)</strong>
            </label>
            <div id="bloco_ebook" class="format-box-fields">
                <input type="number" step="0.01" name="preco_ebook" placeholder="Preço do eBook (ex: 19.90)" class="format-input">
                <input type="hidden" name="qtd_ebook" value="999">
            </div>
        </div>

        <!-- Versão Livro Físico -->
        <div class="format-box">
            <label class="format-box-toggle">
                <input type="checkbox" name="tem_fisico" id="check_fisico" value="1" data-toggle-formato="fisico"> <strong>Livro Físico</strong>
            </label>
            <div id="bloco_fisico" class="format-box-fields is-hidden">
                <div class="format-box-row">
                    <input type="number" step="0.01" name="preco_fisico" placeholder="Preço Físico (ex: 49.90)" class="format-input--inline">
                    <input type="number" name="qtd_fisico" placeholder="Quantidade em Estoque" class="format-input--inline">
                </div>
            </div>
        </div>

        <!-- Versão Audiobook -->
        <div class="format-box format-box--last">
            <label class="format-box-toggle">
                <input type="checkbox" name="tem_audio" id="check_audio" value="1" data-toggle-formato="audio"> <strong>Audiobook</strong>
            </label>
            <div id="bloco_audio" class="format-box-fields is-hidden">
                <input type="number" step="0.01" name="preco_audio" placeholder="Preço Audiobook (ex: 29.90)" class="format-input">
                <input type="hidden" name="qtd_audio" value="999">
            </div>
        </div>

        <button type="submit" class="btn-submit-dark">Cadastrar Livro</button>
    </form>
</main>

<?php include('../src/includes/footer.php'); ?>