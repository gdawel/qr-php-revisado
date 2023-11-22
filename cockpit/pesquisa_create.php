<?php
$pageTitle = 'SOBRARE Cockpit Admin | Criar pesquisa';
include_once '../App_Code/User.class';
include_once '../App_Code/Filter.class';
include_once '../App_Code/Pesquisa.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin,Gestor', 'login.php');
include_once '../MasterPageCockpit.htm.php';

$msg_style = 'Info';

function Router() {	
	global $msg, $msg_style;

	$action = getPost('action');
	
	switch ($action) {		
		case 'save':		
			if (!isPageRefresh()) savePesquisa();
			RenderDefault();
			break;	
		
		default:
			RenderDefault();		
	}
}

function RenderDefault() {
	checkPageRefreshSessionVar();
	
	global $msg, $msg_style, $version;
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home'); echo "
			</div>";

	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
	
	echo "<h1>Criar Pesquisa</h1>";
					
	echo "<form action='pesquisa_create.php' method='post' name='frm' id='frm'>
		<input type='hidden' name='action' id='action' value='save' />
		<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
		
		<table class='Form'>
			<tr>
				<td class='Right' colspan='2'><span class='Red'>*</span> = Item obrigatório</td>
			</tr>
				
			<tr class='Field'>
				<td colspan='2'>Título <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='80' maxlength='100' name='titulo' id='titulo' value='".getPost('titulo', null, true)."' /></td>
			</tr>
					
			<tr class='Field'>
				<td colspan='2'>Público-alvo <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='80' name='publico' id='publico' value='".getPost('publico', null, true)."' /></td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>Finalidade</td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='80' name='finalidade' id='finalidade' value='".getPost('finalidade', null, true)."' /></td>
			</tr>
			
			<tr class='Field'>
				<td>Pacote<span class='Red'>*</span></td>
				<td>Tipo da Pesquisa</td>
			</tr>
			<tr>
				<td>"; ListItemPicker::Render('pacote', 'pacotes', null, false); echo "</td>
				<td>"; ListItemPicker::Render('pesquisa_tipo', 'pesquisas_tipos', getPost('pesquisa_tipo', 1, true), false); echo "</td>
			</tr>
			
			<tbody id='questsNormalLabel'>
				<tr class='Field'>				
					<td colspan='2'>Qtde Quests <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='10' name='qtde' id='qtde' value='".getPost('qtde', null, true)."' /></td>
				</tr>
			</tbody>
			<tbody id='questsAglutinadoraLabel'>
				<tr class='Field'>
					<td colspan='2'>Relação dos questionários a serem aglutinados <small>(separados por vírgula)</small> <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td colspan='2'>
						<textarea cols='80' rows='3' name='quests_ids' id='quests_ids'>".getPost('quests_ids', '', true)."</textarea>
					</td>
				</tr>
			</tbody>		
			
			<tr class='Field'>				
				<td colspan='2'>Produtos Associados <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='2'><div id='divProdutos'></div></td>
			</tr>
			
			<tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
		</table>
		
		<div class='Buttons'>";
			Button::RenderSubmit(null, 'Criar Pesquisa', 'Cria uma nova pesquisa', 'save', 'positive');
			Button::Render(null, 'Voltar', 'index.php', 'Voltar à página anterior', 'undo'); 
			echo "
		</div>		
		</form>
		
		<div id='frm_errorloc' class='Error'></div>
		
		<script language='JavaScript' type='text/javascript'>
			var vld  = new Validator('frm');
			
			vld.addValidation('titulo', 'req', 'Título obrigatório');
			vld.addValidation('publico', 'req', 'Público-alvo obrigatório');			
			vld.addValidation('qtde', 'req', 'Quantidade de questionários obrigatória');
			vld.addValidation('qtde', 'num', 'Quantidade de questionários inválida');
			vld.addValidation('quests_ids', 'req', 'Lista de questionários obrigatória');
			
			vld.EnableOnPageErrorDisplaySingleBox();
			vld.EnableMsgsTogether();
		</script>
		
		<script type=\"text/javascript\" src=\"pesquisa_create.js?v=$version\"></script>
	";
}

function savePesquisa() {
	updatePageRefreshChecker();
	
	global $msg, $msg_style;
	
	$p = new Pesquisa();	
	
	$p->id = getPost('id', null);
	$p->titulo = getPost('titulo', null);
	$p->finalidade = getPost('finalidade', null);
	$p->publico = getPost('publico', null);
	$p->pacote->id = getIntPost('pacote', null, true);
	$usr = Users::getCurrent();
	$p->pesquisadorid = $usr->userid;
	$p->statusid = 1; //ativa
	$p->tipoid = getIntPost('pesquisa_tipo', 1, true);
	if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
		$p->count_questionarios = getPost('quests_ids', null);
	} else {
		$p->count_questionarios = getIntPost('qtde', 1, true);
	}
	
	if (isset($_POST['produtos'])) {
		$selectedProdutos = !empty($_POST['produtos']) ? $_POST['produtos'] : array();
                
                //add por default os produtos 3 (tabela de índice) e 4 (tabela de categorias)
                $selectedProdutos[] = PRODUTO_TABELA_INDICE;
                $selectedProdutos[] = PRODUTO_TABELA_CATEGORIA;
                
		$produtos = new Produtos();
				
		foreach ($selectedProdutos as $prodId) {
			$prod = $produtos->getProduto($prodId);
			$prod->selected = true;
			$p->produtos[$prod->id] = $prod;
		}
	}
	
	$pesquisas = new Pesquisas();
	if (!$pesquisas->add($p)) {
		$msg = $pesquisas->error;
		$msg_style = 'Error';
		return false;
	} else {
		//ob_clean();
		//header("Location: index.php");
		$msg = 'Pesquisa criada com sucesso. '.$pesquisas->error;
		$msg_style = 'Info';
		return true;
	}
}
?>