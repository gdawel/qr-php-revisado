<?php
$pageTitle = 'SOBRARE Cockpit Admin | Publicações';
include_once '../App_Code/User.class';
include_once '../App_Code/Publicacao.class';
include_once '../App_Code/CommonFunctions.php';
include_once 'admin.index.php';
include_once '../Controls/pagination.ctrl.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {	
	$publicacoes = new Publicacoes();

	$action = getPost('action');
	$id = getPost('id', 0);
	switch ($action) {
		case 'publicar':
			if ($publicacoes->publicar($id, 1)) {
				$msg = 'Publicado com sucesso';
			} else {
				$msg = 'Erro ao publicar.';
			}
			break;
			
		case 'naopublicar':		
			if ($publicacoes->publicar($id, 0)) {
				$msg = 'Publicação retirada com sucesso';
			} else {
				$msg = 'Erro ao retirar publicação';
			}
			break;
			
		case 'delete':		
			if ($publicacoes->delete($id)) {
				$msg = 'Publicação excluída com sucesso';
			} else {
				$msg = 'Erro ao excluir publicação';
			};
			break;		
	}


	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home'); echo "
			</div>";
				
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg);
				
	//Filter
	$filter = new Filter();
	$s_nome = $filter->addFromPost('titulo', 'LIKE','s_nome');
	$s_autor = $filter->addFromPost('autor', 'LIKE', 's_autor');
	$s_status = getPost('s_status', 0); 
    if ($s_status) {
        $filter->add('isPublicado', '=', "0"); 
        $_SESSION['s_status'] = "checked='checked'"; 
        $s_status="checked='checked'";
    } else 
        $s_status='';
    
    $s_orderby = getPost("s_orderby", "Data", true);
    
	
	//Data
	$page = getIntQueryString('page', 1, true);
	$pagesize = 10;	
	$lst = $publicacoes->Items($page, $pagesize, $s_orderby, $totalrows, $filter);
	if ($totalrows) $countmsg = "($totalrows itens encontrados)"; else $countmsg = '';
	
	echo "<h1>Cockpit do Admin</h1>
				<h2>Publicações</h2>
				<fieldset>
					<legend>Filtros <span class='FieldsetMsg'>$countmsg</span></legend>					
					<form id='frm' name='frm' method='post' action='publicacoes.php'>
						<input type='hidden' name='action' id='action' value='0' />
						<input type='hidden' name='id' id='id' value='0' />
					
						<table class='Form'>
							<tr class='Field'>
								<td>Nome</td>
								<td>Autor</td>
                                <td>Classificar por</td>	
								<td>Status</td>						
								<td rowspan='2' class='SearchButtonCell'>
									<div class='Buttons'>";
									Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frm'); echo "
									</div>
								</td>
							</tr>
							<tr>
								<td><input type='text' id='s_nome' name='s_nome' value='$s_nome' size='30' /></td>
								<td><input type='text' id='s_autor' name='s_autor' value='$s_autor' size='20' /></td>
                                <td>
                                    <select id='s_orderby' name='s_orderby'>
                                        <option value='Data'" . ($s_orderby == 'Data'?" selected='selected'":"") . ">Data de Publicação</option>
                                        <option value='Titulo'" . ($s_orderby == 'Titulo'?" selected='selected'":"") . ">Título</option>
                                    </select>
                                </td>
								<td><input type='checkbox' $s_status name='s_status' value='1'/> Exibir somente não publicados</td>
							</tr>
						</table>
					</form>
				</fieldset>";
				
	
	echo "<script type='text/javascript'>
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
									if ($p->url) echo "<a target='_blank' href='".htmlspecialchars($p->url)."'>".htmlspecialchars($p->url)."</a>";
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
							</tr><tr>
								<td class='Field'>Ações</td>
								<td>
									<div class='Buttons'>";
										if ($p->ispublicado == '0') {
											Button::Render(null, 'Publicar', '#', 'Publicar esse item no site', 'url', true, 'positive', "Action($p->id, 'publicar');");
										} else {
											Button::Render(null, 'Ocultar', '#', 'Ocultar essa publicação no site', 'warning', true, 'regular', "Action($p->id, 'naopublicar');");
										}
										Button::Render(null, 'Excluir', '#', 'Excluir esta publicação', 'delete', true, 'negative', "if (confirm('Deseja realmente excluir este item?')) Action($p->id, 'delete');");										
				echo "			</div>
								</td>
							</tr>
						</table>
						<br />";	
		}
		
		Pagination::Render('publicacoes.php?page=%s', $totalrows, $pagesize, $page);
		
	} else {
		echo "<p><i>Nenhum item encontrado.</i></p>";
	}
}



?>