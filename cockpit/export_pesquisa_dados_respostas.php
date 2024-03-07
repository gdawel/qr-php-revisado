<?php
include_once '../App_Code/Pesquisa.class.php';
include_once '../App_Code/User.class.php';
include_once '../App_Code/CommonFunctions.php';

$usr = Users::getCurrent();
if ((!$usr) || (!$usr->isinrole('Admin'))) {
	echo "Acesso negado.";
	exit;
}

$pesquisas = new Pesquisas();
$id = getIntQueryString('id', 0, true);
$pesquisa = $pesquisas->item($id);
if (!$pesquisa) {
	echo "Pesquisa não encontrada".
	exit;
}

$filter = new Filter();
$filter->add('q.StatusId', '=', '3');
$quests = $pesquisa->getQuestionarios($filter);
if (!$quests) {
	echo "Nenhum questionário concluído para esta pesquisa.".
	exit;
}

//Start
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Pesquisa_$id.xls");
									
echo "<h1>" . convertIsoUtf($pesquisa->titulo) . "</h1>";
									
echo "<table border='1'>
				<tr>
					<th>" . convertIsoUtf('Código') . "</th>
					<th>Pessoa</th>
					<th>" . convertIsoUtf('1a. Doença') . "</th>
					<th>Idade</th>
					<th>Tempo</th>
					<th>" . convertIsoUtf('Consequência') . "</th>
					<th>" . convertIsoUtf('2a. Doença') . "</th>
					<th>Idade</th>
					<th>Tempo</th>
					<th>" . convertIsoUtf('Consequência') . "</th>
					<th>Sexo</th>
					<th>UF Nascimento</th>
					<th>Cidade</th>
					<th>UF</th>
					<th>" . convertIsoUtf('Formação') . "</th>
					<th>Atividade</th>
					<th>Escolaridade</th>
					<th>Estado Civil</th>
					<th>" . convertIsoUtf('Religião') . "</th>
					<th>Outro Idioma</th>";
				//for($i = 1; $i<=count($quests[0]->perguntas); $i++) {echo "<th>Item $i</th>\n";}
					
echo "				</tr>";

foreach ($quests as $q) {
	echo "<tr>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->id); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['PessoasDificuldade']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['SituacaoGrave']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Quando']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Duracao']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['SituacaoGraveComentario']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['SituacaoGrave2']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Quando2']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Duracao2']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['SituacaoGrave2Comentario']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Sexo']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['UFNascimento']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Cidade']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['UF']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['FormacaoProfissional']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['AtividadeProfissional']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Escolaridade']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['EstadoCivil']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Religiao']); echo "</td>
				<td style=\"text-align:center;\">"; echo convertIsoUtf($q->infos['Idioma']); echo "</td>";
				//for($i = 1; $i<=count($q->perguntas); $i++) {echo "<td style=\"text-align:center;\">".$q->perguntas[$i]->respostavalor."</td>\n";}
	echo "
			</tr>";
}
echo "</table>";

echo convertIsoUtf("<p>Relatório extraído em ".date('d/m/Y H:m').".</p>");

?>