<?php
include_once 'SqlHelper.class';

//tem referencia em Controls/list.ctrl.php
define('PRODUTO_TABELA_INDICE', 3);
define('PRODUTO_TABELA_CATEGORIA', 4);

class Pacote
{
    var $nome, $id, $produtos, $descricao, $tipo, $modeloquestionarioid, $enabled, $saldo;
    //texto que aparece ao rspondente na introdu?ao do questionario 
    var $questintrotext;

    function __construct(){
    	$this->tipo = new PacoteTipo(null, null, null, null, null);
    }
	 
	 function fill($nome, $descricao, $id, $modeloquestionarioid, $enabled)
    {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->id = $id;
        $this->modeloquestionarioid = $modeloquestionarioid;
        $this->enabled = $enabled;
    }
}

class PacoteTipo {
    var $id, $nome, $descricao, $slogan;
    
    function __construct($id, $nome, $descricao, $slogan, $introducao) {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->slogan = $slogan;
        $this->introducao = $introducao;
    }    
}

class Produto
{
    var $id, $nome, $descricao, $preco, $obrigatorio, $selected, $porpacote, $enabled;

    function __construct() {}
    
    function fill($id, $nome, $descricao, $preco, $obrigatorio, $porpacote = 0)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->preco = $preco;
        $this->obrigatorio = $obrigatorio;
        $this->porpacote = $porpacote;
    }
}

class Produtos
{
	var $error;
	
	function getProdutos($filter = null) {
		$sql = new SqlHelper();
		
		if (!$filter) 	$filter = new Filter();
		//$filter->add('p.Enabled', '=', 1); //somente ativos
		
      $sql->command = "SELECT ProdutoId, Nome, Descricao, Enabled, PorPacote
								FROM produtos p 
								$filter->expression 
								ORDER BY Nome";
		$sql->execute();
		
      while ($r = $sql->fetch()) {
         $p = new Produto;
         $p->id = $r['ProdutoId'];
         $p->nome = $r['Nome'];
         $p->descricao = $r['Descricao'];
         $p->porpacote = $r['PorPacote'];
         $p->enabled = $r['Enabled'];
         
         $lst[$p->id] = $p;
		}
  		
  		if (isset($lst)) return $lst; else return null;		
	}
	
	function getProdutosByPesquisa($pesquisaid) {
		$filter = new Filter();
		$filter->add('p.ProdutoId', 'IN', "(SELECT ProdutoId FROM pesquisas_produtos WHERE PesquisaId=$pesquisaid)", '%s');
		return $this->getProdutos($filter);
	}	
	
	function getProduto($id)
    {
        $f = new Filter();
        $f->add('ProdutoId', '=', $id);

        $lst = $this->getProdutos($f);
        if ($lst) {
            return array_pop($lst);
        } else {
            return null;
        }
    }
    
    function saveProduto($produto) {
    	$sql = new SqlHelper();
    	
    	if (!$produto->id) {
    		$sql->command = 'INSERT INTO produtos (nome, descricao, porpacote, enabled) VALUES ('.
			 						$sql->escape_string($produto->nome, true).', '.
									 $sql->escape_string($produto->descricao, true).', '.
									 $produto->porpacote.', '.									 
									 $sql->escape_string($produto->enabled).') ';									 	
    	} else {
    		$sql->command = 'UPDATE produtos SET nome = '.$sql->escape_string($produto->nome, true).
														', descricao = '.$sql->escape_string($produto->descricao, true).
														', porpacote = '.$produto->porpacote.
														', enabled = '.$sql->escape_string($produto->enabled).
														' WHERE produtoid = '.$sql->escape_id($produto->id);
														
    	}
    	
    	if ($sql->execute()) 
		 	return true;
    	else {
    		$this->error = $sql->error;
    		return false;
    	}
    }
    
    function getPacote($id)
    {
        $f = new Filter();
        $f->add('PacoteId', '=', $id);

        $lst = $this->getPacotes($f);
        if ($lst) {
            return $lst[0];
        } else {
            return null;
        }
    }

    function getPacotesByTipo($tipo)
    {
        $f = new Filter();
        $f->add('p.PacoteTipoId', '=', $tipo);
        $f->add('p.Enabled', '=', 1); //somente ativos
        return $this->getPacotes($f);
    }

