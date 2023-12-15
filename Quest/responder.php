<?php
ob_start();
$pageTitle = 'SOBRARE - Sociedade Brasileira de Resiliência | Quest_Resiliência';
include_once '../Controls/events.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Questionario.class.php';
include_once '../App_Code/CommonFunctions.php';

checkAuth();

//verificar qual master sera exibida
$usr = Users::getCurrent();
$quest = new Questionario($usr->questid);
$quest->getIsAgrupado();
if ($quest->isagrupado == '1')
    include_once '../MasterPageQuestAdolescentes.htm.php';
else
    include_once '../MasterPageQuest.htm.php';


$GLOBALS['error'] = ''; //var para descricao do erro

function checkAuth() {
	$usr = Users::getCurrent();
	
	if ((!$usr) || (!$usr->isinrole('Respondente'))) {
		header("Location: login.php");
		ob_flush();
	}
}

function Router() {	
	$action = getPost('action', '');

	switch ($action) {
		case 'update_info':
			if (DadosPreliminaresUpdate()) {
				//Próximo passo
				DefaultAction();
				
			} else {
				//Erro
				DadosPreliminaresForm();
			}
			break;
		
		case 'update_resposta':
			if (UpdateResposta()) {
				//Próxima pergunta
				DefaultAction();
				
			} else {
				echo "
					<h1>Oooops...</h1>
					<div class='Error'><p>Ocorreu um erro ao atualizar resposta do Questionário. 
						Por favor, faça o <a href='login.php'>acesso</a> e tente novamente.</p>
					</div>";
			}
			break;

        case 'update_grupo_respostas':
            if (UpdateGrupoRespostas()) {
                //Próxima pergunta
                DefaultAction();

            } else {
                echo "
					<h1>Oooops...</h1>
					<div class='Error'><p>Ocorreu um erro ao atualizar resposta do Questionário.
						Por favor, faça o <a href='login.php'>acesso</a> e tente novamente.</p>
					</div>";
            }
            break;

        default:
			DefaultAction();
	}
}

function DefaultAction() {
	//Verifica em qual passo está o respondente
	$usr = Users::getCurrent();
	$quest = new Questionario($usr->questid);
	
	$step = $quest->nextstep();
	if (is_string($step)) { 
		switch ($step) {
			case 'info':
				DadosPreliminaresForm();
				break;
			case 'concluido':
				Finalizar();
				break;
		}
	
	} elseif (!$step) {
		//Fetch complete quest before finish
		$quests = new Questionarios;		
		$quest = $quests->item($usr->questid);
		//Finalizar
		$quest->Finalizar();
		Finalizar();
	
	} else {
		//Atualiza andamento
		$color = get_gradient_point_color('FF0000', '00FF00', $quest->andamento);
		echo "<div id='andamento' style='color:$color;'>[$quest->andamento% Concluído]</div>";
		
		//Responde pergunta
        if ($quest->isagrupado == 1)
            RenderGrupoPergunta($step);
		else
            RenderPergunta($step);
	}
}

