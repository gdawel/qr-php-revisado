<?php
ob_start();
include_once 'SqlHelper.class.php';
include_once 'Mail.class.php';
ob_clean();

if (!session_id()) session_start();


class User {
	var $userid, $password, $nome, $email, $email2, $uf, $cidade, $pais, $endereco, $numero, $complemento, $cep, $bairro, 
			$telefonecomercial, $telefoneresidencial, $celular, $datanascimento,
			$atividades, $interesses, $instituicao, $ativo, $publicacoes, $url,
			$cpf, $nivelacademico, $areaocupacao, $profissao, $sexo, $sexoid, $createddate;
	var $roles, $questid;
    var $valor; //utilizada no cart (valor individual do curso)

	function __construct() {}
	
	function isinrole($rolename) {		
		if (!$this->roles) return false;
		
		foreach (explode(',', $rolename) as $r) {
			if (in_array($r, $this->roles)) return true;
		}
		return false;
	}
}


class Users {
	var $error;

	function items($pageindex = 1, $pagesize = 10, $orderby='nome', $totalrows = 0, $filter = null) {
		$sql = new SqlHelper();
		
		//Validate params
		if (!is_int($pageindex)) $pageindex = 1;
		if (!is_int($pagesize)) $pagesize = 10;
		if ($pagesize > 50) $pagesize = 50;
		$start = $pagesize * ($pageindex-1);		
		
		//Set orderby
		switch ($orderby) {
			case 'createddate':
				$orderby = 'CreatedDate';
				break;
			default:
				$orderby = 'Nome';				
		}
		
		//where
		if (!$filter) $filter = new Filter();
		
		//Rowcount
		$sql->command = "SELECT Count(*) AS 'Rows' FROM users u
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
								$filter->expression 
								ORDER BY $orderby LIMIT $start, $pagesize";		
		if ($sql->execute()) {
			while ($r = $sql->fetch()) {
				$usr = new User();
				
				$usr->userid = $r['UserId'];
				$usr->nome = $r['Nome'];
				$usr->email = $r['Email'];
				$usr->email2 = $r['Email2'];
				$usr->endereco = $r['Endereco'];
				$usr->sexoid = $r['SexoId'];
				$usr->cpf = $r['CPF'];
				$usr->cep = $r['CEP'];
				$usr->instituicao = $r['Instituicao'];
				$usr->bairro = $r['Bairro'];
				$usr->cidade = $r['Cidade'];
				$usr->pais = $r['Pais'];
				$usr->uf = $r['UF'];
				$usr->telefonecomercial = $r['TelefoneComercial'];
				$usr->telefoneresidencial = $r['TelefoneResidencial'];
				$usr->celular = $r['Celular'];
				$usr->nivelacademico = $r['NivelAcademico'];
				$usr->areaocupacao = $r['AreaOcupacao'];
				$usr->instituicao = $r['Instituicao'];
				$usr->profissao = $r['Profissao'];
				$usr->atividades = $r['Atividades'];
				$usr->interesses = $r['Interesses'];
				$usr->url = $r['URL'];
				$usr->createddate = $r['CreatedDate'];
				$usr->ativo = $r['Ativo'];

				$lst[] = $usr;
			}
			
			if (isset($lst)) {
				foreach ($lst as $usr) {
					$sql->command = "SELECT Rolename FROM usersinroles WHERE UserId = $usr->userid";
					$sql->execute();
					while ($r = $sql->fetch()) {
						$usr->roles[] = $r['Rolename'];
					}
				}
			}
			
			if (isset($lst)) return $lst; else return null;
			
		}	else {
			$this->error = $sql->error;
			return false;
		}
	}

	function getUserIdByCPF($cpf) {
		$sql = new SqlHelper();
		$sql->command = "SELECT u.* FROM users u WHERE u.CPF = ".$sql->escape_string($cpf, true);
		
		if ($sql->execute()) {
			if ($r = $sql->fetch())
				return $r['UserId'];
			else
				return null;
		}
	}
	
	function getAssociadoIdByCPF($cpf) {
		$sql = new SqlHelper();
		$sql->command = "SELECT u.* 
										FROM users u
										INNER JOIN usersinroles r ON u.UserId = r.UserId
										WHERE 
											r.Rolename = 'Associado'
											AND u.CPF = ".$sql->escape_string($cpf, true);
		
		if ($sql->execute()) {
			if ($r = $sql->fetch())
				return $r['UserId'];
			else
				return null;
		}
	}
	
