<?php
$pageTitle = 'SOBRARE Cockpit | Home';
include_once '../App_Code/User.class';
include_once '../App_Code/Pesquisa.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';
include_once '../Controls/pesquisa_image.ctrl.php';

Users::checkAuth('Gestor,Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';

function Router() {
	global $msg, $msg_style, $id;
	
	$action = getPost('action', null);
	//echo "action = $action"; 
	
	//Check for page refresh
	if (isPageRefresh()) {
		//echo "isPageRefresh";
		RenderDefault();
		return;	
	}
	 
	switch ($action) {
		case 'sendnotification':
			sendNotification();
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'updateTitulo':
			updateInfo('Titulo');
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'updatePublico':
			updateInfo('Publico');
			updatePageRefreshChecker();
			RenderDefault();
			break;
			
		case 'updateFinalidade':
			updateInfo('Finalidade');
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'updateGestor':
			updateInfo('Gestor');
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'updateProdutos':
			updateProdutos();
			updatePageRefreshChecker();
			RenderDefault();
			break;

		case 'QtdeIncluir':
			updateQtdeQuest(1);
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'QtdeExcluir':
			updateQtdeQuest(2);
			updatePageRefreshChecker();
			RenderDefault();
			break;
					
		case 'upload_image':
			uploadImage();
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'Encerrar':
			encerrarPesquisa();
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'Ativar':
			ativarPesquisa();
			updatePageRefreshChecker();
			RenderDefault();
			break;

		case 'RemoveQuest':
			removeQuest();
			updatePageRefreshChecker();
			RenderDefault();
			break;
		
		case 'Aglutinar':
			aglutinarQuests();
			updatePageRefreshChecker();
			RenderDefault();
			break;
												
		default:
			RenderDefault();	
	}	
}

function RenderDefault() {
	global $msg, $msg_style, $id;
	
	$p = checkPermission();
	if (!$p) return false;
	
	//Render message box
	if (isset($msg)) MessageBox::Render($msg, $msg_style);	
	$usr = Users::getCurrent();
	
	echo "<div class='Buttons NavButtons'>";
				if ($usr->isinrole('Admin')) {
					if ($p->statusid != PESQUISA_STATUS_CANCELADA) Button::Render(null, 'Cancelar Pesquisa', '#', 'Cancela a pesquisa atual', 'delete', true, 'negative', "if (confirm('Deseja realmente cancelar esta pesquisa?')) return cancelarPesquisa(); else return false;");
					if ($p->statusid != PESQUISA_STATUS_ATIVA) Button::Render(null, 'Ativar Pesquisa', '#', 'Ativa a pesquisa atual', 'change', true, 'positive', "submitUpdatePesquisaForm('Ativar')");
				}
				if ($p->statusid == PESQUISA_STATUS_ATIVA) Button::Render(null, 'Encerrar Pesquisa', '#', 'Encerra a pesquisa atual. A pesquisa só pode ser ativada novamente pelo Administrador.', 'warning', true, 'regular', "if (confirm('Deseja realmente encerrar esta pesquisa?')) submitUpdatePesquisaForm('Encerrar'); else return false;");				
				Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home');
	echo "
			</div>";
	
	echo "<form enctype='multipart/form-data' action='pesquisa.php?id=$p->id' method='post' id='frm' name='frm'>
				<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
				<input type='hidden' name='action' id='action' value='' />
				<input type='hidden' name='questid' id='questid' value='0' />
				<input type='hidden' name='value' id='value' value='0' />";
		
	echo "<h1>
				$p->titulo
			</h1>

			<div class='InfoBox'>
				<a href='#' class='CloseButton' onclick=\"javascript:return closeInfoBox();\" title='Fechar esta mensagem'>[X]</a>
				<p>Acompanhe detalhadamente o andamento da pesquisa, tendo a possibilidade de editar as informações principais, adicionar uma logomarca aos relatórios e acompanhar os questionários em andamento, concluídos e não iniciados.</p> 
				<p><img src='../Images/icon-info.png' alt='Ícone Exibir' /> - Acesse detalhadamente as informações de cada respondente.</p>
				<p><img src='../Images/icon-pdf.png' alt='Ícone Relatório' /> - Acesse os relatórios desta pesquisa ou de cada respondente.</p>
			</div>			
			<h2>Informações da Pesquisa</h2>	
				<table class='Form' width='100%'>
					<tr>
						<td class='Field' width='100px'>Nome</td>
						<td>
							<div id='TituloReadOnly'>
								<span class='EditBox' title='Clique para alterar' onmouseover=\"$(this).addClass('EditBoxHover');\" onmouseout=\"$(this).removeClass('EditBoxHover');\" onclick=\"javascript:showEditForm('Titulo', true);\">$p->titulo</span>
							</div>
							<div id='TituloEdit' class='Hidden'>
								<input onkeydown=\"javascript:resizeInput(this);\" type='text' id='txtTitulo' size='"; echo strlen($p->titulo);  echo "' value='$p->titulo' class='InlineEdit' />
								<a href=\"javascript:submitUpdatePesquisaForm('Titulo');\"><small>[salvar]</small></a>
								<a href=\"javascript:showEditForm('Titulo', false);\"><small>[cancelar]</small></a>
							</div>
						</td>
						
						<td rowspan='6'>"; 
							PesquisaImage::Render($p->id); echo "
						</td>
					</tr>
					<tr>
						<td class='Field'>Público-Alvo</td>
						<td>
							<div id='PublicoReadOnly'>
								<span class='EditBox' title='Clique para alterar' onmouseover=\"$(this).addClass('EditBoxHover');\" onmouseout=\"$(this).removeClass('EditBoxHover');\" onclick=\"javascript:showEditForm('Publico', true);\">
									$p->publico
								</span> 
							</div>
							<div id='PublicoEdit' class='Hidden'>
								<input onkeydown=\"javascript:resizeInput(this);\" type='text' id='txtPublico' size='"; echo strlen($p->publico);  echo "' value='$p->publico' class='InlineEdit' />
								<a href=\"javascript:submitUpdatePesquisaForm('Publico');\"><small>[salvar]</small></a>
								<a href=\"javascript:showEditForm('Publico', false);\"><small>[cancelar]</small></a>
							</div>
						</td>
					</tr>
					<tr>
						<td class='Field'>Finalidade</td>
						<td>
							<div id='FinalidadeReadOnly'>
								<span class='EditBox' title='Clique para alterar' onmouseover=\"$(this).addClass('EditBoxHover');\" onmouseout=\"$(this).removeClass('EditBoxHover');\" onclick=\"javascript:showEditForm('Finalidade', true);\">
									$p->finalidade
								</span>
							</div>
							<div id='FinalidadeEdit' class='Hidden'>
								<input onkeydown=\"javascript:resizeInput(this);\" type='text' id='txtFinalidade' size='"; echo strlen($p->finalidade);  echo "' value='$p->finalidade' class='InlineEdit' />
								<a href=\"javascript:submitUpdatePesquisaForm('Finalidade');\"><small>[salvar]</small></a>
								<a href=\"javascript:showEditForm('Finalidade', false);\"><small>[cancelar]</small></a>
							</div>							
						</td>
					</tr>";
					
					if ($usr->isinrole('Admin')) {
						echo "<tr>
									<td class='Field'>Gestor</td>
									<td>
										<div id='GestorReadOnly'>
											<span class='EditBox' title='Clique para alterar' onmouseover=\"$(this).addClass('EditBoxHover');\" onmouseout=\"$(this).removeClass('EditBoxHover');\" onclick=\"javascript:showEditForm('Gestor', true);\">
												$p->pesquisador
											</span>											
										</div>
										<div id='GestorEdit' class='Hidden'>"; 
											ListItemPicker::Render('lstGestores', 'gestores', $p->pesquisadorid, true, $p->pesquisadorid); echo "
											<a href=\"javascript:submitUpdatePesquisaForm('Gestor');\"><small>[salvar]</small></a>
											<a href=\"javascript:showEditForm('Gestor', false);\"><small>[cancelar]</small></a>
										</div>
									</td>
								</tr>";
					}	
					
	echo "		<tr>
						<td class='Field'>Status</td>
						<td>
							<span class='StatusPesquisa$p->statusid'>$p->status</span>
						</td>
					<tr>
						<td class='Field'>Questionários</td>
						<td>
							<div id='QtdeReadOnly'>
								<span class='EditBox' title='Clique para alterar' onmouseover=\"$(this).addClass('EditBoxHover');\" onmouseout=\"$(this).removeClass('EditBoxHover');\" onclick=\"javascript:showEditForm('Qtde', true);\">
									$p->count_questionarios questionários"; if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) echo " aglutinados"; echo "
								</span>"; echo 
									'<ul><li><small>&nbsp;&nbsp;' . (($p->count_concluidos == 0) ? 'Nenhum concluído' :  $p->count_concluidos . ' concluídos') . '</small></li>'; echo
									'<li><small>&nbsp;&nbsp;' . (($p->count_emandamento == 0) ? 'Nenhum em andamento' :  $p->count_emandamento . ' em andamento') . '</small></li></ul>'; echo "
							</div>
							<div id='QtdeEdit' class='Hidden'>";
								if ($p->tipoid == PESQUISA_TIPO_NORMAL) {
									echo "
											$p->count_questionarios questionários
											<small>
												<ul>
												 	<li>
													 	Incluir &nbsp;<input type='text' id='txtQtdeIncluir' size='3' class='InlineEdit' alt='999' /> questionários
												 		<a href=\"javascript:submitUpdatePesquisaForm('QtdeIncluir');\">[incluir]</a>
												 	</li>
													<li>
													 	Excluir <input type='text' id='txtQtdeExcluir' size='3' class='InlineEdit' alt='999' /> questionários
												 		<a href='#' onclick=\"javascript:if (confirm('Desejar realmente excluir a quantidade informada de questionários?')) {submitUpdatePesquisaForm('QtdeExcluir'); return true;} else {return false;}\">[excluir]</a>
												 	</li>
											 	</ul>
										 	</small>
											<a href=\"javascript:showEditForm('Qtde', false);\"><small>[cancelar]</small></a>
											";
								} else if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
									echo "
											$p->count_questionarios questionários aglutinados
											<small>
												<ul>
												 	<li>
													 	Aglutinar os questionários &nbsp;<input type='text' id='txtQtdeIncluir' size='30' class='InlineEdit' />
												 		<a href=\"javascript:submitUpdatePesquisaForm('Aglutinar');\">[aglutinar]</a>
												 		<a href=\"javascript:showEditForm('Qtde', false);\">[cancelar]</a>
												 	</li>
											 	</ul>
										 	</small>											
											";
								}
	echo "							
							</div>
						</td>
					</tr>
					<tr>
						<td class='Field'>Produtos</td>
						<td>
							<div id='produtosReadOnly' class='ProdutosAdquiridos'>";
                                                            echo "<div class='Buttons'>";
                                                                if ($usr->isinrole('Admin')) {
                                                                    Button::Render(null, 'Exportar Tabela de Índices', "export_pesquisa_resultados.php?id=$p->id&tipo=1", 'Exportar resultados (índices) desta pesquisa', 'excel', true, 'regular', null, '_blank');
                                                                    Button::Render(null, 'Exportar Tabela de Categorias', "export_pesquisa_resultados.php?id=$p->id&tipo=2", 'Exportar resultados (categorias) desta pesquisa', 'excel', true, 'regular', null, '_blank');

                                                                    //Button::Render(null, 'Exportar Respostas', "export_pesquisa_respostas.php?id=$p->id", 'Exportar respostas dos questionários desta pesquisa', 'excel');

                                                                    Button::Render(null, 'Exportar Infos Questionários', "export_pesquisa_dados_respostas.php?id=$p->id", 'Exportar dados sóciodemográficos e respostas desta pesquisa', 'excel', true, 'regular', null, '_blank');
                                                                    Button::Render(null, 'Exportar Legenda', "export_pesquisa_legenda.php", 'Exportar legenda dos dados sóciodemográficos', 'excel', true, 'regular', null, '_blank');
                                                                }
                                                            echo "</div>";
	echo "					<hr class='clear' />";
								echo "<div class='Buttons'>";
									if ($p->produtos) {									
										foreach ($p->produtos as $produto) { 
											if ($produto->porpacote) {
												Button::Render(null, $produto->nome, "../Quest/report_prod_$produto->id.php?id=$p->id", $produto->descricao, "pdf", true, 'regular', null, '_blank');
											}
										}
									}
									if ($usr->isinrole('Admin')) {
                                                                            echo " <a href=\"javascript:showEditForm('produtos', true);\" title='Incluir ou remover produtos desta pesquisa'><small>[adicionar/remover produtos]</small></a>";
                                                                        }
								echo "</div>";
	echo "							
							</div>
							<div id='produtosEdit' class='Hidden'>";
								$produtos = new Produtos();
								$filter = new Filter();								
								$filter->add('p.Enabled', '=', 1);								
								$lst = $produtos->getProdutos($filter);
								if ($lst) {
									foreach ($lst as $produto) {
										$checked = ($p->isProdutoAdquirido($produto->id) ? "checked" : "");
										echo "<input type='checkbox' name='chkProduto' value='$produto->id' $checked />&nbsp;<span>$produto->nome</span><br />";
									}
								}								
	echo "					<a href=\"javascript:submitUpdatePesquisaForm('Produtos');\"><small>[salvar]</small></a>
								<a href=\"javascript:showEditForm('produtos', false);\"><small>[cancelar]</small></a>										
							</div>
						</td>
					</tr>
				</table>
			</form>
			
			<form action='index.php' method='post' name='frmCancelarPesquisa' id='frmCancelarPesquisa'>
				<input type='hidden' name='id' id='frmCancelarPesquisaId' value='$p->id' />
				<input type='hidden' name='action' id='frmCancelarPesquisaAction' value='deletepesquisa' />
			</form>
			
			<script type='text/javascript'>
				function resizeInput(ctrl)
				{
				   ctrl.size = 1 + ctrl.value.length;
				}
				
				function cancelarPesquisa() {
					var frmAction = document.getElementById('frmCancelarPesquisa');
					if (frmAction) frmAction.submit();
				}
			
				function removeQuest(questId) {
					var frmAction = document.getElementById('frm');
					var ctrlAction = document.getElementById('action');
					var ctrlQuestId = document.getElementById('questid');
					
					ctrlQuestId.value = questId;
					ctrlAction.value = 'RemoveQuest';
					frmAction.submit();
					
					return false;
				}
				
				function submitUpdatePesquisaForm(fieldName) {
					var txt = '';
					
					var frmAction = document.getElementById('frm');
					var ctrlAction = document.getElementById('action');
					var ctrlValue = document.getElementById('value');
					
					switch (fieldName) {
						case 'Image':
							ctrlAction.value = 'upload_image';
							txt = 'upload_image';
							break; 							
						case 'Titulo':
							ctrlAction.value = 'updateTitulo';
							txt = document.getElementById('txtTitulo').value;
							break; 
						case 'Publico':
							ctrlAction.value = 'updatePublico';
							txt = document.getElementById('txtPublico').value;
							break;	
						case 'Finalidade':
							ctrlAction.value = 'updateFinalidade';
							txt = document.getElementById('txtFinalidade').value;
							break;
						
						case 'Gestor':
							ctrlAction.value = 'updateGestor';
							txt = document.getElementById('lstGestores').options[document.getElementById('lstGestores').selectedIndex].value;
							break;
						
						case 'QtdeIncluir':
							ctrlAction.value = 'QtdeIncluir';
							txt = document.getElementById('txtQtdeIncluir').value;
							if (txt == '') {
								alert('Informe a quantidade desejada.');
								return;
							}
							break;
						
						case 'Aglutinar':
							ctrlAction.value = 'Aglutinar';
							txt = document.getElementById('txtQtdeIncluir').value;
							if (txt == '') {
								alert('Informe os ID\'s dos questionários.');
								return;
							}
							break;				
						
						case 'QtdeExcluir':
							ctrlAction.value = 'QtdeExcluir';
							txt = document.getElementById('txtQtdeExcluir').value;
							if (txt == '') {
								alert('Informe a quantidade desejada.');
								return;
							}
							break;
								
						case 'Produtos':
							ctrlAction.value = 'updateProdutos';
							var produtosSelecionados = document.getElementsByName('chkProduto');
							if (produtosSelecionados) {
								for (var i = 0; i < produtosSelecionados.length; i++) {
					            var obj = document.getElementsByName('chkProduto').item(i);
					            if (obj.checked) {
					            	if (txt!='') txt = txt + ',';
			            			txt = txt + obj.value;
			            		}
								}
							}
							txt = txt + ' ';
							break;
							
						case 'Encerrar':
							txt = 'Encerrar';
							ctrlAction.value = 'Encerrar';
							break;
						
						case 'Ativar':
							txt = 'Ativar';
							ctrlAction.value = 'Ativar';
							break;
								
						default:
							return;
					}		
					if (!txt) return;
										
					if (frmAction) {			
						ctrlValue.value = txt;
						frmAction.submit();	
					}
					return true;
				}
				
				function showEditForm(divId, show) {
					if (show) {
						$('#' + divId +'ReadOnly').hide();
						$('#' + divId +'Edit').show();
					} else {
						$('#' + divId +'ReadOnly').show();
						$('#' + divId +'Edit').hide();
					}	
					$('#txt' + divId).focus();
				}	
			</script>";


	echo "<h2>Questionários Em Andamento</h2>";						
	$lst = $p->getQuestionarioListaBasicaByStatus(QUESTIONARIO_STATUS_EMANDAMENTO);
	
	if ($lst) {
		echo "<table class='List'>
						<tr>
							<th style=\"width:15%;\"></th>
							<th width='10%'>Código</th>
							<th>Nome</th>
							<th width='30%'>Status</th>
						</tr>";
						
		foreach ($lst as $q) {
			echo "<tr>
							<td>
								<a href='quest.php?id=$q->id&aglutinadoraId=$p->id' title='Exibir detalhes deste questionário'>
									<img src='../Images/icon-info.png' alt='Detalhes' />
								</a>";
								if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
									echo "<a href='#' onclick=\"javscript:if (confirm('Deseja realmente remover este questionário da pesquisa aglutinadora?')) return removeQuest($q->id); else return false;\" title='Remover este questionário da pesquisa aglutinadora'>
												<img src='../Images/icon-remove.png' />
											</a>";
								}
			echo "
							</td>
							<td align='center'><a name='Quest$q->id'>$q->id</a></td>
							<td>$q->nome</td>
							<td>$q->status desde ".format_datetime($q->iniciadoem); echo "</td>
						</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}	
				
	echo "<h2>Questionários Concluídos</h2>";					
	
	$lst = $p->getQuestionarioListaBasicaByStatus(QUESTIONARIO_STATUS_CONCLUIDO);
	
	if ($lst) {
		echo "<table class='List'>
						<tr>
							<th style=\"width:15%;\"></th>
							<th width='10%'>Código</th>
							<th>Nome</th>
							<th width='30%'>Status</th>
						</tr>";
						
		foreach ($lst as $q) {
            if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA)
                $aglutinadoraParams = "&aglutinadoraId=$p->id";
            else 
                $aglutinadoraParams = "";
		      
			echo "<tr>
							<td>
                            
								<a href='quest.php?id=$q->id$aglutinadoraParams' title='Exibir detalhes deste questionário'>
									<img src='../Images/icon-info.png' alt='Detalhes' />
								</a>";
                                 
								if ($p->isProdutoAdquirido(2)) {
								    $produto_nome = $p->produtos[2]->nome;
									echo "<a href='../Quest/report.php?id=$q->id&comentado=1$aglutinadoraParams' title='Exibir \"$produto_nome\" com os resultados do questionário' target='_blank'>
												<img src='../Images/icon-pdf.png' alt='Exibir \"$produto_nome\" com os resultados do questionário' />
											</a>";	
                                }   
								if ($p->isProdutoAdquirido(1)) {
								    $produto_nome = $p->produtos[1]->nome;
									echo "<a href='../Quest/report.php?id=$q->id&comentado=0$aglutinadoraParams' title='Exibir \"$produto_nome\" com os resultados do questionário' target='_blank'>
												<img src='../Images/icon-pdf-half.png' alt='Exibir o \"$produto_nome\" com os resultados do questionário' />
											</a>";
                                }		
								if ($p->isProdutoAdquirido(25)) {
								    $produto_nome = $p->produtos[25]->nome;
									echo "<a href='../Quest/report_prod_25.php?id=$q->id$aglutinadoraParams' title='Exibir \"$produto_nome\" com os resultados do questionário' target='_blank'>
												<img src='../Images/icon-pdf-blue.png' alt='Exibir \"$produto_nome\" com os resultados do questionário' />
											</a>";
								}
                                /*if ($p->isProdutoAdquirido(27)) {
								    $produto_nome = $p->produtos[27]->nome;
									echo "<a href='../Quest/report_mapeamento.php?id=$q->id$aglutinadoraParams' title='Exibir \"$produto_nome\" com os resultados do questionário' target='_blank'>
												<img src='../Images/icon-pdf-orange.png' alt='Exibir \"$produto_nome\" com os resultados do questionário' />
											</a>";
								}*/
                                			
								if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
									echo "<a href='#' onclick=\"javscript:if (confirm('Deseja realmente remover este questionário da pesquisa aglutinadora?')) return removeQuest($q->id); else return false;\" title='Remover este questionário da pesquisa aglutinadora'>
												<img src='../Images/icon-remove.png' />
											</a>";
								}			
			echo "	
							</td>
							<td align='center'><a name='Quest$q->id'>$q->id</a></td>
							<td>$q->nome</td>
							<td>$q->status"; if ($q->status == 'Concluído') {echo ' em '.format_datetime($q->concluidoem);} echo "</td>
						</tr>";
		}
		echo "</table>";
	
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
	
	
	checkPageRefreshSessionVar();
	echo "<h2>Questionários Não Iniciados</h2>
			
			<script type='text/javascript'>
				function submitSendNotificationForm(id) {
					var frmAction = document.getElementById('frm');
					var ctrlId = document.getElementById('questid');
					var ctrlAction = document.getElementById('action');
					var ctrlValue = document.getElementById('value');
					
					if (frmAction) {
						ctrlAction.value = 'sendnotification';
						ctrlId.value = id;
						frmAction.submit();	
					}
					return false;
				}
			</script>";
	
	$lst = $p->getQuestionarioListaBasicaByStatus(QUESTIONARIO_STATUS_NAOINICIADO);
	
	if ($lst) {
		echo "<div id='nao_iniciados'>";
		echo "		
				<table class='List'>
						<tr>							
							<th width='15%'></th>
							<th width='10%'>Código</th>
							<th>Senha</th>
							<th>Nome</th>
							<th>E-mail</th>
						</tr>";
						
		foreach ($lst as $q) {
			if (!$q->nome) $q->nome = '&nbsp;';
			if (!$q->email) $q->email = '&nbsp;';
			
			if ($q->email) 
				$html_notification_button = "<a href='javascript:submitSendNotificationForm($q->id);' title='Enviar notificação para respondente'>
															<img src='../Images/icon-mail.png' alt='Notificação' />
														</a>";
			else $html_notification_button = '';
			
			echo "	<tr>
							<td>
								<a href='quest_addinfo.php?id=$q->id' title='Incluir detalhes básicos deste respondente'>
									<img src='../Images/icon-edit.png' alt='Detalhes' />
								</a>
								$html_notification_button";
								if ($p->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
									echo "<a href='#' onclick=\"javscript:if (confirm('Deseja realmente remover este questionário da pesquisa aglutinadora?')) return removeQuest($q->id); else return false;\" title='Remover este questionário da pesquisa aglutinadora'>
												<img src='../Images/icon-remove.png' />
											</a>";
								}
			echo "
							</td>
							<td align='center'><a name='Quest$q->id'>$q->id</a></td>
							<td>$q->password</td>
							<td>$q->nome</td>
							<td>$q->email</td>
						</tr>";
		}
		echo "</table></div>";
		echo "			
				<script type='text/javascript'>
					/*
					animatedcollapse.addDiv('nao_iniciados', 'fade=0,speed=400,group=pacotes,hide=1');

					animatedcollapse.ontoggle=function($, divobj, state){ //fires each time a DIV is expanded/contracted
						//$: Access to jQuery
						//divobj: DOM reference to DIV being expanded/ collapsed. Use 'divobj.id' to get its ID
						//state: 'block' or 'none', depending on state
						
						
					};
					animatedcollapse.init();
					*/
				</script>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}