function RenderPergunta($p) {
	$usr = Users::getCurrent();
	$questid = $usr->questid;
	
	echo "<h1>Item $p->posicao</h1>
	
				<form method='post' action='responder.php' name='frm' onSubmit=\"return validateForm();\">
					<input type='hidden' name='action' id='action' value='update_resposta' />
					<input type='hidden' value='$questid' name='questid' id='questid' />
					<input type='hidden' value='$p->id' name='perguntaid' id='perguntaid' />
				
					<div class='Pergunta'>
						<p>$p->texto</p>
						<div class='Opcoes'>";                            
							foreach ($p->alternativas as $alt) {
								echo "<input type='radio' name='resposta' value='$alt->id' id='resposta$alt->id' /><span>$alt->texto</span>";
							}
	echo "		        </div>
					</div>
					
					<br />
					<div class='Right'>
						<div id='frm_errorloc' class='Error'>&nbsp;</div>
						<input type='image' src='../Images/button-next.jpg' value='Submit' name='Submit' id='cmdSubmit' />
					</div>
					
				</form>	
				
				<script language='JavaScript' type='text/javascript'>
					function validateForm() {						
						
						var radios = document.getElementsByTagName('input');
						var value;
						for (var i = 0; i < radios.length; i++) {
							if (radios[i].type === 'radio' && radios[i].checked) {
								//Add a wait msg
								document.getElementById('frm_errorloc').innerHTML = '<strong>Aguarde...</strong>';
								document.getElementById('cmdSubmit').disabled = true;
								return true;
							}
						}						
						//No answer selected
						document.getElementById('frm_errorloc').innerText = 'Selecione uma resposta antes de prosseguir.';
						return false;
					}
					
					
					/*var vld  = new Validator('frm');
					
					vld.addValidation('resposta','selone_radio','Escolha a resposta antes de prosseguir.');
										
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();*/
				</script>
					";
}

