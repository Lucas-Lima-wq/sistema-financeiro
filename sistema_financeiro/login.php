
<?php
require_once "conexao.php";
session_start();

$erro = "";

$emailSalvo = "";

if (isset($_COOKIE["email_login"])) {
    $emailSalvo = $_COOKIE["email_login"];
}

$email = $emailSalvo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ?";

    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $id,
        $nome,
        $senhaBanco
    );

    if (mysqli_stmt_fetch($stmt) && $senha == $senhaBanco) {

        $_SESSION["usuario_id"] = $id;
        $_SESSION["usuario_nome"] = $nome;

        setcookie(
            "email_login",
            $email,
            time() + (30 * 24 * 60 * 60),
            "/"
        );

        mysqli_stmt_close($stmt);

        header("Location: dashboard.php");
        exit;

    } else {

        $erro = "E-mail ou senha incorretos.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body class="fundo">

<div class="caixa acesso">

    <h1>Controle Financeiro</h1>

    <p class="subtitulo">
        Entre para acessar sua conta.
    </p>

    <?php if (isset($_GET["cadastro"])) { ?>
        <p class="sucesso">
            Cadastro realizado com sucesso!
        </p>
    <?php } ?>

    <?php if ($erro != "") { ?>
        <p class="erro">
            <?php echo htmlspecialchars($erro); ?>
        </p>
    <?php } ?>

    <form method="POST" action="login.php">

        <label>E-mail</label>
        <input
            type="email"
            name="email"
            value="<?php echo htmlspecialchars($email); ?>"
            required
        >

        <label>Senha</label>
        <input
            type="password"
            name="senha"
            required
        >

        <button type="submit">Entrar</button>

    </form>

    <p class="centro">
        Ainda não tem conta?
        <a href="cadastro.php">Cadastre-se</a>
    </p>

</div>

</body>
</html>
