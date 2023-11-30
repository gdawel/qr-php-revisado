<?php
$pageTitle = 'SOBRARE Cockpit | Home';
include_once '../App_Code/User.class';
include_once '../App_Code/Pesquisa.class';
include_once '../Controls/pagination.ctrl.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once 'admin.index.php';
include_once 'associado.index.php';

Users::checkAuth('Gestor,Admin,Associado', 'login.php');
include_once '../MasterPageCockpit.htm.php';
$msg = ''; $msg_style = 'Info';

function Router() {	
	$action = getPost('action', null);
	
	switch ($action) {
		case 'deletepesquisa':
			DeletePesquisa();
			RenderDefault();
		
		default:
			RenderDefault();
	}	
}

function RenderDefault() {
	global $msg, $msg_style; 

	if ($msg) MessageBox::Render($msg, $msg_style);

	$usr = Users::getCurrent();
	if (($usr->isinrole('Gestor')) || ($usr->isinrole('Admin'))) GestorIndex();
	if ($usr->isinrole('Associado')) AssociadoIndex();
	if ($usr->isinrole('Admin')) AdminIndex();
}

function GestorIndex() {
	global $msg, $msg_style;
	
	//Filter
	$filter = new Filter();
	$s_pesquisa = $filter->addFromPost('p.Titulo', 'LIKE','s_pesquisa');
	$s_respondente = $filter->addFromPost('p.PesquisaId', 'IN', 's_respondente', "(SELECT q.PesquisaId FROM questionarios q WHERE q.Nome LIKE '%%%s%%')");
	$s_status = $filter->addFromPost('p.StatusId', 'IN', 's_status_cockpit_index', '(%s)', '1,4');
        $s_gestor = $filter->addFromPost('u.Nome', 'LIKE','s_gestor');
	if ($s_status == '1,4') $s_status = '0'; //corrigir para o ListItemPicker.value;
	
	//echo $filter->expression;
	
	//Data
	$cockpit = new Pesquisas();
	$quests = new Questionarios();
	$page = getIntQueryString('page', 1, true);
	$pagesize = 10;	
	$lst = $cockpit->MinhasPesquisas($page, $pagesize, null, $totalrows, $filter);
	if ($totalrows) $countmsg = "($totalrows itens encontrados)"; else $countmsg = '';
	$usr = Users::getCurrent();


	//Action buttons
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Nova Pesquisa', 'pesquisa_create.php', 'Criar nova pesquisa', 'add'); echo "
			</div>";
				
	echo "<h1>Área do Gestor</h1>";

	//Creditos
	echo "<h2>Meus Saldos</h2>
		<p>Visualize <a href='creditos_saldo.php'>aqui</a> seu saldo de créditos do QUEST_Resiliência. 
		</p>";


	//Minhas pesquisas
	echo "<h2>Minhas Pesquisas</h2>
			<div class='InfoBox'>
				<a href='#' class='CloseButton' onclick=\"javascript:return closeInfoBox();\" title='Fechar esta mensagem'>[X]</a>
				<p>Esta área é destinada para o acompanhamento e visualização de suas pesquisas.</p> 
				<p>Clique no ícone <img src='../Images/icon-info.png' alt='Ícone Exibir' /> e acesse detalhadamente as informações e o andamento de cada pesquisa.</p>
			</div>
			<fieldset>
				<legend>Filtros <span class='FieldsetMsg'>$countmsg</span></legend>
				<form id='frm' name='frm' method='post' action='index.php'>
					<input type='hidden' name='action' id='action' value='0' />
					<input type='hidden' name='id' id='id' value='0' />
				
					<table class='Form'>
						<tr class='Field'>
							<td>Pesquisa</td>
							<td>Respondente</td>
                                                        <td>Gestor</td>";
							if ($usr->isinrole('Admin')) echo "<td>Status da Pesquisa</td>";
	echo "													
							<td rowspan='2' class='SearchButtonCell'>
								<div class='Buttons'>";
								Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frm'); echo "
								</div>
							</td>
						</tr>
						<tr>
							<td><input type='text' id='s_pesquisa' name='s_pesquisa' value='$s_pesquisa' size='25' /></td>
							<td><input type='text' id='s_respondente' name='s_respondente' value='$s_respondente' size='25' /></td>
                                                        <td><input type='text' id='s_gestor' name='s_gestor' value='$s_gestor' size='25' /></td>";
							if ($usr->isinrole('Admin')) {
								echo "<td>"; 
								ListItemPicker::Render('s_status_cockpit_index','pesquisas_status', getPost('s_status_cockpit_index', $s_status, true), true, null, '0', 'Ativa ou Encerrada'); 
								echo "</td>";
							}
	echo "											
						</tr>
					</table>
				</form>
			</fieldset>
			
			<script type='text/javascript'>
				function submitDeletePesquisaForm(id) {
					var frm = document.getElementById('frm');
					var ctrl_id = document.getElementById('id');
					var ctrl_action = document.getElementById('action');
					
					if (frm) {
						ctrl_id.value = id;
						ctrl_action.value = 'deletepesquisa';
						frm.submit();	
					}
					return false;
				}
			</script>";
	
	if ($lst) {
		echo "<table class='List'>
						<tr>
							<th width='3%'></th>
							<th>Título</th>
							<th>Pacote</th>";
							if ($usr->isinrole('Admin')) echo "<th>Gestor</th>";
		echo "			<th width='150px'>Progresso</th>
							<th width='100px'>Status</th>
						</tr>";
		foreach ($lst as $p) {
			echo "<tr style=\"height:45px;\">
							<td>
								<a href='pesquisa.php?id=$p->id' title='Exibir detalhes desta pesquisa'><img src='../Images/icon-info.png' alt='Clique para exibir detalhes desta pesquisa' /></a>";
			echo "		</td>
							<td>$p->titulo";
								if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) echo " <span class='Label' title='Esta é uma pesquisa aglutinadora'>Aglutinadora</span>";
			
								//Exibir respondentes de acordo com o filtro
								if ($s_respondente) {
									$filter_resp = new Filter();
									$filter_resp->add('Nome', 'LIKE', $s_respondente);									
									$lst_resp = $quests->listaByPesquisa($p, $filter_resp);
									if ($lst_resp) {
										echo "<ul>";
										foreach ($lst_resp as $q) {
											echo "<li>
														<a href='quest.php?id=$q->id' title='Exibir detalhes deste questionário'>
															$q->nome									
														</a>
													</li>";
										}
										echo "</ul>";
									}
								}				
							
			echo "		</td>
							<td>";
								echo $p->pacote->nome;
			echo "		</td>";
							if ($usr->isinrole('Admin')) echo "<td class='Top'>$p->pesquisador</td>";
			echo "		<td>
								$p->count_questionarios questionários"; echo 
									($p->count_concluidos == 0) ? '<small>&nbsp;&nbsp;<li>Nenhum concluído</li>' : '<small>&nbsp;&nbsp;<li>' . $p->count_concluidos . ' concluídos</li></small>'; echo
									($p->count_emandamento == 0) ? '' : '<small>&nbsp;&nbsp;<li>' . $p->count_emandamento . ' em andamento</li></small>'; echo "								
							</td>
							<td class='Center'><span class='StatusPesquisa$p->statusid'>$p->status</span></td>
						</tr>";
		}
		echo "</table>";
		
		Pagination::Render('index.php?page=%s', $totalrows, $pagesize, $page);
	} else {
		echo '<p>Nenhum item encontrado.</p>';
	}	
}

function DeletePesquisa() {
	global $msg, $msg_style;
	
	$id = getIntPost('id', 0, true);
	if (!$id) {
		$msg = 'Pesquisa inválida';
		$msg_style = 'Error';
		return false;
	} 
	
	$pesquisas = new Pesquisas();
	$p = $pesquisas->item($id);
	if (!$p) {
		$msg = 'Pesquisa inválida';
		$msg_style = 'Error';
		return false;
	}	
	
	if ($p->Cancelar()) {
		$msg = 'Pesquisa cancelada com sucesso';
		$msg_style = 'Info';
		return true;
	}
	else {
		$msg = $p->error;
		$msg_style = 'Error';
		return false;
	}
}
?>