function sendNotification() {
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) return false;
	
	$questid = getPost('questid', null);
	$quests = new Questionarios();
	$q = $quests->item($questid);	
	
	if ($q) {
		if ($q->sendNotification()) {
			$msg = 'E-mail enviado com sucesso.';
			$msg_style = 'Info';
			return true; 
		} else {
			$msg = 'Erro ao enviar e-mail de notificação.<br />'.$q->error;
			$msg_style = 'Error';
			return false;;
		}
	} else {
		return false;
	}
}

function updateInfo($infoType) {
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Erro ao atualizar Título da Pesquisa. Permissão negada.';	
		return false;
	}
	
	$pesquisas = new Pesquisas();
	switch ($infoType) {
		case 'Titulo':
			$p->titulo = getPost('value');
			break;
		case 'Publico':
			$p->publico = getPost('value');
			break;
		case 'Finalidade':
			$p->finalidade = getPost('value');
			break;
		case 'Gestor':
			$p->pesquisadorid = getPost('value');
			break;
		default:
			$msg = 'Nenhum atributo selecionado';
			return false;			
	}	
	
	if ($pesquisas->updateInfo($p, $infoType)) {
		$msg = "$infoType atualizado com sucesso";
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao atualizar $infoType.<br />" . $pesquisas->error;
		$msg_style = 'Error';
		return false;
	}
}

