<?php
include_once '../App_Code/Pesquisa.class.php';
include_once '../App_Code/User.class.php';
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

//Se relatório de indices ou de categorias
$tipo_relatorio = getIntQueryString('tipo', 1, true);
$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($id);
if (!$pesquisa) {
	$this->error = 'Pesquisa não encontrada';
	return false;
}
if ($pesquisa->isAccessDenied()) {
	$this->error = 'Acesso negado a esta pesquisa';
	return false;
}
//$quests = $pesquisas->QuestListByPesquisaId($id);
$quests = $pesquisa->getQuestionariosByStatus(QUESTIONARIO_STATUS_CONCLUIDO);

//Header
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=pesquisa_resultados_$id.xls");


echo "<h1>$pesquisa->titulo</h1>";
echo "<h2>Resultados dos Questionários</h2>";

if ($quests) {
	echo "<table class='List' border='1'>
					<tr>
						<th>Núm. do Sujeito</th>";
	foreach ($quests[0]->fatores as $fator) {
		echo "<th>$fator->nome</th>";
	}
	echo "</tr>";
	
	foreach ($quests as $quest) {
		if ($quest->infos['StatusId'] == 3) {
	 		echo "<tr>
			 				<td>$quest->id</td>";
				if ($tipo_relatorio == 1) {
					foreach ($quest->fatores as $fator) echo "<td>$fator->valor</td>";					
				} else {
					foreach ($quest->fatores as $fator) echo "<td>$fator->classificacao</td>";
				} 
				
			echo "</tr>";
		}
	}
	echo "</table>";
}
?>