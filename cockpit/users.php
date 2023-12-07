<?php
$pageTitle = 'SOBRARE Cockpit | Administração de usuários';
$pesquisaid = '';
include_once '../App_Code/User.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';
include_once '../Controls/pagination.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {
	global $msg, $msg_class;
	$action = getPost('action');

	switch ($action) {
		case '':
			RenderDefault();
			break;
		
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg, $msg_class, $totalrows;
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_class);

	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Novo Usuário', 'user.php', 'Incluir novo usuário', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home'); echo "
			</div>";
				
	echo "<h1>Administração de Usuários</h1>";
	
	//Filter
	$filter = new Filter();
	$s_user_nome = $filter->addFromPost('u.Nome', 'LIKE','s_user_nome');
	
	$s_user_status = getPost('s_user_status', '1');  
	if ($s_user_status != -1) $filter->add('u.Ativo', '=', $s_user_status);	
	 
	$s_user_funcao = $filter->addFromPost('u.UserId', 'IN', 's_user_funcao', "(SELECT ur.UserId FROM usersinroles ur WHERE ur.Rolename = '%s')");
	$page = getIntQueryString('page', 1, true);
	$pagesize = 25;
	
	$users = new Users();
	// $lst = $users->items($page, $pagesize, 'nome', $totalrows, $filter);	
	$lst = $users->items($page, $pagesize, 'nome', $totalrows, $filter);	
	if ($totalrows) $countmsg = "($totalrows itens encontrados)"; else $countmsg = '';
	
	
	echo "<fieldset>
				<legend>Filtros <span class='FieldsetMsg'>$countmsg</span></legend>
				<form id='frm' name='frm' method='post' action='users.php'>
					<input type='hidden' name='a' id='a' value='0' />
					<input type='hidden' name='userid' id='userid' value='0' />
				
					<table class='Form'>
						<tr class='Field'>
							<td>Nome</td>
							<td>Função</td>
							<td>Status</td>
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frm'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td><input type='text' id='s_user_nome' name='s_user_nome' value='$s_user_nome' size='30' /></td>
							<td>"; ListItemPicker::Render('s_user_funcao', 'roles', getPost('s_user_funcao', ''), true); echo "</td>
							<td>"; ListItemPicker::Render('s_user_status', 'users_status', getPost('s_user_status', '1'), true, null, '-1', '(Todos)'); echo "</td>
						</tr>
					</table>
				</form>
			</fieldset>
			
			<script type='text/javascript'>
				function submitActivateUser(id, actionType) {
					var frm = document.getElementById('frm');
					var ctrl_id = document.getElementById('userid');
					var ctrl_action = document.getElementById('a');
					
					if (frm) {
						ctrl_id.value = id;
						ctrl_action.value = actionType;
						frm.submit();	
					}
					return false;
				}
			</script>";
	
	if ($lst) {
		echo "<table class='List'>
						<tr>
							<th width='3%'></th>
							<th>Nome</th>
							<th>E-mail</th>
							<th>Funções</th>
							<th>Ativo</th>
						</tr>";
		foreach ($lst as $u) {
			echo "<tr>
						<td>
							<a href='user.php?id=$u->userid' title='Exibir detalhes deste usuário'><img src='../Images/icon-info.png' alt='Clique para exibir detalhes deste usuário' /></a>	
						</td>
						<td>$u->nome</td>
						<td>$u->email</td>
						<td>";
							if ($u->roles) {
								$separator='';
								foreach ($u->roles as $role) {
									echo $separator."$role";
									$separator=', ';
								}
							} else {
								echo "&nbsp;";
							}
			echo "	</td>
						<td class='Center "; echo (($u->ativo) ? "Ativo'>Ativo" : "Inativo'>Inativo"); echo "</td>
					</tr>";
		}
		echo "</table>";
		
		Pagination::Render('users.php?page=%s', $totalrows, $pagesize, $page);
		
	} else {
		echo "Nenhum item encontrado.";
	}
}
?>