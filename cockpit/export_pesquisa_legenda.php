<?php
include_once '../App_Code/User.class.php';
include_once '../Controls/list.ctrl.php';
include_once '../App_Code/CommonFunctions.php';


$usr = Users::getCurrent();
if (!$usr->isinrole('Admin')) {
	echo "Acesso negado.";
	exit;
}

function getListValues($type) {
	$sql = new SqlHelper();
	$sql->command = ListItemPicker::getSqlCommand($type);
	$sql->execute();
	
	return $sql->dataset();
}

function renderListValues($title, $ds) {
	echo "<h2>".convertIsoUtf($title)."</h2>";
	
	echo "<table border='1'>
				<tr><th>Valor</th><th>Texto</th></tr>";
				
	foreach ($ds as $r) {
		
		echo "<tr><td style=\"text-align:center;\">".convertIsoUtf($r['value'])."</td><td style=\"text-align:center;\">".convertIsoUtf($r['text'])."</td></tr>";
	}
		echo "</table>";
	
}

//Start
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Pesquisa_Legenda.xls");
									
echo "<h1>Legenda</h1>";
								
$ds = getListValues('pessoas_dificuldades');
if ($ds) renderListValues('Pessoas que mais te ajudaram a superar dificuldades', $ds);

$ds = getListValues('situacao_qdo');
if ($ds) renderListValues('Com que idade você estava quando aconteceu?', $ds);

$ds = getListValues('situacao_duracao');
if ($ds) renderListValues('Quanto tempo durou aproximadamente?', $ds);

$ds = getListValues('sexo');
if ($ds) renderListValues('Sexo', $ds);

$ds = getListValues('uf_legenda');
if ($ds) renderListValues('UF', $ds);

$ds = getListValues('escolaridade');
if ($ds) renderListValues('Escolaridade', $ds);

$ds = getListValues('estadocivil');
if ($ds) renderListValues('Estado Civil', $ds);

$ds = getListValues('religiao');
if ($ds) renderListValues('Religião', $ds);

//Idioma
$ds = null;
$ds[0]['value'] = 0;
$ds[0]['text'] = 'Não';
$ds[1]['value'] = 1;
$ds[1]['text'] = 'Sim';
if ($ds) renderListValues('Fala outro idioma?', $ds);

?>