
<?php
ob_start();

date_default_timezone_set("America/Sao_Paulo");

require_once "conexao.php";
require_once "funcoes.php";

verificarLogin();

// Processar receitas e despesas recorrentes
processarRecorrencias($conexao, usuarioAtual());
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Sistema Financeiro</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>

<header class="topo">

    <div class="largura topo-conteudo">

        <strong>Meu Financeiro</strong>

        <nav>
            <a href="dashboard.php">Início</a>
            <a href="receitas.php">Receitas</a>
            <a href="despesas.php">Despesas</a>
            <a href="categorias.php">Categorias</a>
            <a href="contas.php">Contas</a>
            <a href="logout.php">Sair</a>
        </nav>

    </div>

</header>

<main class="largura conteudo">
