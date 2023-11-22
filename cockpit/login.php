<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
@session_start();
ob_start();

$pageTitle = 'SOBRARE - Sociedade Brasileira de Resiliência | Acesso do Gestor';
$mnuServiços = 'active';
include_once '../App_Code/CommonFunctions.php';
include_once '../App_Code/User.class';
include_once '../Controls/button.ctrl.php';
include_once '../MasterPageQuest.htm.php';

// A função Router() abaixo é chamada pelo MasterPageQuest.htm.php - Nota Dawel: 20/10/2023
function Router() {	
	global $msg;
	
	$action = getPost('action');
	$result = true;
	if ($action == '1') $result = DoLogin();

	
	$form_type = getQueryString('type', '1');	
	if ($form_type == '1') 
		echo "<h1>Acesso do Gestor</h1>";
	else
		echo "<h1>Acesso do Associado</h1>";
	
	echo "
				<div class='grid_4 alpha '>
					<h2>Acesse aqui</h2>
					
					<div id='frmLogin_errorloc' class='Error'>";		
						if (!$result) {echo $msg;}
					echo "</div>
					
					<form action='login.php?type=$form_type' method='post' name='frmLogin' id='frmLogin'>
						<input type='hidden' name='action' value='1' />
						<table class='Form'>
							<tr class='Field'>
								<td>E-mail</td>
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
						<p>
							<a href='../esqueciminhasenha.php' title='Clique para recuperar sua senha'><small>Esqueci minha senha</small></a>
						</p>		
					</form>
	
					<script language='JavaScript' type='text/javascript'>
						var vld  = new Validator('frmLogin');
						
						vld.addValidation('user','email', 'E-mail inválido.');
						vld.addValidation('user','req','E-mail é obrigatório.');
						
						vld.addValidation('password','alphanumeric_space');
						vld.addValidation('password','req','Senha é obrigatória.');
						
						vld.EnableOnPageErrorDisplaySingleBox();
						vld.EnableMsgsTogether();
						
						window.onload = function() {document.getElementById('user').focus()}; 
					</script>
				</div>
				
				<div class=grid_3 omega'>
					<h2>Não possui acesso?</h2>";
					
					if ($form_type == '1')
						echo "<p>Clique <a href='../contato.php'>aqui</a> e solicite mais informações de como utilizar uma pesquisa com o Quest_Resiliência.</p>";
					else
						echo "<p>Clique <a href='../associar.php'>aqui</a> e sabia como se associar a SOBRARE.</p>";
	echo "				
				</div>
	";
}

function DoLogin() {
	global $msg;
	
	$user_mng = new Users();
	
	if ($user_mng->login($_POST['user'], $_POST['password'], 1)) {
		header("Location: index.php");
		ob_flush();
		return true;
	} else {
		$msg = $user_mng->error;
		return false;
	}
}

function RouterSub() {
	echo '<div class="padding_0">
					<h2>Orientações</h2>
					<p></p>
				</div>';
}
?>