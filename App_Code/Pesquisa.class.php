<?php
include_once 'SqlHelper.class.php';
include_once 'Questionario.class.php';
include_once 'Produto.class.php';
include_once 'ModeloQuestionario.class.php';

//Definir constantes
define('PESQUISA_STATUS_ATIVA', 1);
define('PESQUISA_STATUS_CANCELADA', 3);
define('PESQUISA_STATUS_ENCERRADA', 4);
define('PESQUISA_TIPO_NORMAL', 1);
define('PESQUISA_TIPO_AGLUTINADORA', 2);

class Pesquisa
{
    var $id, $titulo, $publico, $finalidade, $pesquisadorid, $pesquisador, $pacote,  
	 		$instituicao, $modeloquestionario, $status, $statusid, $tipo, $tipoid;
    var $questionarios, $count_questionarios, $count_concluidos, $count_emandamento;
    var $createddate;
    var $error;
    /**
	 * Lista dos produtos adquiridos para a pesquisa
	 */
	 var $produtos;

    function __construct()
    {
    	$this->pacote = new Pacote();
    	$this->modeloquestionario = new ModeloQuestionario();
    }

    function isAccessDenied()
    {
        $usr = Users::getCurrent();
        if (!$usr->isinrole('Admin')) {
            if ($usr->userid != $this->pesquisadorid) {
                return true;
            }
        }
        return false;
    }

	 function Pesquisador() {
	 	$users = new Users();
	 	return $users->item($this->pesquisadorid);	
	 }
	 
	 /**
	  * Atualiza o status da pesquisa para Encerrado.
	  * 
	  * @return void
	  */
	 function Encerrar() {	 	
	 	$pesquisas = new Pesquisas();
	 	$ret = $pesquisas->updateStatus($this->id, PESQUISA_STATUS_ENCERRADA);
	 	if ($ret) {
	 		$this->statusid = PESQUISA_STATUS_ENCERRADA;
	 	}
	 	$this->error = $pesquisas->error;
	 	return $ret;	 	
	 }
	 
	 /**
	  * Atualiza o status da pesquisa para Ativa.
	  * 
	  * @return void
	  */
	 function Ativar() {
	 	$usr = Users::getCurrent();
	 	if (!$usr->isinrole('Admin')) {
	 		$this->error = 'Acesso negado.';
	 		return false;
	 	}
	 	
	 	$pesquisas = new Pesquisas();
	 	$ret = $pesquisas->updateStatus($this->id, PESQUISA_STATUS_ATIVA);
	 	if ($ret) {
	 		$this->statusid = PESQUISA_STATUS_ATIVA;
	 	}
	 	$this->error = $pesquisas->error;
	 	return $ret;	 	
	 }
	 
	 /**
	  * Cancela a pesquisa.
	  * 
	  * @return void
	  */
	 function Cancelar() {
	 	$usr = Users::getCurrent();
	 	if (!$usr->isinrole('Admin')) {
	 		$this->error = 'Acesso negado.';
	 		return false;
	 	}
	 	
	 	$pesquisas = new Pesquisas();
	 	$ret = $pesquisas->updateStatus($this->id, PESQUISA_STATUS_CANCELADA);
	 	if ($ret) {
	 		$this->statusid = PESQUISA_STATUS_CANCELADA;
	 	}
	 	$this->error = $pesquisas->error;
	 	return $ret;	 	
	 }
	 
	 function isProdutoAdquirido($produtoid) {
	 	return isset($this->produtos[$produtoid]);
	 }
	 
	 function DesaglutinarQuest($questId) {
	 	$quests = new Questionarios();
	 	if (!$quests->desaglutinarToPesquisa($this->id, array($questId))) {
		 	$this->error = $quests->error;
		 	return false;
		 } else {
		 	return true;
		 }
	 }

