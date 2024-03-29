<?php
include_once 'SqlHelper.class.php';
include_once 'Produto.class.php';

class Credito
{
    var $id, $userid, $username, $pacoteid, $pacote, $qtde, $createddate, $statusid, $produtos;

    function __construct(){
    }
}

class Creditos
{
	var $error;

	function getItems($filter = null) {
		$sql = new SqlHelper();

		if (!$filter) 	$filter = new Filter();
		$filter->add('c.StatusId', '=', 1); //somente ativos

      $sql->command = "SELECT c.CreditoId, c.UserId, u.Nome AS Username, c.Qtde,
								c.PacoteId, p.Nome as Pacote, c.CreatedDate, c.StatusId
							FROM creditos c
							JOIN users u ON c.UserId = u.UserId
							JOIN pacotes p ON p.PacoteId = c.PacoteId
							$filter->expression
							ORDER BY c.CreatedDate DESC";
      $sql->execute();

      while ($r = $sql->fetch()) {
         $c = new Credito;
         $c->id = $r['CreditoId'];
         $c->userid = $r['UserId'];
         $c->username = $r['Username'];
         $c->pacoteid = $r['PacoteId'];
         $c->pacote = $r['Pacote'];
         $c->qtde = $r['Qtde'];
         $c->createddate = $r['CreatedDate'];
         $c->statusid = $r['StatusId'];

         $lst[] = $c;
		}

  		if (isset($lst)) {
	  		//Fetch produtos
	  		$produtos = new Produtos();

	  		foreach ($lst as $c) {
	  			$sql->command = "SELECT ProdutoId FROM creditos_produtos WHERE CreditoId = $c->id AND ProdutoId NOT IN (3,4,5)";
	  			if ($sql->execute()) {
	  				while ($r = $sql->fetch()) {
	  					$prod = $produtos->getProduto($r['ProdutoId']);
					  	if ($prod) $c->produtos[] = $prod;
	  				}
	  			}
	  		}
  		}

  		if (isset($lst)) return $lst; else return null;
	}

	function getItemsByGestor($userid) {
		$filter = new Filter();

		$usr = Users::getCurrent();
		$filter->add('c.UserId', '=', $usr->userid);
		$filter->add('c.StatusId', '=', 1); //somente ativos
		return $this->getItems($filter);
	}

	function getItem($id)
    {
        $f = new Filter();
        $f->add('c.CreditoId', '=', $id);

        $lst = $this->getItems($f);
        if ($lst) {
            return $lst[0];
        } else {
            return null;
        }
    }

    function save($credito) {
    	//Validate user
    	$usr = Users::getCurrent();
    	if (!$usr->isinrole('Admin')) {
    		$this->error = 'Somente administradores podem incluir cr?ditos';
    		return false;
    	}

    	//Validate input
    	if (!$credito->userid) {
    		$this->error = 'Gestor inv?lido';
    		return false;
    	}
    	if (!$credito->qtde) {
    		$this->error = 'Quantidade de cr?ditos inv?lida';
    		return false;
    	}
    	if (!$credito->pacoteid) {
    		$this->error = 'Pacote inv?lido';
    		return false;
    	}

    	//Do it!
    	$sql = new SqlHelper();

    	//if (!$credito->id) {
    		$sql->command = 'INSERT INTO creditos (userid, pacoteid, qtde, createddate, statusid) VALUES ('.
			 						$sql->escape_string($credito->userid, true).', '.
									 $sql->escape_string($credito->pacoteid, true).', '.
									 $sql->escape_string($credito->qtde).', '.
									 'now(), 1);';
    	/*} else {
    		$sql->command = 'UPDATE produtos SET nome = '.$sql->escape_string($produto->nome, true).
														', descricao = '.$sql->escape_string($produto->descricao, true).
														', enabled = '.$sql->escape_string($produto->enabled).
														' WHERE produtoid = '.$sql->escape_id($produto->id);

    	}*/

    	if ($sql->execute()) {
		 	//Recupera id
		 	$credito->id = $sql->getInsertId();
			//Insere produtos
			if ($credito->produtos) {
				foreach ($credito->produtos as $p) {
					$sql->command = "INSERT INTO creditos_produtos (CreditoId, ProdutoId) VALUES ($credito->id, $p->id)";
					$sql->execute();
				}
			}

			return true;
    	} else {
    		$this->error = $sql->error;
    		return false;
    	}
    }


    function delete($id) {
    	//Validate user
    	$usr = Users::getCurrent();
    	if (!$usr->isinrole('Admin')) {
    		$this->error = 'Somente administradores podem excluir cr?ditos';
    		return false;
    	}


    	//Do it!
    	$sql = new SqlHelper();
 		$sql->command = "DELETE FROM creditos WHERE CreditoId = $id";


    	if ($sql->execute()) {
			return true;
    	} else {
    		$this->error = $sql->error;
    		return false;
    	}
    } //delete


    function getSaldo($userId = null, $filter = null) {
    	$sql = new SqlHelper();

    	if (!$filter) $filter = new Filter();
        if ($userId) $filter->add('c.UserId', '=', $userId);
        $filter->add('pr.Enabled', '=', '1');
        $filter->add('pr.ProdutoId', 'NOT IN', '(3, 4, 5)', '%s'); //nao exibir produtos Tabelas Indice e Categoria

    	$sql->command = "SELECT u.Nome AS Gestor, pc.Nome AS Pacote, pr.Nome AS Produto, SUM(Qtde) as Saldo
								FROM view_extrato_creditos c
								join pacotes pc ON c.PacoteId = pc.PacoteId
								join produtos pr on pr.ProdutoId = c.Produtoid
								join users u ON c.UserId = u.UserId
								$filter->expression
								group by u.Nome, pc.Nome, pr.Nome
								order by 1,2,3";

		if ($sql->execute()) {
			$c = null;
			while ($r = $sql->fetch()) {
				if ((!$c) || ($c->username != $r['Gestor']) || ($c->pacote != $r['Pacote'])) {
					$c = new Credito();
					$c->username = $r['Gestor'];
					$c->pacote = $r['Pacote'];
					$lst[] = $c;
				}
				$p = new Produto();
				$p->nome = $r['Produto'];
				$p->porpacote = $r['Saldo'];
				$c->produtos[] = $p;
			}

			return $lst;
		} else {
			$this->error = $sql->error;
			return false;
		}
    }

} //Creditos


?>