function updateProdutos() {
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Erro ao atualizar Produtos. Permissão negada.';	
		return false;
	}
	
	$pesquisas = new Pesquisas();
	$produtos = getPost('value');
	
	if ($pesquisas->updateProdutos($p, $produtos)) {
		$msg = "Produtos atualizados com sucesso";
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao atualizar produtos.<br />" . $pesquisas->error;
		$msg_style = 'Error';
		return false;
	}
}

function updateQtdeQuest($action) {	
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Erro ao atualizar quantidade de questionários. Permissão negada.';	
		return false;
	}
	
	$quests = new Questionarios();
	$qtde = getIntPost('value', 0, true);

	if ($qtde < 1) {
		$msg = 'Quantidade deve ser maior que zero.';
		$msg_style = 'Error';
		return false;
	}
		
	if ($action == 1) {
		if ($quests->addToPesquisa($p, $qtde)) {
			$msg = "Questionários incluídos com sucesso.<br />" . $quests->error;
			$msg_style = 'Info';
			return true;
		} else {
			$msg = "" . $quests->error;
			$msg_style = 'Error';
			return false;
		}
	} else {
		if ($quests->removeFromPesquisa($p->id, $qtde)) {
			$msg = "Questionários excluídos com sucesso.<br />". $quests->error;
			$msg_style = 'Info';
			return true;
		} else {
			$msg = "Erro ao remover questionários.<br />" . $quests->error;
			$msg_style = 'Error';
			return false;
		}
	}
	
	return true;
}


