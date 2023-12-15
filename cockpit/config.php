<?php
$pageTitle = 'SOBRARE Cockpit | Opções e configurações';
$pesquisaid = '';
include_once '../App_Code/User.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Gestor,Admin,Associado', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {
	global $msg, $msg_class;
	$action = getPost('action');

	switch ($action) {
		case 'changepassword':
			$users = new Users();
			if ($users->changePassword(getpost('newpassword'), getPost('confirmnewpassword'))) {
				$msg = 'Senha alterada com sucesso';		
				$msg_class = 'Info';		
			} else {
				$msg = "Erro ao alterar senha <br />$users->error";
				$msg_class = 'Error';
			}
			RenderDefault();
			break;
		
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg, $msg_class;
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_class);
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	echo "<h1>Opções do Cockpit</h1>
				
			<h3>Alterar Senha</h3>
			<form name='frm' id='frm' action='config.php' method='POST'>
				<input type='hidden' name='action' value='changepassword' /> 
			
				<table class='Form'>
					<tr>
						<td class='Field'>Nova Senha</td>
						<td><input type='password' size='20' name='newpassword' id='newpassword'></td>
					</tr>
					<tr>
						<td class='Field'>Confirme Nova Senha</td>
						<td><input type='password' size='20' name='confirmnewpassword' id='confirmnewpassword'></td>
					</tr>
				</table>
				
				<div class='Buttons'>";				
					Button::RenderSubmit(null, 'Alterar Senha', 'Alterar Senha', 'change', 'positive');
					Button::Render(null, 'Voltar', 'index.php', 'Voltar para a home','undo'); echo "
				</div>
			</form>
			
			<div id='frmPwd_errorloc' class='Error'></div>
		
			<script type='text/javascript'>
				var vld  = new Validator('frm');
				
				vld.addValidation('newpassword', 'req','Nova senha é obrigatória');
				vld.addValidation('confirmnewpassword', 'req','Confirme a nova senha');
				
				vld.EnableOnPageErrorDisplaySingleBox();
				vld.EnableMsgsTogether();
			</script>";
}
?>