	/**
	 * Pesquisa::AglutinarQuests()
	 * 
	 * @param array $quests_ids
	 * @return
	 */
	function AglutinarQuests($quests_ids) {
	 	$quests = new Questionarios();
	 	if (!$quests->aglutinarToPesquisa($this, $quests_ids)) {
		 	$this->error = $quests->error;
		 	return false;
		 } else {
		 	return true;
		 }
	 }
	 	 
	 function getProdutosAdquiridos() {
	 	$produtos = new Produtos();
	 	return $produtos->getProdutosByPesquisa($this->id);
	 }
	 	 
	 function getQuestionarioListaBasicaByStatus($status = QUESTIONARIO_STATUS_CONCLUIDO) {
 		$qs = new Questionarios();
 		
 		$filter = new Filter();
	  	if ($status) $filter->add('q.StatusId', '=', $status);
	  	
	  	//Order
	  	switch ($status) {
	  		case QUESTIONARIO_STATUS_CONCLUIDO:
	  			$orderby = 'q.ConcluidoEm DESC';
	  			break;
	  
	  		case QUESTIONARIO_STATUS_EMANDAMENTO:
	  			$orderby = 'q.IniciadoEm';
	  			break;
	  			
  			default:
  				$orderby = 'q.QuestionarioId';
	  	}	  	
	  	
 		return $qs->listaByPesquisa($this, $filter, $orderby);
	 }
	 	 
    function getQuestionarios($filter = null)
    {
     	$qs = new Questionarios();                
     	if (!$filter) $filter = new Filter();
		//$filter->add('q.PesquisaId', '=', $this->id);
     	return $qs->itemsByPesquisa($this, $filter);
    }
    
    function getQuestionariosByStatus($statusid) {
    	$filter = new Filter();
	  	if ($statusid) $filter->add('q.StatusId', '=', $statusid);
		  	  	
 		return $this->getQuestionarios($filter);
    }
    
    
    /**
     * Retorna todos os questionarios concluidos da pesquisa.
     * */
    function getQuestionariosConcluidos() {
        $this->checkQuestionariosConcluidosCache();
        return $this->__questionariosConcluidosCache;
    }
    
    
    private $__questionariosConcluidosCache;
	private $__questionariosConcluidosCacheCreated;
	 
