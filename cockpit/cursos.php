<?php
$pageTitle = 'SOBRARE Cockpit Admin | Associados';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Filter.class.php';
include_once '../App_Code/Curso.class.php';
include_once '../App_Code/Inscricao.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../App_Code/FileHandler.class.php';
include_once 'admin.index.php';
include_once '../Controls/pagination.ctrl.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
require_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {	
	global $cursos, $msg;
	$cursos = new Cursos();

	$action = getPost('action');
	$id = getPost('id', 0);
	
	switch ($action) {			
		case 'delete':	
			if ($cursos->Delete($id)) $msg = 'Curso excluído com sucesso';
			else $msg = 'Erro ao excluir curso';
			RenderDefault();
			break;	
		
		case 'save':		
			if (!isPageRefresh()) {
				if (!saveCurso()) RenderForm($id); else RenderDefault();
			} else {RenderDefault();}
			break;	
			
		case 'new':
			RenderForm();
			break;
			
		case 'edit':
			RenderForm($id);
			break;
		
		default:
			RenderDefault($cursos);		
	}
}

function RenderDefault() {
	global $cursos, $msg;
	
	echo "<div class='Buttons NavButtons'>";
				Button::Render(null, 'Novo Curso', '#', 'Incluir novo curso', 'add', true, 'regular', "Action(0, 'new')");
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "	
			</div>";
				
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg);
				
	//Filter
	$filter = new Filter();
	$s_nome = $filter->addFromPost('nome', 'LIKE','s_nome');
	$s_local = $filter->addFromPost('local', 'LIKE', 's_local');	
		
	$page = getIntQueryString('page', 1, true);
	$pagesize = 10;
	$totalrows = 0;
	$lst = $cursos->Items($page, $pagesize, null, $totalrows, $filter);			
	
	if ($totalrows) $countmsg = "($totalrows itens encontrados)"; else $countmsg = '';
	
	echo "<h1>Cockpit do Admin</h1>
				<h2>Cursos</h2>
				<fieldset>
					<legend>Filtros <span class='FieldsetMsg'>$countmsg</span></legend>					
					<form id='frm' name='frm' method='post' action='$_SERVER[REQUEST_URI]'>
						<input type='hidden' name='action' id='action' value='0' />
						<input type='hidden' name='id' id='id' value='0' />
						
						<table class='Form'>
							<tr class='Field'>
								<td>Nome</td>
								<td>Local</td>								
								<td rowspan='2' class='SearchButtonCell'>
									<div class='Buttons'>";
									Button::RenderSubmit(null, 'Pesquisar', 'Pesquisa os itens conforme os filtros informados', 'search', 'regular', 'frm'); echo "
									</div>
								</td>
							</tr>
							<tr>
								<td><input type='text' id='s_nome' name='s_nome' value='$s_nome' size='30' /></td>
								<td><input type='text' id='s_local' name='s_local' value='$s_local' size='30' /></td>
							</tr>
						</table>
					</form>
				</fieldset>";
				
	echo "<script type='text/javascript'>
					function Action(id, action) {
						document.getElementById('id').value = id;
						document.getElementById('action').value = action;
						
						document.getElementById('frm').submit();
						return false;
					}
				</script>";
	
    //echo "<pre>"; print_r($lst); echo "</pre>";
	if ($lst) {
		foreach ($lst as $c) {
            //$c = cast($c, new Curso());
			echo "<div class='curso clearleft'>
							<a id='$c->id'></a>
							<h3>$c->nome ($c->tipo)</h3>
							<p class='Info'>
								<span class='date'>".date('d/M/Y', strtotime($c->datainicio))." &nbsp;$c->horario</span> | 
								<span class='local'>$c->local</span><br />
								$c->endereco
							</p>
							<div class='Buttons'>";
								Button::Render(null, 'Editar', '#', 'Edita o item selecionado', 'edit', true, 'regular', "return Action($c->id, 'edit');");
								$count_inscritos_total = $c->getInscritosTotal();
                                $count_inscritos_conf = $c->getInscritosConfirmados();
								Button::Render(null, "Inscrições <small>($count_inscritos_conf confirmados de $count_inscritos_total inscrições)</small>", "inscricoes.php?cursoId=$c->id", 'Visualiza as inscrições deste curso.', 'list', true, 'regular');
								Button::Render(null, 'Excluir', '#', 'Exclui permanentemente o item do site', 'delete', true, 'negative', "if (confirm('Deseja realmente excluir este item? Essa ação não pode ser cancelada.')) return Action($c->id, 'delete'); else return false;");								
			echo "		</div>
							<br />
						</div>";	
		}
		
		Pagination::Render('cursos.php?page=%s', $totalrows, $pagesize, $page);
		
	} else {
		echo "<p><i>Nenhum item encontrado.</i></p>";
	}
}

