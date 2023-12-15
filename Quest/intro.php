<?php
ob_start();

$pageTitle = 'SOBRARE - Sociedade Brasileira de Resiliência | Quest_Resiliência';
include_once '../Controls/events.ctrl.php';
include_once '../App_Code/Pesquisa.class.php';
include_once '../App_Code/User.class.php';

checkAuth();
include_once '../MasterPageQuest.htm.php';

function checkAuth() {
	$usr = Users::getCurrent();
	
	if ((!$usr) || (!$usr->isinrole('Respondente'))) {
		header("Location: login.php");
		ob_flush();
	}
}

function Router() {
	$usr = Users::getCurrent();
	
	if ($usr->nome) echo "<h1>Bem-vindo, ".htmlspecialchars($usr->nome)."!</h1>"; 
	else echo "<h1>Bem-vindo!</h1>";
	
	//Recuperar pesquisa para texto da introducao
	$pesquisas = new Pesquisas();
	$pesquisa = $pesquisas->itemByQuestionarioId($usr->questid);
	
	echo "<h2>Orientações sobre a escala “Quest_Resiliência”</h2>";

	echo "<p>".str_replace(chr(13), '</p><p>', $pesquisa->pacote->questintrotext).'</p>';
	echo "<h3>Exemplo de como responder</h3>
				
			<div class='Pergunta'>
				<p>Eu costumo pensar em como está minha saúde.</p>
				<div class='Opcoes'>
					<input type='radio' name='resposta' value='1' /><span>Raras vezes</span>
					<input type='radio' name='resposta' value='2' /><span>Poucas vezes</span>
					<input type='radio' name='resposta' value='3' /><span>Muitas vezes</span>
					<input type='radio' name='resposta' value='4' /><span>Quase Sempre</span>
				</div>
			</div>
			
			<div class='Right'>
				<a href='responder.php'><img src='../Images/button-start.jpg' alt='Começar o Quest_Resiliência' /></a>
			</div>";

}

?>