    /**
     * Verifica se a variavel interna de cache para questionarions concluidos está populada. Se não, populate it. 
     * 
     * @return void
     */
    private function checkQuestionariosConcluidosCache() {
  		//Verifica se cache está populado
    	if (!$this->__questionariosConcluidosCacheCreated) {
    		$this->__questionariosConcluidosCacheCreated = true;
    		$this->__questionariosConcluidosCache = $this->getQuestionariosByStatus(QUESTIONARIO_STATUS_CONCLUIDO);
    	}
    }
    
    
    /**
     * Retorna a quantidade de questionários, de acordo com o fator e descricao informados.
     * Utilizado principalmente no Relatório Analitico Quantitativo.
     * 
     * @param mixed $fatorId
     * @param mixed $descricao
     * @return
     */    
    function getCountQuestionariosByValorDescricao($fatorId, $valorDescricao) {
    	$count = 0;
    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) { 			
    		foreach ($this->__questionariosConcluidosCache as $q) {
    			if ($q->fatores[$fatorId]->valordescricao == $valorDescricao) $count++; 
    		}
    		return $count;
    		
    	} else {
    		return null;
    	}
    }
    
    
    function getValorMinimoByFator($fatorId) {
    	$min = 999999;
    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) { 			
    		foreach ($this->__questionariosConcluidosCache as $q) {
    			if ($q->fatores[$fatorId]->valor < $min) $min = $q->fatores[$fatorId]->valor; 
    		}
    		return $min;
    		
    	} else {
    		return null;
    	}
    }
    
    function getValorMaximoByFator($fatorId) {
    	$max = -999999;
    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) { 			
    		foreach ($this->__questionariosConcluidosCache as $q) {
    			if ($q->fatores[$fatorId]->valor > $max) $max = $q->fatores[$fatorId]->valor; 
    		}
    		return $max;
    		
    	} else {
    		return null;
    	}
    }
    
    function getAmplitudeByFator($fatorId) {
    	$min = $this->getValorMinimoByFator($fatorId);
    	$max = $this->getValorMaximoByFator($fatorId);
    	
    	if ((!$min) || (!$max)) return null;
    	return ($max - $min);
    }
    
    function getVarianceByFator($fatorId) {    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) {
    		foreach ($this->__questionariosConcluidosCache as $q) {
    			$values[] = $q->fatores[$fatorId]->valor;
    		}
    		
    		$count = count($values);
    		if ($count <= 1) return null; //nao aplicavel
    		
    		$media = media_aritmetica($values);
    		
    		$soma_dos_quadrados = 0;
    		foreach ($values as $v) {
    			$soma_dos_quadrados += (($v - $media) ^ 2);
    		}
    		
		 	$variance = ($soma_dos_quadrados / ($count -1));
	 		return $variance;
			 	
    	} else {
    		return null;
    	}    	
    }
    
    function getStandardDeviationByFator($fatorId) {
    	$variance = $this->getVarianceByFator($fatorId);
    	
    	if ($variance)
    		return sqrt($variance);
   	else
   		return null;
    }
    
    function getMedianaByFator($fatorId) {    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) {
    		foreach ($this->__questionariosConcluidosCache as $q) {
    			$values[] = $q->fatores[$fatorId]->valor;
    		}
    	
		 	$median = mediana($values);
	 		return $median;
			 	
    	} else {
    		return null;
    	}    	
    }
    
    function getGrausDeLiberdadeByFator($fatorId) {    	
    	$this->checkQuestionariosConcluidosCache();
    	
    	if ($this->__questionariosConcluidosCache) {    	
		 	$gl = count($this->__questionariosConcluidosCache) - 2;
		 	if ($gl < 0) $gl = 0;
		 	
	 		return $gl;
			 	
    	} else {
    		return null;
    	}    	
    }
    
    function hasQuest($questId) {
    	$pesquisas = new Pesquisas();
    	
    	return $pesquisas->hasQuest($this->id, $questId);
    }
} //class Pesquisa


class Pesquisas
{
	var $error;

    function __construct()
    {
    }

    function item($id)
    {
        $sql = new SqlHelper();

        $sql->command = "SELECT p.PesquisaId, p.Titulo, p.Finalidade, p.Publico, p.PacoteId, pacote.Nome AS `Pacote`,
		  									pacote.ModeloQuestionarioId,
											p.QtdeQuest AS `Total`, p.PesquisadorId, u.Nome AS `Pesquisador`,
											p.CreatedDate, p.TipoId, tipo.Tipo, p.StatusId, st.Status,
											(SELECT Qtde FROM questionarioscountbystatusid WHERE PesquisaId = p.PesquisaId AND StatusId = 3) AS `Concluidos`,
											(SELECT Qtde FROM questionarioscountbystatusid WHERE PesquisaId = p.PesquisaId AND StatusId = 2) AS `EmAndamento`
										FROM pesquisas p
										INNER JOIN pesquisas_tipos tipo ON tipo.TipoId = p.TipoId
										INNER JOIN pesquisas_status st ON st.StatusId = p.StatusId
          							    INNER JOIN pacotes pacote ON p.PacoteId = pacote.PacoteId
										INNER JOIN modelosquestionarios md ON md.ModeloQuestionarioId = pacote.ModeloQuestionarioId
										LEFT JOIN users u ON u.UserId = p.PesquisadorId
										WHERE PesquisaId = $id";

        if ($sql->execute()) {

            if ($r = $sql->fetch()) {
                $pesq = new Pesquisa();
                $pesq->id = $r['PesquisaId'];
                $pesq->titulo = $r['Titulo'];
                $pesq->finalidade = $r['Finalidade'];
                $pesq->publico = $r['Publico'];
                $pesq->pesquisadorid = $r['PesquisadorId'];
                $pesq->pesquisador = $r['Pesquisador'];
                $pesq->createddate = $r['CreatedDate'];
                $pesq->tipoid = $r['TipoId'];
                $pesq->tipo = $r['Tipo'];
                $pesq->statusid = $r['StatusId'];
                $pesq->status = $r['Status'];
                
                //Pacote
                $pacotes = new Produtos();
                $pesq->pacote = $pacotes->getPacote($r['PacoteId']);
                
                $pesq->count_questionarios = $r['Total'];
                $pesq->count_concluidos = $r['Concluidos'];
                $pesq->count_emandamento = $r['EmAndamento'];
                
                //Modelo relatorio
                $modelos = new ModelosQuestionarios();
                $pesq->modeloquestionario = $modelos->item($r['ModeloQuestionarioId']);
                
                //Get produtos adquiridos
                $pesq->produtos = $pacotes->getProdutosByPesquisa($pesq->id);

                return $pesq;
            } else {
                return null;
            }
        } else {
            throw new Exception($sql->error);
            return null;
        }
    }

