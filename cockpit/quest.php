<?php
$pageTitle = 'SOBRARE Cockpit | Detalhes do Questionário';
include_once '../App_Code/User.class';
include_once '../App_Code/Questionario.class';
include_once '../App_Code/Pesquisa.class';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Gestor,Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$pesquisaid = '';

function Router() {
	global $msg, $msg_style;
	
	$action = getQueryString('a', null) ? getQueryString('a', null) : getPost('a', null);

	switch ($action) {
		case 'recalc':
			recalcFatores();
			break;
		
		case 'resetquest':
			resetQuest();
			break;
	}
	
	RenderDefault();
}

function RenderDefault() {
	global $msg, $msg_style;
	
	$usr = Users::getCurrent();
	$q = checkPermission();
	if (!$q) return false;
	$p = $GLOBALS['pesquisa'];
	$pesquisaAglutinadoraId = (getIntQueryString('aglutinadoraId', false, true) ? getIntQueryString('aglutinadoraId', false, true) : $p->id);
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Voltar para Pesquisa', "pesquisa.php?id=$pesquisaAglutinadoraId", 'Voltar para a pesquisa');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";				
	
	if ($msg) MessageBox::Render($msg, $msg_style);
				
	echo "<h1>Questionário #$q->id</h1>
			<div class='grid_9 alpha omega'>
				<h2>Informações da Pesquisa</h2>
				
				<table class='Form'>
					<tr>
						<td class='Field'>Nome</td>
						<td>$p->titulo</td>
					</tr>
					<tr>
						<td class='Field'>Público</td>
						<td>$p->publico</td>
					</tr>
					<tr>
						<td class='Field'>Finalidade</td>
						<td>$p->finalidade</td>
					</tr>
					<tr>
						<td class='Field'>Questionários</td>
						<td>$p->count_questionarios Questionários</td>
					</tr>
				</table>
			</div>
			
			<div class='grid_7 alpha omega'>
				<h2>Informações do Questionário</h2>
				<table class='Form'>
					<tr>
						<td class='Field'>Login</td>
						<td>".$q->infos['QuestionarioId']."</td>
					</tr>
					<tr>
						<td class='Field'>Senha</td>
						<td>".$q->infos['Password']."</td>
					</tr>
				</table>
			</div>
				<br />";
				
	
				
	echo "<div class='grid_9 alpha omega'>
				<h2>Informações do Respondente</h2>
	
				<table class='Form'>	";
		if ($usr->isinrole('Admin,Gestor')) {
			echo "
					<tr class='Field'>
						<td colspan='3'>Marque qual a pessoa que mais ajudou você a vencer na vida, a superar dificuldades pessoais, escolares, doenças, acidentes, etc.</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['PessoasDificuldade'].' '.$q->infos['PessoaDificuldadeOutro']."
						</td>
					</tr>
					
					<tr class='Field'><td colspan='3'>&nbsp;</td></tr>
					
					<tr class='Field'>
						<td colspan='3'>Qual foi a doença, o acidente ou a situação de conseqüências mais graves que você já viveu?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['SituacaoGrave']."</td>
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>Com que idade você estava quando aconteceu?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['Quando']."</td>
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>Quanto tempo durou aproximadamente?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['Duracao']."</td>							
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>Comente as consequências desta situação em você.</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['SituacaoGraveComentario']."</td>							
					</tr>
					
					<tr class='Field'><td colspan='3'>&nbsp;</td></tr>
					
					<tr class='Field'>
						<td colspan='3'>Há uma 2ª situação muito marcante que você quer registrar?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['SituacaoGrave2']."</td>
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>Com que idade você estava quando aconteceu?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['Quando2']."</td>
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>Quanto tempo durou aproximadamente?</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['Duracao2']."</td>							
					</tr>					
					
					<tr class='Field'>
						<td colspan='3'>Comente as consequências desta situação em você.</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['SituacaoGrave2Comentario']."</td>							
					</tr>
						
					<tr class='Field'><td colspan='3'>&nbsp;</td></tr>";
		}
		
		echo "					
					<tr class='Field'>
						<td colspan='3'>Nome</td>
					</tr>
					<tr>
						<td colspan='3'>$q->nome</td>
					</tr>
					
					<tr class='Field'>
						<td colspan='3'>E-mail</td>
					</tr>
					<tr>
						<td colspan='3'>".$q->infos['Email']."</td>
					</tr>
											
					<tr class='Field'>
						<td colspan='3'>Sexo</td>
					</tr>
					<tr>							
						<td colspan='3'>".$q->infos['Sexo']."</td>
					</tr>";
		
		if ($usr->isinrole('Admin')) {
			echo "					
					<tr class='Field'>
						<td>Data de Nascimento</td>
						<td>UF Nascimento</td>
						<td></td>
					</tr>
					<tr>
						<td>".format_date($q->infos['DataNascimento'])."</td>
						<td>".$q->infos['UFNascimento']."</td>
						<td></td>
					</tr>
					
															
					<tr class='Field'>
						<td>Cidade Onde Mora</td>
						<td>UF</td>
						<td></td>
					</tr>
					<tr>
						<td>".$q->infos['Cidade']."</td>
						<td>".$q->infos['UF']."</td>
						<td></td>
					</tr>
					
					
					<tr class='Field'>
						<td>Formaçao Profissional</td>
						<td>Atividade Profissional</td>
						<td></td>
					</tr>
					<tr>
						<td>".$q->infos['FormacaoProfissional']."</td>
						<td>".$q->infos['AtividadeProfissional']."</td>
						<td></td>
					</tr>
					
					<tr class='Field'>
						<td>Escolaridade</td>
						<td>Estado Civil</td>
						<td></td>
					</tr>
					<tr>
						<td>".$q->infos['Escolaridade']."</td>
						<td>".$q->infos['EstadoCivil']."</td>
						<td></td>
					</tr>
					
					<tr class='Field'>
						<td>Religião</td>
						<td colspan='2'>Fala outro idioma além do português?</td>
					</tr>
					<tr>
						<td>".$q->infos['Religiao']."</td>
						<td colspan='2'>".$q->infos['Idioma']."</td>
					</tr>";
		}
		
		echo "			
				</table>	
		
			</div>";	
	
	echo "<div class='grid_7 alpha omega'>
					<h2>Resultados dos Fatores</h2>
					
					<table class='List'>
						<tr>
							<th>Sigla</th>
							<th>Fator</th>
							<th>Resultado</th>";
							if ($usr->isinrole('Admin')) {echo "<th>Score</th>";}
	echo "			</tr>";
	
	foreach ($q->fatores as $f) {
		echo "	<tr>
							<td class='Center'>$f->sigla</td>
							<td>$f->nome</td>
							<td class='Center NoWrap'>$f->result &nbsp;</td>";
							if ($usr->isinrole('Admin')) {echo "<td class='Center'>$f->valor &nbsp;</td>";}
		echo "					
						</tr>";
	}
	echo "	</table>
				
					<div class='Buttons'>";
						Button::Render(null, 'Reiniciar Quest', "quest.php?id=$q->id&a=resetquest", 'Exclui todas as respostas e infos deste questionário', 'warning', true, 'regular', "return confirm('Deseja realmente reiniar este questionário? Esta ação apagará todas as informações sóciodemográficas e perguntas já respondidas.');");
						Button::Render(null, 'Recalcular Fatores', "quest.php?id=$q->id&a=recalc", 'Recalcula os fatores deste questionário', 'warning', true, 'regular', "return confirm('Deseja realmente recalcular os fatores este questionário?');"); echo "
					</div>
					<div class='Buttons'>";
						Button::Render(null, 'Exibir Relatório', "../Quest/report.php?id=$q->id&comentado=0", 'Exibe o relatório deste questionário', 'list', true, 'regular', null, '_blank');
					
						if ( ($usr->isinrole('Admin')) || (isset($p->produtos[2])) )
							Button::Render(null, 'Exibir Relatório Detalhado', "../Quest/report.php?id=$q->id&comentado=1", 'Exibe o relatório detahado deste questionário', 'list', true, 'regular', null, '_blank');
	echo "					
					</div>
				</div>";

	
	if ($usr->isinrole('Admin')) {
		echo "<br class='clearleft'>
					<br />
					<h2>Perguntas e Respostas</h2>";
		
		if ($q->perguntas) {
			echo "<table class='List'>
							<tr>
								<th>Posição</th>
								<th>Pergunta</th>
								<th>Resposta</th>
							</tr>";
			
			foreach ($q->perguntas as $f) {
				echo "<tr>
								<td class='Center'>$f->posicao</td>
								<td>$f->texto</td>
								<td class='NoWrap'>$f->respostavalor - $f->resposta</td>
							</tr>";
			}
			echo "</table>";
		} else {
			echo "<p><i>Nenhum item encontrado.</i></p>";
		}
	}
}


