<?php
require_once __DIR__ . '/../src/includes/header.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nascimento = $_POST['nascimento'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $pergunta_seguranca = trim($_POST['pergunta_seguranca'] ?? '');
    $resposta_seguranca = trim($_POST['resposta_seguranca'] ?? '');

    if (empty($nome) || empty($email) || empty($nascimento) || empty($senha) || empty($pergunta_seguranca) || empty($resposta_seguranca)) {
        $mensagem = 'Preencha todos os campos obrigatórios!';
        $tipo_mensagem = 'erro';
    } else {
        $stmt_check = $conexao->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $mensagem = 'Este e-mail já está cadastrado!';
            $tipo_mensagem = 'erro';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            // Salva a resposta em minúsculo e criptografada para evitar erros de digitação de maiúsculas/minúsculas na recuperação
            $resposta_hash = password_hash(mb_strtolower($resposta_seguranca), PASSWORD_DEFAULT);

            $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, nascimento, senha_segura, cpf, telefone, pergunta_seguranca, resposta_seguranca) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $nome, $email, $nascimento, $senha_hash, $cpf, $telefone, $pergunta_seguranca, $resposta_hash);

            if ($stmt->execute()) {
                header('Location: login.php?msg=cadastro_sucesso');
                exit;
            } else {
                $mensagem = 'Erro ao cadastrar usuário. Tente novamente.';
                $tipo_mensagem = 'erro';
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Criar sua Conta 🌸</h2>
        <p>Junte-se à comunidade Páginas Vivas!</p>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php" class="auth-form">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required placeholder="Seu nome completo">
            </div>

            <div class="form-group">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="nascimento">Data de Nascimento *</label>
                <input type="date" id="nascimento" name="nascimento" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" required placeholder="Crie uma senha segura">
            </div>

            <div class="form-group">
                <label for="pergunta_seguranca">Pergunta de Segurança *</label>
                <select id="pergunta_seguranca" name="pergunta_seguranca" required>
                    <option value="">Selecione uma pergunta...</option>
                    <option value="Qual é o nome do seu primeiro pet?">Qual é o nome do seu primeiro pet?</option>
                    <option value="Qual é o nome da sua cidade natal?">Qual é o nome da sua cidade natal?</option>
                    <option value="Qual o seu livro ou filme favorito?">Qual o seu livro ou filme favorito?</option>
                    <option value="Qual o nome da sua primeira escola?">Qual o nome da sua primeira escola?</option>
                </select>
            </div>

            <div class="form-group">
                <label for="resposta_seguranca">Resposta de Segurança *</label>
                <input type="text" id="resposta_seguranca" name="resposta_seguranca" required placeholder="Sua resposta">
            </div>

            <div class="form-group">
                <label for="cpf">CPF (opcional)</label>
                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00">
            </div>

            <div class="form-group">
                <label for="telefone">Telefone (opcional)</label>
                <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000">
            </div>

            <button type="submit" class="btn-auth">Cadastrar</button>
        </form>

        <p class="auth-footer">Já possui uma conta? <a href="login.php">Faça login</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>