<?php
// Dados da conexao
$servidor = "localhost";
$usuario = "root";
$senha = "senac";
$banco = "paginas_vivas";
$porta = 3307;

// Cria conexao
$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);
// Verifica se ocorreu algum erro   ERRNO
if ($conexao->connect_errno){
    die("Erro ao conectar ao banco de dados: " . $conexao->connect_errno);
}
// Define UTF-8 como padrao
$conexao->set_charset("utf8mb4");
?> 