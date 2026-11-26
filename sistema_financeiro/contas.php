
<?php
require_once "conexao.php";
require_once "funcoes.php";

verificarLogin();

$id = usuarioAtual();
$erro = "";

// cadastrar sua conta //
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $tipo = $_POST["tipo"];
    $saldo = str_replace(",", ".", $_POST["saldo"]);

    if ($nome == "" || $tipo == "" || !is_numeric($saldo)) {

        $erro = "Preencha todos os campos corretamente.";

    } else {

        $sql = "INSERT INTO contas (usuario_id, nome, tipo, saldo, data_cadastro)
                VALUES (?, ?, ?, ?, CURDATE())";

        $stmt = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param($stmt, "issd", $id, $nome, $tipo, $saldo);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: contas.php?sucesso=1");
            exit;
        } else {
            $erro = "Não foi possível cadastrar a conta.";
        }
    }
}

// excluir a conta //
if (isset($_GET["excluir"])) {

    $idConta = (int) $_GET["excluir"];

    $sql = "DELETE FROM contas WHERE id = ? AND usuario_id = ?";

    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $idConta, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: contas.php?excluido=1");
        exit;
    } else {
        header("Location: contas.php?erro=1");
        exit;
    }
}

// buscar contas //
$sql = "SELECT * FROM contas WHERE usuario_id = ? ORDER BY id DESC";

$stmt = mysqli_prepare($conexao, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

require_once "topo.php";
?>

<h1>Minhas contas</h1>

<p class="subtitulo">
    Cadastre e organize suas contas financeiras.
</p>

<?php if ($erro != "") { ?>
    <p class="erro"><?php echo protegerTexto($erro); ?></p>
<?php } ?>

<?php if (isset($_GET["sucesso"])) { ?>
    <p class="sucesso">Conta cadastrada com sucesso!</p>
<?php } ?>

<?php if (isset($_GET["excluido"])) { ?>
    <p class="sucesso">Conta excluída com sucesso!</p>
<?php } ?>

<?php if (isset($_GET["erro"])) { ?>
    <p class="erro">Não foi possível excluir. Essa conta pode estar vinculada a movimentações.</p>
<?php } ?>

<div class="caixa">

    <h2>Adicionar conta</h2>

    <form method="POST">

        <label>Nome da conta</label>
        <input type="text" name="nome" placeholder="Ex: Minha conta" required>

        <label>Tipo de conta</label>
        <select name="tipo" required>
            <option value="">Selecione</option>
            <option value="Conta corrente">Conta corrente</option>
            <option value="Conta poupança">Conta poupança</option>
            <option value="Dinheiro">Dinheiro</option>
            <option value="Carteira digital">Carteira digital</option>
        </select>

        <label>Saldo inicial</label>
        <input type="number" name="saldo" step="0.01" min="0" placeholder="0.00" required>

        <button type="submit">Cadastrar conta</button>

    </form>

</div>

<div class="caixa">

    <h2>Contas cadastradas</h2>

    <table>

        <tr>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Saldo</th>
            <th>Ação</th>
        </tr>

        <?php while ($conta = mysqli_fetch_assoc($resultado)) { ?>

            <tr>
                <td><?php echo protegerTexto($conta["nome"]); ?></td>

                <td><?php echo protegerTexto($conta["tipo"]); ?></td>

                <td>
                    R$ <?php echo number_format($conta["saldo"], 2, ",", "."); ?>
                </td>

                <td>
                    <a href="contas.php?excluir=<?php echo $conta["id"]; ?>"
                       onclick="return confirm('Deseja realmente excluir esta conta?')">
                        Excluir
                    </a>
                </td>
            </tr>

        <?php } ?>

    </table>

</div>

<?php require_once "rodape.php"; ?>
