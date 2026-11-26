
<?php

if (!isset($_SESSION)) {
    session_start();
}

// VERIFICAR LOGIN
function verificarLogin() {

    if (!isset($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }

}

// PROTEGER TEXTO
function protegerTexto($texto) {

    if ($texto == NULL) {
        $texto = "";
    }

    return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");

}

// USUARIO ATUAL
function usuarioAtual() {

    return (int) $_SESSION["usuario_id"];

}

// PROCESSAR MOVIMENTACOES RECORRENTES
function processarRecorrencias($conexao, $id) {

    $mesAtual = date("Y-m");
    $hoje = date("Y-m-d");

    $sql = "SELECT * FROM recorrencias
            WHERE usuario_id = ?
            AND ativa = 1
            AND data_inicio <= ?";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "is", $id, $hoje);
    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    while ($item = mysqli_fetch_assoc($resultado)) {

        $dia = (int) $item["dia_mes"];
        $mesInicio = date("Y-m", strtotime($item["data_inicio"]));

        if ($item["ultimo_mes"] == NULL) {
            $mesProcessar = $mesInicio;
        } else {
            $mesProcessar = date(
                "Y-m",
                strtotime($item["ultimo_mes"] . "-01 +1 month")
            );
        }

        while ($mesProcessar <= $mesAtual) {

            $ultimoDia = (int) date(
                "t",
                strtotime($mesProcessar . "-01")
            );

            $diaLancamento = $dia;

            if ($diaLancamento > $ultimoDia) {
                $diaLancamento = $ultimoDia;
            }

            $dataLancamento = $mesProcessar . "-" .
                str_pad($diaLancamento, 2, "0", STR_PAD_LEFT);

            if ($dataLancamento > $hoje) {
                break;
            }

            if ($dataLancamento >= $item["data_inicio"]) {

                if ($item["tipo"] == "receita") {
                    $tabela = "receitas";
                } else {
                    $tabela = "despesas";
                }

                $sqlInsert = "INSERT INTO $tabela
                (usuario_id, conta_id, categoria_id, descricao, valor, data)
                VALUES (?, ?, ?, ?, ?, ?)";

                $stmtInsert = mysqli_prepare($conexao, $sqlInsert);

                mysqli_stmt_bind_param(
                    $stmtInsert,
                    "iiisds",
                    $id,
                    $item["conta_id"],
                    $item["categoria_id"],
                    $item["descricao"],
                    $item["valor"],
                    $dataLancamento
                );

                if (!mysqli_stmt_execute($stmtInsert)) {
                    mysqli_stmt_close($stmtInsert);
                    break;
                }

                mysqli_stmt_close($stmtInsert);
            }

            $sqlUpdate = "UPDATE recorrencias
                          SET ultimo_mes = ?
                          WHERE id = ? AND usuario_id = ?";

            $stmtUpdate = mysqli_prepare($conexao, $sqlUpdate);

            mysqli_stmt_bind_param(
                $stmtUpdate,
                "sii",
                $mesProcessar,
                $item["id"],
                $id
            );

            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);

            $mesProcessar = date(
                "Y-m",
                strtotime($mesProcessar . "-01 +1 month")
            );
        }
    }

    mysqli_stmt_close($stmt);

}

?>
