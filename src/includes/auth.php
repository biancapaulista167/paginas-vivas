<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioEstaLogado(): bool {
    return isset($_SESSION['id_usuario']);
}

function exigirLogin(): void {
    if (!usuarioEstaLogado()) {
        header('Location: login.php?msg=precisa_logar');
        exit;
    }
}