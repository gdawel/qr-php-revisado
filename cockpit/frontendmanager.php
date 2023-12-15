<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Produtos';
include_once '../App_Code/User.class.php';
include_once '../App_Code/FrontEndManager.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/frontendmanager.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$msg_style = 'Info';

function Router() {
	global $msg, $msg_style;
	$action = getPost('action', null) ? getPost('action', null) : getQueryString('action', null);	

	switch ($action) {
		case 'edit':
			RenderEdit();
			break;
		
		case 'save':
			if (!isPageRefresh()) {				
				if (saveItem()) RenderDefault();
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
    global $msg, $msg_style;
    
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Novo Item', 'frontendmanager.php?action=edit', 'Incluir novo item', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    
	if ($msg) MessageBox::Render($msg, $msg_style);
                		
	echo "<h1>Conteúdo do Site</h1>";
	
	$manager = new FrontEndManager();
	$filter = new Filter();
	//status
	$s_tipoid = $filter->addFromPost('c.TipoId', '=', 's_tipoid', null, null, -1);
	
	$lst = $manager->getItems($filter);
	echo "<fieldset>
				<legend>Filtros</legend>
				<form id='frmSearch' name='frmSearch' method='post' action='frontendmanager.php'>				
					<table class='Form'>
						<tr class='Field'>
							<td>Tipo do Conteúdo</td>
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frmSearch'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td>"; echo ListItemPicker::Render('s_tipoid', 'contents_tipos', getPost('s_tipoid', $s_tipoid), true, null, -1, '(Todos)'); echo "</td>							
						</tr>
					</table>
				</form>
			</fieldset>
			";
			
	if ($lst) {
	    $table_headers = "<tr>
        						<th></th>
        						<th>Título</th>
                                <th>Texto</th>
        						<th>Ordem</th>
        					</tr>";
                            
		echo "<table class='List'>";
        $last_group = '';         
           
		foreach ($lst as $i) {
            if ($last_group != $i->tipoid) {
                echo "<tr class='GroupHeader'>
                        <td colspan='4'>$i->tipo</td>
                     </tr>
                     $table_headers";
                $last_group = $i->tipoid;
            }
			echo "<tr rel='$i->tipoid'>
						<td>
							<a href='frontendmanager.php?action=edit&id=$i->id'><img src='../Images/icon-edit.png' title='Editar este item' /></a>							
						</td>
                        <td style=\"width:200px;\">"; echo ($i->title) ? $i->title : '<i>Sem título</i>'; echo "</td>
						<td>".htmlentities(substr(utf8_decode($i->texto), 0, 200))."(...)</td>
                        <td class='Center'>$i->index</td>
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}

function RenderEdit() {
	global $msg, $msg_style;
    
    checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
	
	if ($id) {			
		$manager = new FrontEndManager();
		$item = $manager->getItem($id);
	}
	if (!isset($item)) { //new produto
		$item = new ContentItem();
		$item->id = 0;
		$item->title = 'Novo item';
	}
		
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Conteúdos', 'frontendmanager.php', 'Voltar para Conteúdos', 'undo');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    if ($msg) MessageBox::Render($msg, $msg_style);
    			
	echo "<h1>$item->title</h1>			
				
				<form action='frontendmanager.php?id=$item->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='save' />
					<input type='hidden' name='id' id='id' value='$item->id' />				
					
					<h2>Informações do Item</h2>
					<table class='Form'>
						<tr>
							<td width='110px' class='Field'>Título</td>
							<td><input type='text' name='title' id='title' value='".getPost('title', $item->title, true)."' size='85' maxlength='150' /></td>
						</tr>
						<tr>
							<td width='110px' class='Field'>SubTítulo</td>
							<td><input type='text' name='subtitle' id='subtitle' value='".getPost('subtitle', $item->subtitle, true)."' size='85' maxlength='150' /></td>
						</tr>
                        
						<tr>
							<td width='110px' class='Field'>URL</td>
							<td><input type='text' name='url' id='url' value='".getPost('url', $item->url, true)."' size='85' maxlength='255' /></td>
						</tr>
						<tr>
							<td width='110px' class='Field'>Label URL</td>
							<td><input type='text' name='urllabel' id='urllabel' value='".getPost('urllabel', $item->urllabel, true)."' size='85' maxlength='45' /></td>
						</tr>
						<tr>
							<td width='110px' class='Field'>Ordem</td>
							<td><input type='text' name='index' id='index' value='".getPost('index', $item->index, true)."' size='3' maxlength='3' /></td>
						</tr>	
                        						
						<tr>
							<td class='Field'>Tipo</td>
							<td>";
								ListItemPicker::Render('tipoid', 'contents_tipos', getIntPost(' tipoid', $item->tipoid, true)); echo "
							</td>
						</tr>
						<tr>
							<td class='Field'>Texto</td>
							<td><textarea name='texto' id='texto' cols='85' rows='15'>".getPost('texto', $item->texto, true)."</textarea>
                                <div id='dialogPreview' class='Hidden'>
            						<div id='main'>
            							<div id='content'>
            							</div>
            						</div>
            					</div>
                            </td>
						</tr>
					</table>
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
			             Button::Render(null, 'Visualizar Descrição', '#', 'Visualizar prévia da descrição do curso', 'search', true, 'regular', "return previewTexto();");
						Button::Render(null, 'Voltar', 'frontendmanager.php', 'Voltar para Conteúdos', 'undo'); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					//vld.addValidation('title', 'req', 'Título obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
                    
                    function previewTexto() {
        				$('#dialogPreview #main #content').html($('#texto').val())
        				$('#dialogPreview').dialog({
        						show: 'fade',
        						hide: 'fade',
        						height: 450, width:700, 
        						modal: true
        						});
        				//alert($('#dialogPreview').html());
        				
        				return false;					
        			}
				</script>";			
}


function saveItem() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);

	$i = new ContentItem();
	$i->id = $id;
	$i->title = getPost('title', null);
 	$i->subtitle = getPost('subtitle', null);
    $i->texto = getPost('texto', null);
 	$i->url = getPost('url', null);
    $i->urllabel = getPost('urllabel', null);
    $i->index = getIntPost('index', 1, true);
    $i->tipoid = getIntPost('tipoid', 1, true);
 	
 	$manager = new FrontEndManager();
 	if ($ret = $manager->save($i)) {
 		$msg = 'Item salvo com sucesso';
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = 'Erro ao salvar conteúdo. '.$manager->error;
        $msg_style = 'Error';
 		return false;
 	}
}
?>