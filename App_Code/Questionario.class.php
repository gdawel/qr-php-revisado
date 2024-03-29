<?php

include_once 'SqlHelper.class.php';
include_once 'User.class.php';
include_once 'Pergunta.class.php';
include_once 'Pesquisa.class.php';
include_once 'ModeloQuestionario.class.php';

define('QUESTIONARIO_STATUS_NAOINICIADO', 1);
define('QUESTIONARIO_STATUS_EMANDAMENTO', 2);
define('QUESTIONARIO_STATUS_CONCLUIDO', 3);


class Questionario
{
    var $error;
	var $id, $password, $nome, $email, $andamento, $pesquisaid;
    var $isagrupado, $modeloquestionarioid;
	var $iniciadoem, $concluidoem, $status, $statusid;
    var $infos; //array com as infos preliminares;
    var $perguntas, $fatores;

    function __construct($id)
    {
        $this->id = $id;
    }

    function getIsAgrupado()
    {
        $sql = new SqlHelper();

        $sql->command = "SELECT m.ModeloQuestionarioId, m.IsAgrupado
                         FROM pesquisas p
                         INNER JOIN pacotes pacote ON p.PacoteId = pacote.PacoteId
                         INNER JOIN questionarios q ON p.PesquisaId = q.PesquisaId
                         INNER JOIN modelosquestionarios m ON pacote.ModeloQuestionarioId = m.ModeloQuestionarioId
                         WHERE q.QuestionarioId = $this->id";
        $sql->execute();
		
        if ($r = $sql->fetch()) {
            $this->isagrupado = $r['IsAgrupado'];
        }
		var_dump($r);
        return true;
    }
    
    /**
     * Indica se o respondente está em condicao de vulnerabilidade
     * 
     * @return true, se estiver em condicao de vulnerabilidade
     */
    function isVulneravel() {
    	$count = 0;
    	
    	if ($this->fatores) {
    		foreach ($this->fatores as $f) {
    			//echo "- $f->classificacao (".substr($f->classificacao, 0, 5).")<br />";
				if (substr($f->classificacao, 0, 5) == 'Fraca') $count += 1;    			
    		}
    		
            //echo "<pre>VulneravelIndex: $count (#$this->id)\n";
    		if ($count >= 4) 
			 	return true;
			else
				return false;
				
    	} else {
    		return false;
    	}
    }

    /*
    Retorna a próxima etapa ou pergunta do questionario.
    info: infos preliminares
    concluido: quest já respondido
    <obj>: pergunta
    */
    function nextstep()
    {
        $sql = new SqlHelper();

        //Verificar se já foram preenchidos os dados preliminares (Status Não iniciado)
        $sql->command = "SELECT StatusId FROM questionarios 
						 WHERE QuestionarioId = '$this->id'";
        $sql->execute();
        if ($r = $sql->fetch()) {
            switch ($r['StatusId']) {
                case QUESTIONARIO_STATUS_NAOINICIADO:
                    return 'info';
                    break;

                case QUESTIONARIO_STATUS_CONCLUIDO:
                    return 'concluido';
                    break;

                default:
            }
        }

        $p = null; $g = null;

        //get modeloquestionario
        $sql->command = "SELECT m.ModeloQuestionarioId, m.IsAgrupado
                         FROM pesquisas p
                         INNER JOIN pacotes pacote ON p.PacoteId = pacote.PacoteId
                         INNER JOIN questionarios q ON p.PesquisaId = q.PesquisaId
                         INNER JOIN modelosquestionarios m ON pacote.ModeloQuestionarioId = m.ModeloQuestionarioId
                         WHERE q.QuestionarioId = $this->id";
        $sql->execute();
        if ($r = $sql->fetch()) {
            $this->modeloquestionarioid = $r['ModeloQuestionarioId'];
            $this->isagrupado = $r['IsAgrupado'];
        } else {
            $this->error = 'Modelo de Questionário não encontrado para este questionário';
            return null;
        }


        //Get andamento
        $sql->command = "SELECT (
                                (SELECT COUNT(*) FROM questionarios_respostas WHERE QuestionarioId = $this->id)
                                / (SELECT COUNT(*) FROM modelosquestionarios_perguntas
                                     WHERE ModeloQuestionarioId = (SELECT pacotes.ModeloQuestionarioId FROM pacotes, pesquisas, questionarios
                                                                                                 WHERE pacotes.pacoteid = pesquisas.pacoteid and
                                                                                                 pesquisas.PesquisaId = questionarios.PesquisaId and questionarios.QuestionarioId = $this->id)) * 100) AS `Andamento`";
        $sql->execute();
        if ($r = $sql->fetch()) {
            $this->andamento = intval($r['Andamento']);
        }