function RenderForm($id = 0) {
	checkPageRefreshSessionVar();
	
	global $cursos, $msg;
	
	if ($id) {
		$cursos = new Cursos();
		if (!$c = $cursos->Item($id)) {$c = new Curso(); $id = 0;}
	} else {
		$c = new Curso();
	}

	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Cursos', 'cursos.php', 'Voltar para Cursos', 'undo'); 
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";

	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, 'Error');
	
	if ($c->nome) echo "<h1>Editar $c->nome</h1>"; else echo "<h1>Incluir Curso</h1>";
					
	echo "<script type='text/javascript' src='cursos.js'></script>
    
    <form action='cursos.php' method='post' name='frm' id='frm' enctype='multipart/form-data'>
		<input type='hidden' name='action' value='upload' />
		<input type='hidden' name='MAX_FILE_SIZE' value='600000' />
		
		<input type='hidden' name='action' id='action' value='save' />
		<input type='hidden' name='id' id='id' value='$id' />
		<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
		
		<table class='Form'>
			<tr>
				<td class='Right' colspan='2'><span class='Red'>*</span> = Item obrigatório</td>
			</tr>
				
			<tr class='Field'>
				<td colspan='2'>Nome <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='100' name='nome' id='nome' value='".getPost('nome', $c->nome, true)."' /></td>
			</tr>
			
			<tr class='Field'>
                <td>Tipo <span class='Red'>*</span></td>
				<td>Inscrições Abertas? <span class='Red'>*</span></td>
			</tr>
			<tr>
                <td>"; ListItemPicker::Render('tipo', 'cursos_tipos', getPost('tipoid', $c->tipoid, true)); echo "</td>
				<td>"; ListItemPicker::Render('inscricoesabertas', 'simnao', getPost('inscricoesabertas', $c->inscricoesabertas, true)); echo "</td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>Local <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='100' name='local' id='local' value='".getPost('local', $c->local, true)."' /></td>
			</tr>
					
			<tr class='Field'>
				<td colspan='2'>Endereço</td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='100' name='endereco' id='endereco' value='".getPost('endereco', $c->endereco, true)."' /></td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>&nbsp;</td>
			</tr>
			
			<tr class='Field'>
				<td>Data Início <span class='Red'>*</span></td>
				<td>Horário</td>
			</tr>
			<tr>
				<td><input type='text' size='10' name='inicio' id='inicio' alt='date' value='".getPost('inicio', format_date($c->datainicio), true)."' /></td>
				<td><input type='text' size='10' name='horario' id='horario' value='".getPost('horario', $c->horario, true)."' /></td>
			</tr>
			
			<tr class='Field'>
				<td>Data Término</td>
			</tr>
			<tr>
				<td colspan='2'><input type='text' size='10' name='termino' id='termino' alt='date' value='".getPost('termino', format_date($c->datatermino), true)."' /></td>
			</tr>
				
			<tr class='Field'>
				<td colspan='2'>&nbsp;</td>
			</tr>
			
			<tr class='Field'>
				<td>Valor Inscrição Antecipada <span class='Red'>*</span></td>
				<td>Data Limite Inscrição Antecipada <span class='Red'>*</span></td>
			</tr>
			<tr>
				<td><input type='text' size='10' name='valor1' id='valor1' alt='decimal' value='".getPost('valor1', $c->valor1, true)."' /></td>
				<td><input type='text' size='10' name='datalimite1' id='datalimite1' alt='date' value='".getPost('datalimite1', format_date($c->datalimite1), true)."' /></td>
			</tr>
				
			<tr class='Field'>
				<td>Valor Final Inscrição</td>
				<td>Data Final Inscrição</td>
			</tr>
			<tr>
				<td><input type='text' size='10' name='valor2' id='valor2' alt='decimal' value='".getPost('valor2', $c->valor2, true)."' /></td>
				<td><input type='text' size='10' name='datalimite2' id='datalimite2' alt='date' value='".getPost('datalimite2', format_date($c->datalimite2), true)."' /></td>
			</tr>
				
			<tr class='Field'>
				<td>Desconto para Associados (%)</td>
				<td></td>
			</tr>
			<tr>
				<td><input type='text' size='10' name='descontoassociado' id='descontoassociado' alt='decimal' value='".getPost('descontoassociado', $c->descontoassociado, true)."' /></td>
				<td></td>
			</tr>
			
			<tr class='Field'>
				<td>Desconto para Grupos (%)</td>
				<td>Grupo Mínimo</td>
			</tr>
			<tr>
				<td><input type='text' size='10' name='descontogrupo' id='descontogrupo' alt='decimal' value='".getPost('descontogrupo', $c->descontogrupo, true)."' /></td>
				<td><input type='text' size='10' name='grupominimo' id='grupominimo' alt='integer' value='".getPost('grupominimo', $c->grupominimo, true)."' /> pessoas</td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>&nbsp;</td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>Descrição <span class='Red'>*</span> <small class='FloatRight'>Tags HTML permitidas</small></td>
			</tr>
			<tr>
				<td colspan='2'>
					<textarea type='text' cols='100' rows='15' name='descricao' id='descricao'>".getPost('descricao', $c->descricao, true)."</textarea>
					<div id='dialogPreview' class='Hidden'>
						<div id='dialog-main'>
						</div>
					</div>
				</td>
			</tr>
			
			<tr class='Field'>
				<td colspan='2'>Imagem&nbsp;&nbsp;<small>Tamanho Máx: 600kb. &nbsp;&nbsp;|&nbsp;&nbsp; Largura Máx: 680px</small></td>
			</tr>
			<tr>
				<td colspan='2'><input name='userfiles[]' type='file' /></td>
			</tr>
		</table>
        
        <h3>Módulos</h3>
        <table class='Hidden'>";
        $templateModulo = "
            <tbody class='templateModulo'>
                <tr class='Field'>
                    <td colspan='2'>Nome do Módulo</td>
                </tr>
                <tr>
                    <td colspan='2'><input type='text' size='60' name='m_nome[]' value='%s' /></td>
                </tr>
                
                <tr class='Field'>
                    <td colspan='2'>Descrição</td>
                </tr>
                <tr>
                    <td colspan='2'><input type='text' size='60' name='m_descricao[]' value='%s' /></td>
                </tr>
                
                <tr class='Field'>
                    <td>Valor</td>
                    <td>Data Limite Inscrição</td>
                </tr>
                <tr>
                    <td><input type='text' size='10' name='m_valor1[]' alt='decimal' value='%s' /></td>
                    <td><input type='text' size='10' name='m_datalimite1[]' alt='date' value='%s' /></td>
                </tr>
                <tr>
                    <td colspan='2'>&nbsp;</td>
                </tr>
            </tbody>"; 
            echo sprintf($templateModulo, '', '', '', '');
            echo " 
        </table>
        
        <table class='Form' id='tbModulos'>";
            if (!$c->modulos) {
                echo "<tr>
                        <td colspan='2'>Nenhum módulo encontrado.</td>
                    </tr>";
            } else {
                foreach ($c->modulos as $modulo) {
                    echo sprintf($templateModulo, $modulo->nome, $modulo->descricao, $modulo->valor1, format_date($modulo->datalimite1));
                }
            }
  echo "</table>
		
        
        <h3>Histórico</h3>
        <table class='Form'>
            <tr class='Field'>
                <td colspan='2'>Imagem (Url) <small>(Dimensões: 180px x 160px)</small></td>
            </tr>
            <tr>
                <td colspan='2'><input type='text' size='100' name='historico_imageurl' id='historico_imageurl' value='".getPost('historico_imageurl', $c->historicoinfo->imageurl, true)."' /></td>
            </tr>
            
            <tr class='Field'>
                <td colspan='2'>Destino (Url)</td>
            </tr>
            <tr>
                <td colspan='2'><input type='text' size='100' name='historico_navigateurl' id='historico_navigateurl' value='".getPost('historico_navigateurl', $c->historicoinfo->navigateurl, true)."' /></td>
            </tr>
            
            <tr class='Field'>
                <td colspan='2'>Descrição</td>
            </tr>
            <tr>
                <td colspan='2'>
                    <textarea type='text' cols='100' rows='5' name='historico_summary' id='historico_summary'>".getPost('historico_summary', $c->historicoinfo->summary, true)."</textarea>
                </td>
            </tr>
        </table>
        
        
        <br />
		<div class='Buttons'>";
			Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
            echo "<a href=\"javascript:addModulo();\">Adicionar Módulo</a>";
			Button::Render(null, 'Visualizar Descrição', '#', 'Visualizar prévia da descrição do curso', 'search', true, 'regular', "return previewDescricao();");
			Button::Render(null, 'Voltar', 'cursos.php', 'Voltar para Cursos', 'undo'); echo "
		</div>		
		</form>
		
		<div id='frm_errorloc' class='Error'></div>
		
		<script type='text/javascript'>
			var vld  = new Validator('frm');
			
			vld.addValidation('nome','alpha', 'Somente letras são permitidas no campo Nome');
			vld.addValidation('nome', 'req','Nome obrigatório');
			
			vld.addValidation('local', 'req','Local obrigatório');
			
			vld.addValidation('inicio', 'req','Data de Início obrigatório');
			vld.addValidation('inicio', 'data','Data de Início inválida');
			vld.addValidation('termino', 'data','Data de Término inválida');
			
			vld.addValidation('descricao', 'req', 'Atividades desenvolvidas obrigatório');
			
			vld.EnableOnPageErrorDisplaySingleBox();
			vld.EnableMsgsTogether();
			
			function previewDescricao() {
				$('#dialogPreview #dialog-main').html($('#descricao').val());
				$('#dialogPreview').dialog({
						show: 'fade',
						hide: 'fade',
						height: 450, width:700, 
						modal: true
						});
				
				return false;					
			}
		</script>
	";
}

