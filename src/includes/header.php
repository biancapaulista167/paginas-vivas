<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/auth.php';

// Detecta se o arquivo atual está dentro da pasta /pages/
$em_pages = (strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false);
$raiz = $em_pages ? '../' : './';
$src  = $em_pages ? '../src/' : 'src/';

// Define a rota correta para o busca.php dependendo de onde a página está
$rota_busca = $em_pages ? 'busca.php' : 'pages/busca.php';

// Rota correta para a página do carrinho
$rota_carrinho = $em_pages ? 'carrinho.php' : 'pages/carrinho.php';

// Recupera o termo de busca atual para manter preenchido no input
$termo_busca = trim($_GET['busca'] ?? '');

// Conta total de itens no carrinho para o badge dinâmico
$total_carrinho_header = isset($_SESSION['carrinho']) ? array_sum($_SESSION['carrinho']) : 0;

// Extrai a inicial do nome do usuário logado em maiúsculo
$inicial_usuario = '';
if (usuarioEstaLogado() && !empty($_SESSION['nome_usuario'])) {
    $inicial_usuario = mb_strtoupper(mb_substr($_SESSION['nome_usuario'], 0, 1, 'UTF-8'), 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Páginas Vivas | Livraria Digital</title>
    <link rel="stylesheet" href="<?= $src ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Script inline para prevenir o piscar branco (FOUC) ao carregar a página no modo escuro -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <header class="main-header">
        <div class="header-container">
            <a href="<?= $raiz ?>index.php" class="logo">
                <img src="<?= $src ?>assets/img/logo.jpg" style="border-radius: 25%;" alt="Páginas Vivas" width="60" height="60">
                <span>Páginas Vivas</span>
            </a>
            
            <form action="<?= $rota_busca ?>" method="GET" class="search-form">
                <input type="text" 
                       name="busca" 
                       placeholder="Buscar livro, autor ou categoria..." 
                       value="<?= htmlspecialchars($termo_busca) ?>">
                <button type="submit">Buscar</button>
            </form>

            <nav class="user-nav" style="display: flex; align-items: center; gap: 20px;">
                <a href="<?= $raiz ?>index.php">Início</a>                
                
                <?php if (usuarioEstaLogado()): ?>
                    <?php if (isset($_SESSION['tipo_usuario']) && in_array($_SESSION['tipo_usuario'], ['admin', 'moderador'])): ?>
                        <a href="<?= $raiz ?>pages/cadastro_livro.php">Cadastrar Livro</a>
                    <?php endif; ?>
                    <a href="<?= $raiz ?>pages/painel.php">Meu Painel</a>
                    
                    <!-- Exibe apenas a inicial do nome estilizada em um círculo -->
                    <span title="<?= htmlspecialchars($_SESSION['nome_usuario']) ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background: #2c3e50; color: #fff; border-radius: 50%; font-weight: bold; font-size: 0.95rem;">
                        <?= $inicial_usuario ?>
                    </span>

                    <a href="<?= $src ?>php/logout.php" class="btn-logout">Sair</a>
                <?php else: ?>
                    <a href="<?= $raiz ?>pages/login.php" class="btn-login">Entrar</a>
                    <a href="<?= $raiz ?>pages/cadastro.php" class="btn-register">Cadastrar</a>
                <?php endif; ?>

                <!-- Ícone do Carrinho Minimalista com Badge Dinâmico -->
                <a href="<?= $rota_carrinho ?>" class="cart-icon-link" style="position: relative; text-decoration: none; font-size: 1.4rem;" title="Ver Carrinho">
                    🛒
                    <span id="cart-count" style="position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; font-weight: bold;">
                        <?= $total_carrinho_header ?>
                    </span>
                </a>

                <!-- Botão de Modo Escuro Integrado -->
                <button id="dark-mode-toggle" title="Alternar Tema" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; transition: transform 0.2s;">
                    🌙
                </button>

            </nav>
        </div>
    </header>
    <main class="main-container">