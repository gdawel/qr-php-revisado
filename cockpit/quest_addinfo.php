<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Adicionar Informações ao Questionário';
include_once '../App_Code/User.class';
include_once '../App_Code/Pesquisa.class';
include_once '../App_Code/Questionario.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Gestor,Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';

$pesquisaid = 0;
$id = 0;

function Router() {
	global $pesquisaid, $id;
	$action = getPost('action', null);	

	switch ($action) {
		case 'addinfo':
			if (addInfo()) {
				ob_clean();
				Header("Location: pesquisa.php?id=$pesquisaid#Quest$id");	
			} else { 
				MessageBox::Render("$GLOBALS[error]");
				RenderDefault();
			}
			break;
		
		default:
			RenderDefault();
	}
	
	ob_flush();
}

function RenderDefault() {
	global $pesquisaid;
	
	$usr = Users::getCurrent();
	$id = getIntQueryString('id', false, true);

	if (!$id) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}
	
	$quests = new Questionarios();
	$q = $quests->item($id);
	
	if (!$q) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}


	$pesquisas = new Pesquisas();
	$p = $pesquisas->item($q->pesquisaid);
	$pesquisaid = $q->pesquisaid;
	
	//Verificar se gestor é o pesquisador
	if ($p->isAccessDenied()) {
		echo "<h1>Acesso negado</h1>
					<p>Você não permissão para visualizar a pesquisa solicitada. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;	
	}	
	
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Pesquisa', "pesquisa.php?id=$pesquisaid#Quest$q->id", 'Voltar para a pesquisa');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";		
				
	echo "<h1>Informações Básicas Questionário #$q->id</h1>
			
				<h2>Informações da Pesquisa</h2>
				
				<table class='Form'>
					<tr>
						<td class='Field' width='110px'>Nome</td>
						<td>$p->titulo</td>
					</tr>
					<tr>
						<td class='Field'>Público</td>
						<td>$p->publico</td>
					</tr>
					<tr>
						<td class='Field'>Finalidade</td>
						<td>$p->finalidade</td>
					</tr>
					<tr>
						<td class='Field'>Questionários</td>
						<td>$p->count_concluidos de $p->count_questionarios concluídos</td>
					</tr>
				</table>
			
			<br />
			
				<h2>Informações do Questionário</h2>
				<form action='quest_addinfo.php' method='post' name='frm' id='frm'>
					<input type='hidden' name='action' id='action' value='addinfo' />
					<input type='hidden' name='id' id='id' value='$q->id' />
					
					<table class='Form'>
						<tr>
							<td class='Field' width='110px'>Login</td>
							<td>".$q->infos['QuestionarioId']."</td>
						</tr>
						<tr>
							<td class='Field'>Senha</td>
							<td>".$q->infos['Password']."</td>
						</tr>
						
						<tr>
							<td class='Field'>Nome</td>
							<td><input type='text' name='quest_nome' id='quest_nome' value='".$q->infos['Nome']."' size='30' maxlength='255' /></td>
						</tr>
						<tr>
							<td class='Field'>E-mail</td>
							<td><input type='text' name='quest_email' id='quest_email' value='".$q->infos['Email']."' size='30' maxlength='45' /></td>
						</tr>
						<tr>
							<td colspan='2'>&nbsp;</td>
						</tr>						
					</table>
					
					<div class='Buttons'>";				
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::Render(null, 'Voltar', "pesquisa.php?id=$pesquisaid#Quest$q->id", 'Voltar para a pesquisa'); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('quest_email', 'email', 'Email inválido');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>
			";
}


function addinfo() {
	global $pesquisaid, $id;
	$id = getIntPost('id', 0, true);

	if (!$id) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}
	
	$quests = new Questionarios();
	$q = $quests->item($id);	
	
	if (!$q) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}


	$pesquisas = new Pesquisas();
	$p = $pesquisas->item($q->pesquisaid);
	$pesquisaid = $q->pesquisaid;
	
	//Verificar se gestor é o pesquisador
	if ($p->isAccessDenied()) {
		echo "<h1>Acesso negado</h1>
					<p>Você não permissão para visualizar a pesquisa solicitada. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;	
	}
	
	$ret = $q->updateBasicInfos(getPost('quest_nome', null), getPost('quest_email', null));
	$GLOBALS['error'] = $q->error;
	return $ret;
}

?>