<?php
include_once 'User.class';
include_once 'Publicacao.class';
include_once 'Mail.class';

class Associados {
	var $error;
	
	function Items($pageindex = 1, $pagesize = 10, $orderby='nome', &$totalrows, $filter = null) {
		$sql = new SqlHelper();
		
		//Validate params
		if (!is_int($pageindex)) $pageindex = 1;
		if (!is_int($pagesize)) $pagesize = 10;
		//if ($pagesize > 50) $pagesize = 50;
		$start = $pagesize * ($pageindex-1);		
		
		//Set orderby
		switch ($orderby) {
			case 'instituicao':
				$orderby = 'instituicao';
				break;
			default:
				$orderby = 'Nome';				
		}
		
		//where
		if (!$filter) $filter = new Filter();
		
		//Rowcount
		$sql->command = "SELECT Count(*) AS 'Rows' FROM users u
											INNER JOIN usersinroles r on r.userid = u.userid AND r.rolename='Associado'
											$filter->expression";		
		if ($sql->execute()) {
			if ($r = $sql->fetch()) $totalrows = $r['Rows'];
			if ($start > $totalrows) {$start = 0; $pageindex = 1;}
		} else {
			return;
		}
		
		//Select
		$sql->command = "SELECT u.* 
								FROM users u
								INNER JOIN usersinroles r on r.userid = u.userid AND r.rolename='Associado'
								$filter->expression 
								ORDER BY $orderby LIMIT $start, $pagesize";		
		if ($sql->execute()) {
			while ($r = $sql->fetch()) {
				$a = new User();
				
				$a->id = $r['UserId'];
				$a->nome = $r['Nome'];
                $a->cpf = $r['CPF'];
				$a->email = $r['Email'];
				$a->email2 = $r['Email2'];
				$a->uf = $r['UF'];
				$a->cep = $r['CEP'];
				$a->pais = $r['Pais'];
				$a->cidade = $r['Cidade'];
				$a->endereco = $r['Endereco'];
				$a->bairro = $r['Bairro'];
				$a->telefoneresidencial = $r['TelefoneResidencial'];
				$a->telefonecomercial = $r['TelefoneComercial'];
				$a->celular = $r['Celular'];				
				$a->nivelacademico = $r['NivelAcademico'];
				$a->areaocupacao = $r['AreaOcupacao'];
				$a->instituicao = $r['Instituicao'];
				$a->profissao = $r['Profissao'];
				$a->atividades = $r['Atividades'];
				$a->interesses = $r['Interesses'];
				$a->url = $r['URL'];
				$a->ativo = $r['Ativo'];
				//$a->publicacoes = $r['Publicacoes'];

				$lst[] = $a;
			}
			
			if (isset($lst)) {
				//Get publicacoes
				$publicacoes = new Publicacoes();				
				foreach ($lst as $a) {
					$a->publicacoes = $publicacoes->getItemsByAssociado($a->id);
				}
				
				return $lst; 
			} else 
				return null;
		} else {
			return null;
		}
	}
	
	function Add(&$assoc) {
		$assoc->roles[] = 'Associado';			
		
		$users = new Users();
		$ret = $users->add($assoc);

		//Send notification email
		if ($ret) {
			//Para admin	
			$email = new Email();
			$email->to = 'faleconosco@sobrare.com.br';
			$email->subject = 'Notifica??o de novo associado';
			$email->message = utf8_encode("Ol?! <br/><br/>".htmlentities($assoc->nome)." se associou ? SOBRARE. Acesse o Cockpit do Administrador e aprove sua associa??o.");

            if (!$email->send())
                $this->error = 'E-mail n?o pode ser enviado.';
			
			//Para novo associado
			$email->to = $assoc->email;
			$email->subject = 'Seja bem-vindo ? SOBRARE';
			$email->message = utf8_encode(
                                "<html>
                                    <head>
                                       <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
                                    </head>
									<style>
										th {text-align:left; vertical-align:top;} 
										body, table {font:normal 10pt Verdana;}
									</style>
									<body>
										<p>Ol?!</p>
										<br />
										<p>Este ? um e-mail de confirma??o da sua associa??o ? SOBRARE - Sociedade Brasileira de Resili?ncia. Seja bem-vindo!</p>
										<p>A partir de agora, voc? poder?:</p>
										<ul>
											<li>Divulgar seus trabalhos cient?ficos no site da SOBRARE, em m?dias e entre as comunidades que pesquisam e trabalham com resili?ncia</li>
											<li>Estar informado sobre cursos, congressos e programas de capacita??o sobre resili?ncia</li>
											<li>Trocar informa??es, d?vidas, achados e conhecimentos com outros envolvidos com o trabalho de resili?ncia</li>
											<li>Receber descontos nos eventos promovidos pela SOBRARE</li>
										</ul>
										<p>Seu cadastro ser? validado pelo administrador do site. Ap?s isto, para acessar sua ?rea exclusiva, 
											entre no site da SOBRARE e clique no bot?o <a href='http://www.sobrare.com.br/cockpit/login.php?type=2'>?rea do Associado</a>, 
											no canto superior esquerdo, e informe seus dados de acesso abaixo:</p>
										<br />
										<p>
											<strong>Usu?rio:</strong> ".htmlentities($assoc->email)."<br />
											<strong>Senha:</strong> ".htmlentities($assoc->password)."<br />
										</p>
										<p>Você também pode ver um breve vídeo que irá explicar como funciona a área do associado: <a href=“https://www.youtube.com/watch?v=ABKESInr4Og”>Quero acessar o vídeo!</a></p>
										<br /><br />
										<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>
										
										<br /><br />
										<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
									 </body>
                                 </html>");
            if (!$email->send())
                $this->error = 'E-mail n?o pode ser enviado.';
		} else {
			$this->error = $users->error;
		} 
		
		return $ret;
	}
	
	function Delete($id) {
		$sql = new SqlHelper();
		
		$sql->command = "DELETE FROM users WHERE UserId = $id";
		return $sql->execute();
	}
	
	function Ativar($id, $ativar=1) {
		$users = new Users();
		
		$ret = $users->ativar($id, $ativar);
		$this->error = $users->error;
		
		return $ret; 		
	}
	
	function getDesativadosCount() {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT COUNT(*) as 'qtde' 
											FROM users u INNER JOIN usersinroles r ON u.userid = r.userid AND r.Rolename = 'Associado'
											WHERE u.Ativo = 0";
		
		if ($sql->execute()) {
			$r = $sql->fetch();
			return $r['qtde'];
		} else {
			return null;
		}
	}
	
	function NewItemsCountByPeriod($interval = 15) {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT COUNT(*) as 'qtde' 
											FROM users u INNER JOIN usersinroles r ON u.userid = r.userid AND r.Rolename = 'Associado'
											WHERE DATEDIFF(now(), u.CreatedDate) <= $interval";
		
		if ($sql->execute()) {
			$r = $sql->fetch();
			return $r['qtde'];
		} else {
			return null;
		}
	}
}
?>