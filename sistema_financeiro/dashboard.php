
<?php

require_once "conexao.php";
require_once "topo.php";

$id = usuarioAtual();

// SOMAR RECEITAS ATE HOJE
$sql = "SELECT SUM(valor) FROM receitas
        WHERE usuario_id = ? AND data <= CURDATE()";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalReceitas);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($totalReceitas == NULL) {
    $totalReceitas = 0;
}

// SOMAR DESPESAS ATE HOJE
$sql = "SELECT SUM(valor) FROM despesas
        WHERE usuario_id = ? AND data <= CURDATE()";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalDespesas);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($totalDespesas == NULL) {
    $totalDespesas = 0;
}

// SOMAR SALDOS INICIAIS DAS CONTAS
$sql = "SELECT SUM(saldo) FROM contas WHERE usuario_id = ?";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $saldoInicial);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($saldoInicial == NULL) {
    $saldoInicial = 0;
}

// CALCULAR SALDO
$saldo = $saldoInicial + $totalReceitas - $totalDespesas;

?>

<h1>Olá, <?php echo protegerTexto($_SESSION["usuario_nome"]); ?>!</h1>

<p class="subtitulo">
    Resumo das suas movimentações financeiras.
</p>

<div class="cards">

    <div class="card">
        <span>Total de receitas</span>
        <strong>
            R$ <?php echo number_format($totalReceitas, 2, ",", "."); ?>
        </strong>
    </div>

    <div class="card">
        <span>Total de despesas</span>
        <strong>
            R$ <?php echo number_format($totalDespesas, 2, ",", "."); ?>
        </strong>
    </div>

    <div class="card">
        <span>Saldo atual</span>
        <strong>
            R$ <?php echo number_format($saldo, 2, ",", "."); ?>
        </strong>
    </div>

</div>

<div class="caixa">

    <h2>Bem-vindo ao sistema!</h2>

    <p>
        Utilize o menu para cadastrar receitas, despesas,
        categorias e contas.
    </p>

</div>

<?php require_once "rodape.php"; ?>