function recalcFatores() {
	global $msg, $msg_style;
	
	$q = checkPermission();
	if (!$q) return false;
	
	$ret = $q->Calculate();
	if ($ret) {
		$msg = 'Fatores recalculados com sucesso.';
		$msg_style = 'Info';
	} else{ 
		$msg = 'Erro ao recalcular fatores. ' . $q->error;
		$msg_style = 'Error';
	}
	return $ret;
}

function resetQuest() {
	global $msg, $msg_style;
	
	$q = checkPermission();
	if (!$q) return false;
	
	$ret = $q->ResetQuest(true);
	if ($ret) {
		$msg = 'Quest reiniciado com sucesso.';
		$msg_style = 'Info';
	} else{ 
		$msg = 'Erro ao reiniciar Quest. ' . $q->error;
		$msg_style = 'Error';
	}
	return $ret;
}

function checkPermission() {
	$id = getIntQueryString('id', false, true);

	if (!$id) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}
	
	$quests = new Questionarios();
	$q = $quests->item($id);	
	
	if (!$q) {
		echo "<h1>Ooops</h1>
					<h2>Tivemos um problema</h2>
					<p>Não encontramos o questionário solicitado. Retorne para a <a href='index.php'>Home</a> e 
					selecione a pesquisa desejada.</p>";
		return false;		
	}


	$pesquisas = new Pesquisas();
	$p = $pesquisas->item($q->pesquisaid);
	$GLOBALS['pesquisaid'] = $q->pesquisaid;
	$GLOBALS['pesquisa'] = $p;
	
	//Verificar se gestor é o pesquisador
	if ($p->isAccessDenied()) {
		echo "<h1>Acesso negado</h1>
					<p>Você não permissão para visualizar a pesquisa solicitada. Retorne para a <a href='index.php'>Home</a> e 
					selecione a pesquisa desejada.</p>";
		return false;	
	}
	
	return $q;
}
?>