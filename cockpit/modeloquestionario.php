<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Modelos de Questionários';
include_once '../App_Code/User.class.php';
include_once '../App_Code/ModeloQuestionario.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/modeloquestionario.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$msg_style = 'Info';

function Router() {
	global $msg, $msg_style;
	$action = getPost('action', null) ? getPost('action', null) : getQueryString('action', null);	

	switch ($action) {
	    case 'Fatores':
			RenderFatores();
			break;
            
		case 'editFator':
			RenderEditFator();
			break;
		
		case 'saveFator':
			if (!isPageRefresh()) {								
				if (saveFator()) RenderFatores();
				else RenderEditFator();
			} else {
				RenderEditFator();
			}
			break;
            
            
        case 'ValoresReferencia':
			RenderValoresReferencia();
			break;
            
		case 'editValorReferencia':
			RenderEditValorReferencia();
			break;
		
		case 'saveValorReferencia':
			if (!isPageRefresh()) {								
				if (saveValorReferencia()) RenderValoresReferencia();
				else RenderEditValorReferencia();
			} else {
				RenderEditValorReferencia();
			}
			break;

        case 'Perguntas':
            RenderPerguntas();
            break;
            
        case 'editPergunta':
            RenderEditPergunta();
            break;
            
        case 'savePergunta':
			if (!isPageRefresh()) {								
				if (savePergunta()) RenderPerguntas();
				else RenderEditPergunta();
			} else {
				RenderPerguntas();
			}
			break;

        case 'GruposPerguntas':
            RenderGruposPerguntas();
            break;

        case 'editGrupoPergunta':
            RenderEditGrupoPergunta();
            break;

        case 'saveGrupoPergunta':
            if (!isPageRefresh()) {
                if (saveGrupoPergunta()) RenderGruposPerguntas();
                else RenderEditGrupoPergunta();
            } else {
                RenderGruposPerguntas();
            }
            break;

		default:
			RenderDefault();
	}
}