	function item($userid) {
		$sql = new SqlHelper();
		$sql->command = "SELECT * FROM users WHERE UserId = $userid";
		$sql->execute();
		
		if ($r = $sql->fetch()) {
			//Populate user obj
			$usr = new User();
			
			$usr->userid = $r['UserId'];
			$usr->nome = $r['Nome'];
			$usr->email = $r['Email'];
			$usr->email2 = $r['Email2'];
			$usr->datanascimento = $r['DataNascimento'];
            $usr->endereco = $r['Endereco'];
            $usr->numero = $r['Numero'];
            $usr->complemento = $r['Complemento'];
			$usr->sexoid = $r['SexoId'];
			$usr->cpf = $r['CPF'];
			$usr->cep = $r['CEP'];
			$usr->instituicao = $r['Instituicao'];
			$usr->bairro = $r['Bairro'];
			$usr->cidade = $r['Cidade'];
			$usr->pais = $r['Pais'];
			$usr->uf = $r['UF'];
			$usr->telefonecomercial = $r['TelefoneComercial'];
			$usr->telefoneresidencial = $r['TelefoneResidencial'];
			$usr->celular = $r['Celular'];
			$usr->nivelacademico = $r['NivelAcademico'];
			$usr->areaocupacao = $r['AreaOcupacao'];
			$usr->instituicao = $r['Instituicao'];
			$usr->profissao = $r['Profissao'];
			$usr->atividades = $r['Atividades'];
			$usr->interesses = $r['Interesses'];
			$usr->url = $r['URL'];
			$usr->createddate = $r['CreatedDate'];
			$usr->ativo = $r['Ativo'];
			
			$sql->command = "SELECT Rolename FROM usersinroles WHERE UserId = $usr->userid";
			$sql->execute();
			while ($r = $sql->fetch()) {
				$usr->roles[] = $r['Rolename'];
			}
			
			return $usr;
		} else {
			return null;
		}
	}
	