function saveCurso() {
	updatePageRefreshChecker();
	
	global $cursos, $msg;
	
	$c = new Curso();	
	
	$c->id = getPost('id', null);
	$c->nome = getPost('nome', null);
    $c->tipoid = getIntPost('tipo', 1);
	$c->local = getPost('local', null);
	$c->endereco = getPost('endereco', null);
	$c->datainicio = getPost('inicio', null);
	$c->termino = getPost('termino', null);
	$c->horario = getPost('horario', null);
	$c->descricao = getPost('descricao', null);
	$c->valor1 = getPost('valor1', null);
	$c->valor2 = getPost('valor2', null);
	$c->datalimite1 = getPost('datalimite1', null);
	$c->datalimite2 = getPost('datalimite2', null);
	$c->descontoassociado = getPost('descontoassociado', null);
	$c->descontogrupo = getPost('descontogrupo', null);
	$c->grupominimo = getPost('grupominimo', null);
	$c->inscricoesabertas = getIntPost('inscricoesabertas', 1);
	
    $c->historicoinfo->imageurl = getPost('historico_imageurl', null);
    $c->historicoinfo->navigateurl = getPost('historico_navigateurl', null);
    $c->historicoinfo->summary = getPost('historico_summary', null);
    
	if (!$cursos->save($c)) {
		$msg = $cursos->error;
		return false;
	} else {
		$msg = 'Curso salvo com sucesso';
		
		//Upload da imagem
		$fs = new FileHandler();
		if (!$fs->uploadFiles('../Uploads', "Curso_$c->id")) $msg .= '<br />Erro ao inserir arquivo.';
		return true;
	}
}
/*
function objectToObject($instance, $className) {
    return unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(strstr(serialize($instance), '"'), ':')
    ));
}

function cast($source, $destination)
{
    $sourceReflection = new ReflectionObject($source);
    $sourceProperties = $sourceReflection->getProperties();
    foreach ($sourceProperties as $sourceProperty) {
        $name = $sourceProperty->getName();
        $destination->$name = $source->$name;
    }
    return $destination;
}*/
?>