function RenderGrupoPergunta($g) {
    $usr = Users::getCurrent();
    $questid = $usr->questid;

    $pIds = "";
    foreach ($g->perguntas as $p)
        $pIds .= "," . $p->id;
    $pIds = substr($pIds, 1);

    echo "    <form method='post' action='responder.php' name='frm' onSubmit=\"return validateForm();\">
					<input type='hidden' name='action' id='action' value='update_grupo_respostas' />
					<input type='hidden' value='$questid' name='questid' id='questid' />
					<input type='hidden' value='$g->id' name='grupoPerguntaid' id='grupoPerguntaid' />
					<input type='hidden' value='$pIds' name='perguntaIds' id='perguntaIds' />

				<table class='Perguntas' cellspacing='0'>
				    <tr>
				        <th rowspan='2' colspan='2' class='Enunciado'>$g->posicao. $g->texto</th>
				        <th colspan='4' style=\"white-space: nowrap; vertical-align: bottom; font-weight: bold\">Eu acredito que isso acontece na minha vida:</th>
				    </tr>
				    <tr>";
	                    foreach ($g->perguntas[0]->alternativas as $alt)
                            echo "<th style='width: 40px'>$alt->texto</th>";
    echo "
				    </tr>";

            $letter = 97;
            foreach ($g->perguntas as $p) {
    echo "
					<tr>";
					    if ($letter == 97) {
                            echo "<td rowspan='" . count($g->perguntas) ."' class='imagem-pergunta'><img src='ReportImages/QuestAdolescentes_".$g->posicao.".jpg' alt='Imagem' /></td>";
                        }
    echo "
						<td>".chr($letter++).". $p->texto</td>";
                            foreach ($p->alternativas as $alt) {
                                echo "<td class='Center'>
                                        <input class='css-checkbox' type='radio' name='resposta_$p->id' value='$alt->id' id='resposta_".$p->id."_$alt->id' title='$alt->texto' />
                                        <label class='css-label' for='resposta_".$p->id."_$alt->id'></label>
                                      </td>";
                            }
    echo "		    </tr>";
            }

    echo "      </table>
					<div class='Right'>
						<div id='frm_errorloc' class='Error' style=\"display:none;\">&nbsp;</div>
						<input type='image' src='../Images/button-next.jpg' value='Submit' name='Submit' id='cmdSubmit' />
					</div>

				</form>

				<script language='JavaScript' type='text/javascript'>
					function validateForm() {
                        var r = true;

						$.each($('#perguntaIds').val().split(','), function(index, value) {
                            r = r && typeof($(\"[name='resposta_\" + value + \"']:checked\").val()) !== 'undefined';
						});


						if (!r) {
						    $('#frm_errorloc').text('Selecione todas as respostas antes de prosseguir.').show();
						}

						return r;
					}
				</script>
	";
}//RenderGrupoPergunta


function UpdateResposta() {
	$usr = Users::getCurrent();
	$quest = new Questionario($usr->questid);	
	$resposta = getIntPost('resposta');
	$perguntaid = getIntPost('perguntaid');
	
	return $quest->updateResposta($perguntaid, $resposta);
}

function UpdateGrupoRespostas() {
    $usr = Users::getCurrent();
    $quest = new Questionario($usr->questid);
    $ids = getPost('perguntaIds', '');
    $result = true;

    foreach (explode(',', $ids) as $perguntaid) {
        $resposta = getIntPost("resposta_$perguntaid");
        $result &= $quest->updateResposta($perguntaid, $resposta);
    }

    return $result;
}

function DadosPreliminaresForm() {
	$usr = Users::getCurrent();
	$questid = $usr->questid;
	
	echo "<div class='intro'>
	        <h1 class='Center'>Vamos Começar!</h1>
	      </div>";
	
	//recover vars
	$nome = getPost('nome', '', true);
	$email = getPost('email', '', true);
	$cidade = getPost('cidade', '', true);
	$uf = getPost('uf', '', true);
	$uf_nasc = getPost('uf_nasc', '', true);	
	$nasc = getPost('nasc', '', true);
	$sexo = getPost('sexo', '', true);
	$formacaoprofissional = getPost('formacaoprofissional', '', true);
	$atividadeprofissional = getPost('atividadeprofissional', '', true);
	$escolaridade = getPost('escolaridade', '', true);
	$estadocivil = getPost('estadocivil', '', true);
	$religiao = getPost('religiao', '', true);
	$situacao = getPost('situacao', '', true);
	$situacao_duracao = getPost('situacao_duracao', '', true);
	$situacao_qdo = getPost('situacao_qdo', '', true);
	$situacao_comentario = getPost('situacao_comentario', '', true);
	$situacao2 = getPost('situacao2', '', true);
	$situacao2_duracao = getPost('situacao2_duracao', '', true);
	$situacao2_qdo = getPost('situacao2_qdo', '', true);
	$situacao2_comentario = getPost('situacao2_comentario', '', true);
	
	
	echo "<form method='post' action='responder.php' name='frm' id='frm'>
					<input type='hidden' value='update_info' name='action' id='action' />			 
					<input type='hidden' value='$questid' name='questid' id='questid' />					 
				
					<table class='FormQuest'>

						<tr class='Field'>
							<td colspan='3'>Nome Completo <span class='Red'>*</span></td>
						</tr>
						<tr>
							<td colspan='3'><input type='text' size='89' name='nome' id='nome' value='$nome' /></td>
						</tr>

						<tr class='Field'>
							<td colspan='3'>E-mail <span class='Red'>*</span></td>
						</tr>
						<tr>
							<td colspan='3'><input type='text' size='89' name='email' id='email' value='$email' /></td>
						</tr>

						<tr class='Field'>
							<td colspan='3'>Sexo</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::Render('sexo', 'sexo', $sexo); echo "</td>
						</tr>

						<tr class='Field'>
							<td>Data do seu Nascimento <span class='Red'>*</span></td>
							<td>Estado onde Nasceu</td>
							<td></td>
						</tr>
						<tr>
							<td><input type='text' size='10' maxlength='10' name='nasc' alt='date' id='nasc' value='$nasc' /></td>
							<td>"; ListItemPicker::Render('uf_nasc', 'uf', $uf_nasc); echo "</td>
							<td></td>
						</tr>


						<tr class='Field'>
							<td>Cidade Onde Você Mora <span class='Red'>*</span></td>
							<td>Estado de sua cidade</td>
							<td></td>
						</tr>
						<tr>
							<td><input type='text' size='20' name='cidade' id='cidade' value='$cidade' /></td>
							<td>"; ListItemPicker::Render('uf', 'uf', $uf); echo "</td>
							<td></td>
						</tr>


						<tr class='Field'>
							<td>Se você já tem uma formação profissional, anote qual é:</td>
							<td>Se você está trabalhando, anote qual é o seu trabalho</td>
							<td></td>
						</tr>
						<tr>
							<td><input type='text' size='20' maxlength='45' name='formacaoprofissional' id='formacaoprofissional' value='$formacaoprofissional' /></td>
							<td><input type='text' size='20' maxlength='45' name='atividadeprofissional' id='atividadeprofissional' value='$atividadeprofissional' /></td>
							<td></td>
						</tr>

						<tr class='Field'>
							<td>Qual sua escolaridade?</td>
							<td>Estado Civil</td>
							<td></td>
						</tr>
						<tr>
							<td>"; ListItemPicker::Render('escolaridade', 'escolaridade', $escolaridade); echo "</td>
							<td>"; ListItemPicker::Render('estadocivil', 'estadocivil', $estadocivil); echo "</td>
							<td></td>
						</tr>

						<tr class='Field'>
							<td>Religião</td>
							<td colspan='2'>Fala outro língua além do português?</td>
						</tr>
						<tr>
							<td>"; ListItemPicker::Render('religiao', 'religiao', $religiao); echo "</td>
							<td colspan='2'>
								<input type='radio' name='idioma' value='0' checked='checked' /><span class='radio'>Não</span>
								<input type='radio' name='idioma' value='1' /><span class='radio'>Sim</span>
							</td>
						</tr>

                        <tr class='Field'><td colspan='3'>&nbsp;</td></tr>

						<tr class='Field'>
							<td colspan='3'>Marque qual a pessoa que mais ajudou você a vencer na vida, a superar dificuldades pessoais, escolares, doenças, acidentes, etc.</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::RenderCheckboxList('pessoas', 'pessoas_dificuldades', '', true, 'checkbox'); echo "
								<input type='text' name='pessoa_outro' id='pessoa_outro' size='25' />
							</td>
						</tr>
						
						<tr class='Field'><td colspan='3'>&nbsp;</td></tr>
						
						<tr class='Field'>
							<td colspan='3'>Qual foi a doença, o acidente ou a situação de conseqüências mais graves que você já viveu?</td>
						</tr>
						<tr>
							<td colspan='3'><input type='text' size='89' name='situacao' id='situacao' value='$situacao' /></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='3'>Com que idade você estava quando aconteceu?</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::RenderRadioList('situacao_qdo', 'situacao_qdo', $situacao_qdo, true); echo "</td>
						</tr>
						
						<tr class='Field'>
							<td colspan='3'>Quanto tempo aproximadamente durou o impacto dessa situação?</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::RenderRadioList('situacao_duracao', 'situacao_duracao', $situacao_duracao, true); echo "</td>							
						</tr>
						
						<tr class='Field'>
							<td colspan='3'>Comente as consequências desta situação em você.</td>
						</tr>
						<tr>
							<td colspan='3'><textarea cols='69' rows='2' name='situacao_comentario' id='situacao_comentario'>$situacao_comentario</textarea></td>
						</tr>
						
						<tr class='Field'><td colspan='3'>&nbsp;</td></tr>
						
						<tr class='Field'>
							<td colspan='3'>Há uma 2ª situação muito marcante que você quer registrar?</td>
						</tr>
						<tr>
							<td colspan='3'><input type='text' size='89' name='situacao2' id='situacao2' value='$situacao2' /></td>
						</tr>
						
						<tr class='Field'>
							<td colspan='3'>Com que idade você estava quando aconteceu?</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::RenderRadioList('situacao2_qdo', 'situacao_qdo', $situacao2_qdo, true); echo "</td>
						</tr>
						
						<tr class='Field'>
							<td colspan='3'>Quanto tempo aproximadamente durou o impacto dessa situação?</td>
						</tr>
						<tr>
							<td colspan='3'>"; ListItemPicker::RenderRadioList('situacao2_duracao', 'situacao_duracao', $situacao2_duracao, true); echo "</td>							
						</tr>						
						
						<tr class='Field'>
							<td colspan='3'>Comente as consequências desta situação em você.</td>
						</tr>
						<tr>
							<td colspan='3'><textarea cols='69' rows='2' name='situacao2_comentario' id='situacao2_comentario'>$situacao2_comentario</textarea></td>
						</tr>

						<tr>
						    <td colspan='3'>
                                <div id='frm_errorloc' class='Error'>
                                    <p>"; if (isset($GLOBALS['error'])) echo $GLOBALS['error']; echo "</p>
                                </div>

                                <div class='Right'>
                                    <input type='image' src='../Images/button-next.jpg' value='Submit' name='Submit' />
                                </div>
						    </td>
						</tr>
					</table>

				</form>
				
				<script language='JavaScript' type='text/javascript'>
					var vld  = new Validator('frm');
					
					vld.addValidation('nome','alphanumeric_space');
					vld.addValidation('nome','req','Nome é obrigatório');
					
					vld.addValidation('email','email', 'E-mail inválido');
					vld.addValidation('email','req','E-mail é obrigatório');
					
					//vld.addValidation('local','alphanumeric_space');
					//vld.addValidation('local','req','Local é obrigatório');
					
					vld.addValidation('cidade','alphanumeric_space');
					vld.addValidation('cidade','req','Cidade é obrigatória');
					
					vld.addValidation('nasc','alphanumeric_space');
					vld.addValidation('nasc','req','Data de nascimento é obrigatória');
     				vld.addValidation('nasc','data','Data de nascimento inválida');
					
					vld.EnableOnPageErrorDisplaySingleBox();
					vld.EnableMsgsTogether();
				</script>";

}

function DadosPreliminaresUpdate() {
	$usr = Users::getCurrent();
	$quest = new Questionario($usr->questid);
	
	//recover vars
	$values['nome'] =  getPost('nome', '');
	$values['email'] =  getPost('email', '');
	$values['cidade'] =  getPost('cidade', '');
	$values['uf'] =  getPost('uf', '');
	$values['uf_nasc'] =  getPost('uf_nasc', '');	
	$values['nasc'] =  getPost('nasc', '');
	$values['sexo'] =  getPost('sexo', '');
	$values['formacaoprofissional'] =  getPost('formacaoprofissional', '');
	$values['atividadeprofissional'] =  getPost('atividadeprofissional', '');
	$values['escolaridade'] =  getPost('escolaridade', '');
	$values['estadocivil'] =  getPost('estadocivil', '');
	$values['religiao'] =  getPost('religiao', '');
	$values['situacao'] =  getPost('situacao', '');
	$values['situacao_duracao'] =  getPost('situacao_duracao', '');
	$values['situacao_qdo'] =  getPost('situacao_qdo', '');
	$values['situacao_comentario'] =  getPost('situacao_comentario', '');
	
	$values['situacao2'] =  getPost('situacao2', '');
	$values['situacao2_duracao'] =  getPost('situacao2_duracao', '');
	$values['situacao2_qdo'] =  getPost('situacao2_qdo', '');
	$values['situacao2_comentario'] =  getPost('situacao2_comentario', '');
	
	$values['idioma'] =  $_POST['idioma'];
	$values['pessoa_outro'] =  getPost('pessoa_outro', '');
	
	$values['pessoas'] = ',';
	if (isset($_POST['pessoas'])) {
		foreach ($_POST['pessoas'] as $pessoa) {
			//if ($values['pessoas']!='') $values['pessoas']=$values['pessoas'].',';
			$values['pessoas'] = $values['pessoas'].$pessoa.',';
		}
	}

	
	if (!$quest->UpdateInfos($values)) {
		$GLOBALS['error'] = $quest->error;
		return false;
	} else {
		return true;
	}
}

function Finalizar() {
	echo "<div class='finalizar'>
            <h1 class='Center'>Obrigado!</h1>
            <h2 class='Center'>Você completou o Quest_Resiliência</h2>

            <br><p class='Center'>Agora, aguarde o contato do seu gestor para obter os resultados.</p>
          </div>
    ";
}
?>