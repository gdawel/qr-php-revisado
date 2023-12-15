<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Créditos';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Creditos.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/creditos.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$msg_style = 'Info';

function Router() {
	global $msg, $msg_style, $version;
	$action = getPost('action', getQueryString('action'));	

	switch ($action) {
		case 'new':
			RenderEdit();
			break;
		
		case 'save':
			if (!isPageRefresh()) {
				saveCredito();			
				updatePageRefreshChecker();
			}
			RenderDefault();
			break;

		case 'delete':
			if (!isPageRefresh()) {
				deleteCredito();
				updatePageRefreshChecker();
			}
			RenderDefault();
			break;

		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg, $msg_style, $version;
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Saldos', 'creditos_saldo.php', 'Verificar saldos');
				Button::RenderNav('Novo Crédito', 'creditos.php?action=new', 'Incluir novo crédito ao gestor', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);		
			
	//Current user
	$usr = Users::getCurrent();
			
			
	echo "<h1>Créditos</h1>";
	
	$creditos = new Creditos();
	$filter = new Filter();
	$lst = $creditos->getItems($filter);
	
	if ($lst) {		
		echo "<table class='List'>
					<tr>
						<th></th>
						<th>Gestor</th>
						<th>Pacote</th>
						<th>Produtos</th>
						<th>Qtde</th>
						<th>Criado em</th>
					</tr>";
		foreach ($lst as $credito) {
			if ($usr->isinrole('Admin'))
				$deleteButton = "<a href=\"javascript:if (confirm('Deseja realmente excluir este item?')) deleteCredito($credito->id);\" title='Excluir este item'><img src='../Images/icon-delete.png' alt='Excluir este crédito' /></a>";
			else
				$deleteButton = "&nbsp;";
			
			echo "<tr>
						<td style=\"vertical-align:top;\">				
							$deleteButton										
						</td>
						<td>
							$credito->username
						</td>
						<td class='Center'>$credito->pacote</td>
						<td>";
							if ($credito->produtos) {
								echo "<ul>";
									foreach ($credito->produtos as $p) echo "<li>$p->nome</li>";
								echo "</ul>";
							} else {
								echo 'Nenhum produto associado';
							}
			echo "	</td>
						<td class='Center'>$credito->qtde</td>
						<td class='Center'>$credito->createddate</td>
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
	
	checkPageRefreshSessionVar();
	echo "<form action='creditos.php' method='post' name='frm' id='frm'>
				<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
				<input type='hidden' name='action' id='action' value='save' />
				<input type='hidden' name='id' id='id' value='$credito->id' />
			</form>
			
			<script type=\"text/javascript\">
				 function deleteCredito(creditoId) {
				 	$('#action').val('delete');
				 	$('#id').val(creditoId);
				 	$('#frm').submit();
				 	
				 	return false;
				 }
			</script>
 ";
}

function RenderEdit() {
	global $msg, $msg_style, $version; 
	
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
	
	if ($id) {			
		$creditos = new Creditos();
		$credito = $creditos->getItem($id);
	}
	if (!isset($credito)) { //new credito
		$credito = new Credito();
		$credito->id = 0;
	}	
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Saldos', 'creditos_saldo.php', 'Voltar para Saldos');
				Button::RenderNav('Créditos', 'creditos.php', 'Voltar para Créditos');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para Home', 'home'); echo "
			</div>";
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
					
	echo "<h1>Crédito</h1>			
				
				<form action='creditos.php' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='save' />
					<input type='hidden' name='id' id='id' value='$credito->id' />				
					
					<h2>Informações do crédito</h2>
					<table class='Form'>
						<tr>
							<td width='110px' class='Field'>Gestor</td>
							<td>"; ListItemPicker::Render('gestor', 'gestores', $credito->userid, false); echo "</td>
						</tr>
						<tr>
							<td class='Field'>Pacote</td>
							<td>"; ListItemPicker::Render('pacote', 'pacotes', $credito->pacoteid, false); echo "</td>
						</tr>
						<tr>
							<td class='Field'>Produtos</td>
							<td id='divProdutos'>
								Produtos disponíveis
							</td>
						</tr>
						<tr>
							<td class='Field'>
								Qtde
							</td>
							<td>
								<input type='texto' name='qtde' id='qtde' value='".getPost('qtde', '1')."' size='5' />
							</td>
						</tr>								
					</table>
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::Render(null, 'Voltar', 'creditos.php', 'Voltar para Créditos', 'undo'); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('qtde', 'num', 'Quantidade inválida');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>
				
				<script type=\"text/javascript\" src=\"creditos.js?v=$version\"></script>";			
}


function saveCredito() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);

	$credito = new Credito();
	$credito->id = $id;
	$credito->userid = getIntPost('gestor');
 	$credito->pacoteid = getIntPost('pacote');
 	$credito->qtde = getIntPost('qtde', 0);
 	
	if (isset($_POST['produtos'])) {
		$selectedProdutos = $_POST['produtos'];		
		foreach ($selectedProdutos as $prodId) {
			$prod = new Produto();
			$prod->id = intval($prodId);
			$prod->selected = true;
			$credito->produtos[$prod->id] = $prod;
		}
	}
 	
 	$creditos = new Creditos();
 	if ($ret = $creditos->save($credito)) {
 		$msg = 'Crédito incluído com sucesso';
 		$msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $creditos->error;
 		$msg_style = 'Error';
 		return false;
 	}
}

function deleteCredito() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);
 	$creditos = new Creditos();
 	
 	if ($ret = $creditos->delete($id)) {
 		$msg = 'Crédito excluído com sucesso';
 		$msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $creditos->error;
 		$msg_style = 'Error';
 		return false;
 	}
}
?>