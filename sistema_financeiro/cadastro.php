
<?php
require_once "conexao.php";
session_start();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);
    $senha = $_POST["senha"];

    if ($nome == "" || $email == "" || $senha == "") {

        $erro = "Preencha todos os campos obrigatorios.";

    } else {

        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $idEncontrado);

        if (mysqli_stmt_fetch($stmt)) {

            $erro = "Este e-mail ja esta cadastrado.";

        } else {

            mysqli_stmt_close($stmt);

            $sql = "INSERT INTO usuarios
                    (nome, email, senha, telefone, data_cadastro)
                    VALUES (?, ?, ?, ?, CURDATE())";

            $stmt = mysqli_prepare($conexao, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $nome,
                $email,
                $senha,
                $telefone
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: login.php?cadastro=sucesso");
                exit;

            } else {

                $erro = "Erro ao realizar o cadastro.";
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body class="fundo">

<div class="caixa acesso">

    <h1>Criar conta</h1>

    <p class="subtitulo">
        Cadastre-se para organizar suas finanças.
    </p>

    <?php if ($erro != "") { ?>
        <p class="erro">
            <?php echo htmlspecialchars($erro); ?>
        </p>
    <?php } ?>

    <form method="POST" action="cadastro.php">

        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Telefone</label>
        <input type="text" name="telefone">

        <label>Senha</label>
        <input type="password" name="senha" required>

        <button type="submit">Cadastrar</button>

    </form>

    <p class="centro">
        Já tem uma conta?
        <a href="login.php">Entrar</a>
    </p>

</div>

</body>
</html>