	 function itemByQuestionarioId($questId) {
	 		$sql = new SqlHelper();
	 		
	 		$sql->command = "SELECT PesquisaId FROM questionarios WHERE QuestionarioId = ".$sql->escape_string($questId, true);
	 		
	 		if ($sql->execute()) {
	 			if ($r = $sql->fetch()) {
		 			$pesquisaId = $r['PesquisaId'];
		 			$pesquisa = $this->item($pesquisaId);
		 			
		 			return $pesquisa;
		 		} else {
		 			$this->error = 'Pesquisa não encontrada.';
		 			return false;
		 		}
	 		} else {
	 			$this->error = 'Erro ao verificar pesquisa do questionário. '.$sql->error;
	 			return false;
	 		} 
	 }
	 
	 //TODO:alterar par retornar objetos ao inves de dataset
    function MinhasPesquisas($pageindex = 1, $pagesize = 10, $orderby='data', &$totalrows, $filter=null)
    {
        $usr = Users::getCurrent();
        if (!$usr)
            throw new Exception("Usuário não logado.");

        $sql = new SqlHelper();
        
        //Validate params
			if (!is_int($pageindex)) $pageindex = 1;
			if (!is_int($pagesize)) $pagesize = 10;
			if ($pagesize > 50) $pagesize = 50;
			$start = $pagesize * ($pageindex-1);
			if (!$filter) $filter = new Filter();
			
			//Set orderby
			switch ($orderby) {
				default:
					$orderby = 'p.CreatedDate DESC';
			}
			
			//Se não Admin, listar apenas pesq proprias
        if (!$usr->isinrole('Admin')) $filter->add('p.PesquisadorId', '=', $usr->userid);
		  		  		
		  //Rowcount
			$sql->command = "SELECT COUNT(*) AS `RowCount` 
								FROM pesquisas p 
								LEFT JOIN users u on u.UserId = p.PesquisadorId 
								$filter->expression";		
			
			if ($sql->execute()) {
				if ($r = $sql->fetch()) $totalrows = $r['RowCount'];
				if ($start > $totalrows) {$start = 0; $pageindex = 1;}
			} else {
				return null;
			}
				
		  //List SELECT		
        $sql->command = "SELECT p.PesquisaId, p.Titulo, p.Publico, p.Finalidade, md.nome as `TipoQuestionario`, pacote.nome as `Pacote`,
													p.QtdeQuest AS `Total`, u.Nome as `Pesquisador`, p.StatusId, st.Status, p.TipoId,
													(SELECT Qtde FROM questionarioscountbystatusid WHERE PesquisaId = p.PesquisaId AND StatusId = 3) AS `Concluidos`,
													(SELECT Qtde FROM questionarioscountbystatusid WHERE PesquisaId = p.PesquisaId AND StatusId = 2) AS `EmAndamento`
											FROM pesquisas p
											INNER JOIN pesquisas_status st ON p.StatusId = st.StatusId
                                 LEFT JOIN pacotes pacote ON p.PacoteId = pacote.PacoteId
											LEFT JOIN modelosquestionarios md ON md.ModeloQuestionarioId = pacote.ModeloQuestionarioId
											Left JOIN users u ON p.PesquisadorId = u.UserId
											$filter->expression 
											ORDER BY $orderby LIMIT $start, $pagesize";

        if ($sql->execute()) {
        		while ($r=$sql->fetch()) {        			
	        		$p = new Pesquisa();
	        		$p->id =	$r['PesquisaId'];
	        		$p->titulo = $r['Titulo'];
	        		$p->publico = $r['Publico'];
	        		$p->finalidade = $r['Finalidade'];
	        		$p->modeloquestionario = new ModeloQuestionario();
	        		$p->modeloquestionario->nome = $r['TipoQuestionario'];
	        		$p->pacote = new Pacote();
					$p->pacote->nome = $r['Pacote'];
					$p->count_questionarios = $r['Total'];
					$p->count_emandamento = $r['EmAndamento'];
					$p->count_concluidos = $r['Concluidos'];
					$p->pesquisador = $r['Pesquisador'];
					$p->statusid = $r['StatusId'];
					$p->status = $r['Status'];
					$p->tipoid = $r['TipoId'];
				   $lst[$p->id] = $p; 
        		}
			   if (isset($lst)) return $lst; else return null; 
            //return $sql->dataset();
        } else {
            throw new Exception($sql->error);
            return null;
        }
    }

