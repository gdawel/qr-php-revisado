<?php
$pageTitle = 'SOBRARE Cockpit | Minhas Publicações';
include_once '../App_Code/User.class';
include_once '../App_Code/Publicacao.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../App_Code/FileHandler.class';
include_once '../Controls/pagination.ctrl.php';
include_once '../Controls/msgbox.ctrl.php';
require_once '../Controls/list.ctrl.php';
require_once '../Controls/button.ctrl.php';

Users::checkAuth('Associado', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {
	global $msg; 

	$action = getPost('action'); if (!$action) $action = getQueryString('action', null);
	$id = getPost('id', 0);
	
	switch ($action) {			
		case 'delete':
			$publicacoes = new Publicacoes();		
			if ($publicacoes->delete($id)) {
				$msg = 'Publicação excluída com sucesso';
			} else {
				$msg = 'Erro ao excluir publicação';
			};
			RenderDefault();
			break;		

		case 'new':
			RenderForm();
			break;

		case 'insert':
			if (!isPageRefresh()) {
				if (InsertPublicacao()) RenderDefault(); else RenderForm();
			}
			else {
				RenderDefault();
			}
			break;

		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg; 
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Nova Publicação', 'associado.publicacoes.php?action=new', 'Incluir nova publicação', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg);
				
	$publicacoes = new Publicacoes();
	//Filter
	$usr = Users::getCurrent();
	$filter = new Filter();
	$filter->add('CreatedBy', '=', $usr->userid); //Somente publicacoes do usuario atual

	
	//Data
	$page = getIntQueryString('page', 1, true);
	$pagesize = 10;	
	$lst = $publicacoes->Items($page, $pagesize, null, $totalrows, $filter);
	if ($totalrows) $countmsg = "$totalrows itens encontrados"; else $countmsg = '';
	
	echo "<h1>Minhas Publicações</h1>
			<form id='frm' name='frm' method='post' action='associado.publicacoes.php'>
						<input type='hidden' name='action' id='action' value='0' />
						<input type='hidden' name='id' id='id' value='0' />
			</form>
				
	
				<script type='text/javascript'>
					function Action(id, action) {
						document.getElementById('id').value = id;
						document.getElementById('action').value = action;
						
						document.getElementById('frm').submit();
					}
				</script>";
	
	if ($lst) {
		foreach ($lst as $p) {
			echo "<h3>$p->titulo</h3>
						<table class='Form'>
							<tr>
								<td class='Field'>Autor</td><td>".htmlspecialchars($p->autor)."</td>
							</tr><tr>	
								<td class='Field'>Coautores</td><td>".htmlspecialchars($p->coautores)."</td>								
							</tr><tr>	
								<td class='Field'>Url</td><td>";
									if ($p->url) echo "<a href='".htmlspecialchars($p->url)."'>".htmlspecialchars($p->url)."</a>";
										else echo "-";
			echo "		</td>
							</tr><tr>	
								<td class='Field'>Arquivo</td><td>";
									if ($p->filename) echo "<a target='_blank' href='../Uploads/".htmlspecialchars($p->filename)."'>".htmlspecialchars($p->filename)."</a>";
										else echo "-";
										
			echo "		</td>
							</tr><tr>
								<td class='Field'>Instituição</td><td>".htmlspecialchars($p->instituicao)."</td>
							</tr><tr>
								<td class='Field'>Resumo</td><td class='Text'>".htmlspecialchars($p->resumo)."</td>
							</tr><tr>	
								<td class='Field'>Data</td><td>".format_date($p->datapublicacao)."</td>
							</tr><tr>
								<td class='Field'>Status</td><td>";
									if ($p->ispublicado == '0') {
										echo "<span class='Red'>Não Publicado</span> ";
									} else {
										echo "<span class=''>Publicado</span>";
									}
				echo "	</td>
							</tr>
							<tr>
								<td class='Field'>Ações</td><td>
									<div class='Buttons'>";
										Button::Render(null, 'Excluir', '#', 'Excluir esta publicação', 'delete', true, 'negative', "if (confirm('Deseja realmente excluir este item?')) Action($p->id, 'delete');");	echo "
									</div>
								</td>
							</tr>
						</table>
						<br />";	
		}
		
		Pagination::Render('associado.publicacoes.php?page=%s', $totalrows, $pagesize, $page);
		
	} else {
		echo "<p class='NoResults'>Nenhuma publicação encontrada.</p>";
	}
}

function InsertPublicacao() {
	global $msg;
	updatePageRefreshChecker();
	
	//upload doc
	$fs = new FileHandler();
	if (!$fs->isAllowedExtension()) {
		$msg = 'Somente arquivos PDF são aceitos para upload.';
		return false;
	}
	if (!$fs->uploadFiles()) {
		$msg = 'Erro ao salvar arquivo. Tente novamente.';
		return false;
	}
	
	$publicacoes = new Publicacoes();
	$p = new Publicacao();
	
	$p->titulo = getPost('titulo');
	$p->autor = getPost('autor');
	//$p->email = getPost('email');
	$p->coautores = getPost('coautores');
	$p->instituicao = getPost('instituicao');
	$p->tipoid = getIntPost('tipo');
	$p->url = getPost('url');
	$p->filename = $fs->filename[0];
	$p->resumo = getPost('resumo');
	
	if (!$publicacoes->add($p)) {
		$msg = 'Erro ao inserir publicação. ' . $publicacoes->error;
		return false;
	} else {
		$msg = 'Publicação incluída com sucesso. Após ser aprovada pelo administrador, será exibida no site.';		
		return true;
	}
}

function RenderForm() {
	global $msg; 
		
	checkPageRefreshSessionVar();
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, 'Error');
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Minhas Publicações', 'associado.publicacoes.php', 'Voltar para Minhas Publicações');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
			
	echo "<h1>Publicações</h1>
				<h2>Inserir nova publicação</h2>			
						
				<form action='associado.publicacoes.php' method='post' name='frm' id='frm' enctype='multipart/form-data'>
					<input type='hidden' name='action' id='action' value='insert' />
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
					
					<table class='Form'>
						<tr>
							<td colspan='2' class='Right'><span class='Red'>*</span> = Item obrigatório</td>
						</tr>
						
						<tr class='Field'>
							<td colspan='2'>Título <span class='Red'>*</span></td>
						</tr>
						<tr>
							<td colspan='2'><input type='text' size='82' name='titulo' id='titulo' value='".getPost('titulo', null, true)."' maxlength='255' /></td>
						</tr>						
						
						<tr class='Field'>
							<td>Autor <span class='Red'>*</span></td>
							<!--<td>E-mail <span class='Red'>*</span></td>-->
							<td></td>
						</tr>
						<tr>
							<td><input type='text' size='42' name='autor' id='autor' value='".getPost('autor', null, true)."' maxlength='150' /></td>
							<!--<td><input type='text' size='32' name='email' id='email' value='".getPost('email', null, true)."' maxlength='45' /></td>-->
							<td></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='2'>Coautores</td>
						</tr>
						<tr>
							<td colspan='2'><input type='text' size='42' name='coautores' id='coautores' value='".getPost('coautores', null, true)."' maxlength='255' /></td>
						</tr>
						
						<tr class='Field'>
							<td>Instituição <span class='Red'>*</span></td>
							<td>Tipo <span class='Red'>*</span></td>
						</tr>
						<tr>
							<td><input type='text' size='42' name='instituicao' id='instituicao' value='".getPost('instituicao', null, true)."' maxlength='150' /></td>
							<td>"; echo ListItemPicker::Render('tipo', 'publicacoes_tipos', getPost('tipo', ''), false); echo "</td>
						</tr>						
						
						<tr class='Field'>
							<td colspan='2'>Endereço Eletrônico<small>(Quando o seu trabalho já estiver publicado em outro site)</small></td>
						</tr>
						<tr>
							<td colspan='2'><input type='text' size='82' name='url' id='url' value='".getPost('url', null, true)."' maxlength='255' /></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='2'>Anexar Documento <small><span class='Red'>[PDF até 10Mb]</span> (Caso deseje publicar seu trabalho diretamente pela SOBRARE)</small></td>
						</tr>
						<tr>
							<td colspan='2'><input type='file' class='File' size='82' name='userfiles[]' /></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='2'>Resumo <span class='Red'>*</span></td>
						</tr>
						<tr>
							<td colspan='2'><textarea cols='101' rows='6' name='resumo' id='resumo'>".getPost('resumo', null, true)."</textarea></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='2'></td>
						</tr>
						<tr>
							<td colspan='2'><input type='checkbox' name='auth' id='auth' /> Autorizo a divulgação desta publicação neste site</td>
						</tr>
					</table>					
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::Render(null, 'Voltar', 'associado.publicacoes.php', 'Voltar para Minhas Publicações'); echo "
					</div>
				</form>
				
				<div id='frm_errorloc' class='Error'>
				</div>
				
				<script type='text/javascript'>			
					var vld  = new Validator('frm');
					
					vld.addValidation('titulo','req','Título obrigatório');
					vld.addValidation('autor','req','Autor obrigatório');
					//vld.addValidation('email','email','E-mail inválido');
					//vld.addValidation('email','req','E-mail obrigatório');
					vld.addValidation('instituicao','req','Instituição obrigatória');
					vld.addValidation('resumo','req','Resumo obrigatório');
					vld.addValidation('resumo','minlen=300','Resumo deve conter no mínimo 300 caracteres');
					vld.addValidation('auth','shouldselchk=on','Você deve autorizar a divulgação desta publicação');
										
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";
}


?>