    function getPacotes($filter = null)
    {
        $sql = new SqlHelper();

        //List pacotes
        if (!$filter) {
        		$filter = new Filter();
        		$filter->add('p.Enabled', '=', 1); //somente ativos
			}
			
        $sql->command = "SELECT PacoteId, Nome, Descricao, PacoteTipoId, ModeloQuestionarioId, Enabled, QuestIntroText 
		  						 FROM pacotes p $filter->expression ORDER BY Nome";
			
			$sql->execute();
				
        while ($r = $sql->fetch()) {
            $p = new Pacote;
				$p->fill($r['Nome'], $r['Descricao'], $r['PacoteId'], $r['ModeloQuestionarioId'], $r['Enabled']);
            $p->tipo = $this->getPacoteTipo($r['PacoteTipoId']);
            $p->questintrotext = $r['QuestIntroText'];
            $pacotes[] = $p;
        }
        if (!isset($pacotes))
            return null;

        //Get produtos dos pacotes
        foreach ($pacotes as $pacote) {
            $sql->command = "Select prod.ProdutoId, prod.Nome as Produto, prod.Descricao, preco.Preco, preco.Obrigatorio, prod.PorPacote
												from pacotes_produtos preco
												inner join produtos prod on prod.ProdutoId = preco.ProdutoId
												inner join pacotes p on p.PacoteId = preco.PacoteId
												WHERE preco.PacoteId = $pacote->id AND prod.Enabled = 1
												order by preco.PacoteId, prod.Nome, preco.Obrigatorio DESC";
            $sql->execute();

            while ($r = $sql->fetch()) {
                $prod = new Produto();
					 $prod->fill($r['ProdutoId'], $r['Produto'], $r['Descricao'], $r['Preco'], $r['Obrigatorio'], $r['PorPacote']);
                $pacote->produtos[$r['ProdutoId']] = $prod;    
            }
        }

        return $pacotes;
    }

    function getItensObrigatorios($pacoteid)
    {
        $sql = new SqlHelper();

        $sql->command = "SELECT p.ProdutoId FROM produtos p 
		  							WHERE p.Enabled = 1 AND
									  		p.ProdutoId IN (SELECT pp.ProdutoId FROM pacotes_produtos pp WHERE pp.PacoteId = $pacoteid AND pp.Obrigatorio = 1)";
        $sql->execute();

        while ($r = $sql->fetch()) {
            $lst[] = $r['ProdutoId'];
        }

        if ($lst) {
            return $lst;
        } else {
            return null;
        }
    }

    function getPacotesTipos($id = null, $somente_ativos = true) {
        $sql = new SqlHelper();

        $filter = new Filter(); 
        if ($id) $filter->add('PacoteTipoId', '=', $id);
        if ($somente_ativos) $filter->add('Enabled', '=', 1);
        $sql->command = "SELECT * FROM pacotes_tipos
                         $filter->expression
                         ORDER BY DisplayOrder";
        
        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $lst[] = new PacoteTipo($r['PacoteTipoId'], $r['Nome'], $r['Descricao'], $r['Slogan'], $r['Introducao']);                
            }   
        }       
        
        if (isset($lst)) return $lst; else return null;
    }
    
    function getPacoteTipo($id) {
        $lst = $this->getPacotesTipos($id, false);
        if ($lst) return $lst[0]; else return null;
    }
    
    function savePacote($pacote) {
    	$sql = new SqlHelper();
    	
    	if (!$pacote->id) {
    		$sql->command = 'INSERT INTO pacotes (nome, descricao, pacotetipoid, modeloquestionarioid, questintrotext, enabled) VALUES ('.
			 						$sql->escape_string($pacote->nome, true).', '.
									 $sql->escape_string($pacote->descricao, true).', '.
									 $sql->escape_id($pacote->tipo->id).', '.
									 $sql->escape_id($pacote->modeloquestionarioid).', '.
									 $sql->escape_string($pacote->questintrotext, true).', '.
									 $sql->escape_string($pacote->enabled).') ';									 	
    	} else {
    		$sql->command = 'UPDATE pacotes SET nome = '.$sql->escape_string($pacote->nome, true).
														', descricao = '.$sql->escape_string($pacote->descricao, true).
														', pacotetipoid = '.$sql->escape_id($pacote->tipo->id).
														', modeloquestionarioid = '.$sql->escape_id($pacote->modeloquestionarioid).
														', enabled = '.$sql->escape_string($pacote->enabled).
														', questintrotext = '.$sql->escape_string($pacote->questintrotext, true).
														' WHERE pacoteid = '.$sql->escape_id($pacote->id);
														
    	}
    	
    	if ($sql->execute()) 
		 	return true;
    	else {
    		$this->error = $sql->error;
    		return false;
    	}
    }
    
    function saveProdutoPacote($pacoteid, $produto, $new = false) {
    	$sql = new SqlHelper();
    	
    	if ($new) {
    		$sql->command = "INSERT INTO pacotes_produtos (PacoteId, ProdutoId, Preco, Obrigatorio) 
                            VALUES ($pacoteid, $produto->id, ".$sql->prepareDecimal($produto->preco).", $produto->obrigatorio)";	
    	} else {
	    	$sql->command = "UPDATE pacotes_produtos set 
                                        preco=".$sql->prepareDecimal($produto->preco)."
                                        , obrigatorio='$produto->obrigatorio'
			 						where PacoteId = $pacoteid AND ProdutoId = $produto->id";
		}
		 						
		if ($sql->execute()) 
			return true;
    	else {
    		$this->error = $sql->error;
    		return false;
    	}
    }
    
    function removeProdutopacote($pacoteid, $produtoid) {
    	$sql = new SqlHelper();
    	$sql->command = "DELETE FROM pacotes_produtos WHERE PacoteId = $pacoteid AND ProdutoId = $produtoid";
    	
    	if ($sql->execute()) 
			return true;
    	else {
    		$this->error = $sql->error;
    		return false;
    	}
    }
} //Produtos


?>