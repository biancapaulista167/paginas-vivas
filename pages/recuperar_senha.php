<?php
require_once __DIR__ . '/../src/includes/header.php';

$mensagem = '';
$tipo_mensagem = '';
$etapa = 1;
$email_informado = '';
$pergunta_usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $email_informado = trim($_POST['email'] ?? '');

    if ($acao === 'buscar_email') {
        if (empty($email_informado)) {
            $mensagem = 'Informe o seu e-mail.';
            $tipo_mensagem = 'erro';
        } else {
            $stmt = $conexao->prepare("SELECT pergunta_seguranca FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email_informado);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($user = $res->fetch_assoc()) {
                $pergunta_usuario = $user['pergunta_seguranca'];
                $etapa = 2;
            } else {
                $mensagem = 'E-mail não encontrado no sistema.';
                $tipo_mensagem = 'erro';
            }
        }
    } elseif ($acao === 'validar_resposta') {
        $resposta = trim($_POST['resposta_seguranca'] ?? '');
        $pergunta_usuario = $_POST['pergunta_seguranca'] ?? '';

        if (empty($resposta)) {
            $mensagem = 'Preencha a resposta de segurança.';
            $tipo_mensagem = 'erro';
            $etapa = 2;
        } else {
            $stmt = $conexao->prepare("SELECT resposta_seguranca FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email_informado);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($user = $res->fetch_assoc()) {
                if (password_verify(mb_strtolower($resposta), $user['resposta_seguranca'])) {
                    $etapa = 3; // Resposta correta: libera os campos de nova senha
                } else {
                    $mensagem = 'Resposta de segurança incorreta.';
                    $tipo_mensagem = 'erro';
                    $etapa = 2;
                }
            } else {
                $mensagem = 'Erro ao processar a solicitação.';
                $tipo_mensagem = 'erro';
            }
        }
    } elseif ($acao === 'redefinir_senha') {
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (empty($nova_senha) || empty($confirmar_senha)) {
            $mensagem = 'Preencha ambos os campos de senha.';
            $tipo_mensagem = 'erro';
            $etapa = 3;
        } elseif ($nova_senha !== $confirmar_senha) {
            $mensagem = 'As senhas não coincidem!';
            $tipo_mensagem = 'erro';
            $etapa = 3;
        } else {
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("UPDATE usuarios SET senha_segura = ? WHERE email = ?");
            $stmt->bind_param("ss", $nova_senha_hash, $email_informado);

            if ($stmt->execute()) {
                header('Location: login.php?msg=senha_redefinida');
                exit;
            } else {
                $mensagem = 'Erro ao atualizar a senha. Tente novamente.';
                $tipo_mensagem = 'erro';
                $etapa = 3;
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Recuperar Senha 🔑</h2>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <!-- ETAPA 1: DIGITAR O E-MAIL -->
        <?php if ($etapa === 1): ?>
            <p>Digite seu e-mail para encontrar sua conta</p>
            <form method="POST" action="recuperar_senha.php" class="auth-form">
                <input type="hidden" name="acao" value="buscar_email">

                <div class="form-group">
                    <label for="email">E-mail Cadastrado</label>
                    <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
                </div>

                <button type="submit" class="btn-auth">Buscar E-mail</button>
            </form>

        <!-- ETAPA 2: RESPONDER A PERGUNTA DE SEGURANÇA -->
        <?php elseif ($etapa === 2): ?>
            <p>Responda à pergunta de segurança para continuar</p>
            <form method="POST" action="recuperar_senha.php" class="auth-form">
                <input type="hidden" name="acao" value="validar_resposta">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email_informado) ?>">
                <input type="hidden" name="pergunta_seguranca" value="<?= htmlspecialchars($pergunta_usuario) ?>">

                <div class="form-group">
                    <label>Pergunta de Segurança</label>
                    <input type="text" value="<?= htmlspecialchars($pergunta_usuario) ?>" disabled readonly>
                </div>

                <div class="form-group">
                    <label for="resposta_seguranca">Sua Resposta</label>
                    <input type="text" id="resposta_seguranca" name="resposta_seguranca" required placeholder="Digite sua resposta">
                </div>

                <button type="submit" class="btn-auth">Validar Resposta</button>
            </form>

        <!-- ETAPA 3: DEFINIR E CONFIRMAR A NOVA SENHA -->
        <?php elseif ($etapa === 3): ?>
            <p>Resposta confirmada! Digite sua nova senha</p>
            <form method="POST" action="recuperar_senha.php" class="auth-form">
                <input type="hidden" name="acao" value="redefinir_senha">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email_informado) ?>">

                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required placeholder="Digite a nova senha">
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Confirme a nova senha">
                </div>

                <button type="submit" class="btn-auth">Salvar Nova Senha</button>
            </form>
        <?php endif; ?>

        <p class="auth-footer"><a href="login.php">Voltar para o Login</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>