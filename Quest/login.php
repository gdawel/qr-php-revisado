<?php
ob_start();

$pageTitle = 'SOBRARE - Sociedade Brasileira de Resiliência | Acesso Quest_Resiliência';
include_once '../Controls/events.ctrl.php';
include_once '../Controls/button.ctrl.php';
include_once '../App_Code/User.class';
include_once '../MasterPageQuest.htm.php';

$msg = '';

function Router() {
	global $msg;
	
	$action = getPost('action', null);
	switch ($action) {
		case '1':
			$result = DoLogin();
			break;
		
		default:
			$result = true;
	} 

	echo "<h1>Acesso Quest_Resiliência</h1>";
	echo "<h2>Primeiro passo para responder o Quest_Resiliência</h2>
	
		<div id='frmLogin_errorloc' class='Error'>";		
			if (!$result) {echo $msg;}
		echo "</div>
		
		<form action='login.php' method='post' name='frmLogin' id='frmLogin'>
			<input type='hidden' name='action' value='1' />
			<table class='Form'>
				<tr class='Field'>
					<td>Código</td>
				</tr>
				<tr>
					<td><input type='text' style='width:150px;' name='user' id='user' /></td>
				</tr>
			
				<tr class='Field'>
					<td>Senha</td>
				</tr>
				<tr>
					<td><input type='password' style='width:150px;' name='password' id='password' /></td>
				</tr>				
			</table>
			
			<div class='Buttons'>";
				Button::RenderSubmit(null, 'Acessar', 'Acesse e responda ao Quest', 'done', 'positive');
				Button::Render(null, 'Voltar', '../index.php', 'Voltar à página inicial da SOBRARE', 'undo'); 
				echo "
			</div>		
			<hr class='clear' />
		</form>
		
		<script language='JavaScript' type='text/javascript'>
			var vld  = new Validator('frmLogin');
			
			vld.addValidation('user','alphanumeric_space');
			vld.addValidation('user','req','Usuário é obrigatório.');
			
			vld.addValidation('password','alphanumeric_space');
			vld.addValidation('password','req','Senha é obrigatória.');
			
			vld.EnableOnPageErrorDisplaySingleBox();
			vld.EnableMsgsTogether();
			
			window.onload = function() {document.getElementById('user').focus()}; 
		</script>
	"; 
	
	/*
	echo "<br />
		<h3>Não possui acesso?</h3>
		<p>Conheça aqui os <a href='/sobrare/servicos.php'>serviços</a> do <strong>Quest_Resiliência</strong> que você pode adquirir.";
	*/
}

function DoLogin() {
	global $msg;
	
	$user_mng = new Users();
	
	if ($user_mng->login($_POST['user'], $_POST['password'], 2)) {
		header("Location: intro.php");
		ob_flush();
		return true;
	} else {
		$msg = $user_mng->error;
		return false;
	}
}

function RouterSub() {
	echo '<div class="padding_0">';		
		EventControls::NextEvents();
	echo '</div>';
}

?>