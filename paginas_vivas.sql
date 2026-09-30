-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Tempo de geração: 30-Set-2026 às 13:33
-- Versão do servidor: 8.0.44
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `paginas_vivas`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacao_curtidas`
--

CREATE TABLE `avaliacao_curtidas` (
  `id_curtida` int NOT NULL,
  `id_avaliacao` int NOT NULL,
  `id_usuario` int NOT NULL,
  `data_curtida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `avaliacao_curtidas`
--

INSERT INTO `avaliacao_curtidas` (`id_curtida`, `id_avaliacao`, `id_usuario`, `data_curtida`) VALUES
(3, 2, 1, '2026-09-21 10:56:41');

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacao_denuncias`
--

CREATE TABLE `avaliacao_denuncias` (
  `id_denuncia` int NOT NULL,
  `id_avaliacao` int NOT NULL,
  `id_usuario` int NOT NULL,
  `motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Conteúdo inadequado',
  `data_denuncia` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `avaliacao_denuncias`
--

INSERT INTO `avaliacao_denuncias` (`id_denuncia`, `id_avaliacao`, `id_usuario`, `motivo`, `data_denuncia`) VALUES
(3, 2, 1, 'Conteúdo inadequado', '2026-09-21 11:07:15');

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id_avaliacao` int NOT NULL,
  `estrelas` tinyint NOT NULL,
  `comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_usuario` int NOT NULL,
  `id_produto` int NOT NULL,
  `data_avaliacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id_avaliacao`, `estrelas`, `comentario`, `id_usuario`, `id_produto`, `data_avaliacao`, `status`) VALUES
(2, 5, 'Muito bom!!', 1, 1, '2026-09-21 10:56:39', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `curtidas_avaliacoes`
--

CREATE TABLE `curtidas_avaliacoes` (
  `id_curtida` int NOT NULL,
  `id_avaliacao` int NOT NULL,
  `id_usuario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id_endereco` int NOT NULL,
  `id_usuario` int NOT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logradouro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `complemento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id_favorito` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_produto` int NOT NULL,
  `data_favoritado` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_favorito` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `favoritos`
--

INSERT INTO `favoritos` (`id_favorito`, `id_usuario`, `id_produto`, `data_favoritado`, `data_favorito`) VALUES
(4, 1, 1, '2026-09-21 11:07:11', '2026-09-21 11:07:11'),
(6, 3, 16, '2026-09-23 10:14:30', '2026-09-23 10:14:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id_item` int NOT NULL,
  `id_pedido` int NOT NULL,
  `id_produto` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `itens_pedido`
--

INSERT INTO `itens_pedido` (`id_item`, `id_pedido`, `id_produto`, `quantidade`, `preco_unitario`) VALUES
(1, 1, 1, 5, 19.90),
(2, 2, 1, 1, 19.90),
(3, 3, 1, 2, 19.90),
(4, 4, 1, 1, 19.90),
(5, 5, 1, 1, 19.90),
(6, 6, 1, 5, 19.90),
(7, 7, 1, 8, 19.90),
(8, 8, 1, 3, 19.90),
(9, 9, 1, 2, 19.90),
(10, 10, 12, 1, 19.90);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int NOT NULL,
  `id_usuario` int NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parcelas` int DEFAULT '1',
  `status_pedido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `data_pedido` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `valor_total`, `forma_pagamento`, `parcelas`, `status_pedido`, `data_pedido`) VALUES