        if ($this->isagrupado == 1) {
            //se Modelo Questionario for do tipo agrupado, recuperar o grupo pergunta
            $sql->command = "SELECT g.GrupoPerguntaId, g.Texto, g.Posicao, g.ModeloQuestionarioId
                             FROM modelosquestionarios_gruposperguntas g
                             INNER JOIN modelosquestionarios_perguntas p ON p.GrupoPerguntaId = g.GrupoPerguntaId
                             WHERE g.ModeloQuestionarioId = ($this->modeloquestionarioid)
                                   AND p.PerguntaId NOT IN (SELECT PerguntaId FROM questionarios_respostas
                                                            WHERE QuestionarioId = $this->id)
                             ORDER BY g.Posicao LIMIT 0,1";

            $sql->execute();

            if ($r = $sql->fetch()) {
                $g = new GrupoPergunta();
                $g->id = $r['GrupoPerguntaId'];
                $g->posicao = $r['Posicao'];
                $g->texto = $r['Texto'];
                $g->modeloquestionarioid = $r['ModeloQuestionarioId'];
            } else {
                return null; //todas as perguntas ja foram respondidas
            }

            //recupera as perguntas do grupo
            $sql->command = "SELECT m.PerguntaId, m.Texto, m.Posicao, m.GrupoAlternativasId
                             FROM modelosquestionarios_perguntas m
                             WHERE m.ModeloQuestionarioId = ($this->modeloquestionarioid)
                                   AND m.GrupoPerguntaId = ($g->id)
                             ORDER BY IFNULL(m.PosicaoGrupo, m.Posicao)";
            $sql->execute();

            while ($r = $sql->fetch()) {
                $p = new Pergunta();

                $p->id = $r['PerguntaId'];
                $p->texto = $r['Texto'];
                $p->posicao = $r['Posicao'];
                $p->grupoalternativas = $r['GrupoAlternativasId'];

                $g->perguntas[] = $p;
            }

            //Get alternativas. Assumindo que as alternativas sao identicas para o mesmo grupo
            $sql->command = "SELECT a.* FROM modelosquestionarios_alternativas a
                             INNER JOIN modelosquestionarios_gruposalternativas g ON g.AlternativaId = a.AlternativaId
                             WHERE g.GrupoAlternativasId = " . $g->perguntas[0]->grupoalternativas . "
                             ORDER BY g.Posicao";
            $sql->execute();

            while ($r = $sql->fetch()) {
                $alt = new Alternativa();
                $alt->id = $r['AlternativaId'];
                $alt->texto = $r['Texto'];
                $alt->valor = $r['Valor'];

                foreach ($g->perguntas as $p)
                    $p->alternativas[] = $alt;
            }

            return $g;

        } else { //nao é agrupado
            //Prox pergunta
            $sql->command = "SELECT m.PerguntaId, m.Texto, m.Posicao, m.GrupoAlternativasId
                             FROM modelosquestionarios_perguntas m
                             WHERE m.ModeloQuestionarioId = ($this->modeloquestionarioid)
                                   AND m.PerguntaId NOT IN (SELECT PerguntaId FROM questionarios_respostas
                                                            WHERE QuestionarioId = $this->id)
                             ORDER BY Posicao LIMIT 0,1";
            $sql->execute();

            if ($r = $sql->fetch()) {
                $p = new Pergunta();

                $p->id = $r['PerguntaId'];
                $p->texto = $r['Texto'];
                $p->posicao = $r['Posicao'];
                $p->grupoalternativas = $r['GrupoAlternativasId'];

                //Get alternativas
                $sql->command = "SELECT a.* FROM modelosquestionarios_alternativas a
                                                    INNER JOIN modelosquestionarios_gruposalternativas g ON g.AlternativaId = a.AlternativaId
                                                    WHERE g.GrupoAlternativasId = $p->grupoalternativas
                                                    ORDER BY g.Posicao";
                $sql->execute();

                while ($r = $sql->fetch()) {
                    $alt = new Alternativa();
                    $alt->id = $r['AlternativaId'];
                    $alt->texto = $r['Texto'];
                    $alt->valor = $r['Valor'];

                    $p->alternativas[] = $alt;
                }

            } else { //todas as perguntas ja foram respondidas
                $p = null;
            }

            return $p;
        }
    }

    /*Atualiza as infos preliminares*/
    function updateInfos($values)
    {
        $sql = new SqlHelper();

        $sql->command = "UPDATE questionarios SET
											Nome=" . $sql->escape_string($values['nome'], true) . ", 
											Email=" . $sql->escape_string($values['email'], true) . ",  
											SexoId=" . $sql->escape_string($values['sexo'], true) . ", 
											UFNascimento=" . $sql->escape_string($values['uf_nasc'], true) . ", 
											DataNascimento=" . $sql->prepareDate($values['nasc']) . ", 
											FormacaoProfissional=" . $sql->escape_string($values['formacaoprofissional'], true) . ",
											AtividadeProfissional=" . $sql->escape_string($values['atividadeprofissional'], true) . ", 
											EscolaridadeId=" . $sql->escape_string($values['escolaridade'], true).", 
											Cidade=" . $sql->escape_string($values['cidade'], true) . ", 
											UF=" . $sql->escape_string($values['uf'], true) . ", 
											EstadoCivilId=" . $sql->escape_string($values['estadocivil'], true).", 
											ReligiaoId=" . $sql->escape_string($values['religiao'], true) . ", 
											SituacaoGrave=" . $sql->escape_string($values['situacao'], true) .", 
											SituacaoGraveQuandoid=" . $sql->escape_id($values['situacao_qdo']) . ", 
											SituacaoGraveDuracaoId=" . $sql->escape_id($values['situacao_duracao']) . ",
											SituacaoGraveComentario=" . $sql->escape_string($values['situacao_comentario'], true) .",											
											SituacaoGrave2=" . $sql->escape_string($values['situacao2'], true) .", 
											SituacaoGrave2Quandoid=" . $sql->escape_id($values['situacao2_qdo']) . ", 
											SituacaoGrave2DuracaoId=" . $sql->escape_id($values['situacao2_duracao']) . ",
											SituacaoGrave2Comentario=" . $sql->escape_string($values['situacao2_comentario'], true) .",
											PessoasDificuldade=" . $sql->escape_string($values['pessoas'], true) . ", 
											PessoaDificuldadeOutro=" . $sql->escape_string($values['pessoa_outro'], true) . ", 
											Idioma=" . $sql->escape_string($values['idioma'], false) . ",
											IniciadoEm='" . date('Y-m-d H:i') . "',
											StatusId = ".QUESTIONARIO_STATUS_EMANDAMENTO."
										WHERE QuestionarioId = $this->id";
        $r = $sql->execute();
        $this->error = $sql->error;
        return $r;

        //echo $sql->command;
        //return false;
    }


	 /**
	  * Atualizar informações básicas do respondente para envio de comunicação em geral. Essa informações
	  * são armazenadas nas Info dos Dados-Preliminares sóciodemográficos.
	  * 
	  * @param mixed $nome	Nome do respondente.
	  * @param mixed $email	E-mail do respondente.
	  * @return boolean
	  */
	 function updateBasicInfos($nome, $email) {
	 	$sql = new SqlHelper();
	 	$sql->command = "UPDATE questionarios SET Nome = " . $sql->escape_string($nome, true) . 
		 														", Email = " . $sql->escape_string($email, true) . 
		 														" WHERE QuestionarioId = $this->id";
		
		$r = $sql->execute();
      $this->error = $sql->error;
      return $r; 														
	 }
	 
	 
	 /**
	  * Envia e-mail de notificação ao respondente, informando login e senha para acessar Quest.
	  * 
	  * @return void true, if successful.
	  */
	 function sendNotification() {
	 	if (!$this->infos['Email']) {
	 		$this->error = 'E-mail deve ser informado para o envio da notificação';
	 		return false;
	 	}
	 	$nome = ($this->infos['Nome']) ? 'Olá, '.$this->infos['Nome'].'.' : 'Olá.';
	 	
	 	$msg = new Email();
	 	$msg->to = $this->infos['Email'];
	 	$msg->subject = utf8_encode('Seu acesso ao QUEST_Resiliência');
	 	$msg->message = utf8_encode("
                            <html>
		 						<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<img src='http://www.sobrare.com.br/css/images/logo2_small.png' alt='SOBRARE' align='right' />
									<p>$nome</p>
									<br />
									<p>Você foi convidado a responder o QUEST_Resiliência!</p>
									<p>Acesse <a href='http://sobrare.com.br/Quest/login.php' title='Acesse o QUEST_Resiliência'>aqui</a> site da SOBRARE e informe seus dados de acesso abaixo.</p>
									<table>
										<tr>
											<th>Usuário: </th>
											<td>$this->id</td>
										</tr>
										<tr>
											<th>Senha: </th>
											<td>$this->password</td>
										</tr>
									</table>
									<p>Obrigado.</p>
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resiliência</p>
								</body>
                            </html>
        ");
		return $msg->send();
	 }

    function updateResposta($id, $resposta)
    {
        $sql = new SqlHelper();

        $sql->command = "DELETE FROM questionarios_respostas WHERE QuestionarioId = $this->id AND PerguntaId = $id";
        $sql->execute();

        $sql->command = "INSERT INTO questionarios_respostas (QuestionarioId, PerguntaId, RespostaId) VALUES ($this->id, $id, $resposta)";
        return $sql->execute();
    }

    function Finalizar()
    {
        $sql = new SqlHelper();

        $now = date('Y-m-d H:i');
        $sql->command = "UPDATE questionarios SET ConcluidoEm = '$now', StatusId = ".QUESTIONARIO_STATUS_CONCLUIDO." WHERE QuestionarioId = $this->id";
        $sql->execute();

        $this->Calculate();        
        $this->sendNotificationQuestionarioConcluido();
    }

    function Calculate()
    {    	
        //somente pesquisas ativas
		
		  $pesquisas = new Pesquisas();
		  $p = $pesquisas->item($this->pesquisaid);
		  if ((!$p) || ($p->statusid != PESQUISA_STATUS_ATIVA)) {
	  			$this->error = 'Somente é possível calcular fatores de um questionário em uma pesquisa ativa.';
	  			return false;
		  }
		  
        $modelos = new ModelosQuestionarios();
        $fatores = $modelos->getFatoresByQuestionarioId($this->id);

        if (!$fatores)
            return false;
        if (!$respostasArray = $this->getRespostasValuesArray()) {
        		$this->error = 'Para calcular os fatores, o questionário deve estar respondido.';
            return false;
        }

        //Limpa calc anterior
        $this->ResetFatores();
		
        foreach ($fatores as $fator) {
            $formula_eval = strtr($fator->formacalculo, $respostasArray);
			
            //check consistency
            if (strpos($formula_eval, '[') === false) {
            } else {
                $this->error = 'Não é possível calcular fatores com questionário incompleto';
                return false;
            }

            eval("\$fator->result = $formula_eval;");
            //echo $formula_eval;
            //echo " => $fator->nome = $fator->result\n";

            //Regras e autoexcludente
            if ($fator->autoexcludentes) {
                foreach ($fator->autoexcludentes as $ae) {
                    
					$ae_validation = true;

                    foreach ($ae->regras as $regra) {
						
                        switch ($regra->operador) {
                            case '=':
                                if (intval($respostasArray['[Q' . $regra->perguntaposicao . ']']) == intval($regra->valorreferencia)) {
                                    $ae_validation = ($ae_validation && true);
									
                                } else {
                                    $ae_validation = false;
                                }
                                break;
                        }
                    }

                    if ($ae_validation) {
                        //Os valores de correcao das autoexcludentes sao acumulativas
                        $fator->result += $ae->valorcorrecao;
						
                        //echo "    => $fator->nome = $fator->result + $ae->valorcorrecao\n";
                    }
                }
            }
            
            $this->StoreFatorValue($fator->id, $fator->result);
        }

        return true;
    }

     /**
	  * Envia e-mail de notificação ao gestor, informando conclusao de um questionario.
	  * 
	  * @return void true, if successful.
	  */
	 function sendNotificationQuestionarioConcluido() {
	 	$pesquisas = new Pesquisas();
	 	$pesquisa = $pesquisas->item($this->pesquisaid);
	 	
	 	if (!$pesquisa) {
	 		$this->error = 'Pesquisa não encontrada.';
	 		return false;
	 	}
	 	
	 	$users = new Users();
	 	$pesquisador = $users->item($pesquisa->pesquisadorid);
	 	
	 	if (!$pesquisador) {
	 		$this->error = 'Gestor não encontrado.';
	 		return false;
	 	}
	 	if (!$pesquisador->email) {
	 		$this->error = 'E-mail deve ser informado para o envio da notificação';
	 		return false;
	 	}
	 	$nome = "Olá, $pesquisador->nome.";
	 	
	 	$msg = new Email();
	 	$msg->to = $pesquisador->email;
	 	$msg->subject = utf8_decode("Questionário #$this->id concluído");
	 	$msg->message = "<html>
		 						<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<img src='http://www.sobrare.com.br/css/images/logo2_small.png' alt='SOBRARE' align='right' />
									<p>$nome</p>
									<br />
									<p>O questionário #$this->id, da sua pesquisa '$pesquisa->titulo' está concluído e disponível para consulta na Área do Gestor.</p>
									<p>Acesse <a href='http://www.sobrare.com.br/cockpit/quest.php?id=$this->id' title='Acesse o QUEST_Resiliência'>aqui</a> o site da 
									SOBRARE e informe seus dados de acesso, caso queira visualizar o questionário.</p>
									<br /><br />
									<p>Obrigado.</p>
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resiliência</p>
								</body></html>";
		return $msg->send();
	 }
	 
    /*Apaga do banco de dados os valores calculados dos fatores do questionario*/
    function ResetFatores()
    {		  
		  $sql = new SqlHelper();

        $sql->command = "DELETE FROM questionarios_fatores WHERE QuestionarioId = $this->id";
        return $sql->execute();
    }

    /*Apaga do banco de dados as respostas do questionario*/
    function ResetRespostas($deleteinfo = false)
    {
        $sql = new SqlHelper();

        $sql->command = "DELETE FROM questionarios_respostas WHERE QuestionarioId = $this->id";
        if ($sql->execute()) {
        		if (!$deleteinfo)
            	$sql->command = "UPDATE questionarios SET 
												StatusId = ".QUESTIONARIO_STATUS_EMANDAMENTO.", ConcluidoEm=NULL 
											WHERE QuestionarioId = $this->id";
            else 
            	$sql->command = "UPDATE questionarios SET 
												StatusId = ".QUESTIONARIO_STATUS_NAOINICIADO.", ConcluidoEm=NULL, 
												Nome=NULL, Email=NULL, SexoId = NULL, UFNascimento = NULL, 
												DataNascimento=NULL, FormacaoProfissional=NULL,
												EscolaridadeId=NULL, IdadeId=NULL, Cidade=NULL, UF=NULL, EstadoCivilId=NULL, 
												ReligiaoId=NULL, SituacaoGrave=NULL, SituacaoGraveQuandoId=NULL, SituacaoGraveDuracaoId=NULL,
												PessoasDificuldade=NULL, PessoaDificuldadeOutro=NULL, Idioma=NULL, IniciadoEm=NULL, 
												AtividadeProfissional=NULL, SituacaoGrave2=NULL, SituacaoGrave2QuandoId=NULL, SituacaoGrave2DuracaoId=NULL
											WHERE QuestionarioId = $this->id";
            	
            return $sql->execute();
        } else {
            return false;
        }
    }

    
    /**
     * Apaga as infos do questionario. Permitido somente para Admins.
     * 
     * @param bool $deleteinfo Indica se as infos sócio-demograficas também devem ser excluídas.
     * @return
     */
    function ResetQuest($deleteinfo = false)
    {
        $usr = Users::getCurrent();
        if (!$usr) {
        	$this->error = 'Usuário não logado';
        	return false;
        }
        
        if (!$usr->isinrole('Admin')) {
        	$this->error = 'Somente administradores podem reiniciar um questionário.';
        	return false;
        }
		  
		  //somente pesquisas ativas
		  $pesquisas = new Pesquisas();
		  $p = $pesquisas->item($this->pesquisaid);
		  if ((!$p) || ($p->statusid != PESQUISA_STATUS_ATIVA)) {
	  			$this->error = 'Somente é possível reiniciar um questionário em uma pesquisa ativa.';
	  			return false;
		  }
		  
		  $ret = $this->ResetFatores();
        if ($ret)
            $ret = $this->ResetRespostas($deleteinfo);
        return $ret;
    }


    /**
     * Valor final do fator, considerando casos especiais e autoexcludentes
     * 
     * @return
     */
    function StoreFatorValue($FatorId, $Valor)
    {
		
        $sql = new SqlHelper();
        $sql->command = "INSERT INTO questionarios_fatores (QuestionarioId, FatorId, Valor) 
      					VALUES ($this->id, $FatorId, ".$sql->prepareDecimal($Valor).")"; 
		
        return $sql->execute();
    }

    
    /**
     * Apaga todas as respostas ou uma resposta específica, se $id for informado
     * 
     * @return true, whether successful
     */
    function DeleteRespostas($RespostaId = null)
    {
        $sql = new SqlHelper();

        if ($RespostaId) {
            $sql->command = "DELETE FROM questionarios_respostas WHERE QuestionarioId = $this->id AND RespostaId = $RespostaId";
        } else {
            $sql->command = "DELETE FROM questionarios_respostas WHERE QuestionarioId = $this->id";
        }
        return $sql->execute();
    }

    /*
    Retorna um array, indexado pela chave [QPos], com os valores das respostas dados.
    */
    function getRespostasValuesArray()
    {
        $sql = new SqlHelper();

        $sql->command = "SELECT q.QuestionarioId, p.PerguntaId, p.Posicao, a.Valor FROM questionarios_respostas q
											INNER JOIN modelosquestionarios_perguntas p ON q.PerguntaId = p.PerguntaId
											INNER JOIN modelosquestionarios_alternativas a ON q.RespostaId = a.AlternativaId
											WHERE q.QuestionarioId = $this->id
											ORDER BY p.Posicao";
        $sql->execute();

        while ($r = $sql->fetch()) {
            $key = '[Q' . $r['Posicao'] . ']';
            $lst[$key] = $r['Valor'];
        }

        if (isset($lst)) {
            return $lst;
        } else {
            return null;
        }
    }
    
}