	/*
	Autentica um usu?rio. 
	$type: 1=>indica que ? um usu?rio com fun??es na aplicacao (gestor, admin, etc)
				 2=>indica que ? um respondente
	*/
	function login($userid, $password, $type=1) {
		$sql = new SqlHelper();
						
		if ($type == 1)  {
			$sql->command = "SELECT *
							FROM users 
							WHERE Email = ".$sql->escape_string($userid, true)." AND Password = '".hash('ripemd160', $password)."'";
			$sql->execute();
			
			if ($r = $sql->fetch()) {
				//Populate user obj
				$usr = new User();
				
				$usr->userid = $r['UserId'];
				$usr->nome = $r['Nome'];
				$usr->email = $r['Email'];
				$usr->email2 = $r['Email2'];
				$usr->endereco = $r['Endereco'];
                $usr->numero = $r['Numero'];
                $usr->complemento = $r['Complemento'];
				$usr->sexoid = $r['SexoId'];
				$usr->cpf = $r['CPF'];
                $usr->datanascimento = $r['DataNascimento'];
				$usr->cep = $r['CEP'];
				$usr->instituicao = $r['Instituicao'];
				$usr->bairro = $r['Bairro'];
				$usr->cidade = $r['Cidade'];
				$usr->pais = $r['Pais'];
				$usr->uf = $r['UF'];
				$usr->telefonecomercial = $r['TelefoneComercial'];
				$usr->telefoneresidencial = $r['TelefoneResidencial'];
				$usr->celular = $r['Celular'];
				$usr->nivelacademico = $r['NivelAcademico'];
				$usr->areaocupacao = $r['AreaOcupacao'];
				$usr->instituicao = $r['Instituicao'];
				$usr->profissao = $r['Profissao'];
				$usr->atividades = $r['Atividades'];
				$usr->interesses = $r['Interesses'];
				$usr->url = $r['URL'];
				
				//Verifica se usu?rio est? ativo
				if ($r['Ativo'] == '0') {
					$this->error = 'A aprova??o de seu acesso est? pendente pelo adminstrador.';
					return false;
				}
				
				//get roles
				$sql->command = "SELECT Rolename FROM usersinroles WHERE UserId = $usr->userid";
				$sql->execute();
				while ($r = $sql->fetch()) {
					$usr->roles[] = $r['Rolename'];
				}
			} else {
				$this->error = 'Usu?rio ou senha inv?lidos. Tente novamente.';
				return false;
			}
		
		} else { //&type==2: Respondente
			$sql->command = "SELECT q.PesquisaId, q.QuestionarioId, q.Nome, q.Email, p.StatusId AS `PesquisaStatusId` 
									FROM questionarios q
									INNER JOIN pesquisas p ON p.PesquisaId = q.PesquisaId 
									WHERE q.QuestionarioId = ".$sql->escape_string($userid, true)." AND q.Password = ".$sql->escape_string($password, true);
		
			$sql->execute();
			
			if ($r = $sql->fetch()) {
				//Verificar status da Pesquisa
				$pesquisaStatusId = $r['PesquisaStatusId'];
				if ($pesquisaStatusId != '1') { //Pesquisa ativa
					$this->error = 'Pesquisa n?o est? ativa. Entre em contato com seu gestor.';
					return false;
				}
				
				//Populate user obj
				$usr = new User();
				
				$usr->userid = $r['QuestionarioId'];
				$usr->questid = $r['QuestionarioId'];
				$usr->nome = $r['Nome'];
				$usr->email = $r['Email'];
				
				//Associar com rolename Respondente
				$usr->roles[] = 'Respondente';
			} else {
				$this->error = 'Usu?rio ou senha inv?lidos. Tente novamente.'; 
			}
		}
	
		//Store new session, if successful
		if (isset($usr)) {
			$_SESSION['user'] = $usr;
			return true;
		} else {
			$_SESSION['user'] = null;
			return false;
		}
	}
	
	
	/**
	 * Define as roles de determinado usu?rio de acordo com o array informado.
	 * 
	 * @param mixed $userid
	 * @param mixed $roles
	 * @return void
	 */
	function setroles($userid, $roles) {
		$sql = New SqlHelper();
		$sql->command = "DELETE FROM usersinroles WHERE userid = $userid";
		if (!$sql->execute()) {
			$this->error = 'Erro ao excluir fun??es. ' . $sql->error;
			return false;
		}
		
		if ($roles) {
			foreach ($roles as $role) {
				$sql->command = "INSERT INTO usersinroles (UserId, Rolename) VALUES ($userid, '$role')";
				if (!$sql->execute()) {
					$this->error = "Erro ao incluir fun??o '$role'. " . $sql->error;
					return false;
				}
			} 
		}
		
		return true;
	}
	
