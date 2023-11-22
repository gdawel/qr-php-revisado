<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Relatórios';
include_once '../App_Code/User.class';
include_once '../App_Code/ModeloQuestionario.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php?returnurl=/cockpit/reports.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$msg_style = 'Info';

function Router() {
	global $msg, $msg_style;
	$action = getPost('action', null) ? getPost('action', null) : getQueryString('action', null);	

	switch ($action) {            
        case 'editSection':
            RenderEditSection();
            break;
            
        case 'saveSection':
			if (!isPageRefresh()) {								
				if (saveSection()) RenderDefault();
				else RenderEditSection();
			} else {
				RenderDefault();
			}
			break;
        
        case 'deleteSection':
            DeleteSection();
            RenderDefault();
            break;
                   
		default:
			RenderDefault();
	}
}

function RenderDefault() {
    global $msg, $msg_style;
    
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
			
	echo "<h1>Relatórios</h1>";
	
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    
	$reports = new Reports();
	$filter = new Filter();
	$lst = $reports->items($filter);

	if ($lst) {
		foreach ($lst as $report) {
		  /*<div class='FloatRight'>
                        
                        
                    </div>*/
                    
			$status = '<span class="Verde">Ativo</span>';
                  
            echo "<h3>$report->nome
                    <a class='FloatRight Small' href='reports.php?action=editSection&id=$report->id&sid=0' title='Incluir nova seção em \"$report->nome\"'>
                        <img src='../Images/icon-add.png'> Incluir nova seção
                    </a>
                </h3>";
            if ($report->descricao)  echo "<p>$report->descricao</p>";

                  
            //render sections por ModeloQuestionarios
            if ($report->sections) {
                $modeloquestionariod_current = null;
                
                echo "<table class='List'>
                        <tr>
                            <th style=\"width:250px;\">Modelo Questionário</th>
                            <th>Seções</th>
                        </tr>";
                
                $isFirstGroup = true; 
                foreach ($report->sections as $section) {
                    if ($modeloquestionariod_current != $section->modeloquestionarioid) {
                        if (!$isFirstGroup) {
                            echo "</td></tr>";
                        } else {
                            $isFirstGroup = false;
                        }
                        
                        echo "<tr>
                                <td>$section->modeloquestionario</td>
                                <td>";
                        
                        $modeloquestionariod_current = $section->modeloquestionarioid;                        
                    }
                    
                    echo "<div>
                            <a href='reports.php?action=editSection&id=$section->reportid&sid=$section->id' id='secaoId$section->id'>
                                <img src='../Images/icon-edit.png' title='Editar a seção $section->title'>
                            </a>
                            <a href='reports.php?action=deleteSection&id=$section->reportid&sid=$section->id' 
                              onclick=\"javascript:return confirm('Deseja realmente excluir essa seção?');\">
                                <img src='../Images/icon-remove.png' title='Excluir a seção $section->title'>
                            </a>
                            $section->title
                        </div>";
                }
                
                echo "</td></tr>";
                echo "</table>";
            }
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
}//RenderDefault()


function RenderEditSection() {
    global $msg, $msg_style;
    
	checkPageRefreshSessionVar();
	
	$id = getIntQueryString('id', false, true);
    $sid = getIntQueryString('sid', false, true);
	
	if ($id) {			
		$reports = new Reports();
        $report = $reports->item($id);
		$section = isset($report->sections[$sid]) ?  $report->sections[$sid] : new ReportSection(0, $id, 0, null, null, 1, 0);
	}
		
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Relatórios', "reports.php#secaoId$section->id", 'Voltar para Relatórios', "undo");
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
	
    //modelo invalido
    if ((!$report) || (!$section)) {
        echo "<h1>Ooops...</h1>
              <h2>Encontramos um erro</h2>
              <p>Relatório ou seção de relatório inválido. Clique <a href='reports.php' title='Voltar para Relatórios'>aqui</a> 
                 para voltar para a lista de relatórios disponíveis.</p>";
        return;
    }
    
    //Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);
    			
	echo "<h1>$report->nome</h1>
            <h2>Seção $section->title</h2>			
				
				<form action='reports.php?action=saveSection&id=$id&sid=$section->id' method='post' name='frm' id='frm'>
					<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
					<input type='hidden' name='sid' id='sid' value='$section->id' />
                    <input type='hidden' name='id' id='id' value='$section->reportid' />
                    
					<table class='Form'>
                        <tr>
                            <td class='Field'>Relatório</td>
                            <td>$report->nome</td>
                        </tr>
                        <tr>
                            <td class='Field'>Modelo de Questionário</td>
                            <td>"; ListItemPicker::Render('modeloquestionarioid', 'modelos_questionarios', getPost('modeloquestionarioid', $section->modeloquestionarioid, true)); echo "</td>
                        </tr>
                        <tr>
                            <td class='Field'>Título da Seção</td>
                            <td>
                                <textarea name='title' id='title' rows='2' cols='110'>".getPost('title', $section->title, true)."</textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class='Field'>Texto da Seção</td>
                            <td>
                                <textarea name='texto' id='texto' rows='10' cols='110'>".getPost('texto', $section->texto, true)."</textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class='Field'>Posição</td>
                            <td>
                                <input type='text' name='posicao' id='posicao' value=".getPost('posicao', $section->posicao, true)." alt='integer' style=\"width:50px\" />
                                <span class='Gray'><small>Utilize 99 para as notas de final de relatório.</small></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class='Field'>Adicionar Página antes da seção?</td>
                            <td>"; ListItemPicker::Render('addpagebreakbefore', 'simnao', getPost('addpagebreakbefore', $section->addpagebreakbefore, true)); echo "</td>
                        </tr>
                    </table>
                    
					<div class='Buttons'>";
						Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
						Button::RenderNav('Voltar para Relatórios', "reports.php#secaoId$section->id", 'Voltar para Relatórios', "undo"); echo "
					</div>
				</form>
				<div id='frm_errorloc' class='Error'></div>
					
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('texto', 'req', 'Texto obrigatório');
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";			
} //editSection


function saveSection() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$sid = getIntPost('sid', 0, true);

	$section = new ReportSection($sid, getIntPost('id', 0, true), getIntPost('modeloquestionarioid'), getPost('title', null),
                                    getPost('texto', null), getIntPost('posicao', 0, true), getIntPost('addpagebreakbefore', 0, true), null);
    
 	$reports = new Reports();
 	if ($ret = $reports->saveSection($section)) {
 		$msg = "Seção salva com sucesso";
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $reports->error;
        $msg_style = 'Error';
 		return false;
 	}
} //saveSection


function deleteSection() {
	global $msg, $msg_style;
	
	updatePageRefreshChecker();
	
	$id = getIntQueryString('id', false, true);
    $sid = getIntQueryString('sid', false, true);
	
	if ($id) {			
		$reports = new Reports();
        $report = $reports->item($id);
		$section = isset($report->sections[$sid]) ?  $report->sections[$sid] : null;
	}

	if (!$section) {
	   $msg = 'Seção não encontrada.';
       $msg_style = 'Info';
       return false;
	}
    
 	$reports = new Reports();
 	if ($ret = $reports->deleteSection($section)) {
 		$msg = "Seção excluída com sucesso";
        $msg_style = 'Info';
 		return true;
 	} else {
 		$msg = $reports->error;
        $msg_style = 'Error';
 		return false;
 	}
} //deleteSection

?>