class Questionarios
{
	var $error;
	
    function __construct()
    {
    }

    function item($id)
    {
        $sql = new SqlHelper();
        
        $sql->command = "select `q`.`QuestionarioId` AS `QuestionarioId`, q.Password, `p`.`Titulo` AS `Pesquisa`,`q`.`IniciadoEm` AS `IniciadoEm`,`q`.`ConcluidoEm` AS `ConcluidoEm`,
														`q`.`Nome` AS `Nome`,`q`.`Email` AS `Email`,`sexo`.`Sexo` AS `Sexo`,
														`q`.`UFNascimento` AS `UFNascimento`,`q`.`DataNascimento` AS `DataNascimento`,`q`.`FormacaoProfissional` AS `FormacaoProfissional`, `q`.`AtividadeProfissional` AS `AtividadeProfissional`,
														`escola`.`Escolaridade` AS `Escolaridade`,`q`.`Cidade` AS `Cidade`,`q`.`UF` AS `UF`,`civil`.
														`EstadoCivil` AS `EstadoCivil`,`rel`.`Religiao` AS `Religiao`,
														ifnull(`q`.`SituacaoGrave`, '') AS `SituacaoGrave`,
														ifnull(`qdo`.`Quando`, '') AS `Quando`,
														ifnull(`duracao`.`Duracao`, '') AS `Duracao`,
														SituacaoGraveComentario,
														ifnull(`q`.`SituacaoGrave2`, '') AS `SituacaoGrave2`,
														ifnull(`qdo2`.`Quando`, '') AS `Quando2`,
														ifnull(`duracao2`.`Duracao`, '') AS `Duracao2`,
														SituacaoGrave2Comentario,
														(case `q`.`Idioma` when 1 then 'Sim' else 'Não' end) AS `Idioma`,
														q.Idioma AS `IdiomaId`,
														`st`.`Status` AS `Status`,
														pac.ModeloQuestionarioId, p.PesquisaId, 
														group_concat(pes.Pessoa) as `PessoasDificuldade`,
														q.PessoasDificuldade AS `PessoasDificuldadeId`,
														q.PessoaDificuldadeOutro, 
														q.SexoId, q.IdadeId, q.EscolaridadeId, q.EstadoCivilId, q.ReligiaoId, 
														q.SituacaoGraveDuracaoId, q.SituacaoGraveQuandoId,
														q.SituacaoGrave2DuracaoId, q.SituacaoGrave2QuandoId,
														q.StatusId,
														`ufs`.Id as `UFId`, `ufs_nasc`.Id as `UFNascimentoId`
												from `questionarios` `q` 
												join `pesquisas` `p` on `p`.`PesquisaId` = `q`.`PesquisaId`
            								join pacotes pac on p.pacoteid = pac.pacoteid                                                  
												left join `sexos` `sexo` on `sexo`.`SexoId` = `q`.`SexoId`
												left join `ufs` `ufs` on `ufs`.UF = `q`.UF
												left join `ufs` `ufs_nasc` on `ufs_nasc`.UF = `q`.UFNascimento  
												left join `escolaridades` `escola` on `escola`.`EscolaridadeId` = `q`.`EscolaridadeId`  
												left join `estadoscivis` `civil` on `civil`.`EstadoCivilId` = `q`.`EstadoCivilId`  
												left join `religioes` `rel` on `rel`.`ReligiaoId` = `q`.`ReligiaoId`  
												left join `situacoesgraves_duracao` `duracao` on `duracao`.`SituacaoGraveDuracaoId` = `q`.`SituacaoGraveDuracaoId`  
												left join `situacoesgraves_quando` `qdo` on `qdo`.`SituacaGraveQuandoId` = `q`.`SituacaoGraveQuandoId`
												left join `situacoesgraves_duracao` `duracao2` on `duracao2`.`SituacaoGraveDuracaoId` = `q`.`SituacaoGrave2DuracaoId`  
												left join `situacoesgraves_quando` `qdo2` on `qdo2`.`SituacaGraveQuandoId` = `q`.`SituacaoGrave2QuandoId`
												left join pessoasdificuldades pes on locate(concat(',',pes.pessoadificuldade,','), q.PessoasDificuldade)  
												left join `questionarios_status` `st` on `st`.`StatusId` = `q`.`StatusId` 
											WHERE q.QuestionarioId = $id";

        if ($sql->execute()) {
            if ($r = $sql->fetch()) {
                $quest = new Questionario($r['QuestionarioId']);

                $quest->id = $r['QuestionarioId'];
					 $quest->nome = $r['Nome'];
                $quest->pesquisaid = $r['PesquisaId'];
                $quest->modeloquestionarioid = $r['ModeloQuestionarioId'];
                $quest->status = $r['Status'];
                $quest->concluidoem = $r['ConcluidoEm'];
                $quest->iniciadoem = $r['IniciadoEm'];
                $quest->password = $r['Password'];
                $quest->email = $r['Email'];
                $quest->infos = $r;
            } else {
                return null;
            }

        } else {
            throw new Exception($sql->error);
        }

        //Perguntas
        $sql->command = "SELECT Pergunta, Resposta, RespostaValor, Posicao 
											 FROM view_export_questionarios_respostas WHERE QuestionarioId = $id";

        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $p = new Pergunta();

                $p->texto = $r['Pergunta'];
                $p->resposta = $r['Resposta'];
                $p->respostavalor = $r['RespostaValor'];
                $p->posicao = $r['Posicao'];

                $quest->perguntas[$p->posicao] = $p;
            }
        } else {
            throw new Exception($sql->error);
        }
        
			
        //Fatores
        $sql->command = "Select mdf.FatorId, f.Nome as `Fator`, f.Sigla, mdf.Descricao AS `DescricaoFator`, qf.Valor, 
		  													mdf.DescricaoFracaResilienciaPCP, mdf.DescricaoFracaResilienciaPCI,
															(SELECT vr.Classificacao FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `Classificacao`,
															(SELECT vr.ClassificacaoDetalhada FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `ClassificacaoDetalhada`,
															(SELECT vr.Devolutiva FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `Devolutiva`,
															(SELECT vr.DevolutivaDetalhamento FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS 'DevolutivaDetalhamento',
														 	(SELECT vr.Descricao FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `ValorDescricao`,
															(SELECT vr.LimiteInferior FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId 
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `ValorRefMin`,
 															(SELECT vr.LimiteSuperior FROM modelosquestionarios_valoresreferencia vr
															 WHERE vr.ModeloQuestionarioId = $quest->modeloquestionarioid AND vr.FatorId = mdf.FatorId 
																		 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `ValorRefMax`
											FROM modelosquestionarios_fatores mdf
											left join fatores f on f.FatorId = mdf.FatorId
											left join questionarios_fatores qf on (mdf.FatorId = qf.FatorId and qf.questionarioid = $quest->id)
											where mdf.ModeloQuestionarioId = $quest->modeloquestionarioid
											order by f.Nome";

        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $f = new Fator();

                $f->id = $r['FatorId'];
                $f->nome = $r['Fator'];
                $f->sigla = $r['Sigla'];
                $f->result = $r['Classificacao'];
                $f->valor = $r['Valor'];
                $f->valorrefmin = $r['ValorRefMin'];
                $f->valorrefmax = $r['ValorRefMax'];
                $f->valordescricao = $r['ValorDescricao'];
                $f->classificacao = $r['Classificacao'];
                $f->classificacaodetalhada = $r['ClassificacaoDetalhada'];
                $f->descricao = $r['DescricaoFator'];
                $f->descricaoFracaResilienciaPCP = $r['DescricaoFracaResilienciaPCP'];
                $f->descricaoFracaResilienciaPCI = $r['DescricaoFracaResilienciaPCI'];
                $f->devolutiva = $r['Devolutiva'];
                $f->devolutivadetalhamento = $r['DevolutivaDetalhamento'];

                $quest->fatores[$f->id] = $f;
            }

        } else {
            throw new Exception($sql->error);
        }

        return $quest;
    }

	function itemsByPesquisa($pesquisa, $filter = null) {
		$sql = new SqlHelper();

		switch ($pesquisa->tipoid) {
			case 1: //normal
	        $sql->command = "SELECT q.QuestionarioId
	        						 FROM questionarios q
									 WHERE q.PesquisaId = $pesquisa->id";
				break;
				
			case 2: //aglutinadora
				$sql->command = "SELECT q.QuestionarioId
        						 FROM questionarios q
								 WHERE QuestionarioId IN (SELECT QuestionarioId FROM pesquisas_questionariosaglutinados WHERE PesquisaId = $pesquisa->id)";
				break;
			
			default:
				throw new Exception('Tipo de Pesquisa inválido');
         	return false;					 
		}
     //Set filter
     if (!$filter) $filter = new Filter();
     $filter->generate(false); //retira o WHERE
     $sql->command .= " AND $filter->expression";

     //Order by
		$sql->command .= " ORDER BY QuestionarioId";

     if ($sql->execute()) {
         while ($r = $sql->fetch()) {
       		$q = $this->item($r['QuestionarioId']);	
       		//ajustar PesquisaId, devido à pesquisa aglutinadora
       		$q->pesquisaid = $pesquisa->id;
				//add ao array 
         	$lst[] = $q;
         }
         if (isset($lst)) {
             return $lst;
         } else {
             return null;
         }

     } else {
         throw new Exception($sql->error);
         return false;
     }
	}
 	
 	
 	/**
 	 * Retorna lista com informações básicas dos questionarios.
 	 * 
 	 * @param mixed $filter
 	 * @param string $orderby
 	 * @return Questionario array
 	 */
 	function listaByPesquisa($pesquisa, $filter = null, $orderby = 'QuestionarioId') {
 		$sql = new SqlHelper();
 		
 		if (!$filter) $filter = new Filter(); 		
 		switch ($pesquisa->tipoid) {
 			case 1: //normal
 				$filter->add('q.PesquisaId', '=', $pesquisa->id);
 				$sql->command = "SELECT q.QuestionarioId, q.Nome, q.Email, q.Password, q.IniciadoEm, q.ConcluidoEm, 
				 							q.StatusId, st.Status
				 						FROM questionarios q
				 						JOIN questionarios_status st ON q.StatusId = st.StatusId
				 						$filter->expression
										 ORDER BY $orderby";	
 				break;
 				
 			case 2: //aglutinada
 				$sql->command = "SELECT q.QuestionarioId, q.Nome, q.Email, q.Password, q.IniciadoEm, q.ConcluidoEm, 
				 							q.StatusId, st.Status
				 						FROM questionarios q
				 						JOIN pesquisas_questionariosaglutinados pqa ON (pqa.QuestionarioId = q.QuestionarioId AND pqa.PesquisaId = $pesquisa->id)
				 						JOIN questionarios_status st ON q.StatusId = st.StatusId
				 						$filter->expression
										 ORDER BY $orderby";
 				break;
 				
 			default:
 				throw new Exception('Tipo de Pesquisa inválido.');
 				return false;
 		}
 		
								 
		if ($sql->execute()) {
			//return $sql->dataset();
			while ($r = $sql->fetch()) {
				$q = new Questionario($r['QuestionarioId']);
				//$q->id = $r['QuestionarioId'];
				$q->nome = $r['Nome'];
				$q->email = $r['Email'];
				$q->password = $r['Password'];
				$q->iniciadoem = $r['IniciadoEm'];
				$q->concluidoem = $r['ConcluidoEm'];
				$q->statusid = $r['StatusId'];
				$q->status = $r['Status'];
				
				$lst[] = $q;
			}
			if (isset($lst)) return $lst; else return null;
		} else {
			$this->error = $sql->error;
			return false;
		}								 
 	}
 	
	function addToPesquisa($pesquisa, $count = 1, $checkSaldo = true, $sql = null) {
		if (!$pesquisa) {
			$this->error = 'Pesquisa não encontrada';
			return false;
		}
		
		if ($pesquisa->statusid != PESQUISA_STATUS_ATIVA) {
			$this->error = "Somente é possível adicionar questionários a uma pesquisa ativa (Status atual: $pesquisa->statusid).";
			return false;
		}

        $pesquisas = new Pesquisas();

        //Verifica se pesquisador possui creditos
		if ($checkSaldo) {		
	 		if (!$pesquisas->checkCredito($pesquisa->pesquisadorid, $pesquisa->pacote->id, $pesquisa->produtos, $count, false)) {
	 			//$this->error = 'Saldo insuficiente para incluir novos questionários. Verifique <a href="creditos_saldo.php">aqui</a> seus créditos disponíveis.';
	 			$this->error = $pesquisas->error;
	 			return false;
	 		}
	 	}
		
		//Do it
        if ($sql == null)
            $sql = new SqlHelper();
		
		for ($index = 1; $index <= $count; $index++) {
			$password = substr(md5(uniqid()), 0, 7);			
			$sql->command = "INSERT INTO questionarios (PesquisaId, Password) VALUES ($pesquisa->id, '$password')";
			if (!$sql->execute()) {
				$this->error = $sql->error;
				return false;
			}
		}
		
		//Atualizar info da pesquisa
        if ($checkSaldo)
		    if (!$pesquisas->updateQtdeQuest($pesquisa->id)) $this->error = $pesquisas->error;

		return true;
	}    
	
	
	/**
	 * Associa (aglutina) questionários a uma pesquisa do tipo aglutinadora
	 * 
	 * @param mixed $pesquisaId
	 * @param array $quests_ids. Array com os ID's dos questionários
	 * @return
	 */
	function aglutinarToPesquisa($pesquisa, $quests_ids, $sql = null) {
		if (!$pesquisa) {
			$this->error = 'Pesquisa não encontrada';
			return false;
		}
                
		if ($pesquisa->statusid != PESQUISA_STATUS_ATIVA) {
			$this->error = 'Somente é possível aglutinar questionários a uma pesquisa ativa. ' . $pesquisa->statusid;
			return false;
		}
		if ($pesquisa->tipoid != PESQUISA_TIPO_AGLUTINADORA) {
			$this->error = 'Tipo da Pesquisa deve ser do tipo Aglutinadora.';
			return false;
		}
		
		//Do it
		$this->error = null;
        if (!$sql) {
		    $sql = new SqlHelper();
            $updateCount = true;
        }
        else
            $updateCount = false;

		$sql->begin();
		
		foreach ($quests_ids as $q) {
            $pesquisaId = $pesquisa->id;

			if (is_numeric($q)) {
				$sql->command = "INSERT INTO pesquisas_questionariosaglutinados (PesquisaId, QuestionarioId) VALUES ($pesquisaId, $q)";
				if (!$sql->execute()) {
					if (strpos(strtolower($sql->error), 'duplicate entry') !== false) {
						$this->error .= "<li>O questionário $q já está aglutinado</li>";
					} else {
						$this->error .= "<li>O questionário $q não existe</li>";	
					}					
				}
			} else {
				$this->error .= '<li>O questionário '.htmlentities($q).' é inválido</li>';
			}
		}
		
		//Atualizar info da pesquisa
        if ($updateCount) {
            $pesquisas = new Pesquisas();
            if (!$pesquisas->updateQtdeQuest($pesquisa->id))
                $this->error .= $pesquisas->error;
        }

		if ($this->error) {
			$sql->rollback();
			return false;
		} else {
			$sql->commit();
			return true;
		}
	}
	
	/**
	 * Remove questionários aglutinados a uma pesquisa do tipo aglutinadora
	 * 
	 * @param mixed $pesquisaId
	 * @param array $quests_ids. Array com os ID's dos questionários
	 * @return
	 */
	function desaglutinarToPesquisa($pesquisa, $quests_ids, $sql = null) {
		if (!$pesquisa) {
			$this->error = 'Pesquisa não encontrada';
			return false;
		}
		if ($pesquisa->statusid != PESQUISA_STATUS_ATIVA) {
			$this->error = 'Somente é possível remover questionários de uma pesquisa ativa.';
			return false;
		}
		if ($pesquisa->tipoid != PESQUISA_TIPO_AGLUTINADORA) {
			$this->error = 'Tipo da Pesquisa deve ser do tipo Aglutinadora.';
			return false;
		}
		
		//Do it
		$this->error = null;
		if (!$sql) {
            $sql = new SqlHelper();
            $updateCount = true;
        }
        else
            $updateCount = false;
		$sql->begin();
		
		foreach ($quests_ids as $q) {
			if (is_numeric($q)) {
				$sql->command = "DELETE FROM pesquisas_questionariosaglutinados WHERE PesquisaId = $pesquisa->id AND QuestionarioId = $q";
				if (!$sql->execute()) {
					$this->error .= "<li>O questionário $q não existe</li>";
				}
			} else {
				$this->error .= '<li>O questionário '.htmlentities($q).' é inválido</li>';
			}
		}
		
		//Atualizar info da pesquisa
        if ($updateCount) {
            $pesquisas = new Pesquisas();
            if (!$pesquisas->updateQtdeQuest($pesquisa->id))
                $this->error .= $pesquisas->error;
        }

		if ($this->error) {
			$sql->rollback();
			return false;
		} else {
			$sql->commit();
			return true;
		}
	}
	
	/**
	 * Exclui questionarios nao iniciados e nao nomeados a respondentes.
	 * 
	 * @param mixed $pesquisaid
	 * @param integer $count
	 * @return
	 */
	function removeFromPesquisa($pesquisaid, $count = 1) {
		$pesquisas = new Pesquisas();
		$pesquisa = $pesquisas->item($pesquisaid);
		
		if (!$pesquisa) {
			$this->error = 'Pesquisa não encontrada';
			return false;
		}
		
		if ($pesquisa->statusid != PESQUISA_STATUS_ATIVA) {
			$this->error = 'Somente é possível excluir questionários de uma pesquisa ativa.';
			return false;
		}		
		
		$sql = new SqlHelper();
		
		#Somente remove questionarios nao iniciados e nao nomeados a respondentes
		$sql->command = "DELETE FROM questionarios 
								WHERE ((PesquisaId = $pesquisaid) AND (StatusId = ".QUESTIONARIO_STATUS_NAOINICIADO.") AND (Email IS NULL)) 
								ORDER BY QuestionarioId DESC 
								LIMIT $count";
		if (!$sql->execute()) {
			$this->error = $sql->error;
			return false;
		}
		
		//atualiza count da pesquisa
		if (!$pesquisas->updateQtdeQuest($pesquisaid)) $this->error = $pesquisas->error;
		return true;	
	}
}
?>