function checkPermission() {
	global $id;
	$id = getIntQueryString('id', '0', true);
	
	if (!$id) {
		echo "<h1>Ooops...</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos a pesquisa solicitada. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}
	$GLOBALS['id'] = $id;
	
	$pesquisas = new Pesquisas();
	$p = $pesquisas->item($id);
	
	if (!$p) {
		echo "<h1>Ooops...</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos a pesquisa solicitada. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}
	
	//Verificar se gestor é o pesquisador
	if ($p->isAccessDenied()) {
		echo "<h1>Acesso negado</h1>
					<p>Você não permissão para visualizar a pesquisa solicitada. Retorne para a <a href='index.php'>Home do Cockpit</a> e 
					selecione a pesquisa desejada.</p>";
		return false;	
	}
	
	return $p;
}


function uploadImage() {
	global $msg, $msg_style, $id;
	
	$pesquisaid = getQueryString('id', null);
	if (!$pesquisaid) {
		$msg = 'Pesquisa inválida';
		$msg_style = 'Error';
		return false;
	}
	
	$ctrl = new PesquisaImage();
	
	if ($ctrl->Save($pesquisaid)) {
		$msg = 'Imagem adicionada';
		$msg_style = 'Info';
		return true;
	} else {
		$msg = $ctrl->error;
		$msg_style = 'Error';
		return false;
	}
}

function encerrarPesquisa() {
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Erro ao encerrar pesquisa.<br />Permissão negada.';	
		return false;
	}
	
	if ($p->Encerrar()) {
		$msg = "Pesquisa encerrada com sucesso";
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao encerrar pesquisa. " . $p->error;
		$msg_style = 'Error';
		return false;
	}
}

function ativarPesquisa() {
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Erro ao ativar pesquisa.<br />Permissão negada.';	
		return false;
	}
	
	if ($p->Ativar()) {
		$msg = "Pesquisa ativada com sucesso";
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao ativar pesquisa.<br />" . $p->error;
		$msg_style = 'Error';
		return false;
	}
}

function removeQuest() {	
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Permissão negada.';	
		return false;
	}
	
	$questid = getIntPost('questid', 0, true);
	
	if ($p->DesaglutinarQuest($questid)) {
		$msg = "Questionário $questid removido da pesquisa aglutinadora.";
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao remover questionário.<br />" . $p->error;
		$msg_style = 'Error';
		return false;
	}
}

function aglutinarQuests() {	
	global $msg, $msg_style;
	
	$p = checkPermission();
	if (!$p) {
		$msg = 'Permissão negada.';	
		return false;
	}
	
	$value = getPost('value', null, true);
	$quests_ids = explode(',', $value);
	
	if (!$quests_ids) {
		$msg = 'Lista de questionários inválida.';
		$msg_style = 'Error';
		return false;
	}
	
	if ($p->AglutinarQuests($quests_ids)) {
		$msg = "Questionários aglutinados na pesquisa.<br />".$p->error;
		$msg_style = 'Info';
		return true;
	} else {
		$msg = "Erro ao aglutinar questionários.<br />" . $p->error." Nenhum questionário foi aglutinado!";
		$msg_style = 'Error';
		return false;
	}
}
?>