(1, 1, 102.49, 'cartao', 5, 'pendente', '2026-09-11 11:35:44'),
(2, 1, 17.91, 'pix', 1, 'aprovado', '2026-09-11 11:37:11'),
(3, 1, 35.82, 'pix', 1, 'aprovado', '2026-09-11 11:38:51'),
(4, 1, 17.91, 'pix', 1, 'aprovado', '2026-09-21 08:53:27'),
(5, 1, 17.91, 'pix', 1, 'aprovado', '2026-09-21 08:56:48'),
(6, 1, 99.50, 'cartao', 3, 'pendente', '2026-09-21 08:58:09'),
(7, 1, 159.20, 'boleto', 1, 'pendente', '2026-09-21 09:19:09'),
(8, 1, 53.73, 'pix', 1, 'aprovado', '2026-09-21 10:59:18'),
(9, 1, 43.38, 'cartao', 9, 'pendente', '2026-09-21 11:07:38'),
(10, 3, 17.91, 'pix', 1, 'aprovado', '2026-09-23 10:12:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id_produto` int NOT NULL,
  `id_produto_pai` int DEFAULT NULL,
  `nome_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `autor_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `editora_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco_produto` decimal(10,2) NOT NULL,
  `paginas_produto` int NOT NULL,
  `categoria_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idioma_produto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `classificacao_indicativa_produto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lancamento_produto` date NOT NULL,
  `descricao_produto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_produto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'eBook',
  `img_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_produto` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id_produto`, `id_produto_pai`, `nome_produto`, `autor_produto`, `editora_produto`, `preco_produto`, `paginas_produto`, `categoria_produto`, `idioma_produto`, `classificacao_indicativa_produto`, `lancamento_produto`, `descricao_produto`, `tipo_produto`, `img_produto`, `quantidade_produto`) VALUES
(1, NULL, 'O Pequeno Príncipe', 'Antoine de Saint‑Exupéry', 'Principis, Rocco, Companhia das Letrinhas, Pae Editora', 19.90, 176, 'Literatura, Ficção', 'Português', 'Livre', '2015-01-01', 'O Pequeno Príncipe conta a história de um piloto que, após sofrer uma pane no deserto do Saara, conhece um menino de cabelos dourados que lhe pede para desenhar um carneiro. Aos poucos, o piloto descobre que o garoto vem do asteroide B‑612, onde vivia sozinho com uma rosa vaidosa e frágil, por quem nutria profundo afeto.', 'eBook', 'src/assets/uploads/livro_6aa4012969df7.jpg', 50),
(4, NULL, 'Minha Culpa', 'Prime', 'Mercedes Ron', 58.89, 400, 'Romance, Dramático', 'Português', '18', '2017-07-01', 'Ao se mudar para Los Angeles após o novo casamento da mãe, Noah conhece Nicholas, seu novo meio-irmão rebelde. Entre rachas e segredos, uma atração proibida e incontrolável surge entre os dois.', 'eBook', 'src/assets/uploads/livro_6ab141021cc95.jpg', 999),
(5, 4, 'Minha Culpa', 'Prime', 'Mercedes Ron', 52.90, 400, 'Romance, Dramático', 'Português', '18', '2017-07-01', 'Ao se mudar para Los Angeles após o novo casamento da mãe, Noah conhece Nicholas, seu novo meio-irmão rebelde. Entre rachas e segredos, uma atração proibida e incontrolável surge entre os dois.', 'Livro Fisico', 'src/assets/uploads/livro_6ab141021cc95.jpg', 50),
(6, 4, 'Minha Culpa', 'Prime', 'Mercedes Ron', 31.99, 400, 'Romance, Dramático', 'Português', '18', '2017-07-01', 'Ao se mudar para Los Angeles após o novo casamento da mãe, Noah conhece Nicholas, seu novo meio-irmão rebelde. Entre rachas e segredos, uma atração proibida e incontrolável surge entre os dois.', 'Audiobook', 'src/assets/uploads/livro_6ab141021cc95.jpg', 999),
(7, NULL, 'Sua Culpa', 'Prime', 'Mercedes Ron', 58.89, 400, 'Romance, Dramático', 'Português', '18', '2024-07-01', 'A paixão entre Noah e Nicholas superou o orgulho, mas novas pressões colocam o relacionamento em risco: a faculdade, os pais e os traumas do passado. Para ficarem juntos, ambos precisarão superar seus medos mais profundos antes que a relação imploda.', 'eBook', 'src/assets/uploads/livro_6ab142010945b.jpg', 999),
(8, 7, 'Sua Culpa', 'Prime', 'Mercedes Ron', 31.99, 400, 'Romance, Dramático', 'Português', '18', '2024-07-01', 'A paixão entre Noah e Nicholas superou o orgulho, mas novas pressões colocam o relacionamento em risco: a faculdade, os pais e os traumas do passado. Para ficarem juntos, ambos precisarão superar seus medos mais profundos antes que a relação imploda.', 'Audiobook', 'src/assets/uploads/livro_6ab142010945b.jpg', 999),
(9, NULL, 'Nossa Culpa', 'Prime', 'Mercedes Ron', 25.90, 416, 'Romance, Dramático', 'Português', '18', '2023-10-04', 'A relação entre Nick e Noah atravessa seu pior momento, e parece que as coisas nunca voltarão a ser como antes… Para descobrir se realmente nasceram um para o outro ou se o melhor é cada um seguir seu caminho, os dois precisarão enfrentar diversas provações. Mas será que um amor tão intenso pode mesmo ser esquecido? Como apagar lembranças que parecem tatuadas no coração?\r\n\r\nO amor, por si só, nem sempre é suficiente. E o perdão, às vezes, não basta para curar feridas antigas. Serão eles capazes de deixar o passado para trás e recomeçar?', 'eBook', 'src/assets/uploads/livro_6ab3b45ca84ec.png', 999),
(10, 9, 'Nossa Culpa', 'Prime', 'Mercedes Ron', 69.90, 416, 'Romance, Dramático', 'Português', '18', '2023-10-04', 'A relação entre Nick e Noah atravessa seu pior momento, e parece que as coisas nunca voltarão a ser como antes… Para descobrir se realmente nasceram um para o outro ou se o melhor é cada um seguir seu caminho, os dois precisarão enfrentar diversas provações. Mas será que um amor tão intenso pode mesmo ser esquecido? Como apagar lembranças que parecem tatuadas no coração?\r\n\r\nO amor, por si só, nem sempre é suficiente. E o perdão, às vezes, não basta para curar feridas antigas. Serão eles capazes de deixar o passado para trás e recomeçar?', 'Livro Fisico', 'src/assets/uploads/livro_6ab3b45ca84ec.png', 17),
(11, 9, 'Nossa Culpa', 'Prime', 'Mercedes Ron', 29.90, 416, 'Romance, Dramático', 'Português', '18', '2023-10-04', 'A relação entre Nick e Noah atravessa seu pior momento, e parece que as coisas nunca voltarão a ser como antes… Para descobrir se realmente nasceram um para o outro ou se o melhor é cada um seguir seu caminho, os dois precisarão enfrentar diversas provações. Mas será que um amor tão intenso pode mesmo ser esquecido? Como apagar lembranças que parecem tatuadas no coração?\r\n\r\nO amor, por si só, nem sempre é suficiente. E o perdão, às vezes, não basta para curar feridas antigas. Serão eles capazes de deixar o passado para trás e recomeçar?', 'Audiobook', 'src/assets/uploads/livro_6ab3b45ca84ec.png', 999),
(12, NULL, 'Sexta-feira 13', 'DarkSide Books.', 'Sean S. Cunningham', 19.90, 320, 'Horror, Terror', 'Português', '18', '1969-12-31', 'Em SEXTA-FEIRA 13 [ARQUIVOS DE CRYSTAL LAKE] você vai entender todos os processos de criação, produção e filmagem do primeiro filme, o eterno Sexta-Feira 13, de 1980. Fotos inéditas e centenas de depoimentos dos atores, membros da equipe e de fãs que também se destacaram no mundo do terror. A cada parágrafo, você vai se sentir andando pelos bastidores das filmagens. Leia o que o astro Kevin Bacon, o diretor Sean S. Cunningham, a donzela Adrienne King, mamãe Betsy Palmer e os rivais Wes Craven e Robert Englund têm a dizer sobre esse clássico. Jason permaneceu calado.\r\nDavid Grove tomou coragem para revirar os corpos empalados a machete, entre outros objetos perfurantes, e encontrou pérolas que os verdadeiros fãs não podem perder por nada. O prefácio é assinado pelo mestre Tom Savini, responsável pela maquiagem e os efeitos especiais de qualquer bom filme sanguinolento que se preze. Incluindo, claro, Sexta-Feira 13.', 'eBook', 'src/assets/uploads/livro_6ab3b71cce8cd.jpg', 999),
(13, 12, 'Sexta-feira 13', 'DarkSide Books.', 'Sean S. Cunningham', 55.90, 320, 'Horror, Terror', 'Português', '18', '1969-12-31', 'Em SEXTA-FEIRA 13 [ARQUIVOS DE CRYSTAL LAKE] você vai entender todos os processos de criação, produção e filmagem do primeiro filme, o eterno Sexta-Feira 13, de 1980. Fotos inéditas e centenas de depoimentos dos atores, membros da equipe e de fãs que também se destacaram no mundo do terror. A cada parágrafo, você vai se sentir andando pelos bastidores das filmagens. Leia o que o astro Kevin Bacon, o diretor Sean S. Cunningham, a donzela Adrienne King, mamãe Betsy Palmer e os rivais Wes Craven e Robert Englund têm a dizer sobre esse clássico. Jason permaneceu calado.\r\nDavid Grove tomou coragem para revirar os corpos empalados a machete, entre outros objetos perfurantes, e encontrou pérolas que os verdadeiros fãs não podem perder por nada. O prefácio é assinado pelo mestre Tom Savini, responsável pela maquiagem e os efeitos especiais de qualquer bom filme sanguinolento que se preze. Incluindo, claro, Sexta-Feira 13.', 'Livro Fisico', 'src/assets/uploads/livro_6ab3b71cce8cd.jpg', 20),
(14, NULL, 'Terrifier 2', 'Titan Books', 'Titan Books', 38.90, 400, 'Horror, Terror', 'Português', '18', '2024-10-08', 'Faz um ano que a pacata cidade de Miles County sobreviveu à onda de assassinatos do demente serial killer Art, o Palhaço, mas mal sabem eles que o pesadelo está prestes a recomeçar. Ressuscitado por uma entidade sinistra, Art está de volta com um apetite por morte e caos — tendo como alvos a recém-enlutada adolescente Sienna e seu irmão mais novo, Jonathan. As ruas estão prestes a escorrer sangue, e Sienna precisa, de alguma forma, sobreviver a esta noite de Halloween assustadora e descobrir como derrotar uma máquina de matar brutal e implacável vinda diretamente dos seus piores pesadelos.', 'eBook', 'src/assets/uploads/livro_6ab3ba4456442.jpg', 999),
(15, 14, 'Terrifier 2', 'Titan Books', 'Titan Books', 114.85, 400, 'Horror, Terror', 'Português', '18', '2024-10-08', 'Faz um ano que a pacata cidade de Miles County sobreviveu à onda de assassinatos do demente serial killer Art, o Palhaço, mas mal sabem eles que o pesadelo está prestes a recomeçar. Ressuscitado por uma entidade sinistra, Art está de volta com um apetite por morte e caos — tendo como alvos a recém-enlutada adolescente Sienna e seu irmão mais novo, Jonathan. As ruas estão prestes a escorrer sangue, e Sienna precisa, de alguma forma, sobreviver a esta noite de Halloween assustadora e descobrir como derrotar uma máquina de matar brutal e implacável vinda diretamente dos seus piores pesadelos.', 'Livro Fisico', 'src/assets/uploads/livro_6ab3ba4456442.jpg', 11),
(16, NULL, 'Batman', 'John Jackson Miller', 'Excelsior', 39.90, 516, 'Ação e aventura', 'Português', '14', '2024-10-24', 'Após a morte do Coringa, Batman continua sua vigília sobre uma cidade que nunca dorme. Mas o caos retorna quando uma onda de incêndios criminosos ameaça Gotham ― alimentada por seguidores do vilão e interesses obscuros de figuras poderosas, como o inescrupuloso Max Shreck.\r\n\r\nEnquanto sobreviventes do gás Smylex lotam os hospitais, Bruce Wayne se une a um cientista misterioso para conter a nova crise. Porém, a sombra do Coringa persiste. Pesadelos, dúvidas e pistas inquietantes colocam em xeque tudo o que Bruce acreditava saber sobre o confronto final na catedral.\r\n\r\nSerá possível que o príncipe palhaço do crime tenha sobrevivido?\r\n\r\nEm meio ao caos, o maior detetive do mundo enfrentará um inimigo que talvez já não esteja entre os vivos ― mas cuja risada ainda ecoa por Gotham.', 'eBook', 'src/assets/uploads/livro_6ab3be0e7b92e.jpg', 999),
(17, 16, 'Batman', 'John Jackson Miller', 'Excelsior', 89.90, 516, 'Ação e aventura', 'Português', '14', '2024-10-24', 'Após a morte do Coringa, Batman continua sua vigília sobre uma cidade que nunca dorme. Mas o caos retorna quando uma onda de incêndios criminosos ameaça Gotham ― alimentada por seguidores do vilão e interesses obscuros de figuras poderosas, como o inescrupuloso Max Shreck.\r\n\r\nEnquanto sobreviventes do gás Smylex lotam os hospitais, Bruce Wayne se une a um cientista misterioso para conter a nova crise. Porém, a sombra do Coringa persiste. Pesadelos, dúvidas e pistas inquietantes colocam em xeque tudo o que Bruce acreditava saber sobre o confronto final na catedral.\r\n\r\nSerá possível que o príncipe palhaço do crime tenha sobrevivido?\r\n\r\nEm meio ao caos, o maior detetive do mundo enfrentará um inimigo que talvez já não esteja entre os vivos ― mas cuja risada ainda ecoa por Gotham.', 'Livro Fisico', 'src/assets/uploads/livro_6ab3be0e7b92e.jpg', 8);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nome` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nascimento` date NOT NULL,
  `senha_segura` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_usuario` enum('admin','usuario','moderador') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  `cpf` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `pergunta_seguranca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `resposta_seguranca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `nascimento`, `senha_segura`, `tipo_usuario`, `cpf`, `telefone`, `data_criacao`, `pergunta_seguranca`, `resposta_seguranca`) VALUES