function RenderDefault() {
    global $msg, $msg_style;
    
	echo "<div class='Buttons NavButtons'>";
				//Button::RenderNav('Novo Produto', 'produto.php?action=edit', 'Incluir novo produto', 'add');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
			
	echo "<h1>Modelos de Questionários</h1>";
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    
	$modelos = new ModelosQuestionarios();
	$filter = new Filter();
	//status
	//$s_pacote_status = getPost('s_produto_status', '1');
	//if ($s_pacote_status != -1) $filter->add('p.Enabled', '=', $s_pacote_status);
	
	$lst = $modelos->items($filter);
	/*echo "<fieldset>
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
			";*/
			
	if ($lst) {
		echo "<table class='List'>
					<tr>
						<th></th>
						<th>Nome</th>
						<th>Tipo</th>
                        <th>Status</th>
					</tr>";
                    
		foreach ($lst as $modelo) {
			$status = ($modelo->enabled == '1') ? '<span class="Verde">Ativo</span>' : '<span class="Red">Inativo</span>';
			echo "<tr>
						<td>
							<a href='modeloquestionario.php?action=ValoresReferencia&id=$modelo->id' title='Editar os valores de referência dos fatores deste modelo de questionário'>[Valores de Referência]</a>
                            <a href='modeloquestionario.php?action=Perguntas&id=$modelo->id' title='Editar as perguntas deste modelo de questionário'>[Perguntas]</a>
                            <a href='modeloquestionario.php?action=GruposPerguntas&id=$modelo->id' title='Editar os grupos de perguntas deste modelo de questionário'>[Grupos de Perguntas]</a>
                            <a href='modeloquestionario.php?action=Fatores&id=$modelo->id' title='Editar os MCDs deste modelo de questionário'>[MCDs]</a>							
						</td>
						<td>$modelo->nome</td>
                        <td>".$modelo->tipo->nome."</td>
						<td class='Center'>$status</td>
					</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}


function RenderFatores() {
    global $msg, $msg_style;
    
	$modelos = new ModelosQuestionarios();
    $id = getQueryString('id', 0);
    if ($id) $modelo = $modelos->item($id); else $modelo = null;
    if (!$modelo) {
        RenderDefault();
        return;
    }
    
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Modelos', 'modeloquestionario.php', 'Voltar para Modelos de Questionários', 'undo');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    		
	echo "<h1>$modelo->nome</h1>";
	echo "<h2>Modelos de Crença Determinantes</h2>";

    if (!isset($modelo->fatores)) {
        echo "Nenhum MCD cadastrado para este Modelo de Questionário.";
          
    } else {
    
        echo "<table class='List'>
    			<tr>
    				<th width='30px'></th>
    				<th>Nome</th>
    				<th>Fórmula</th>
    			</tr>";
                
        foreach ($modelo->fatores as $f) {		
    		echo "<tr>
    					<td>
    						<a href='modeloquestionario.php?action=editFator&id=$modelo->id&fid=$f->id' title='Editar este modelo de crença determinante'><img src='../Images/icon-edit.png' title='Editar este modelo de crença determinante' /></a>							
    					</td>
                        <td>$f->nome</td>
    					<td class=''>$f->formacalculo</td>
    				</tr>";
    	}
       	
        echo "</table>";
    }
}


function RenderEditFator() {
    global $msg, $msg_style;
    
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
    $fid = getIntQueryString('fid', false, true);
	
	if ($id) {			
		$modelos = new ModelosQuestionarios();
		$modelo = $modelos->item($id);
	}
	if (!isset($modelo)) { 
		$msg = 'Modelo de Questionário inválido.';
        RenderDefault();
        return;
	}
    if (!isset($modelo->fatores[$fid])) {
        $msg = 'Fator inválido para este Modelo de Questionário';
        RenderDefault();
        return;        
    } else {
        $f = $modelo->fatores[$fid];
    }
		
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para MCDs', "modeloquestionario.php?action=Fatores&id=$modelo->id", 'Voltar para Modelo', "undo");
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    			
	echo "<h1>$modelo->nome</h1>
            <h2>MCD '$f->nome'</h2>			
				
				<form action='modeloquestionario.php?action=editFator&id=$modelo->id&fid=$f->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='saveFator' />
					<input type='hidden' name='fatorid' id='fatorid' value='$f->id' />
                    
                    <input type='hidden' name='modeloquestionarioid' id='modeloquestionarioid' value='$modelo->id' />
					
					<table class='Form'>
                        <tr>
                            <td class='Field'>Modelo Questionário</td>
                            <td>$modelo->nome</td>
                        </tr>
                        <tr>
                            <td class='Field'>Fator</td>
                            <td>$f->nome ($f->sigla)</td>
                        </tr>
                        
						<tr>
							<td width='110px' class='Field'>Descrição</td>
							<td>
                                <textarea type='text' cols='100' rows='5' name='descricao' id='descricao'>".getPost('descricao', $f->descricao, true)."</textarea>
                            </td>
						</tr>

						<tr>
							<td width='110px' class='Field'>Descrição Análise Quantitativa</td>
							<td>
                                <textarea type='text' cols='100' rows='5' name='descricaoAnaliseQuantitativa' id='descricaoAnaliseQuantitativa'>".getPost('descricaoAnaliseQuantitativa', $f->descricaoAnaliseQuantitativa, true)."</textarea>
                            </td>
						</tr>

                        <tr>
                            <td class='Field'>Descrição Fortaleza Visão Geral</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='descricaoFortalezaVisaoGeral' id='descricaoFortalezaVisaoGeral'>".getPost('descricaoFortalezaVisaoGeral', $f->descricaoFortalezaVisaoGeral, true)."</textarea>
                            </td>                            
                        </tr>

                        <tr>
							<td width='110px' class='Field'>Descrição Fraca Resiliência PCP</td>
							<td>
                                <textarea type='text' cols='100' rows='5' name='descricaoFracaResilienciaPCP' id='descricaoFracaResilienciaPCP'>".getPost('descricaoFracaResilienciaPCP', $f->descricaoFracaResilienciaPCP, true)."</textarea>
                            </td>
						</tr>
                        <tr>
							<td width='110px' class='Field'>Descrição Fraca Resiliência PCI</td>
							<td>
                                <textarea type='text' cols='100' rows='5' name='descricaoFracaResilienciaPCI' id='descricaoFracaResilienciaPCI'>".getPost('descricaoFracaResilienciaPCI', $f->descricaoFracaResilienciaPCI, true)."</textarea>
                            </td>
						</tr>                       
                        						
                        <tr>
                            <td class='Field'>Descrição Segurança PCP</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='descricaoSegurancaPCP' id='descricaoSegurancaPCP'>".getPost('descricaoSegurancaPCP', $f->descricaoSegurancaPCP, true)."</textarea>
                            </td>                            
                        </tr>
                        <tr>
                            <td class='Field'>Descrição Segurança PCI</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='descricaoSegurancaPCI' id='descricaoSegurancaPCI'>".getPost('descricaoSegurancaPCI', $f->descricaoSegurancaPCI, true)."</textarea>
                            </td>                            
                        </tr>
                                                
                        <tr>
                            <td class='Field'>Descrição Excelente</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='descricaoExcelente' id='descricaoExcelente'>".getPost('descricaoExcelente', $f->descricaoExcelente, true)."</textarea>
                            </td>                            
                        </tr>
					</table>
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::RenderNav('Voltar para MCDs', "modeloquestionario.php?action=Fatores&id=$modelo->id", 'Voltar para Modelo', "undo"); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";			
}


function saveFator() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$fid = getIntPost('fatorid', 0, true);

	$f = new Fator();
	$f->id = $fid;
    $f->modeloquestionarioid = getIntPost('modeloquestionarioid', 0, true);
    $f->descricao = getPost('descricao', null);
	$f->descricaoFracaResilienciaPCP = getPost('descricaoFracaResilienciaPCP', null);
    $f->descricaoFracaResilienciaPCI = getPost('descricaoFracaResilienciaPCI', null);
 	$f->descricaoAnaliseQuantitativa = getPost('descricaoAnaliseQuantitativa', null);
 	$f->descricaoFortalezaVisaoGeral = getPost('descricaoFortalezaVisaoGeral', null);
    $f->descricaoSegurancaPCP = getPost('descricaoSegurancaPCP', null);
    $f->descricaoSegurancaPCI = getPost('descricaoSegurancaPCI', null);
    $f->descricaoExcelente = getPost('descricaoExcelente', null);
 	
 	$modelos = new ModelosQuestionarios();
 	if ($ret = $modelos->saveFator($f)) {
 		$msg = 'Fator salvo com sucesso';
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $modelos->error;
        $msg_style = 'Error';
 		return false;
 	}
}


/*****************************************************/

function RenderValoresReferencia() {
    global $msg, $msg_style;
    
	$modelos = new ModelosQuestionarios();
    $id = getQueryString('id', 0);
    if ($id) $modelo = $modelos->item($id); else $modelo = null;
    if (!$modelo) {
        RenderDefault();
        return;
    }
    
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Modelos', 'modeloquestionario.php', 'Voltar para Modelos de Questionários', 'undo');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    		
	echo "<h1>$modelo->nome</h1>";
	echo "<h2>Valores de Referência</h2>";


    foreach ($modelo->fatores as $f) {		
	    echo "<h3>$f->nome</h3>";
        
        echo "<table class='List'>
				<tr>
					<th width='30px'></th>
					<th>Descrição</th>
					<th>Limites</th>
                    <th>Classificação</th>
				</tr>";
                
    	foreach ($f->valoresreferencia as $vr) {
    		echo "<tr>
    					<td>
    						<a href='modeloquestionario.php?action=editValorReferencia&id=$vr->modeloquestionarioid&vrid=$vr->id' title='Editar este valor de referência'><img src='../Images/icon-edit.png' title='Editar este valor de referência' /></a>							
    					</td>
                        <td>$vr->descricao</td>
    					<td class='Center'>"; echo $vr->faixa(); echo "</td>
    					<td class='Center'>$vr->classificacao</td>
    				</tr>";
    	}
    	echo "</table>";
    }
}

function RenderEditValorReferencia() {
    global $msg, $msg_style;
    
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('vrid', false, true);
	
	if ($id) {			
		$modelos = new ModelosQuestionarios();
		$vr = $modelos->getValorReferencia($id);
	}
	if (!isset($vr)) { //new 
		$vr = new Fator_ValorReferencia();
		$vr->id = 0;
		$vr->classificacao = 'Novo';
	}
		
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=ValoresReferencia&id=$vr->modeloquestionarioid", 'Voltar para Modelo', "undo");
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    			
	echo "<h1>$vr->modeloquestionario</h1>
            <h2>Valor de Referência para '$vr->classificacao'</h2>			
				
				<form action='modeloquestionario.php?action=editValorReferencia&id=$vr->modeloquestionarioid&vrid=$vr->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='saveValorReferencia' />
					<input type='hidden' name='id' id='id' value='$vr->id' />
                    
                    <input type='hidden' name='modeloquestionarioid' id='modeloquestionarioid' value='$vr->modeloquestionarioid' />
                    <input type='hidden' name='fatorid' id='fatorid' value='$vr->fatorid' />
					
					<table class='Form'>
                        <tr>
                            <td class='Field'>Modelo Questionário</td>
                            <td>$vr->modeloquestionario</td>
                        </tr>
                        <tr>
                            <td class='Field'>Fator</td>
                            <td>$vr->fator</td>
                        </tr>
                        
						<tr>
							<td width='110px' class='Field'>Descrição</td>
							<td><input type='text' name='descricao' id='descricao' value='".getPost('descricao', $vr->descricao, true)."' size='45' maxlength='45' /></td>
						</tr>
                        <tr>
							<td width='110px' class='Field'>Limite Inferior</td>
							<td><input type='text' name='limiteinferior' id='limiteinferior' value='".getPost('limiteinferior', $vr->limiteinferior, true)."' size='7' alt='signed-decimal' /></td>
						</tr>
                        <tr>
							<td width='110px' class='Field'>Limite Superior</td>
							<td><input type='text' name='limitesuperior' id='limitesuperior' value='".getPost('limitesuperior', $vr->limitesuperior, true)."' size='7' alt='signed-decimal' /></td>
						</tr>
                        
                        
						<tr>
							<td width='110px' class='Field'>Classificação</td>
							<td><input type='text' name='classificacao' id='classificacao' value='".getPost('classificacao', $vr->classificacao, true)."' size='45' maxlength='45' /></td>
						</tr>
                        <tr>
                            <td class='Field'>Classificação Detalhada</td>
                            <td>
                                <textarea type='text' cols='100' rows='10' name='classificacaodetalhada' id='classificacaodetalhada'>".getPost('classificacaodetalhada', $vr->classificacaodetalhada, true)."</textarea>
                            </td>                            
                        </tr>
						
                        <tr>
                            <td class='Field'>Devolutiva</td>
                            <td>
                                <textarea type='text' cols='100' rows='10' name='devolutiva' id='devolutiva'>".getPost('devolutiva', $vr->devolutiva, true)."</textarea>
                            </td>                            
                        </tr>
                        <tr>
                            <td class='Field'>Devolutiva Detalhada</td>
                            <td>
                                <textarea type='text' cols='100' rows='10' name='devolutivadetalhamento' id='devolutivadetalhamento'>".getPost('devolutivadetalhamento', $vr->devolutivadetalhamento, true)."</textarea>
                            </td>                            
                        </tr>
                        
                        
                        <tr>
                            <td class='Field'>Esilo</td>
                            <td>
                                <textarea type='text' cols='100' rows='10' name='estilo' id='estilo'>".getPost('estilo', $vr->estilo, true)."</textarea>
                            </td>                            
                        </tr>
                        <tr>
                            <td class='Field'>Objetivos da Capacitação</td>
                            <td>
                                <textarea type='text' cols='100' rows='10' name='objetivoscapacitacao' id='objetivoscapacitacao'>".getPost('objetivoscapacitacao', $vr->objetivoscapacitacao, true)."</textarea>
                            </td>                            
                        </tr>
					</table>
					
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=ValoresReferencia&id=$vr->modeloquestionarioid", 'Voltar para Modelo', "undo"); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('limiteinferior', 'req', 'Limite Inferior obrigatório');
                    vld.addValidation('limitesuperior', 'req', 'Limite Superior obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";			
}


function saveValorReferencia() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntPost('id', 0, true);

	$vr = new Fator_ValorReferencia();
	$vr->id = $id;
    $vr->modeloquestionarioid = getIntPost('modeloquestionarioid', 0, true);
    $vr->fatorid = getIntPost('fatorid', 0, true);
	$vr->limiteinferior = getPost('limiteinferior', null);
    $vr->limitesuperior = getPost('limitesuperior', null);
 	$vr->descricao = getPost('descricao', null);
 	$vr->classificacao = getPost('classificacao', null);
    $vr->classificacaodetalhada = getPost('classificacaodetalhada', null);
    $vr->devolutiva = getPost('devolutiva', null);
    $vr->devolutivadetalhamento = getPost('devolutivadetalhamento', null);
    $vr->estilo = getPost('estilo', null);
    $vr->objetivoscapacitacao = getPost('objetivoscapacitacao', null);
 	
 	$modelos = new ModelosQuestionarios();
 	if ($ret = $modelos->saveValorReferencia($vr)) {
 		$msg = 'Valor-Referência salvo com sucesso';
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $modelos->error;
        $msg_style = 'Error';
 		return false;
 	}
}


function RenderPerguntas() {
    global $msg, $msg_style;
    
	$modelos = new ModelosQuestionarios();
    $id = getQueryString('id', 0);
    if ($id) $modelo = $modelos->item($id); else $modelo = null;
    if (!$modelo) {
        RenderDefault();
        return;
    }
    
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Modelos', 'modeloquestionario.php', 'Voltar para Modelos de Questionários', 'undo');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    		
	echo "<h1>$modelo->nome</h1>";
	echo "<h2>Perguntas do questionário</h2>";

    if ($modelo->perguntas) {
        echo "<table class='List'>
    			<tr>
    				<th width='30px'></th>
    				<th>Posição</th>
    				<th>Pergunta</th>
                    <th width='150px'>Alternativas</th>
    			</tr>";
                
    	foreach ($modelo->perguntas as $p) {
    		echo "<tr>
    					<td>
    						<a href='modeloquestionario.php?action=editPergunta&id=$modelo->id&pid=$p->id' title='Editar esta pergunta'><img src='../Images/icon-edit.png' title='Editar esta pergunta' /></a>							
    					</td>
    					<td class='Center'>$p->posicao</td>
                        <td>$p->texto</td>
    					<td class='Center'>". str_replace(',', '<br/>', $p->alternativas)."</td>
    				</tr>";
    	}
    	echo "</table>";
    } else {
        echo "<p>Nenhuma pergunta encontrada para este modelo de questionário.</p>";
    }
} //renderPerguntas


function RenderEditPergunta() {
    global $msg, $msg_style;
    
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
    $pid = getIntQueryString('pid', false, true);
	
	if ($id) {			
		$modelos = new ModelosQuestionarios();
        $modelo = $modelos->item($id);
		$p = isset($modelo->perguntas[$pid]) ?  $modelo->perguntas[$pid] : null;
	}
		
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=Perguntas&id=$id", 'Voltar para Modelo', "undo");
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //modelo invalido
    if ((!$modelo) || (!$p)) {
        echo "<h1>Ooops...</h1>
                <h2>Encontramos um erro</h2>
              <p>Modelo de Questionário inválido. Clique <a href='modeloquestionario.php' title='Voltar para Modelos de Questionários'>aqui</a> 
                 para voltar para a lista de Modelos de Questionários.</p>";
        return;
    }
    
	/*if (!isset($p)) { //new pergunta 
		$p = new Pergunta();
		$p->id = 0;
		$p->texto = 'Nova pergunta';
	}*/
    
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    			
	echo "<h1>$modelo->nome</h1>
            <h2>Pergunta #$p->posicao</h2>			
				
				<form action='modeloquestionario.php?action=editPergunta&id=$id&pid=$p->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='savePergunta' />
					<input type='hidden' name='pid' id='pid' value='$p->id' />
                    
                    <input type='hidden' name='modeloquestionarioid' id='modeloquestionarioid' value='$id' />
                    <input type='hidden' name='perguntaid' id='perguntaid' value='$p->id' />
					
					<table class='Form'>
                        <tr>
                            <td class='Field'>Modelo Questionário</td>
                            <td>$modelo->nome</td>
                        </tr>
                        <tr>
                            <td class='Field'>Posição</td>
                            <td>
                                #$p->posicao
                                <input type='hidden' name='posicao' id='posicao' value='$p->posicao' />
                            </td>
                        </tr>	
                        <tr>
                            <td class='Field'>Texto</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='texto' id='texto'>".getPost('texto', $p->texto, true)."</textarea>
                            </td>                            
                        </tr>
                        <tr>
                            <td class='Field'>Alternativas</td>
                            <td>". str_replace(',', '<br/>', $p->alternativas)."</td>
                        </tr>
                        <tr>
                            <td class='Field'>Grupo</td>
                            <td>"; echo ListItemPicker::Render('s_grupo', 'grupos_perguntas', getPost('s_grupo', $p->grupoperguntaid), true, $id); echo "</td>
                        </tr>
                        <tr>
                            <td class='Field'>Posição Grupo</td>
                            <td>
                                <input type='text' name='posicaogrupo' id='posicaogrupo' value='$p->posicaogrupo' />
                            </td>
                        </tr>
                    </table>
                    
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=Perguntas&id=$id", 'Voltar para Modelo', "undo"); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('texto', 'req', 'Texto obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";			
} //editPergunta


function savePergunta() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$pid = getIntPost('pid', 0, true);

	$p = new Pergunta();
	$p->id = $pid;
    $p->texto = getPost('texto', null);
    $p->posicao = getIntPost('posicao', 0, true);
    $p->posicaogrupo = getIntPost('posicaogrupo', 0, true);
    $p->grupoperguntaid = getIntPost('s_grupo', 0, true);
    
 	$modelos = new ModelosQuestionarios();
 	if ($ret = $modelos->savePergunta($p)) {
 		$msg = "Pergunta #$p->posicao salva com sucesso";
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $modelos->error;
        $msg_style = 'Error';
 		return false;
 	}
} //savePergunta






