[README.md](https://github.com/user-attachments/files/32959050/README.md)
# 🎬 CatalogoFlix

Sistema web de catálogo de filmes e venda de ingressos e alimentos de cinema, feito com PHP e MySQL. O visitante navega pelos filmes e sessões, monta um carrinho com ingressos e alimentos e finaliza o pedido depois de entrar na conta. O administrador gerencia filmes, sessões e alimentos em um painel próprio.

## Funcionalidades

**Para o cliente**
- Cadastro e login de usuários, com senhas armazenadas com hash (`password_hash` / `password_verify`).
- Catálogo de filmes com sinopse, imagem e preço.
- Sessões por filme, com data, horário e sala.
- Carrinho de compras guardado na sessão, com ingressos e alimentos (pipoca, refrigerante etc.).
- Finalização do pedido, gravado no banco com os itens comprados.

**Para o administrador**
- Cadastro, edição e exclusão de filmes, sessões e alimentos.
- Páginas restritas por nível de acesso (`admin` ou `user`).

> O pagamento é **simulado**: a tela de pagamento é apenas visual e não há integração com nenhum meio de pagamento real.

## Tecnologias

- **Front-end:** HTML, CSS, JavaScript, Bootstrap e Font Awesome
- **Back-end:** PHP com PDO
- **Banco de dados:** MySQL

## Como rodar localmente

1. Instale o [XAMPP](https://www.apachefriends.org/) (ou outro ambiente com Apache, PHP e MySQL) e inicie o Apache e o MySQL.
2. Copie a pasta `Projeto Catalogo` para a pasta `htdocs` do XAMPP.
3. No phpMyAdmin, crie um banco chamado `db_catalogoflix` e importe o arquivo `db_catalogoflix.sql`.
4. Confira os dados de conexão em `banco.php`. O padrão é usuário `root` e senha vazia.
5. Acesse `http://localhost/Projeto%20Catalogo/` no navegador.

**Para criar um administrador:** cadastre um usuário normalmente e, no phpMyAdmin, altere a coluna `role` desse usuário na tabela `tb_user` para `admin`.

## Banco de dados

| Tabela | Conteúdo |
|---|---|
| `tb_user` | Usuários, com e-mail, senha em hash e nível de acesso |
| `tb_filmes` | Filmes: título, gênero, sinopse, ano, imagem e preço |
| `tb_sessoes` | Sessões de cada filme: data, horário, sala e preço |
| `tb_alimentos` | Alimentos e bebidas à venda |
| `tb_pedidos` | Pedidos feitos pelos usuários |
| `tb_itens_pedido` | Itens de cada pedido (ingressos e alimentos) |

## Estrutura do projeto

```  
Projeto Catalogo/
├── banco.php               # conexão com o banco (PDO)
├── login.php, logout.php   # autenticação
├── cad_usuario.php         # cadastro de usuários
├── filmes.php              # catálogo de filmes
├── sessao.php              # sessões de um filme
├── alimentos.php           # escolha de alimentos
├── carrinho.php            # carrinho de compras
├── finalizar_compra.php    # grava o pedido no banco
├── menu_admin.php          # painel do administrador
├── cad_*.php, editar_*.php, excluir_*.php   # CRUD de filmes, sessões e alimentos
├── style.css, script.js    # estilos e scripts do front-end
└── img/                    # imagens do projeto
```

## Sobre o projeto

Projeto pessoal desenvolvido por **Bruno Misorelli Madeira**, estudante de Análise e Desenvolvimento de Sistemas na Fatec Campinas. O front-end (HTML, CSS e JavaScript) foi escrito por mim, e o back-end em PHP foi desenvolvido com o apoio de ferramentas de IA, como parte do meu aprendizado.
