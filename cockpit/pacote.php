<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Pacotes';
include_once '../App_Code/User.class';
include_once '../App_Code/Produto.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/pacote.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';

function Router() {
	global $pacoteid, $msg;
	$action = getPost('action', null) ? getPost('action', null) : getQueryString('action', null);	

	switch ($action) {
		case 'edit':
			RenderEditPacote();
			break;
		
		case 'save':
			if (!isPageRefresh()) {				
				if ($msg) MessageBox::Render($msg);
				
				if (savePacote()) RenderDefault();
				else RenderEditPacote();
			} else {
				RenderDefault();
			}
			break;

		case 'save_produto':
			if (!isPageRefresh()) {				
				saveProduto();
				if ($msg) MessageBox::Render($msg);
				RenderEditPacote();
			} else {
				RenderDefault();
			}
			break;
		
		case 'remove_produto':
			if (!isPageRefresh()) {				
				removeProduto();
				if ($msg) MessageBox::Render($msg);
				RenderEditPacote();
			} else {
				RenderDefault();
			}
			break;
					
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Novo Pacote', 'pacote.php?action=edit', 'Incluir novo pacote', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "	
			</div>";
			
	echo "<h1>Pacotes</h1>";
	
	$produtos = new Produtos();
	$filter = new Filter();
	//status
	$s_pacote_status = getPost('s_pacote_status', '1');
	if ($s_pacote_status != -1) $filter->add('p.Enabled', '=', $s_pacote_status);
	
	//get data
	$lst = $produtos->getPacotes($filter);
	
	
	echo "<fieldset>
				<legend>Filtros</legend>
				<form id='frmSearch' name='frmSearch' method='post' action='pacote.php'>				
					<table class='Form'>
						<tr class='Field'>
							<td>Status</td>
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td>"; echo ListItemPicker::Render('s_pacote_status', 'users_status', getPost('s_pacote_status', '1'), true, null, '-1', '(Todos)'); echo "</td>
						</tr>
					</table>
				</form>
			</fieldset>
			";
			
	if ($lst) {
		echo "<table class='List'>
					<tr>
						<th></th>
						<th>Nome</th>
						<th>Descrição</th>
						<th>Tipo</th>
						<th>Status</th>
					</tr>";
		foreach ($lst as $pacote) {
			$status = ($pacote->enabled == '1') ? '<span class="Verde">Ativo</span>' : '<span class="Red">Inativo</span>';
			echo "<tr>
						<td>
							<a href='pacote.php?action=edit&id=$pacote->id'><img src='../Images/icon-edit.png' title='Editar este pacote' /></a>							
						</td>
						<td>$pacote->nome</td>
						<td>$pacote->descricao</td>
						<td>".$pacote->tipo->nome."</td>
						<td class='Center'>$status</td>
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}

function RenderEditPacote() {
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
	
	if ($id) {			
		$produtos = new Produtos();
		$pacote = $produtos->getPacote($id);
	}
	if (!isset($pacote)) {
		$pacote = new Pacote();
		$pacote->id = 0;
		$pacote->nome = 'Novo';
	}
	
	if (getPost('action', null) == 'save') {
		$pacote->id = getIntPost('id', null, true);
		$pacote->nome = getPost('nome', null, true);
	 	$pacote->descricao = getPost('descricao', null, true);
	 	$pacote->questintrotext = getPost('questintrotext', null, true);
	 	$pacote->tipo->id = getIntPost('tipo', 0, true);
	 	$pacote->modeloquestionarioid = getPost('modeloquest', null, true);
	}
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Pacotes', 'pacote.php', 'Voltar para Pacotes');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	echo "<h1>Pacote $pacote->nome</h1>			
				
				<form action='pacote.php?id=$pacote->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='save' />
					<input type='hidden' name='id' id='id' value='$pacote->id' />				
					
					<h2>Informações do Pacote</h2>
					<table class='Form'>
						<tr>
							<td width='110px' class='Field'>Nome</td>
							<td><input type='text' name='nome' id='nome' value='$pacote->nome' size='65' maxlength='100' /></td>
						</tr>
						<tr>
							<td class='Field'>Descrição</td>
							<td><textarea name='descricao' id='descricao' cols='65' rows='5'>$pacote->descricao</textarea></td>
						</tr>
						
						<tr>
							<td class='Field'>Tipo</td>
							<td>"; ListItemPicker::Render('tipo', 'pacotes_tipos', $pacote->tipo->id, false); echo "</td>
						</tr>
						<tr>
							<td class='Field'>Modelo Questionário</td>
							<td>"; ListItemPicker::Render('modeloquest', 'modelos_questionarios', $pacote->modeloquestionarioid, false); echo "</td>
						</tr>						
						
						<tr>
							<td class='Field'>
								Texto de Introdução<br /> do Questionário
							</td>
							<td><textarea name='questintrotext' id='descricao' cols='65' rows='10'>$pacote->questintrotext</textarea></td>
						</tr>
						
						<tr>
							<td class='Field'>
								Ativo?"; if ($pacote->enabled) $checked = 'checked = "checked"'; else $checked=''; echo "
							</td>
							<td>
								<input type='checkbox' name='enabled' id='enabled' $checked value='1' />
							</td>
						</tr>
					</table>
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::Render(null, 'Voltar', 'pacote.php', 'Voltar para Pacotes', 'undo'); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('nome', 'req', 'Nome do pacote obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";
				
	if ($pacote->id != 0) {		
		echo "<hr class='clear' />";
		echo "<h2>Produtos Associados</h2>";
				
				echo "<form name='frmProduto' id='frmProduto' action='pacote.php?id=$pacote->id' method='post'>
							<input type='hidden' name='PageRefreshChecker' id='frmProduto_PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
							<input type='hidden' name='action' id='frmProduto_action' value='save_produto' />
							<input type='hidden' name='id' id='frmProduto_id' value='$pacote->id' />
							
							<input type='hidden' name='prodid' id='frmProduto_prodid' value='' />
							<input type='hidden' name='preco' id='frmProduto_preco' value='' />
							<input type='hidden' name='obrigatorio' id='frmProduto_obrigatorio' value='' />
							<input type='hidden' name='porpacote' id='frmProduto_porpacote' value='' />
							
							<input type='hidden' name='new' id='frmProduto_new' value='0' />	
						</form>
						
						<table class='List'>
							<tr>
								<th style=\"width:6%;\"></th>
								<th>Produto</th>
								<th>Preço</th>
								<th>Obrigatório?</th>
							</tr>";
				if ($pacote->produtos) {
					foreach ($pacote->produtos as $prod) {
						$obr = ($prod->obrigatorio) ? 'checked="checked"' : '';
						echo "<tr>
									<td>
										<a href=\"javascript:saveProduto($prod->id);\">
											<img src='../Images/icon-save.png' title='Salvar alterações neste produto' />
										</a>
										&nbsp;
										<a href=\"javascript:if (confirm('Deseja realmente remover este produto do pacote?')) removeProduto($prod->id);\">
											<img src='../Images/icon-delete.png' title='Remover este produto do pacote' />
										</a>
									</td>
									<td>$prod->nome</td>
									<td class='Center'><input type='text' name='preco_$prod->id' id='preco_$prod->id' value='$prod->preco' size='3' /></td>									
									<td class='Center'><input type='checkbox' name='obrigatorio_$prod->id' id='obrigatorio_$prod->id' value='1' $obr / ></td>
								</tr>";
					}
				}
				echo "	<tr>				
								<td>
									<a href=\"javascript:saveProduto(0);\"><img src='../Images/icon-save.png' title='Inserir produto' /></a>
								</td>
								<td>"; ListItemPicker::Render('produto_0', 'produtos_faltantes_no_pacote', null, true, $pacote->id); echo "</td>
								<td class='Center'><input type='text' name='preco_0' id='preco_0' value='1.00' size='3' /></td>
								<td class='Center'><input type='checkbox' name='obrigatorio_0' id='obrigatorio_0' value='1' / ></td>					
							</tr>
				
						</table>";
						
		echo "<script type='text/javascript'>
					function saveProduto(ProdutoId) {
						document.getElementById('frmProduto_preco').value = 	document.getElementById('preco_' + ProdutoId).value;	
						document.getElementById('frmProduto_obrigatorio').value = 	document.getElementById('obrigatorio_' + ProdutoId).checked ? 1 : 0;
					 	//document.getElementById('frmProduto_porpacote').value = 	document.getElementById('porpacote_' + ProdutoId).checked ? 1 : 0;
						
						if (ProdutoId == 0) {
							if (document.getElementById('produto_0').value == 0) {
								alert('Selecione o produto a ser inserido no pacote.');
								return false;
							} 
							document.getElementById('frmProduto_new').value = 1;
							document.getElementById('frmProduto_prodid').value = document.getElementById('produto_0').value;
						} else {
							document.getElementById('frmProduto_prodid').value = ProdutoId;
						}
						
						document.getElementById('frmProduto').submit();
					}
					
					function removeProduto(prodId) {
						var frm = document.getElementById('frmProduto');
						var id = document.getElementById('frmProduto_prodid');
						var action = document.getElementById('frmProduto_action');
						
						if (frm) {
							action.value = 'remove_produto';
							id.value = prodId;
							
							frm.submit();
						}
					}
				</script>";
	}
}


function savePacote() {
	global $msg;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);

	$pacote = new Pacote();
	$pacote->id = $id;
	$pacote->nome = getPost('nome', null);
 	$pacote->descricao = getPost('descricao', null);
 	$pacote->questintrotext = getPost('questintrotext', null);
 	$pacote->tipo->id = getIntPost('tipo', 0, true);
 	$pacote->modeloquestionarioid = getPost('modeloquest', 0, true);
 	$pacote->enabled = getPost('enabled', 0);
 	
 	$produtos = new Produtos();
 	if ($ret = $produtos->savePacote($pacote)) {
 		$msg = 'Pacote salvo com sucesso';
 		return true;
 	} else {
 		$msg = $produtos->error;
 		return false;
 	}
}

function saveProduto() {
	global $msg;
	
	updatePageRefreshChecker();
	
	$pacoteid = getIntPost('id', 0, true);
	
	$produto = new Produto();
	$produto->id = getIntPost('prodid', 0, true);
	$produto->preco = getPost('preco', null);
	$produto->obrigatorio = getIntPost('obrigatorio', 0, true);
	//$produto->porpacote = getIntPost('porpacote', 0, true);	
	$new = getIntPost('new', 0, true);
	
	$produtos = new Produtos();
 	if ($ret = $produtos->saveProdutoPacote($pacoteid, $produto, $new)) {
 		$msg = 'Produto salvo com sucesso';
 		return true;
 	} else {
 		$msg = $produtos->error;
 		return false;
 	}
}

function removeProduto() {
	global $msg;
	
	updatePageRefreshChecker();
	
	$pacoteid = getIntPost('id', 0, true);
	$produtoid = getIntPost('prodid', 0, true);
	$produtos = new Produtos();
 	if ($ret = $produtos->removeProdutoPacote($pacoteid, $produtoid)) {
 		$msg = 'Produto removido com sucesso';
 		return true;
 	} else {
 		$msg = $produtos->error;
 		return false;
 	}
}
?>