	function ativar($id, $ativar = 1) {
		$sql = new SqlHelper();
		
		$sql->command = "UPDATE users SET Ativo=$ativar WHERE UserId = $id";
		if ($sql->execute()) {
			if ($ativar == 0) return true;
		
			//Send a confirmation email
			$sql->command = "SELECT u.Nome, u.Email, 
											(SELECT COUNT(*) FROM usersinroles ur WHERE ur.UserId = u.UserId AND ur.Rolename = 'Gestor') AS `IsGestor`  
									FROM users u WHERE UserId = $id";
			$sql->execute();
			if ($r = $sql->fetch()) {
				$isGestor = $r['IsGestor'];
				$rolename = ($isGestor == 0 ? 'Associado' : 'Gestor');
				$loginType = ($isGestor == 0 ? '2' : '1');
				
				$email = new Email();
				$email->to = $r['Email'];
				$email->subject = utf8_decode('Seu cadastro foi validado');
				$email->message = "<html>
										<style>
											th {text-align:left; vertical-align:top;} 
											body, table {font:normal 10pt Verdana;}
										</style>
										<body>
											<p>Ol?, $r[Nome]!</p>
											<br />
											<p>Seu cadastro foi aprovado. ? um enorme prazer ter voc? como $rolename da SOBRARE.</p>
											<p>Acesse agora sua <u>?rea exclusiva</u> no site da SOBRARE, clicando no bot?o 
												<a href='http://www.sobrare.com.br/cockpit/login.php?type=$loginType'>?rea do $rolename</a>, no canto superior direito, 
												e informe seus dados de acesso enviados anteriormente para seu e-mail.</p>

											<p>Nesta ?rea, no link <b>Meu Perfil</b>, voc? pode completar seu perfil com informa??es relevantes aos seus trabalhos e projetos. 
											Se desejar, divulgue um e-mail para poss?veis contatos e publique trabalhos desenvolvidos na tem?tica 
											da resili?ncia.</p>
							
											<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>	
											<br />
											<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
										</body>
										</html>";
				if (!$email->send()) $this->error = 'Email n?o enviado. Verifique o endere?o de destino.';
				else $this->error = 'Email enviado com sucesso';
			}
			return true;
		} else {
			return false;
		}
	}
	
	
	function add(&$user) {
		//Validation
		if ((!$user->nome) || (!$user->password) || (!$user->email) ) {
			$this->error = 'Nome, e-mail e senha s?o obrigat?rios';
			return false;
		}
			
		$sql = new SqlHelper();
		
		//check if email already exists
		$sql->command = "SELECT email FROM users WHERE email=".$sql->escape_string($user->email, true);
		if ($sql->execute()) {
			if ($sql->rowscount() != 0) {
				$this->error = 'E-mail j? utilizado por um associado. 
									Solicite sua associa??o pela <a href="contato.php" title="Solicite sua associa??o">P?gina de Contato</a>.';
				return false;
			}
		} else {
			$this->error = $sql->error;
			return false;
		}	

		//Some adjusts
		if ($user->ativo != 1) $user->ativo = 0;
		
		$sql->command = "INSERT INTO users (nome, email, password, uf, cidade, pais, cep, endereco, bairro, telefoneresidencial, telefonecomercial, 
																				celular, cpf, sexoid, profissao, atividades, interesses, areaocupacao, ativo, createddate) VALUES
																				(".$sql->escape_string($user->nome, true)."
																				,".$sql->escape_string($user->email, true)."
																				, '".hash("ripemd160", $user->password)."' 
																				,".$sql->escape_string($user->uf, true)."
																				,".$sql->escape_string($user->cidade, true)."
																				,".$sql->escape_string($user->pais, true)."
																				,".$sql->escape_string($user->cep, true)."
																				,".$sql->escape_string($user->endereco, true)."
																				,".$sql->escape_string($user->bairro, true)."
																				,".$sql->escape_string($user->telefoneresidencial, true)."
																				,".$sql->escape_string($user->telefonecomercial, true)."
																				,".$sql->escape_string($user->celular, true)."
																				,".$sql->escape_string($user->cpf, true)."
																				,".$sql->escape_id($user->sexoid)."
																				,".$sql->escape_string($user->profissao, true)."
																				,".$sql->escape_string($user->atividades, true)."
																				,".$sql->escape_string($user->interesses, true)."
																				,".$sql->escape_string($user->areaocupacao, true).", $user->ativo, now())";
		if ($sql->execute()) {
			$user->userid = $sql->getInsertId();
			
			if ($user->roles) {
				foreach ($user->roles as $role) {
					$sql->command = "INSERT INTO usersinroles (UserId, Rolename) VALUES ($user->userid, '$role')";
					$sql->execute();
				}
			}
		} else {
			$this->error = $sql->error;
			return false;
		}
		