function RenderGruposPerguntas() {
    global $msg, $msg_style;

    $modelos = new ModelosQuestionarios();
    $id = getQueryString('id', 0);
    if ($id) $modelo = $modelos->item($id); else $modelo = null;
    if (!$modelo) {
        RenderDefault();
        return;
    }

    echo "<div class='Buttons NavButtons'>";
    Button::RenderNav('Voltar para Modelos', 'modeloquestionario.php', 'Voltar para Modelos de Questionários', 'undo');
    Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";

    //Print any messages
    if (isset($msg)) MessageBox::Render($msg, $msg_style);

    echo "<h1>$modelo->nome</h1>";
    echo "<h2>Grupo de Perguntas</h2>";

    if ($modelo->gruposperguntas) {
        echo "<table class='List'>
    			<tr>
    				<th width='30px'></th>
    				<th>Posição</th>
    				<th>Texto do Grupo</th>
    			</tr>";

        foreach ($modelo->gruposperguntas as $g) {
            echo "<tr>
    					<td>
    						<a href='modeloquestionario.php?action=editGrupoPergunta&id=$modelo->id&gid=$g->id' title='Editar este grupo'><img src='../Images/icon-edit.png' title='Editar este grupo' /></a>
    					</td>
    					<td class='Center'>$g->posicao</td>
                        <td>$g->texto</td>
    				</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum grupo encontrado para este modelo de questionário.</p>";
    }
} //renderGruposPerguntas


function RenderEditGrupoPergunta() {
    global $msg, $msg_style;

    checkPageRefreshSessionVar();

    $id = getIntQueryString('id', false, true);
    $gid = getIntQueryString('gid', false, true);

    if ($id) {
        $modelos = new ModelosQuestionarios();
        $modelo = $modelos->item($id);
        $g = isset($modelo->gruposperguntas[$gid]) ?  $modelo->gruposperguntas[$gid] : null;
    }

    echo "<div class='Buttons NavButtons'>";
    Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=Perguntas&id=$id", 'Voltar para Modelo', "undo");
    Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";

    //modelo invalido
    if ((!$modelo) || (!$g)) {
        echo "<h1>Ooops...</h1>
                <h2>Encontramos um erro</h2>
              <p>Modelo de Questionário inválido. Clique <a href='modeloquestionario.php' title='Voltar para Modelos de Questionários'>aqui</a>
                 para voltar para a lista de Modelos de Questionários.</p>";
        return;
    }

    /*if (!isset($p)) { //new pergunta
        $p = new Pergunta();
        $p->id = 0;
        $p->texto = 'Nova pergunta';
    }*/

    //Print any messages
    if (isset($msg)) MessageBox::Render($msg, $msg_style);

    echo "<h1>$modelo->nome</h1>
            <h2>Grupo #$g->posicao</h2>

				<form action='modeloquestionario.php?action=editGrupoPergunta&id=$id&gid=$g->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='action' id='action' value='saveGrupoPergunta' />
					<input type='hidden' name='gid' id='gid' value='$g->id' />

                    <input type='hidden' name='modeloquestionarioid' id='modeloquestionarioid' value='$id' />
                    <input type='hidden' name='grupoperguntaid' id='grupoperguntaid' value='$g->id' />

					<table class='Form'>
                        <tr>
                            <td class='Field'>Modelo Questionário</td>
                            <td>$modelo->nome</td>
                        </tr>
                        <tr>
                            <td class='Field'>Posição</td>
                            <td>
                                #$g->posicao
                                <input type='hidden' name='posicao' id='posicao' value='$g->posicao' />
                            </td>
                        </tr>
                        <tr>
                            <td class='Field'>Texto</td>
                            <td>
                                <textarea type='text' cols='100' rows='5' name='texto' id='texto'>".getPost('texto', $g->texto, true)."</textarea>
                            </td>
                        </tr>
                    </table>

					<div class='Buttons'>";
                        Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
                        Button::RenderNav('Voltar para Modelo', "modeloquestionario.php?action=GruposPerguntas&id=$id", 'Voltar para Modelo', "undo"); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>

				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');

					vld.addValidation('texto', 'req', 'Texto obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";
} //editGrupoPergunta


function saveGrupoPergunta() {
    global $msg, $msg_style;

    updatePageRefreshChecker();

    $gid = getIntPost('gid', 0, true);

    $g = new GrupoPergunta();
    $g->id = $gid;
    $g->texto = getPost('texto', null);
    $g->posicao = getIntPost('posicao', 0, true);

    $modelos = new ModelosQuestionarios();
    if ($ret = $modelos->saveGrupoPergunta($g)) {
        $msg = "Grupo #$g->posicao salvo com sucesso";
        $msg_style = 'Info';
        return true;
    } else {
        $msg = $modelos->error;
        $msg_style = 'Error';
        return false;
    }
} //saveGrupoPergunta


?>