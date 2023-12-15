<?php
$pageTitle = 'SOBRARE Cockpit Admin | Editar Usuário';
include_once '../App_Code/User.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {	
	global $msg;

	$action = getPost('a');
	
	switch ($action) {		
		case 'save':		
			if (!isPageRefresh()) saveUser();
			RenderDefault();
			break;	
		
		case 'Ativar':		
			if (!isPageRefresh()) ativarUser("1");
			RenderDefault();
			break;
		
		case 'Desativar':		
			if (!isPageRefresh()) ativarUser("0");
			RenderDefault();
			break;
				
		default:
			RenderDefault();		
	}
}

function RenderDefault() {
	checkPageRefreshSessionVar();
	
	global $msg, $msg_style;
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Usuários', 'users.php', 'Voltar para Administração dos usuários', 'undo');
				Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home'); echo "
			</div>";

	//Fetch data
	$id = getIntQueryString('id', 0, true);
	$users = new Users();
	$usr = $users->item($id);
	if (!$usr) {
		$usr = new User();
		$usr->userid = 0;
	}

	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
	
	if ($usr->userid) echo "<h1>Editar Usuário</h1>"; else echo "<h1>Novo Usuário</h1>";
					
	echo "<form action='user.php?id=$usr->userid' method='post' name='frm' id='frm'>
				<input type='hidden' name='a' id='a' value='save' />
				<input type='hidden' name='userid' id='userid' value='$usr->userid' />
				<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
		
		<table class='Form' style=\"width:500px;\">
			<tr>
				<td colspan='2'><span class='Red'>*</span> = Item obrigatório</td>
			</tr>
				
			<tr class='Field'>
				<td colspan='3'>Nome <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='3'>";
					if (!$usr->userid) 
						echo "<input type='text' size='80' maxlength='100' name='nome' id='nome' value='".getPost('nome', $usr->nome, true)."' />";
					else
						echo htmlspecialchars($usr->nome . ' (' . (($usr->ativo) ? 'Ativo':'Inativo') . ')');
	echo "	</td>
			</tr>
					
			<tr class='Field'>
				<td colspan='3'>E-mail <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='3'>";
					if (!$usr->userid) 
						echo "<input type='text' size='80' name='email' id='email' value='".getPost('email', $usr->email, true)."' />";
					else
						echo htmlspecialchars($usr->email);
	echo "	</td>
			</tr>";
			
			
			if (!$usr->userid) { 
			echo " 
				<tr class='Field'>
					<td>Senha Inicial <span class='Red'>*</span></td>
					<td>Sexo <span class='Red'>*</span></td>
					<td>Status <span class='Red'>*</span></td> 
				</tr>
				<tr>
					<td><input type='text' size='20' name='password' id='password' value='".getPost('password', null, true)."' /></td>
					<td>"; ListItemPicker::Render('sexo', 'sexo', getPost('sexo', 1)); echo "</td>
					<td>"; ListItemPicker::Render('status', 'users_status', getPost('status', '0'));	echo "</td>
				</tr>
			";
			}
	echo "<tr class='Field'>
				<td>Funções <span class='Red'>*</span></td>
				<td></td>
			</tr>
			<tr>
				<td>"; ListItemPicker::RenderCheckboxList('funcoes', 'roles', (($usr->roles) ? implode(",", $usr->roles) : null), false); echo "</td>
				<td></td>
			</tr>
		</table>
		<div class='Buttons'>";
			Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
			if ($usr->userid) {
				if ($usr->ativo) 
					Button::Render(null, 'Desativar Usuário', '#', 'Desativa o usuário, não permitindo seu login na SOBRARE', 'warning', true, 'negative', "submitAtivarUsuario('Desativar');");
				else
					Button::Render(null, 'Ativar Usuário', '#', 'Ativa o usuário, habilitando seu login na SOBRARE', 'change', true, 'positive', "submitAtivarUsuario('Ativar');");
			}
			Button::Render(null, 'Voltar', 'users.php', 'Voltar à página anterior', 'undo'); 
			echo "
		</div>		
		</form>
		
		<div id='frm_errorloc' class='Error'></div>
		
		<script language='JavaScript' type='text/javascript'>
			var vld  = new Validator('frm');";
			
			if (!$usr->userid) echo "
				vld.addValidation('nome', 'req', 'Nome obrigatório');
				vld.addValidation('email', 'req', 'E-mail obrigatório');			
				vld.addValidation('password', 'req', 'Senha Inicial obrigatória');";
				
	echo "		
			vld.EnableOnPageErrorDisplaySingleBox();
			vld.EnableMsgsTogether();
			
			function submitAtivarUsuario(actionType) {
				var frmAction = document.getElementById('frm');
				var ctrlAction = document.getElementById('a');
				
				if (frmAction) {
					ctrlAction.value = actionType;
					frmAction.submit();	
				}
				return false;				
			}
		</script>		
	";
}

function saveUser() {
	updatePageRefreshChecker();
	
	global $msg, $msg_style;
	
	$users = new Users();	
	$userid = getPost('userid', null);
	$roles = (isset($_POST['funcoes']) ? $_POST['funcoes'] : null);
	//print_r($roles);
	
	//Incluir usuario, se add
	if (!$userid) {
		$usr = new User();
		$usr->nome = getPost('nome', null);
		$usr->email = getPost('email', null);
		$usr->password = getPost('password', null);
		$usr->sexoid = getPost('sexo', 1);
		$usr->ativo = getPost('status', 0);
		$usr->roles = $roles;
		
		if ($users->add($usr)) {
			$msg = 'Usuário incluído com sucesso';
			$msg_style = 'Info';
			header('Location: users.php');
			return true;
		} else {
			$msg = ($users->error) ? $users->error : 'Erro ao salvar usuário';
			$msg_style = 'Error';
			return false;
		}	
		
	} else {
		if ($users->setroles($userid, $roles)) {
			$msg = 'Usuário alterado com sucesso';
			$msg_style = 'Info';
			return true;
		} else {
			$msg = 'Erro ao salvar permissões do usuário. ' + $users->error;
			$msg_style = 'Error';
			return false;
		}
	}
}

function ativarUser($type) {
	updatePageRefreshChecker();
	
	global $msg, $msg_style;
	
	$users = new Users();	
	$userid = getPost('userid', null);
	
	if ($users->ativar($userid, $type)) {
		$msg = 'Usuário ativado/desativado com sucesso. '.$users->error;
		$msg_style = 'Info';
		return true;
	} else {
		$msg = 'Erro ao ativar/desativar usuário. '.$users->error;
		$msg_style = 'Error';
		return false;
	}
}
?>