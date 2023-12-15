<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Inscrições';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Inscricao.class.php';
include_once '../App_Code/Curso.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/inscricoes.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';

function Router() {
	global $msg;
	$action = getPost('action', null) ? getPost('action') : getQueryString('action');	

	if (isPageRefresh()) {
		RenderDefault();
		return;
	}
	
	switch ($action) {
		case 'change_status':
			ChangeStatus();
			RenderDefault();
			break;
		
        case 'delete_inscricao':
			DeleteInscricao();
			RenderDefault();
			break;
            		
        case 'reenviarSolicitacaoPagamento':
            ReenviarSolicitacaoPagamento();
            RenderDefault();
            break;
        	
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg, $msg_style;
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);		
	
	//get curso
	$cursoId = getIntQueryString('cursoId', 0);
	$cursos = new Cursos();
	$curso = $cursos->Item($cursoId);
	if (!$curso) {
		echo "<h1>Inscrições</h1>";
		echo "<p>Curso inválido.</p>";
		return;
	}
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Cursos', "cursos.php", 'Ir para a página dos Cursos', 'undo');
				Button::RenderNav('Exportar Inscritos', "export_curso_inscritos.php?cursoId=$cursoId", 'Exportar inscritos', 'excel'); 
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
	
	echo "<h1>Inscrições</h1>";
	echo "<h2>$curso->nome</h2>";
	
	//filter
	$filter = new Filter();
	//status
	$s_status = getPost('s_status', '-1');
	if ($s_status != -1) $filter->add('ci.StatusId', '=', $s_status);
	//curso
	if ($cursoId) $filter->add('ci.CursoId', '=', $cursoId);
	
	//get data
	$inscricoes = new Inscricoes();
	$lst = $inscricoes->Items($filter);

	echo "<fieldset>
				<legend>Filtros</legend>
				<form id='frm' name='frm' method='post' action='inscricoes.php?cursoId=$cursoId'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='0' />
					<input type='hidden' name='id' id='id' value='0' />
					<input type='hidden' name='value' id='value' value='0' />
										
					<table class='Form'>
						<tr class='Field'>
							<td>Status</td>
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td>"; echo ListItemPicker::Render('s_status', 'cursos_inscricoes_status', getPost('s_status', '-1'), true, null, '-1', '(Todos)'); echo "</td>							
						</tr>
					</table>
				</form>
			</fieldset>			
			";
	
	echo "<script type='text/javascript'>
				function Action(id, action) {
					document.getElementById('id').value = id;
					document.getElementById('action').value = action;
					
					document.getElementById('frm').submit();
					return false;
				}
				
				function showEdit(id, show) {
					if (show) {
						$('#cmdEdit'+id).hide();
						$('#lbStatus'+id).hide();
						$('#cmdSave'+id).show();
						$('#cmdBack'+id).show();
						$('#divStatus'+id).show();
					} else {						
						$('#cmdEdit'+id).show();
						$('#lbStatus'+id).show();
						$('#cmdSave'+id).hide();
						$('#cmdBack'+id).hide();
						$('#divStatus'+id).hide();
					}
					
					return false;
				}
				
				function submitChangeStatus(id) {
					document.getElementById('id').value = id;
					document.getElementById('action').value = 'change_status';
					document.getElementById('value').value = $('#lstStatus'+id).val();
					
					document.getElementById('frm').submit();
					return false;
				}
                
                function submitDeleteInscricao(id) {
					document.getElementById('id').value = id;
					document.getElementById('action').value = 'delete_inscricao';
					
					document.getElementById('frm').submit();
					return false;
				}
			</script>";		
			
	if ($lst) {
		echo "<table class='List'>
					<tr>
						<th></th>
                        <th>Id</th>
						<th>Nome</th>
						<th>Telefones</th>
						<th>Email</th>
						<th>Data Inscrição</th>
                        <th>Valor</th>
						<!--<th>Condição Pagamento</th>-->
						<th>Status</th>
                        <th></th>
					</tr>";
		foreach ($lst as $i) {
			echo "<tr>
						<td>
                            <!--
							<a id='cmdEdit$i->id' href='#' onclick=\"javascript:return showEdit($i->id, true);\" title='Editar inscrições'><img src='../images/icon-edit.png' alt='Editar inscrições' /></a>							
							<a id='cmdSave$i->id' class='Hidden' href='#' onclick=\"javascript:return submitChangeStatus($i->id);\" title='Salvar alterações'><img src='../images/icon-save.png' alt='Salvar alterações' /></a>
							<a id='cmdBack$i->id' class='Hidden' href='#' onclick=\"javascript:return showEdit($i->id, false);\" title='Cancelar alterações'><img src='../images/icon-undo.png' alt='Cancelar alterações' /></a>
                            -->
                            <a id='cmdDelete$i->id' href='#' onclick=\"javascript:if (confirm('Deseja realmente cancelar esta inscrição?')) return submitDeleteInscricao($i->id); else return false;\" title='Cancelar esta inscrição'><img src='../Images/icon-delete.png' alt='Excluir esta inscrição' /></a>
						</td>
                        <td>#$i->id</td>
						<td>";
    						switch ($i->tipoid) {
    							case INSCRICAO_TIPO_ASSOCIADO:
    								echo htmlspecialchars($i->responsavel->nome);
    								echo " <span class='Label'>Associado</span><br />";
    								break;
    								
    							case INSCRICAO_TIPO_JURIDICA:
    								echo "$i->razaosocial<br /><small>
                                          <b>Responsável:</b> ".htmlspecialchars($i->responsavel->nome)."";
    								
    								echo "<br /><b>".count($i->participantes)." Inscritos:</b><br /><ul>";
    								foreach ($i->participantes as $p) {
    									echo "<li>".htmlspecialchars($p->nome)." | ".htmlspecialchars($p->email);
    									if ($p->userid) echo " <span class='Label'>Associado</span>";
    									echo "</li>";
    								}
    								echo "</ul></small>";
    								break;
                                    
                                default:
    								echo htmlspecialchars($i->responsavel->nome).'<br />'; 
    								break;
    						}
                        
                            //modulos
                            if ($i->modulos) {
                                echo "<small><b>Módulos:</b>
                                <ul>";
                                foreach ($i->modulos as $m) {
                                    echo "<li>$m->nome</li>";
                                }
                                echo "</ul></small>";                                
                            }
			echo "	    </td>
						<td class='Center NoWrap'>".htmlspecialchars($i->responsavel->telefonecomercial)."<br />".htmlspecialchars($i->responsavel->celular)."</td>
						<td class='Center'>".htmlspecialchars($i->responsavel->email)."</td>
						<td class='Center'>".format_date($i->createddate).'<br />'.format_time($i->createddate)."</td>
                        <td class='Center'>".number_format($i->valor, 2, ',', '.')."</td>
						<!--<td class='Center'>$i->condicaopagamento</td>-->
						<td class='Center'>
							<span id='lbStatus$i->id' class='StatusInscricao$i->statusid'>$i->status</span>
                                  
							<div id='divStatus$i->id' class='Hidden'>";
								echo ListItemPicker::Render("lstStatus$i->id", 'cursos_inscricoes_status', $i->statusid);
			echo "		</div>
						</td>
                        <td>";
                            if ($i->allowReenviarSolicitacaoPagamento())
                                echo "<a href='#' onclick=\"javascript:Action($i->id, 'reenviarSolicitacaoPagamento'); return false;\" title='Reenviar solicitação de pagamento'><img src='../images/icon-change.png' alt='Reenviar solicitação de pagamento' /></a>";
            
            echo "      </td>    
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}


function ChangeStatus() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);	
	$new_status = getIntPost('value', 0);
 	
 	if (!$new_status) {
 		$msg = 'Status inválido!';
 		$msg_style = 'Error';
 		return false;
 	}
 	
 	$inscricoes = new Inscricoes();
 	if ($ret = $inscricoes->changeStatus($id, $new_status)) {
 		$msg = 'Status alterado com sucesso.';
 		$msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $inscricoes->error;
 		$msg_style = 'Error';
 		return false;
 	}
}

function DeleteInscricao() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);	
 		
 	$inscricoes = new Inscricoes();
 	if ($ret = $inscricoes->changeStatus($id, INSCRICAO_STATUS_CANCELADO)) {
 		$msg = 'Inscrição cancelada com sucesso.';
 		$msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $inscricoes->error;
 		$msg_style = 'Error';
 		return false;
 	}
}

function ReenviarSolicitacaoPagamento() {
    global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);	
	
    $inscricoes = new Inscricoes();
    if ($inscricoes->sendEmailNovaSoliciticaoPagamento($id)) {
        $msg = 'Solicitação de pagamento reenviada com sucesso.';
 		$msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $inscricoes->error;
 		$msg_style = 'Error';
 		return false;
    }
        
}
?>