	//TODO:Alterar SQL para atender pesquisas agregadoras
    function ExportRespostasByPesquisa($pesquisaid)
    {
        $sql = new SqlHelper();
        $sql->command = "SELECT QuestionarioId, Pergunta, Resposta, RespostaValor, Posicao 
											 FROM view_export_questionarios_respostas 
											 WHERE QuestionarioId IN (SELECT QuestionarioId FROM questionarios WHERE PesquisaId = $pesquisaid)
										 ORDER BY QuestionarioId, Posicao";

        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $p = new Pergunta();

                $p->texto = $r['Pergunta'];
                $p->resposta = $r['Resposta'];
                $p->respostavalor = $r['RespostaValor'];
                $p->posicao = $r['Posicao'];
                $p->questionarioid = $r['QuestionarioId'];

                $lst[] = $p;
            }
        } else {
            throw new Exception($sql->error);
        }

        if (isset($lst)) {
            return $lst;
        } else {
            return null;
        }
    }


    function add($pesquisa)
    {
        //Toda pesquisa deve ter produtos
        if (!$pesquisa->produtos) {
            $this->error = "Nenhum produto selecionado.";
            return false;
        }

        //Qtde deve ser superior a zero
        switch ($pesquisa->tipoid) {
            case PESQUISA_TIPO_NORMAL:
                if ($pesquisa->count_questionarios < 1) {
                    $this->error = 'Quantidade de ser superior a zero.';
                    return false;
                }
                break;

            case PESQUISA_TIPO_AGLUTINADORA:
                $quests_ids = explode(',', $pesquisa->count_questionarios);
                $pesquisa->count_questionarios = count($quests_ids);
                if ((!$pesquisa->count_questionarios) || ($pesquisa->count_questionarios < 1)) {
                    $this->error = 'Ao menos um questionário deve ser informado para ser aglutinado na pesquisa.';
                    return false;
                }
        }

        //Verifica se pesquisador possui creditos, somente para pesquisas Normal
        //TODO:verificar se esta é a regra de negócio
        if ($pesquisa->tipoid == PESQUISA_TIPO_NORMAL) {
            if (!$this->checkCredito($pesquisa->pesquisadorid, $pesquisa->pacote->id, $pesquisa->produtos, $pesquisa->count_questionarios)) {
                return false;
            }
        }

        $sql = new SqlHelper();
        $sql->begin(); //start transaction

        //Check and set default status and tipo
        if (!$pesquisa->statusid) $pesquisa->statusid = PESQUISA_STATUS_ATIVA;
        if (!$pesquisa->tipoid) $pesquisa->tipoid = PESQUISA_TIPO_NORMAL;
			
        $sql->command = "INSERT INTO pesquisas (titulo, publico, finalidade, pesquisadorid, pacoteid, qtdequest, statusid, tipoid, createddate) VALUES (".
                            $sql->escape_string($pesquisa->titulo, true).", ".
                            $sql->escape_string($pesquisa->publico, true).", ".
                            $sql->escape_string($pesquisa->finalidade, true).", ".
                            $pesquisa->pesquisadorid.", ".
                            $pesquisa->pacote->id.", ".
                            $pesquisa->count_questionarios.", $pesquisa->statusid, $pesquisa->tipoid, now())";
        if  ($sql->execute()) {
			 $pesquisa->id = $sql->getInsertId();

			 //Add produtos adquiridos
			 if ($pesquisa->produtos) {
				 foreach ($pesquisa->produtos as $prod) {
				 	if ($prod->selected) {
					 	$sql->command = "INSERT INTO pesquisas_produtos (PesquisaId, ProdutoId) VALUES ($pesquisa->id, $prod->id)";
					 	$sql->execute();
					}
				 }
  			 }
			 
			 //Add Quests
			 $quests = new Questionarios();
			 if ($pesquisa->tipoid == PESQUISA_TIPO_AGLUTINADORA) {
			 	$ret = $quests->aglutinarToPesquisa($pesquisa, $quests_ids, $sql);
			 	if ($quests->error)
                    $this->error .= $quests->error; //alertas de itens inválidos
			 } else {
			 	$ret = $quests->addToPesquisa($pesquisa, $pesquisa->count_questionarios, false, $sql);
                if ($quests->error)
                    $this->error .= $quests->error; //alertas de itens inválidos
			 }
			 
			 if (!$ret) {
			 	$sql->rollback();
				return false;
			 } else  {
			 	$sql->commit();
			 	return $pesquisa;
			 } 
		  } else {
				$sql->rollback();
				$this->error = $sql->error;
				return false;
        }
    }
    
    function updateInfo($pesquisa, $infoType = 'Titulo') {
    	//$pesquisas = new Pesquisas();		
		//$pesquisa = $pesquisas->item($pesquisaid);
		
		if (!$pesquisa) {
			$this->error = 'Pesquisa não encontrada';
			return false;
		}
		
		if ($pesquisa->statusid != PESQUISA_STATUS_ATIVA) {
			$this->error = 'Somente é possível atualizar informações de uma pesquisa ativa.';
			return false;
		}
    	
		$sql = new SqlHelper();
		
		switch ($infoType) {
			case 'Titulo':
				$sql->command = "UPDATE pesquisas set Titulo = ".$sql->escape_string($pesquisa->titulo, true)." WHERE PesquisaId = $pesquisa->id";
				break;
			
			case 'Finalidade':
				$sql->command = "UPDATE pesquisas set Finalidade = ".$sql->escape_string($pesquisa->finalidade, true)." WHERE PesquisaId = $pesquisa->id";
				break;
				
			case 'Publico':
				$sql->command = "UPDATE pesquisas set Publico = ".$sql->escape_string($pesquisa->publico, true)." WHERE PesquisaId = $pesquisa->id";
				break;
				
			case 'Gestor':
				$sql->command = "UPDATE pesquisas set PesquisadorId = ".$sql->escape_string($pesquisa->pesquisadorid, true)." WHERE PesquisaId = $pesquisa->id";
				break;
			
			default:
				$this->error = 'Atributo inválido';
				return false;
		}
		
		return ($sql->execute());
    }
    
    function updateProdutos($pesquisa, $produtoList) {
    	//TODO: verificar se deve haver creditos para isso. Da forma como está, nao há verificaçao, 
    	//pois somente admin pode fazer esta operação.
    	
		$usr = Users::getCurrent();
		if (!$usr->isinrole('Admin')) {
			$this->error = "Acesso negado";
			return false;
		}
    	
    	$sql = new SqlHelper();
    	
    	$sql->command = "DELETE FROM pesquisas_produtos WHERE PesquisaId = $pesquisa->id";
    	if (!$sql->execute()) {
    		$this->error = 'Erro ao excluir produtos.<br />' . $sql->error;
    		return false;
    	}
    	
    	$lst = explode(',', trim($produtoList));
    	if ($lst) {
    		foreach ($lst as $p) {
  				if ($p) {
		    		$sql->command = "INSERT INTO pesquisas_produtos (PesquisaId, ProdutoId) VALUES ($pesquisa->id, $p)";
		    		if (!$sql->execute()) {
						$this->error = 'Erro ao incluir produto.<br />' . $sql->error;
	    				return false;	    			
		    		}
		    	}
	    	}
    	}
    	
    	return true;
    }
    
    
    /**
     * Atualiza a quantidade de quest de uma deterinada pesquisa, de acordo com a tabela Questionarios. 
     * 
     * @param mixed $pesquisaid
     * @return void
     */
    function updateQtdeQuest($pesquisaid) {
    	/*$usr = Users::getCurrent();
		if (!$usr->isinrole('Admin,Gestor')) {
			$this->error = "Acesso negado";
			return false;
		}*/
			
    	$sql = new SqlHelper();
    	$qtde = 0;
    	
    	//Qtde do tipo de pesquisa 1
    	$sql->command = "SELECT COUNT(*) as `Qtde` FROM questionarios WHERE PesquisaId = $pesquisaid";
    	
		 if ($sql->execute()) {
	 		$r = $sql->fetch();
	 		$qtde = $r['Qtde'];			
    	} else {
    		$this->error = 'Erro ao verificar quantidade de questionario da pesquisa';
    		return false;
    	}
    	
    	//qtde do tipo de pesquisa 2
    	$sql->command = "SELECT COUNT(*) as `Qtde` FROM pesquisas_questionariosaglutinados WHERE PesquisaId = $pesquisaid";
    	
		 if ($sql->execute()) {
	 		$r = $sql->fetch();
	 		$qtde += $r['Qtde'];			
    	} else {
    		$this->error = 'Erro ao verificar quantidade de questionario da pesquisa';
    		return false;
    	}
    	
    	$sql->command = "UPDATE pesquisas set QtdeQuest = $qtde WHERE PesquisaId = $pesquisaid";
		if ($sql->execute())
			return true;
		else  {
			$this->error = 'Erro ao atualizar quantidade de questionarios da pesquisa';
			return false;
		}     	
    }
    
    //function QuestListByPesquisaId($pesquisaid, $filter = null) {    	
