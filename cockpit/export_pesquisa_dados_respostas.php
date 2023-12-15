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
	echo "Pesquisa n�o encontrada".
	exit;
}

$filter = new Filter();
$filter->add('q.StatusId', '=', '3');
$quests = $pesquisa->getQuestionarios($filter);
if (!$quests) {
	echo "Nenhum question�rio conclu�do para esta pesquisa.".
	exit;
}

//Start
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Pesquisa_$id.xls");
									
echo "<h1>".utf8_decode($pesquisa->titulo)."</h1>";
									
echo "<table border='1'>
				<tr>
					<th>C�digo</th>
					<th>Pessoa</th>
					<th>1a. Doen�a</th>
					<th>Idade</th>
					<th>Tempo</th>
					<th>Consequencia</th>
					<th>2a. Doen�a</th>
					<th>Idade</th>
					<th>Tempo</th>
					<th>Consequencia</th>
					<th>Sexo</th>
					<th>UF Nascimento</th>
					<th>Cidade</th>
					<th>UF</th>
					<th>Forma��o</th>
					<th>Atividade</th>
					<th>Escolaridade</th>
					<th>Estado Civil</th>
					<th>Religi�o</th>
					<th>Outro Idioma</th>";
				//for($i = 1; $i<=count($quests[0]->perguntas); $i++) {echo "<th>Item $i</th>\n";}
					
echo "				</tr>";

foreach ($quests as $q) {
	echo "<tr>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->id); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['PessoasDificuldade']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['SituacaoGrave']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Quando']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Duracao']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['SituacaoGraveComentario']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['SituacaoGrave2']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Quando2']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Duracao2']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['SituacaoGrave2Comentario']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Sexo']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['UFNascimento']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Cidade']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['UF']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['FormacaoProfissional']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['AtividadeProfissional']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Escolaridade']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['EstadoCivil']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Religiao']); echo "</td>
				<td style=\"text-align:center;\">"; echo utf8_decode($q->infos['Idioma']); echo "</td>";
				//for($i = 1; $i<=count($q->perguntas); $i++) {echo "<td style=\"text-align:center;\">".$q->perguntas[$i]->respostavalor."</td>\n";}
	echo "
			</tr>";
}
echo "</table>";

echo utf8_decode("<p>Relat�rio extra�do em ".date('d/m/Y H:m').".</p>");

?>