		return true;
	}

	function update($user) {
		//Validation
		if ((!$user->nome) || (!$user->email) ) {
			$this->error = 'Nome e e-mail s?o obrigat?rios';
			return false;
		}
			
		$sql = new SqlHelper();
		
		//check if email already exists
		$sql->command = "SELECT email FROM users WHERE email = ".$sql->escape_string($user->email, true)." AND userid <> $user->userid";
		if ($sql->execute()) {
			if ($sql->rowscount() != 0) {
				$this->error = 'E-mail j? utilizado por um associado.';
				return false;
			}
		} else {
			return false;
		}	
		
		$sql->command = "UPDATE users SET 
								nome = ".$sql->escape_string($user->nome, true).", 
								email = ".$sql->escape_string($user->email, true).", 
								email2 = ".$sql->escape_string($user->email2, true).",
                                datanascimento = ".$sql->prepareDate($user->datanascimento, true).",
								uf = ".$sql->escape_string($user->uf, true).", 
								cidade = ".$sql->escape_string($user->cidade, true).",
								pais = ".$sql->escape_string($user->pais, true).", 
								cep = ".$sql->escape_string($user->cep, true).", 
								endereco = ".$sql->escape_string($user->endereco, true).",
                                numero = ".$sql->escape_string($user->numero, true).",
                                complemento = ".$sql->escape_string($user->complemento, true).", 
								bairro = ".$sql->escape_string($user->bairro, true).", 
								telefoneresidencial = ".$sql->escape_string($user->telefoneresidencial, true).", 
								telefonecomercial = ".$sql->escape_string($user->telefonecomercial, true).", 
								celular = ".$sql->escape_string($user->celular, true).", 
								cpf = ".$sql->escape_string($user->cpf, true).", 
								sexoid = ".$sql->escape_id($user->sexoid).", 
								profissao = ".$sql->escape_string($user->profissao, true).", 
								nivelacademico = ".$sql->escape_string($user->nivelacademico, true).", 
								areaocupacao = ".$sql->escape_string($user->areaocupacao, true).", 
								instituicao = ".$sql->escape_string($user->instituicao, true).", 
								atividades = ".$sql->escape_string($user->atividades, true).",
								interesses = ".$sql->escape_string($user->interesses, true).",
								url = ".$sql->escape_string($user->url, true)." 
							WHERE userid = $user->userid";
							
		if ($sql->execute()) {
			return true;
		} else {
			$this->error = $sql->error;
			return false;
		}		
	}

	
	static function getCurrent() {
		if (!isset($_SESSION['user'])) return false;
		return $_SESSION['user'];
	}
	
	static function Logout() {
		$_SESSION = array();
		session_destroy();
	}
	
	static function checkAuth($rolelist, $url) {
		$usr = Users::getCurrent();
	
		if (!$usr) { //Denied
			header("Location: $url");
			ob_flush();
			exit;
		}
		
		$lst = explode(",", $rolelist);
		foreach ($lst as $role) {
			if ($usr->isinrole($role)) return true; //Ok
		}
	
		//Denied
		header("Location: $url");
		ob_flush();
		exit;
	}
	
	function changePassword($newpassword, $confirmnewpassword, $userid = null) {
		if (!$userid) {
			$usr = $this->getCurrent();
			if (!$usr) {
				$this->error = 'Usu?rio n?o logado.';
				return false;
			} else  {
				$userid = $usr->userid;
			}
		}
		
		if ((!$newpassword) || (!$confirmnewpassword)) {
			$this->error = 'Senha n?o pode ser vazia';
			return false;
		}
		
		if (strlen($newpassword) < 4) {
			$this->error = 'Senha deve possuir 4 caracteres ou mais';
			return false;
		}		
	
		if ($newpassword != $confirmnewpassword) {
			$this->error = 'Senhas n?o conferem';
			return false;
		}	
		
		$sql = new SqlHelper();
		$sql->command = "UPDATE users SET password='".hash('ripemd160', $newpassword)."' WHERE userid=$userid";
		return $sql->execute();
	}
	
	function GenerateNewPassword($email) {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT UserId FROM users WHERE email=".$sql->escape_string($email, true);
		
		if ($sql->execute()) {
			if (!$r = $sql->fetch()) {
				$this->error = 'E-mail n?o existente.';
				return false;
			} else {
				$userid = $r['UserId'];
			}
		} else {
			return false;
		}	
		
		//Generate new password
		$new_password = substr(md5(uniqid()), 0, 7);
		
		//update db
		$sql->command = "UPDATE users SET password='".hash('ripemd160', $new_password)."' WHERE userid=$userid";
		if ($sql->execute()) {		
			$msg = new Email();
	
			$msg->to = $email;
			$msg->subject = 'Nova senha para acesso ao site da SOBRARE';	
			$msg->message = "	<style>
														th {text-align:left; vertical-align:top;} 
														body, table {font:normal 10pt Verdana;}
													</style>
			
													<p>Ol?.</p>
													<p>Segue nova senha para acesso ao site da <a href='http://www.sobrare.com.br/'>SOBRARE</a>.</p>
													<table>
														<tr>
															<th>Login</th><td>$email</td>
														</tr>
														<tr>
															<th>Senha</th><td>$new_password</td>
														</tr>
													</table>
													
													<p>Com esta nova senha, voc? pode acessar os servi?os j? contratados ou a se??o de associados.</p>
													<p>Para alterar esta senha, acesse o menu Op??es (canto superior direito), no <a href='http://www.sobrare.com.br/cockpit/login.php'>Acesso do Gestor</a>.</p>
													
													<br />
													<p><strong>Equipe SOBRARE</strong></p>";
			
			$msg->send();
			return true;		
		} else {
			return false;
		}
	}
}

?>