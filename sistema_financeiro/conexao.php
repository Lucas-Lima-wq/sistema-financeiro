<?php
$servidor = "localhost";
$usuario = "root";
$senha = "usbw";
$banco = "sistema_financeiro";

$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro ao estabelecer a conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");
?>