//    	$sql = new SqlHelper();
//    	
//    	if (!$filter) $filter = new Filter();
//    	switch ($this->tipoid) {
//    		case PESQUISA_TIPO_NORMAL:
//			 	$filter->add('PesquisaId', '=', $psquisaid);
//				break;     			
//			
//			case PESQUISA_TIPO_AGLUTINADORA:
//			 	$filter->add('QuestionarioId', 'IN', "(SELECT QuestionarioId FROM pesquisas_questionariosaglutinados WHERE PesquisaId = $pesquisaid", '%s');
//				break;
//			
//			default:
//				throw new Exception('Tipo de Pesquisa inválido.');	
//    	}
//    	
//    	
//    	$sql->command = "SELECT QuestionarioId FROM questionarios 
//		 							$filter->expression";
//    	
//    	if ($sql->execute()) {    		
//    		$quests = new Questionarios();
//    		
//    		while ($r = $sql->fetch()) {
//    			$lst[] = $quests->item($r['QuestionarioId']);
//    		}
//    		
//    		if (isset($lst)) return $lst; else return null;
//    	} else {
//    		$this->error = $sql->error;
//			 return false; 
//    	}
//    }

    function updateStatus($pesquisaid, $statusid) {
    	$sql = new SqlHelper();
    	
    	$sql->command = "UPDATE pesquisas SET StatusId=$statusid WHERE PesquisaId=$pesquisaid";
    	return $sql->execute();
    }
    
    
    /**
     * Verifica se determinado usuário possui creditos para a criacao de uma pesquisa
     * 
     * @param mixed $userId
     * @param mixed $pacoteId
     * @param mixed $produtos
     * @param mixed $qtde
     * @param bool $checkProdutosPorPacote Indica se os produtos que possuem saldo apurado por pacote devem ser checados. 
	  * Utilizado na inclusao de questionarios em pesquisas existentes. 
     * @return
     */
    function checkCredito($userId, $pacoteId, $produtos, $qtde, $checkProdutosPorPacote = true) {		    	
    	$sql = new SqlHelper();
    	
    	//Se pesquisa não tiver produtos associados, nao necessita crédito
    	if (!$produtos) return true;
    	
    	//Verificar saldo para cada produto solicitado
    	foreach ($produtos as $produto) {
            if (($produto->id != PRODUTO_TABELA_CATEGORIA) && ($produto->id != PRODUTO_TABELA_INDICE)) {
            
    		$sql->command = "SELECT UserId, PacoteId, ProdutoId, PorPacote, SUM(Qtde) as `Saldo`
								FROM view_extrato_creditos
								WHERE UserId = $userId AND PacoteId = $pacoteId AND ProdutoId = $produto->id
								group by UserId, PacoteId, ProdutoId";
					
			if ($sql->execute()) {
				if ($r = $sql->fetch()) $saldo = $r['Saldo']; else $saldo = 0;
				$produto->saldo = $saldo;
				$produto->porpacote = $r['PorPacote'];
				
				if ($produto->porpacote) {
					if ($checkProdutosPorPacote) {
						//Se produto for por pacote, basta ter saldo >= 1 para criar a pesquisa.
						if ($saldo < 1) {
							$produtosSemSaldo[] = $produto;
						}
					}	
				} else {
					//Para os demais produtos, saldo deve ser >= qtde solicitada.
					if ($saldo < $qtde) {
						$produtosSemSaldo[] = $produto; 
					}
				}
			} else {
				$this->error = $sql->error;
				return false;
			}
            }
    	}
    	
    	if (isset($produtosSemSaldo)) {
    		$this->error = "Créditos insuficientes para os produtos abaixo: <br /><br /><div class='Left Small'><ul>";
			foreach ($produtosSemSaldo as $produto) {$this->error .= "<li>$produto->nome (Saldo: $produto->saldo) </li>";}
			$this->error .= '</ul></div>';
			return false;
    	} else {
    		return true;
   	}
    }
    
    
    
    function hasQuest($pesquisaId, $questId) {
    	$sql = new SqlHelper();
    	
    	$sql->command = "select ifnull(q.QuestionarioId, pqa.QuestionarioId) AS `QuestionarioId`
								from pesquisas p
								left join questionarios q ON q.PesquisaId = p.PesquisaId
								left join pesquisas_questionariosaglutinados pqa ON pqa.PesquisaId = p.PesquisaId
								where (p.PesquisaId = $pesquisaId) 
										AND (q.QuestionarioId = $questId OR pqa.QuestionarioId = $questId)";
		
		if ($sql->execute()) {
			return $sql->hasrows();
		} else {
			return false;
		}
    } //hasQuest
}
?>