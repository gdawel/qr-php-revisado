<?php
include_once '../App_Code/Pesquisa.class';
include_once '../App_Code/User.class';
include_once '../App_Code/CommonFunctions.php';

$usr = Users::getCurrent();
if (!$usr->isinrole('Admin')) {
	echo "Acesso negado.";
	exit;
}

$id = getIntQueryString('id', 0, true);
if (!$id) {
	echo "Pesquisa não encontrada".
	exit;
}

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=pesquisa_$id.xls");

$pesquisas = new Pesquisas();
$resps = $pesquisas->ExportRespostasByPesquisa($id);
									
echo "<table class='List'>
				<tr>
					<th>QuestionárioId</th>
					<th>Pergunta #</th>
					<th>Pergunta</th>
					<th>Resposta</th>
					<th>Valor Resposta</th>
				</tr>";

foreach ($resps as $r) {
	echo "<tr>
					<td class='Center'>Quest #$r->questionarioid</td>
					<td class='Center'>Pergunta $r->posicao</td>
					<td>$r->texto</td>
					<td class='Center'>$r->resposta</td>
					<td class='Center'>$r->respostavalor</td>
				</tr>";
}
echo "</table>";
?>