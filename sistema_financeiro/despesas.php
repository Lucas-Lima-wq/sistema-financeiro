
<?php
date_default_timezone_set("America/Sao_Paulo");

require_once "conexao.php";
require_once "topo.php";

$id = usuarioAtual();
$mensagem = "";

// EXCLUIR DESPESA
if (isset($_GET["excluir"])) {
    $registro = (int) $_GET["excluir"];

    $sql = "DELETE FROM despesas WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $registro, $id);
    mysqli_stmt_execute($stmt);

    header("Location: despesas.php?msg=excluido");
    exit;
}

// CADASTRAR DESPESA
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conta = (int) $_POST["conta_id"];
    $categoria = (int) $_POST["categoria_id"];
    $descricao = trim($_POST["descricao"]);
    $valor = (float) $_POST["valor"];
    $data = $_POST["data"];
    $recorrente = isset($_POST["recorrente"]);

    // VERIFICAR CONTA
    $sql = "SELECT id FROM contas WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $conta, $id);
    mysqli_stmt_execute($stmt);

    $contaOk = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;

    // VERIFICAR CATEGORIA
    $tipo = "despesa";

    $sql = "SELECT id FROM categorias
            WHERE id = ? AND usuario_id = ? AND tipo = ?";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $categoria, $id, $tipo);
    mysqli_stmt_execute($stmt);

    $categoriaOk = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;

    if ($contaOk && $categoriaOk && $valor > 0 && $data != "") {

        if ($recorrente) {

            $dia = (int) date("d", strtotime($data));

            $sql = "INSERT INTO recorrencias
            (usuario_id, conta_id, categoria_id, tipo, descricao,
             valor, dia_mes, data_inicio)
            VALUES (?, ?, ?, 'despesa', ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conexao, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiisdis",
                $id,
                $conta,
                $categoria,
                $descricao,
                $valor,
                $dia,
                $data
            );

        } else {

            $sql = "INSERT INTO despesas
            (usuario_id, conta_id, categoria_id, descricao, valor, data)
            VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conexao, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiisds",
                $id,
                $conta,
                $categoria,
                $descricao,
                $valor,
                $data
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            header("Location: despesas.php?msg=salvo");
            exit;
        }
    }

    $mensagem = "Confira os dados e tente novamente.";
}

// BUSCAR CONTAS
$sql = "SELECT id, nome FROM contas
        WHERE usuario_id = ?
        ORDER BY nome";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$contas = mysqli_stmt_get_result($stmt);

// BUSCAR CATEGORIAS
$tipo = "despesa";

$sql = "SELECT id, nome FROM categorias
        WHERE usuario_id = ? AND tipo = ?
        ORDER BY nome";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "is", $id, $tipo);
mysqli_stmt_execute($stmt);

$categorias = mysqli_stmt_get_result($stmt);

// HISTÓRICO
$sql = "SELECT d.id, d.descricao, d.valor, d.data,
        c.nome AS conta, g.nome AS categoria
        FROM despesas d
        JOIN contas c ON d.conta_id = c.id
        JOIN categorias g ON d.categoria_id = g.id
        WHERE d.usuario_id = ?
        ORDER BY d.data DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$lista = mysqli_stmt_get_result($stmt);
?>

<h1>Despesas</h1>

<p class="subtitulo">
    Cadastre e consulte suas despesas.
</p>

<?php if ($mensagem != "") { ?>
    <p class="erro">
        <?php echo protegerTexto($mensagem); ?>
    </p>
<?php } ?>

<?php if (isset($_GET["msg"])) { ?>
    <p class="sucesso">
        Operação realizada com sucesso.
    </p>
<?php } ?>

<div class="caixa">

    <h2>Adicionar despesa</h2>

    <form method="POST" class="form-grid">

        <div>
            <label>Conta</label>

            <select name="conta_id" required>
                <option value="">Selecione</option>

                <?php while ($c = mysqli_fetch_assoc($contas)) { ?>
                    <option value="<?php echo $c["id"]; ?>">
                        <?php echo protegerTexto($c["nome"]); ?>
                    </option>
                <?php } ?>

            </select>
        </div>

        <div>
            <label>Categoria</label>

            <select name="categoria_id" required>
                <option value="">Selecione</option>

                <?php while ($c = mysqli_fetch_assoc($categorias)) { ?>
                    <option value="<?php echo $c["id"]; ?>">
                        <?php echo protegerTexto($c["nome"]); ?>
                    </option>
                <?php } ?>

            </select>
        </div>

        <div>
            <label>Descrição</label>
            <input type="text" name="descricao" maxlength="255">
        </div>

        <div>
            <label>Valor</label>
            <input type="number" name="valor" min="0.01" step="0.01" required>
        </div>

        <div>
            <label>Data</label>
            <input type="date" name="data" required>
        </div>

        <div class="campo-recorrente">
            <input type="checkbox" id="recorrente" name="recorrente" value="1">
            <label for="recorrente">Repetir mensalmente</label>
        </div>

        <div class="alinha">
            <button type="submit">Salvar despesa</button>
        </div>

    </form>

</div>

<div class="caixa">

    <h2>Histórico de despesas</h2>

    <table>

        <tr>
            <th>Descrição</th>
            <th>Categoria</th>
            <th>Conta</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Ação</th>
        </tr>

        <?php while ($item = mysqli_fetch_assoc($lista)) { ?>

            <tr>

                <td>
                    <?php echo protegerTexto($item["descricao"]); ?>
                </td>

                <td>
                    <?php echo protegerTexto($item["categoria"]); ?>
                </td>

                <td>
                    <?php echo protegerTexto($item["conta"]); ?>
                </td>

                <td>
                    R$ <?php echo number_format($item["valor"], 2, ",", "."); ?>
                </td>

                <td>
                    <?php echo date("d/m/Y", strtotime($item["data"])); ?>
                </td>

                <td>
                    <a class="link-excluir"
                       href="despesas.php?excluir=<?php echo $item["id"]; ?>"
                       onclick="return confirm('Deseja excluir esta despesa?')">
                        Excluir
                    </a>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>

<?php require_once "rodape.php"; ?>
