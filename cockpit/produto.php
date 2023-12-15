<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Produtos';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Produto.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/produto.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';

function Router() {
	global $msg;
	$action = getPost('action', null) ? getPost('action', null) : getQueryString('action', null);	

	switch ($action) {
		case 'edit':
			RenderEdit();
			break;
		
		case 'save':
			if (!isPageRefresh()) {				
				if ($msg) MessageBox::Render($msg);
				
				if (saveProduto()) RenderDefault();
				else RenderEdit();
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
				Button::RenderNav('Novo Produto', 'produto.php?action=edit', 'Incluir novo produto', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
			
	echo "<h1>Produtos</h1>";
	
	$produtos = new Produtos();
	$filter = new Filter();
	//status
	$s_pacote_status = getPost('s_produto_status', '1');
	if ($s_pacote_status != -1) $filter->add('p.Enabled', '=', $s_pacote_status);
	
	$lst = $produtos->getProdutos($filter);
	echo "<fieldset>
				<legend>Filtros</legend>
				<form id='frmSearch' name='frmSearch' method='post' action='produto.php'>				
					<table class='Form'>
						<tr class='Field'>
							<td>Status</td>
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frmSearch'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td>"; echo ListItemPicker::Render('s_produto_status', 'users_status', getPost('s_produto_status', '1'), true, null, '-1', '(Todos)'); echo "</td>							
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
						<th>Status</th>
					</tr>";
		foreach ($lst as $produto) {
			$status = ($produto->enabled == '1') ? '<span class="Verde">Ativo</span>' : '<span class="Red">Inativo</span>';
			echo "<tr>
						<td>
							<a href='produto.php?action=edit&id=$produto->id'><img src='../Images/icon-edit.png' title='Editar este produto' /></a>							
						</td>
						<td>
							$produto->nome<br />
							<small>$produto->descricao</small>
						</td>
						<td class='Center'>$status</td>
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}

function RenderEdit() {
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
	
	if ($id) {			
		$produtos = new Produtos();
		$produto = $produtos->getProduto($id);
	}
	if (!isset($produto)) { //new produto
		$produto = new Produto();
		$produto->id = 0;
		$produto->nome = 'Novo';
	}
	
	if (getPost('action', null) == 'save') {
		$pacote->id = getIntPost('id', null, true);
		$pacote->nome = getPost('nome', null, true);
	 	$pacote->descricao = getPost('descricao', null, true);
	 	$pacote->enabled = getIntPost('enabled', 0, true);
	}
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Produtos', 'produto.php', 'Voltar para Produtos');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	echo "<h1>Produto $produto->nome</h1>			
				
				<form action='produto.php?id=$produto->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='save' />
					<input type='hidden' name='id' id='id' value='$produto->id' />				
					
					<h2>Informações do Produto</h2>
					<table class='Form'>
						<tr>
							<td width='110px' class='Field'>Nome</td>
							<td><input type='text' name='nome' id='nome' value='$produto->nome' size='85' maxlength='155' /></td>
						</tr>
						<tr>
							<td class='Field'>Descrição</td>
							<td><textarea name='descricao' id='descricao' cols='66' rows='5'>$produto->descricao</textarea></td>
						</tr>
						
						<tr>
							<td class='Field'>
								Por Pacote?
							</td>
							<td>";
								ListItemPicker::Render('porpacote', 'simnao', $produto->porpacote); echo "
							</td>
						</tr>
						
						<tr>
							<td class='Field'>
								Ativo?
							</td>
							<td>";
								ListItemPicker::Render('enabled', 'simnao', $produto->enabled); echo "
							</td>
						</tr>
					</table>
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::Render(null, 'Voltar', 'produto.php', 'Voltar para Produtos', 'undo'); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('nome', 'req', 'Nome do pacote obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";			
}


function saveProduto() {
	global $msg;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);

	$produto = new Produto();
	$produto->id = $id;
	$produto->nome = getPost('nome', null);
 	$produto->descricao = getPost('descricao', null);
 	$produto->porpacote = getIntPost('porpacote', 0, true);
 	$produto->enabled = getIntPost('enabled', 0, true);
 	
 	$produtos = new Produtos();
 	if ($ret = $produtos->saveProduto($produto)) {
 		$msg = 'Produto salvo com sucesso';
 		return true;
 	} else {
 		$msg = $produtos->error;
 		return false;
 	}
}
?>