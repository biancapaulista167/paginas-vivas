<?php
require_once __DIR__ . '/../src/includes/header.php';

$mensagem = '';
$tipo_mensagem = '';

// No bloco de verificação do GET 'msg':
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cadastro_sucesso') {
        $mensagem = 'Cadastro realizado com sucesso! Faça login para continuar.';
        $tipo_mensagem = 'sucesso';
    } elseif ($_GET['msg'] === 'precisa_logar') {
        $mensagem = 'Você precisa estar logado para acessar esta página.';
        $tipo_mensagem = 'erro';
    } elseif ($_GET['msg'] === 'senha_redefinida') {
        $mensagem = 'Senha redefinida com sucesso! Faça login com a nova senha.';
        $tipo_mensagem = 'sucesso';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagem = 'Preencha todos os campos!';
        $tipo_mensagem = 'erro';
    } else {
        $stmt = $conexao->prepare("SELECT id_usuario, nome, senha_segura, tipo_usuario FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            if (password_verify($senha, $usuario['senha_segura'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

                header('Location: ../index.php');
                exit;
            } else {
                $mensagem = 'Senha incorreta.';
                $tipo_mensagem = 'erro';
            }
        } else {
            $mensagem = 'E-mail não cadastrado.';
            $tipo_mensagem = 'erro';
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Entrar no Páginas Vivas ✨</h2>
        <p>Acesse seu acervo de e-books</p>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha">
                <a href="recuperar_senha.php" class="forgot-password-link">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-auth">Entrar</button>
        </form>

        <p class="auth-footer">Ainda não tem conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>