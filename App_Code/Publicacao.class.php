<?php
include_once('SqlHelper.class.php');
include_once('FileHandler.class.php');
include_once('Filter.class.php');
include_once('Mail.class.php');

class Publicacao {
	var $id, $titulo, $autor, $resumo, $coautores, $email, $instituicao, $filename;
	var $ispublicado;
	var $tipo, $tipoid, $url, $autoriza, $publicado, $datapublicacao, $createdby, $createdbyname;
}

class Publicacoes {
	var $error;

	function add(&$p) {
		$sql = new SqlHelper();
		
		if (!$p->createdby) {
			$usr = Users::getCurrent();
			if (!$usr) {
				$this->error = '? necess?rio informar um associado para esta publica??o.';
				return false;
			}
			$p->createdby = $usr->userid;
		}
		
		$sql->command = "INSERT INTO publicacoes (titulo, resumo, autor, email, coautores, instituicao, publicacaotipoid, url, filename, autorizapublicacao, ispublicado, datapublicacao, createdby)
											VALUES (".
												$sql->escape_string($p->titulo, true).', '.
												$sql->escape_string($p->resumo, true).', '.
												$sql->escape_string($p->autor, true).', '.
												//$sql->escape_string($p->email, true).', '.
												'NULL, '.
												$sql->escape_string($p->coautores, true).', '.
												$sql->escape_string($p->instituicao, true).', '.
												$sql->escape_string($p->tipoid, true).', '.
												$sql->escape_string($p->url, true).', '.
												$sql->escape_string($p->filename, true).', '.
												'1, 0, now(), ' . $sql->escape_id($p->createdby) .')';
		
		if (!$sql->execute()) {
			$this->error = $sql->error;
			return false;
			
		} else {
			//Get id
			$p->id = $sql->getInsertId();
			
			//Get nome do associado
			$sql->command = "SELECT Nome FROM users WHERE userid= $p->createdby";
			if ($sql->execute()) {
				$r = $sql->fetch();
				$nome_associado = $r['Nome'];
			} else
				$nome_associado = "Associado";
						
			//Send notification email
			$email = new Email();
			$email->to = 'faleconosco@sobrare.com.br';
			$email->subject = utf8_decode('Notifica??o de nova publica??o');
			$email->message = "<html><body>
										<p>Ol?!</p>
										<p><strong>".htmlentities(utf8_decode($nome_associado))."</strong> publicou o documento <strong>".htmlentities(utf8_decode($p->titulo))."</strong>. Acesse o <a href='http://www.sobrare.com.br/cockpit/'>Cockpit do Administrador</a> e aprove esta publica??o.</p>
									 </body></html>";
			$email->send();
			return true;
		}
	}

	function getPendentesCount() {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT COUNT(*) as `qtde` 
											FROM publicacoes p
											WHERE p.IsPublicado = 0";
		
		if ($sql->execute()) {
			$r = $sql->fetch();
			return $r['qtde'];
		} else {
			return null;
		}
	}

	function NewItemsCountByPeriod($interval = 15) {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT COUNT(*) as `qtde` 
											FROM publicacoes p
											WHERE DATEDIFF(now(), p.DataPublicacao) <= $interval";
		
		if ($sql->execute()) {
			$r = $sql->fetch();
			return $r['qtde'];
		} else {
			return null;
		}
	}
	
	function getPublicados($pageindex = 1, $pagesize = 10, $orderby, &$totalrows, $filter) {
		if (!$filter)
            $filter = new Filter();

		$filter->add('isPublicado', '=', '1');
		$filter->add('AutorizaPublicacao', '=', '1');
		
		return $this->Items($pageindex, $pagesize, $orderby, $totalrows, $filter);
	}
	
	function getItemsByAssociado($userid) {
		$filter = new Filter();
		$filter->add('isPublicado', '=', '1');
		$filter->add('AutorizaPublicacao', '=', '1');
		$filter->add('CreatedBy', '=', $userid);
		
		return $this->Items(1, 25, null, $totalrows, $filter);
	}
	
	function Items($pageindex = 1, $pagesize = 10, $orderby, &$totalrows, $filter = null) {		
		$sql = new SqlHelper();
		
		//Validate params
		if (!is_int($pageindex)) $pageindex = 1;
		if (!is_int($pagesize)) $pagesize = 10;
		if ($pagesize > 50) $pagesize = 50;
		$start = $pagesize * ($pageindex-1);		
		
		//Set orderby
		switch ($orderby) {
			case 'Data':
				$orderby = 'p.DataPublicacao DESC';
				break;
			default:
				$orderby = 'p.Titulo';				
		}
		
		//Filter
		if (!$filter) $filter = new Filter();
		
		//Rowcount
		$sql->command = "SELECT Count(*) AS 'Rows' FROM publicacoes p
											$filter->expression";		
		$sql->execute();
		if ($r = $sql->fetch()) $totalrows = $r['Rows'];
		if ($start > $totalrows) {$start = 0; $pageindex = 1;}
		
		//Select
		$sql->command = "SELECT p.*, t.PublicacaoTipo, u.Nome AS `CreatedByName` 
											FROM publicacoes p
											INNER JOIN publicacoes_tipos t ON p.PublicacaoTipoId = t.PublicacaoTipoId
											INNER JOIN users u on p.CreatedBy = u.UserId 
											$filter->expression
											ORDER BY $orderby LIMIT $start, $pagesize";		
		$sql->execute();
			
		while ($r = $sql->fetch()) {
			$it = new Publicacao();
			
			$it->id = $r['PublicacaoId'];
			$it->titulo = $r['Titulo'];
			$it->autor = $r['Autor'];
			$it->email = $r['Email'];
			$it->coautores = $r['Coautores'];
			$it->resumo = $r['Resumo'];
			$it->instituicao = $r['Instituicao'];
			$it->url = $r['URL'];
			$it->ispublicado = $r['IsPublicado'];
			$it->datapublicacao = $r['DataPublicacao'];
			$it->tipo = $r['PublicacaoTipo'];
			$it->tipoid = $r['PublicacaoTipoId'];
			$it->filename = $r['Filename'];
			$it->createdby = $r['CreatedBy'];
			$it->createdbyname = $r['CreatedByName'];
			
			$lst[] = $it;
		}
		
		if (isset($lst)) {return $lst;} else {return null;}
	}
	
	function Publicar($id, $publicar = 1) {
		$sql = new SqlHelper();
		
		$sql->command = "UPDATE publicacoes SET IsPublicado = $publicar WHERE PublicacaoId = $id";
		return $sql->execute();
	}
	
	function Delete($id) {
		$sql = new SqlHelper();
		
		//Delete filename
		$sql->command = "SELECT Filename FROM publicacoes WHERE PublicacaoId = $id";
		$sql->execute();
		$r = $sql->fetch();
			
		if ($r) {
			$filename = $r['Filename'];			
			if ($filename) {
				$fs = new FileHandler();
				$retDelete = $fs->deleteFile($filename);
			}	
		}		
		
		//Delete row
		$sql->command = "DELETE FROM publicacoes WHERE PublicacaoId = $id";
		return $sql->execute();
	}
}

?>