(1, 'Lucas dos Santos Camilo', 'lucas.6161@df.senac.br', '1995-11-28', '$2y$10$0w8FlVOKByxe.Nw8n0K.rulNpCgJMupFbN4dWXFo5LwoUChOwus.m', 'admin', '78965432110', '61999999999', '2026-09-11 10:03:54', 'Qual é o nome do seu primeiro pet?', '$2y$10$D9S7HMtjbHcoIDawIptTNeYMck38H6QagWX.6NvxZ9ya2GCcBnr6S'),
(2, 'Antônia Coelho', 'antonia.coelho23@gmail.com', '2011-05-19', '$2y$10$5OwMgfhXRVydC.M3bhr8YOh.KxiAAmUQc2Yof3/NJK8Cshsck9V3a', 'usuario', '98765432110', '99999999999', '2026-09-11 11:50:13', 'Qual o seu livro ou filme favorito?', '$2y$10$Pxs2K6kWoO20Zi9GuHVkgO1b6Pg2Ykc/fJ30BssDhslAnFQW9HvEa'),
(3, 'Bianca Paulista', 'biancapaulista@gmail.com', '2007-07-26', '$2y$10$spOqhK5agwejKjK3IIiKc.vAaPwCxZHSiTGqRu7FAjbdNGbyRBQ7K', 'admin', '', '', '2026-09-21 11:17:52', 'Qual o seu livro ou filme favorito?', '$2y$10$lpg2seiZoUvG3Ude9DSyXO0v8CMgvnnNumO3Q99zwvM5Qbbi0lJxm');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `avaliacao_curtidas`
--
ALTER TABLE `avaliacao_curtidas`
  ADD PRIMARY KEY (`id_curtida`),
  ADD KEY `id_avaliacao` (`id_avaliacao`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `avaliacao_denuncias`
--
ALTER TABLE `avaliacao_denuncias`
  ADD PRIMARY KEY (`id_denuncia`),
  ADD KEY `id_avaliacao` (`id_avaliacao`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id_avaliacao`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices para tabela `curtidas_avaliacoes`
--
ALTER TABLE `curtidas_avaliacoes`
  ADD PRIMARY KEY (`id_curtida`),
  ADD UNIQUE KEY `avaliacao_usuario_unico` (`id_avaliacao`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id_endereco`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id_favorito`),
  ADD UNIQUE KEY `usuario_produto_unico` (`id_usuario`,`id_produto`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices para tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Índices para tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produto`),
  ADD KEY `fk_produtos_pai` (`id_produto_pai`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `UNIQUE_email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacao_curtidas`
--
ALTER TABLE `avaliacao_curtidas`
  MODIFY `id_curtida` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `avaliacao_denuncias`
--
ALTER TABLE `avaliacao_denuncias`
  MODIFY `id_denuncia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id_avaliacao` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `curtidas_avaliacoes`
--
ALTER TABLE `curtidas_avaliacoes`
  MODIFY `id_curtida` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id_endereco` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id_favorito` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id_item` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `avaliacao_curtidas`
--
ALTER TABLE `avaliacao_curtidas`
  ADD CONSTRAINT `avaliacao_curtidas_ibfk_1` FOREIGN KEY (`id_avaliacao`) REFERENCES `avaliacoes` (`id_avaliacao`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_curtidas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `avaliacao_denuncias`
--
ALTER TABLE `avaliacao_denuncias`
  ADD CONSTRAINT `avaliacao_denuncias_ibfk_1` FOREIGN KEY (`id_avaliacao`) REFERENCES `avaliacoes` (`id_avaliacao`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_denuncias_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_avaliacoes_produtos` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avaliacoes_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Limitadores para a tabela `curtidas_avaliacoes`
--
ALTER TABLE `curtidas_avaliacoes`
  ADD CONSTRAINT `fk_curtidas_avaliacoes` FOREIGN KEY (`id_avaliacao`) REFERENCES `avaliacoes` (`id_avaliacao`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_curtidas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Limitadores para a tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `enderecos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `fk_favoritos_produtos` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favoritos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produtos_pai` FOREIGN KEY (`id_produto_pai`) REFERENCES `produtos` (`id_produto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
