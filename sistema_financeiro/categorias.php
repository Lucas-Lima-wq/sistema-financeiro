<?php
require_once "conexao.php";
require_once "topo.php";

$id = usuarioAtual();
$mensagem = "";

// excluir categoria //
if (isset($_GET["excluir"])) {

    $registro = (int) $_GET["excluir"];

    $sql = "DELETE FROM categorias WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($stmt, "ii", $registro, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: categorias.php?msg=excluido");
        exit;
    } else {
        $mensagem = "Não foi possível excluir. A categoria pode estar em uso.";
    }
}

// cadastrar categoria //
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $tipo = $_POST["tipo"];

    if ($nome != "" && in_array($tipo, ["receita", "despesa"])) {

        $sql = "INSERT INTO categorias
        (usuario_id, nome, tipo, data_cadastro)
        VALUES (?, ?, ?, CURDATE())";

        $stmt = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param($stmt, "iss", $id, $nome, $tipo);

        mysqli_stmt_execute($stmt);

        header("Location: categorias.php?msg=salvo");
        exit;

    } else {
        $mensagem = "Preencha todos os campos.";
    }
}

// listar categorias //
$sql = "SELECT id, nome, tipo FROM categorias
WHERE usuario_id = ? ORDER BY nome";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$lista = mysqli_stmt_get_result($stmt);
?>

<h1>Categorias</h1>
<p class="subtitulo">Organize suas movimentações por tipo.</p>

<?php if ($mensagem != "") { ?>
    <p class="erro"><?= protegerTexto($mensagem) ?></p>
<?php } ?>

<?php if (isset($_GET["msg"])) { ?>
    <p class="sucesso">Operação concluída com sucesso.</p>
<?php } ?>

<div class="caixa">

    <h2>Adicionar categoria</h2>

    <form method="POST" class="form-grid">

        <div>
            <label>Nome da categoria</label>
            <input type="text" name="nome" required maxlength="100">
        </div>

        <div>
            <label>Tipo</label>
            <select name="tipo" required>
                <option value="">Selecione</option>
                <option value="receita">Receita</option>
                <option value="despesa">Despesa</option>
            </select>
        </div>

        <div class="alinha">
            <button type="submit">Adicionar categoria</button>
        </div>

    </form>

</div>

<div class="caixa">

    <h2>Categorias cadastradas</h2>

    <table>

        <tr>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Ação</th>
        </tr>

        <?php while ($item = mysqli_fetch_assoc($lista)) { ?>

            <tr>
                <td><?= protegerTexto($item["nome"]) ?></td>
                <td><?= ucfirst($item["tipo"]) ?></td>

                <td>
                    <a class="link-excluir"
                    href="categorias.php?excluir=<?= $item["id"] ?>"
                    onclick="return confirm('Deseja excluir esta categoria?')">
                    Excluir
                    </a>
                </td>
            </tr>

        <?php } ?>

    </table>

</div>

<?php require "rodape.php"; ?>