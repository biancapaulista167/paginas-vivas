<?php
session_start();
include('../config/conexao.php');

// Trava de seguranca: apenas usuarios logados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recebe e sanitiza os dados gerais do formulario
    $nome_produto       = trim($_POST['nome_produto'] ?? '');
    $autor_produto      = trim($_POST['autor_produto'] ?? '');
    $editora_produto    = trim($_POST['editora_produto'] ?? '');
    $paginas_produto    = (int) ($_POST['paginas_produto'] ?? 0);
    
    // Processamento de categorias
    $cats = $_POST['categorias'] ?? [];
    $nova_cat = trim($_POST['nova_categoria'] ?? '');
    if (!empty($nova_cat)) {
        $cats[] = $nova_cat;
    }
    $categoria_produto  = implode(", ", array_unique(array_filter($cats)));

    $idioma_produto     = trim($_POST['idioma_produto'] ?? '');
    $classificacao      = trim($_POST['classificacao_indicativa_produto'] ?? '');
    $lancamento_produto = $_POST['lancamento_produto'] ?? '';
    $descricao_produto  = trim($_POST['descricao_produto'] ?? '');

    // Validacao dos campos obrigatorios
    if (
        empty($nome_produto) || empty($autor_produto) || empty($editora_produto) || 
        $paginas_produto <= 0 || empty($categoria_produto) || 
        empty($idioma_produto) || empty($classificacao) || empty($lancamento_produto) || 
        empty($descricao_produto)
    ) {
        die("Preencha todos os campos obrigatorios gerais corretamente.");
    }

    // Coleta e valida os formatos marcados
    $formatosParaCadastrar = [];

    if (isset($_POST['tem_ebook']) && $_POST['tem_ebook'] == '1') {
        $preco = (float) ($_POST['preco_ebook'] ?? 0);
        if ($preco > 0) {
            $formatosParaCadastrar[] = [
                'tipo' => 'eBook',
                'preco' => $preco,
                'quantidade' => 999
            ];
        }
    }

    if (isset($_POST['tem_fisico']) && $_POST['tem_fisico'] == '1') {
        $preco = (float) ($_POST['preco_fisico'] ?? 0);
        $qtd = (int) ($_POST['qtd_fisico'] ?? 0);
        if ($preco > 0) {
            $formatosParaCadastrar[] = [
                'tipo' => 'Livro Fisico',
                'preco' => $preco,
                'quantidade' => $qtd
            ];
        }
    }

    if (isset($_POST['tem_audio']) && $_POST['tem_audio'] == '1') {
        $preco = (float) ($_POST['preco_audio'] ?? 0);
        if ($preco > 0) {
            $formatosParaCadastrar[] = [
                'tipo' => 'Audiobook',
                'preco' => $preco,
                'quantidade' => 999
            ];
        }
    }

    if (empty($formatosParaCadastrar)) {
        die("Selecione pelo menos um formato e informe um preco valido.");
    }

    // 2. Processamento do upload da capa para src/assets/uploads/
    if (!isset($_FILES['img_produto']) || $_FILES['img_produto']['error'] !== UPLOAD_ERR_OK) {
        die("Erro no envio da imagem da capa.");
    }

    $arquivo = $_FILES['img_produto'];
    $tamanhoMaximo = 5 * 1024 * 1024; // 5 MB

    if ($arquivo['size'] > $tamanhoMaximo) {
        die("A imagem deve ter no maximo 5 MB.");
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $extensoesPermitidas)) {
        die("Formato nao permitido. Envie JPG, JPEG, PNG ou WEBP.");
    }

    // Salva na pasta uploads dentro de assets dentro de src
    $novoNome = uniqid("livro_") . "." . $extensao;
    $diretorioDestino = __DIR__ . '/../assets/uploads/';

    if (!is_dir($diretorioDestino)) {
        mkdir($diretorioDestino, 0755, true);
    }

    $caminhoFisico = $diretorioDestino . $novoNome;
    $caminhoBanco = "src/assets/uploads/" . $novoNome;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFisico)) {
        die("Falha ao salvar a imagem no servidor.");
    }

    // 3. Gravacao no banco de dados com MySQLi Prepared Statements
    $id_produto_pai = null;

    foreach ($formatosParaCadastrar as $index => $fmt) {
        $tipo_produto       = $fmt['tipo'];
        $preco_produto      = $fmt['preco'];
        $quantidade_produto = $fmt['quantidade'];

        if ($index === 0) {
            // Registro Principal (Pai)
            $sql = "INSERT INTO produtos (
                        id_produto_pai, nome_produto, autor_produto, editora_produto, 
                        preco_produto, paginas_produto, categoria_produto, idioma_produto, 
                        classificacao_indicativa_produto, lancamento_produto, descricao_produto, 
                        tipo_produto, img_produto, quantidade_produto
                    ) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);
            if (!$stmt) {
                die("Erro na preparacao do SQL: " . $conexao->error);
            }

            $stmt->bind_param(
                "sssdisssssssi",
                $nome_produto,
                $autor_produto,
                $editora_produto,
                $preco_produto,
                $paginas_produto,
                $categoria_produto,
                $idioma_produto,
                $classificacao,
                $lancamento_produto,
                $descricao_produto,
                $tipo_produto,
                $caminhoBanco,
                $quantidade_produto
            );
        } else {
            // Variacoes secundarias (ligadas pelo id_produto_pai)
            $sql = "INSERT INTO produtos (
                        id_produto_pai, nome_produto, autor_produto, editora_produto, 
                        preco_produto, paginas_produto, categoria_produto, idioma_produto, 
                        classificacao_indicativa_produto, lancamento_produto, descricao_produto, 
                        tipo_produto, img_produto, quantidade_produto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);
            if (!$stmt) {
                die("Erro na preparacao do SQL: " . $conexao->error);
            }

            $stmt->bind_param(
                "isssdisssssssi",
                $id_produto_pai,
                $nome_produto,
                $autor_produto,
                $editora_produto,
                $preco_produto,
                $paginas_produto,
                $categoria_produto,
                $idioma_produto,
                $classificacao,
                $lancamento_produto,
                $descricao_produto,
                $tipo_produto,
                $caminhoBanco,
                $quantidade_produto
            );
        }

        if ($stmt->execute()) {
            if ($index === 0) {
                $id_produto_pai = $conexao->insert_id;
            }
            $stmt->close();
        } else {
            die("Erro ao cadastrar o formato " . $tipo_produto . ": " . $stmt->error);
        }
    }

    $conexao->close();
    header("Location: ../../index.php?sucesso=livro_cadastrado");
    exit();
}
?>