<?php
include_once 'User.class.php';
include_once 'Cart.class';
include_once 'Curso.class';

define('INSCRICAO_TIPO_FISICA', 1);
define('INSCRICAO_TIPO_ASSOCIADO', 2);
define('INSCRICAO_TIPO_JURIDICA', 3);
define('INSCRICAO_TIPO_ESTUDANTE', 4);
define('CONDICAO_PAGAMENTO_AVISTA', 1);
define('CONDICAO_PAGAMENTO_2X', 2);
define('CONDICAO_PAGAMENTO_3X', 3);

define('INSCRICAO_STATUS_CANCELADO', '7');
define('INSCRICAO_STATUS_PAGO', '3');

class Inscricao
{
    var $id, $tipoid, $tipo, $cursoid, $curso, $modulos;
    var $responsavel, $participantes;
    var $razaosocial, $cnpj, $ie;
    var $valor, $desconto, $createddate;
    var $statusid, $status;
    var $condicaopagamentoid, $condicaopagamento;
    var $cupomid, $cupom_codigo, $cupom;
    var $fontedivulgacaoid, $fontedivulgacao;
    var $PagSeguroCode, $PagSeguroTransaction;

    function __construct()
    {
    }

    function checkAssociadosInParticipantes()
    {
        if (!$this->participantes)
            return;

        $users = new Users();
        foreach ($this->participantes as $p) {
            $p->userid = $users->getAssociadoIdByCPF($p->cpf);
        }
    }

    function calculateValor()
    {
        if (!$this->cursoid) {
            throw new Exception('Nenhum curso selecionado para a inscri??o.');
        }
        if (!$this->curso) {
            $cursos = new Cursos();
            $this->curso = $cursos->Item(($this->cursoid));
            if (!$this->curso) {
                throw new Exception('Nenhum curso selecionado para a inscri??o.');
            }
        }

        $this->checkAssociadosInParticipantes();
        $this->valor = 0;
        $this->desconto = 0;
        $valorCurso = $this->curso->getValor($this->modulos);

        //get info do cupom
        if ($this->cupom_codigo) {
            $cupons = new Cupons();
            $this->cupom = $cupons->ItemByCodigo($this->cupom_codigo);
            $this->cupomid = $this->cupom->id;
        } else {
            $this->cupom = null;
        }
        
        
        //BIZ: o uso do cupom substitui qualquer outro desconto existente.
        if ($this->cupom) {
            $this->valor = $valorCurso * count($this->participantes);
            
            if ($this->cupom->isValido()) {
                switch ($this->cupom->tipodescontoid) {
                    case CUPOM_TIPODESCONTO_VALOR:
                        $this->desconto += $this->cupom->desconto;
                        break;
                    
                    case CUPOM_TIPODESCONTO_PORCENTAGEM:
                    default:
                        $this->desconto += ($this->valor * ($this->cupom->desconto / 100));
                        break;                        
                }
            }
        } else {
            //remove o cupom
            $this->cupom_codigo = null;
            $this->cupomid = null;        
        
            //precificar por participante
            foreach ($this->participantes as $p) {
                //Aqui assume-se que, se o participante tem id, ? um associado.
                //Esta condicao ? gerada pela function checkAssociadosInParticipantes();
                //TODO: verificar se esta condicao sempre eh valida
                if ($p->userid) {
                    $p->valor = $valorCurso * (1 - $this->curso->descontoassociado / 100);
                } else
                    if ($this->tipoid == INSCRICAO_TIPO_ESTUDANTE) {
                        //aplica-se o mesmo desconto de associado para estudantes
                        $p->valor = $valorCurso * (1 - $this->curso->descontoassociado / 100);
    
                    } else {
                        $p->valor = $valorCurso;
                    }
                    $this->valor += $p->valor;
            }
    
            //calc desconto modulos
            if (($this->curso->modulosminimo > 0) && (count($this->modulos) >= $this->curso->modulosminimo)) {
                $this->desconto += ($this->valor * ($this->curso->descontomodulos / 100));
            }
    
            //calc desconto para grupos
            if (($this->curso->grupominimo > 0) && (count($this->participantes) >= $this->curso->grupominimo)) {
                $this->desconto += ($this->valor * ($this->curso->descontogrupo / 100));
            }
        }
        
        //aplicar descontos
        $this->valor -= $this->desconto;

        return $this->valor;
    }

    function allowReenviarSolicitacaoPagamento()
    {
        //return (($this->statusid == INSCRICAO_STATUS_CANCELADO) || (!$this->PagSeguroTransaction));
        return (($this->statusid == INSCRICAO_STATUS_CANCELADO));
    }

    /**
     * C?digo de verificao utilizado no envio de arquivos pelo cliente, para congressos, por exemplo
     * */
    function getCodigoVerificacao()
    {
        return substr($this->PagSeguroCode, 0, 10);
    }
} //inscricao class


class Inscricoes
{
    var $error, $urlPagSeguro;

    function getInscritosCountByCursoId($cursoId, $confirmados = false)
    {
        if ($confirmados)
            $sql_confirmados = " AND ci.StatusId IN (3,4)";
        else
            $sql_confirmados = "";

        $sql = new SqlHelper();
        $sql->command = "SELECT COUNT(*) AS Qtde 
								FROM cursos_inscritos i
								INNER JOIN cursos_inscricoes ci ON ci.InscricaoId = i.InscricaoId 
								WHERE ci.CursoId = $cursoId
                                      $sql_confirmados";

        if ($sql->execute()) {
            if ($r = $sql->fetch())
                return $r['Qtde'];
            else
                return null;
        } else {
            return null;
        }
    }

    function Items($filter = null)
    {
        if (!$filter)
            $filter = new Filter();

        $sql = new SqlHelper();
        $sql->command = "SELECT 
									ci.*,
									c.Nome AS Curso,
								 	t.InscricaoTipo,
								 	st.Status,
								 	pag.CondicaoPagamento
								FROM cursos_inscricoes ci
								INNER JOIN cursos_inscricoes_status st ON st.StatusId = ci.StatusId
								INNER JOIN condicoespagamento pag ON ci.CondicaoPagamentoId = pag.CondicaoPagamentoId
								INNER JOIN cursos c ON ci.CursoId = c.CursoId
								INNER JOIN cursos_inscricoes_tipos t ON t.InscricaoTipoId = ci.InscricaoTipoId
								$filter->expression
								ORDER BY ci.CreatedDate DESC";

        if ($sql->execute()) {
            $sql2 = new SqlHelper();
            $cursos = new Cursos();

            while ($r = $sql->fetch()) {
                $i = new Inscricao();
                $i->id = $r['InscricaoId'];
                $i->tipoid = $r['InscricaoTipoId'];
                $i->tipo = $r['InscricaoTipo'];
                $i->razaosocial = $r['RazaoSocial'];
                $i->statusid = $r['StatusId'];
                $i->status = $r['Status'];
                $i->valor = $r['Valor'];
                $i->condicaopagamentoid = $r['CondicaoPagamentoId'];
                $i->condicaopagamento = $r['CondicaoPagamento'];

                $i->PagSeguroCode = $r['PagSeguroCode'];
                $i->PagSeguroTransaction = $r['PagSeguroTransaction'];

                $i->cursoid = $r['CursoId'];
                /*$i->curso = new Curso();
                $i->curso->nome = $r['Curso'];
                $i->curso->id = $r['CursoId'];*/
                $i->curso = $cursos->Item($i->cursoid);

                $i->responsavel = new User();
                $i->responsavel->nome = $r['Nome'];
                $i->responsavel->email = $r['Email'];
                $i->responsavel->profissao = $r['Funcao'];
                $i->responsavel->sexoid = $r['SexoId'];
                $i->responsavel->datanascimento = $r['DataNascimento'];
                $i->responsavel->telefonecomercial = $r['Telefone'];
                $i->responsavel->celular = $r['Celular'];
                $i->responsavel->endereco = $r['Endereco'];
                $i->responsavel->numero = $r['Numero'];
                $i->responsavel->complemento = $r['Complemento'];
                $i->responsavel->cep = $r['CEP'];
                $i->responsavel->bairro = $r['Bairro'];
                $i->responsavel->cidade = $r['Cidade'];
                $i->responsavel->uf = $r['UF'];
                $i->createddate = $r['CreatedDate'];

                //get participantes inscritos
                $sql2->command = "SELECT * FROM cursos_inscritos
										WHERE InscricaoId = $i->id";
                if ($sql2->execute()) {
                    while ($r2 = $sql2->fetch()) {
                        $p = new User();
                        $p->nome = $r2['Nome'];
                        $p->email = $r2['Email'];
                        $p->cpf = $r2['CPF'];
                        $p->userid = $r2['AssociadoId'];
                        $i->participantes[] = $p;
                    }
                }

                //get modulos inscritos
                $sql2->command = "SELECT cim.*, m.Nome FROM cursos_inscricoes_modulos cim
                                        join cursos_modulos m on m.ModuloId = cim.ModuloId 
										WHERE InscricaoId = $i->id";
                if ($sql2->execute()) {
                    while ($r2 = $sql2->fetch()) {
                        $m = new Modulo();
                        $m->id = $r2['ModuloId'];
                        $m->nome = $r2['Nome'];
                        $i->modulos[$m->id] = $m;
                    }
                }


                $lst[] = $i;
            } //while

            if (isset($lst))
                return $lst;
            else
                return null;
        } else {
            $this->error = $sql->error;
            return false;
        }
    }

    function Item($id)
    {
        $filter = new Filter();
        $filter->add('ci.InscricaoId', '=', $id);

        $lst = $this->Items($filter);
        if ($lst) {
            return $lst[0];
        } else {
            $this->error = 'Inscri??o n?o encontrada.';
            return false;
        }
    }

    function add($inscricao)
    {         
        //validacao
        $this->error = null;
        if (!$inscricao->responsavel->nome)
            $this->error = 'Nome ? obrigat?rio. ';
        if (!$inscricao->responsavel->cpf)
            $this->error .= 'CPF ? obrigat?rio. ';
        if (!$inscricao->responsavel->datanascimento)
            $this->error .= 'Data de Nascimento ? obrigat?rio. ';
        if (!$inscricao->responsavel->email)
            $this->error .= 'Email ? obrigat?rio. ';
        if (!$inscricao->responsavel->cep)
            $this->error .= 'CEP ? obrigat?rio. ';
        if (!$inscricao->responsavel->endereco)
            $this->error .= 'Endere?o ? obrigat?rio. ';
        if (!$inscricao->responsavel->numero)
            $this->error .= 'N?mero do endere?o ? obrigat?rio. ';
        if (!$inscricao->responsavel->bairro)
            $this->error .= 'Bairro ? obrigat?rio. ';
        if (!$inscricao->responsavel->cidade)
            $this->error .= 'Cidade ? obrigat?ria. ';

        $inscricao->responsavel->uf = strtoupper($inscricao->responsavel->uf);
        if (!$inscricao->responsavel->uf)
            $this->error .= 'UF ? obrigat?ria. ';

        if (!$inscricao->responsavel->nome)
            $this->error .= 'Nome ? obrigat?rio. ';
        if (!$inscricao->responsavel->telefonecomercial)
            $this->error .= 'Telefone Comercial ? obrigat?rio. ';
        if (!$inscricao->responsavel->celular)
            $this->error .= 'Celular ? obrigat?rio. ';
        if (!$inscricao->responsavel->profissao)
            $this->error .= 'Fun??o ? obrigat?ria. ';
        if (!$inscricao->condicaopagamentoid)
            $inscricao->condicaopagamentoid = CONDICAO_PAGAMENTO_AVISTA; //fixo, desde a integracao com o pagSeguro
        if ($this->error) {
            $this->error .= '<br />Verifique os dados n?o informados ou complete o seu perfil na ?rea do Associado.';
            return false;
        }

        $sql = new SqlHelper();

        try {
            $inscricao->calculateValor();

            //echo "valor2: $inscricao->valor";
            //echo "desconto cupom2: $inscricao->desconto";
            
            $sql->begin();
            $sql->command = "INSERT INTO `cursos_inscricoes`
									(
									`CursoId`,
									InscricaoTipoId,
									`AssociadoId`,
									`Nome`,
									`CPF`,
                                    DataNascimento,
									`SexoId`,
									`Email`,
                                    `CEP`,
									`Endereco`,
                                    Numero,
                                    Complemento,
									`Bairro`,
									`Cidade`,
									`UF`,
									`Telefone`,
									`Celular`,
									`RazaoSocial`,
									`Funcao`,
									`CNPJ`,
									`IE`,
									Valor,
									CondicaoPagamentoId,
                                    CupomId,
                                    FonteDivulgacaoId,
									`CreatedDate`,
									StatusId
									) VALUES (
									$inscricao->cursoid,
									" . $sql->escape_id($inscricao->tipoid) . ",
									" . $sql->escape_id($inscricao->responsavel->userid) . ",
									" . $sql->escape_string($inscricao->responsavel->nome, true) . ",
									" . $sql->escape_string($inscricao->responsavel->cpf, true) . ",
                                    " . $sql->prepareDate($inscricao->responsavel->datanascimento, true) . ",
									" . $sql->escape_id($inscricao->responsavel->sexoid) . ",
									" . $sql->escape_string($inscricao->responsavel->email, true) . ",
                                    " . $sql->escape_string($inscricao->responsavel->cep, true) . ",
									" . $sql->escape_string($inscricao->responsavel->endereco, true) . ",
                                    " . $sql->escape_string($inscricao->responsavel->numero, true) . ",
                                    " . $sql->escape_string($inscricao->responsavel->complemento, true) . ",
									" . $sql->escape_string($inscricao->responsavel->bairro, true) . ",
									" . $sql->escape_string($inscricao->responsavel->cidade, true) . ",
									" . $sql->escape_string($inscricao->responsavel->uf, true) . ",
									" . $sql->escape_string($inscricao->responsavel->telefonecomercial, true) . ",
									" . $sql->escape_string($inscricao->responsavel->celular, true) . ",
									" . $sql->escape_string($inscricao->razaosocial, true) . ",
									" . $sql->escape_string($inscricao->responsavel->profissao, true) . ",
									" . $sql->escape_string($inscricao->cnpj, true) . ",
									" . $sql->escape_string($inscricao->ie, true) . ",
									" . $sql->prepareDecimal($inscricao->valor, true) . ",
									" . $sql->escape_id($inscricao->condicaopagamentoid, true) . ",
									" . $sql->escape_id($inscricao->cupomid) . ",
                                    " . $sql->escape_id($inscricao->fontedivulgacaoid) . ",
                                    now(),
									1
									);";

            if ($sql->execute()) {
                $inscricao->id = $sql->getInsertId();

                //add participantes
                foreach ($inscricao->participantes as $p) {
                    $sql->command = "INSERT INTO cursos_inscritos (InscricaoId, Nome, Email, CPF, AssociadoId) 
											VALUES (
												$inscricao->id,
												" . $sql->escape_string($p->nome, true) . ",
												" . $sql->escape_string($p->email, true) . ",
												" . $sql->escape_string($p->cpf, true) . ",
												" . $sql->escape_string($p->userid, true) . "
											);";
                    if (!$sql->execute()) {
                        $this->error = $sql->error;
                        $sql->rollback();
                        return false;
                    }
                }

                //add modulos
                if ($inscricao->modulos) {
                    foreach ($inscricao->modulos as $m) {
                        $sql->command = "INSERT INTO cursos_inscricoes_modulos (InscricaoId, ModuloId) 
    											VALUES (
    												$inscricao->id,
    												$m->id
    											);";
                        if (!$sql->execute()) {
                            $this->error = $sql->error;
                            $sql->rollback();
                            return false;
                        }
                    }
                }

                //cart, somente se curso nao for gratuito
                if ($inscricao->valor > 0) {
                    $cart = $this->createCart($inscricao);
                    $this->urlPagSeguro = $cart->register();
    
                    //erro ao registrar no pagSeguro
                    if (!$this->urlPagSeguro) {
                        $this->error = $cart->error;
                        $sql->rollback();
                        return false;
                    }
                                        
                    //save transaction code
                    $sql->command = "UPDATE cursos_inscricoes set PagSeguroCode = '$cart->code' WHERE InscricaoId = $inscricao->id";
                    $sql->execute();
                    
                } else {
                    $this->urlPagSeguro = 'N/A';
                    
                    //Set Status=PAGO, j? que o curso ? gratuito
                    $sql->command = "UPDATE cursos_inscricoes set StatusId = ".INSCRICAO_STATUS_PAGO." WHERE InscricaoId = $inscricao->id";
                    $sql->execute();
                }


                //commit das transacoes
                $sql->commit();

                //enviar emails de notificacao
                $this->sendEmailConfirmacaoInscricao($inscricao);
                $this->sendEmailNotificacaoNovaInscricao($inscricao);

                return true;

            } else {
                $sql->rollback();
                $this->error = $sql->error;
                return false;
            }

        }
        catch (exception $e) {
            $sql->rollback();
            //$this->error = $e->getMessage();
            throw $e;
            return false;
        }
    } //add


    function ReiniciarPagamento($inscricaoId)
    {
        $filter = new Filter();
        $filter->add('ci.InscricaoId', '=', $inscricaoId);

        $lst = $this->Items($filter);
        if ($lst) {
            $inscricao = $lst[0];
        } else {
            $this->error = 'Inscri??o n?o encontrada.';
            return false;
        }

        //validacao
        if (!$inscricao->allowReenviarSolicitacaoPagamento()) {
            $this->error = 'Esta inscri??o n?o atende aos crit?rios de reenvio de solicita??o de pagamento';
            return false;
        }


        try {
            $sql = new SqlHelper();
            $sql->begin();

            $inscricao->calculateValor();

            //cart
            $cart = $this->createCart($inscricao);
            $this->urlPagSeguro = $cart->register();

            //erro ao registrar no pagSeguro
            if (!$this->urlPagSeguro) {
                $this->error = $cart->error;
                $sql->rollback();
                return false;
            }

            //save transaction code
            $sql->command = "UPDATE cursos_inscricoes set PagSeguroCode = '$cart->code' WHERE InscricaoId = $inscricao->id";
            $sql->execute();

            //commit das transacoes
            $sql->commit();
            return $inscricao;

        }
        catch (exception $e) {
            $this->error = $e->getMessage();
            $sql->rollback();
            return false;
        }
    }

    /**
     * Cria um cart com base em uma inscricao
     * */
    function createCart($inscricao)
    {
        //Criar cart e pagSeguro
        $cart = new Cart();

        //add cart items
        $index = 1;
        $qtde = count($inscricao->participantes);

        //criar descricao
        if ($qtde == 1) $txtItemDescription = $inscricao->curso->nome . " para $qtde participante";
        else $txtItemDescription = $inscricao->curso->nome . " para $qtde participantes";
        //limitar a 100 caracteres, devido ao pagSeguro
        if (strlen($txtItemDescription) > 100) $txtItemDescription = substr($txtItemDescription, 0, 99);
        //adicionar ao cart
        $cart->addItem($index, $txtItemDescription, $inscricao->valor, 1);

        $cart->setSender($inscricao->responsavel);
        $cart->setReference($inscricao);

        return $cart;
    }


    /**
     * E-mail ao cliente de confirmacao da inscricao. 
     * 
     * */
    function sendEmailConfirmacaoInscricao($inscricao)
    {
        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));

        $email = new Email();
        $email->to = $inscricao->responsavel->email;

        $email->subject = utf8_encode("Pr?-Confirma??o de inscri??o - " . $inscricao->curso->nome);
        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));

        if ($inscricao->valor > 0)
            $msgConfirmacao = "<p>Ap?s a confirma??o pagamento, voc? receber? um e-mail com as informa??es completas do evento.</p>";
        else 
            $msgConfirmacao = "<p>Em breve, voc? receber? um e-mail com as informa??es completas do evento.</p>";


        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?, $nome!</p>
									<br />
									<p>Esta ? a pr?-confirma??o de sua inscri??o no <strong>$cursoNome</strong> (Inscri??o #$inscricao->id).</p>
									
									$msgConfirmacao
					
									<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send())
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
        else
            $this->error .= 'Email enviado com sucesso';
    }

    /**
     * E-mail ao cliente de confirmacao do pagamento. 
     * 
     * */
    function sendEmailConfirmacaoPagamento($inscricaoId)
    {
        $filter = new Filter();
        $filter->add('ci.InscricaoId', '=', $inscricaoId);

        $lst = $this->Items($filter);
        if ($lst) {
            $inscricao = $lst[0];
        } else {
            return;
        }

        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));

        $email = new Email();
        $email->to = $inscricao->responsavel->email;
        $email->subject = utf8_encode("Confirma??o de pagamento - " . $inscricao->curso->nome);

        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));
        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?, $nome!</p>
									<br />
									<p>Esta ? a confirma??o do pagamento de sua inscri??o no <strong>$cursoNome</strong> (Inscri??o #$inscricao->id).</p>
									
									<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send())
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
        else
            $this->error .= 'Email enviado com sucesso';
    }


    /**
     * E-mail ao cliente de nova solicitacao do pagamento. 
     * 
     * */
    function sendEmailNovaSoliciticaoPagamento($inscricaoId)
    {
        $filter = new Filter();
        $filter->add('ci.InscricaoId', '=', $inscricaoId);

        $lst = $this->Items($filter);
        if ($lst) {
            $inscricao = $lst[0];
        } else {
            $this->error = 'Inscri??o n?o encontrada.';
            return false;
        }

        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));

        $email = new Email();
        $email->to = $inscricao->responsavel->email;
        $email->subject = utf8_encode("Solicita??o de pagamento - " . $inscricao->curso->nome);

        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));
        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?, $nome!</p>
									<br />
									<p>Verificamos que o pagamento de sua inscri??o no <strong>$cursoNome</strong> (Inscri??o #$inscricao->id) n?o foi confirmado.</p>
									
									<p>Voc? pode acessar este <a href='http://www.sobrare.com.br/cursos_inscricao.php?a=reiniciarPagamento&inscricaoId=$inscricao->id' title='Reinicie aqui o processo de pagamento'>link</a>
                                    para reiniciar o processo de pagamento, confirmando assim sua inscri??o.</p>
					
									<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send()) {
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
            return false;

        } else {
            return true;
        }
    }

    /**
     * Email ao admin de notificacao de nova inscricao. 
     * */
    function sendEmailNotificacaoNovaInscricao($inscricao)
    {
        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));
        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));

        $email = new Email();
        $email->to = $email->adminAddress;
        $email->subject = utf8_encode("Nova inscri??o - " . $inscricao->curso->nome);
        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?!</p>
									<br />
									<p>Esta ? uma notifica??o de nova inscri??o no <strong>$cursoNome</strong> (Inscri??o #$inscricao->id), feita por <strong>$nome</strong>.</p>
									
									<p>Acesse a <a href='http://www.sobrare.com.br/cockpit/cursos.php?cursoId=$inscricao->cursoid'>?rea de Cursos</a>
									para verificar detalhes do novo inscrito.</p>
										
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send())
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
        else
            $this->error .= 'Email enviado com sucesso';
    }

    /**
     * E-mail ao cliente dando instrucoes sobre upload de arquivo para congresso. 
     * 
     * */
    function sendEmailUploadCongresso($inscricao)
    {
        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));

        $email = new Email();
        $email->to = $inscricao->responsavel->email;
        $email->subject = utf8_encode("Instru??es para envio de material ao " . $inscricao->curso->nome);

        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));
        $codigo_verificacao = $inscricao->getCodigoVerificacao();
        
        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?, $nome!</p>
									<br />
									<p>Obrigado por participar do <strong>$cursoNome</strong>!</p>
									
									<p>Agora, voc? deve enviar o seu material para a SOBRARE. Acesse este
                                        <a href='http://www.sobrare.com.br/cursos_inscricao.php?a=sendfile&inscricaoId=$inscricao->id' title='Envie seu material'>link</a> 
                                    para fazer o upload do arquivo que ser? armazenado para a apresenta??o.</p>
                                    
                                    <p>Para enviar o arquivo, ser? necess?rio informar este <b>C?digo de Verifica??o</b>: $codigo_verificacao.</p>
					
                                    <p>&nbsp;</p>
									<p>Qualquer d?vida, entre em <a href='http://www.sobrare.com.br/contato.php'>contato</a>.</p>
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send()) {
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
            return false;

        } else {
            return true;
        }
    }


    /**
     * Email ao admin de notificacao de novo arquivo para o Congresso. 
     * */
    function sendEmailNotificacaoNovoArquivoCongresso($inscricao)
    {
        $nome = htmlentities(utf8_decode($inscricao->responsavel->nome));
        $cursoNome = htmlentities(utf8_decode($inscricao->curso->nome));

        $email = new Email();
        $email->to = $email->from;
        $email->subject = utf8_encode("Novo arquivo enviado - " . $inscricao->curso->nome);
        $email->message = utf8_encode("<html>
								<style>
									th {text-align:left; vertical-align:top;} 
									body, table {font:normal 10pt Verdana;}
								</style>
								<body>
									<p>Ol?!</p>
									<br />
									<p>Esta ? uma notifica??o de que um novo arquivo foi enviado
                                    para <strong>$cursoNome</strong> (Inscri??o #$inscricao->id), feita por <strong>$nome</strong>.</p>
									
									<p>Acesse o <a href='ftp://ftp.georgebarbosa.com.br/georgebarbosa/Web/sobrare/Uploads/Congresso/'>FTP</a>
									para verificar os arquivos j? enviados.</p>
										
									<br />
									<p><strong>SOBRARE</strong><br />Sociedade Brasileira de Resili?ncia</p>
								</body>
								</html>");
        if (!$email->send())
            $this->error .= 'Email n?o enviado. Verifique o endere?o de destino.';
        else
            $this->error .= 'Email enviado com sucesso';
    }


    function changeStatus($id, $statusId)
    {
        $sql = new SqlHelper();

        $sql->command = "UPDATE cursos_inscricoes SET StatusID = $statusId WHERE InscricaoId = $id";
        if ($sql->execute()) {
            return true;
        } else {
            $this->error = $sql->error;
            return false;
        }
    }

    /**
     * Envia as notificacoes de acordo com novo o status da inscricao.
     * ? chamada pelo Cart.checkTransactionStatus()
     * */
    function sendNotificationOnStatusChange($inscricaoId)
    {
        $filter = new Filter();
        $filter->add('ci.InscricaoId', '=', $inscricaoId);

        $lst = $this->Items($filter);
        if ($lst) {
            $inscricao = $lst[0];
        } else {
            $this->error = 'Inscri??o n?o encontrada.';
            return false;
        }

        switch ($inscricao->statusid) {
            case INSCRICAO_STATUS_CANCELADO:
                $this->sendEmailNovaSoliciticaoPagamento($inscricaoId);
                echo "sendEmailNovaSoliciticaoPagamento() ok";
                break;

            case INSCRICAO_STATUS_PAGO:
                $this->sendEmailConfirmacaoPagamento($inscricaoId);
                echo "sendEmailConfirmacaoPagamento() ok";
                
                //Espeficamente para o congreso, enviar notificacao de upload de arquivo
                //TODO: tornar isso parametrizado
                /*if (($inscricao->cursoid == 999) && ($inscricao->tipoid == INSCRICAO_TIPO_ESTUDANTE)) {
                    $this->sendEmailUploadCongresso($inscricao);
                    echo "sendEmailUploadCongresso() ok";
                }*/
                
                break;
        }

    }

    function getExportDataInscritos($filter)
    {
        if (!$filter)
            $filter = new Filter();

        $sql = new SqlHelper();
        $sql->command = "SELECT 
									i.*, 
									s.Status,
									t.InscricaoTipo,
									sexo.Sexo,
									ins.Nome as InscritoNome,
									ins.CPF as InscritoCPF,
									ins.Email as InscritoEmail,
									ins.Associadoid as InscritoAssociadoId,
									pag.CondicaoPagamento, 
									(SELECT COUNT(*) FROM cursos_inscritos WHERE InscricaoId = i.InscricaoId) AS QtdeInscritos
								FROM cursos_inscricoes i 
								INNER JOIN cursos_inscricoes_status s ON i.StatusId = s.StatusId
								INNER JOIN condicoespagamento pag on i.CondicaoPagamentoId = pag.CondicaoPagamentoId
								INNER JOIN cursos_inscricoes_tipos t ON t.InscricaoTipoId = i.InscricaoTipoId
								INNER JOIN sexos sexo ON sexo.SexoId = i.SexoId
								INNER JOIN cursos_inscritos ins ON i.InscricaoId = ins.InscricaoId
								$filter->expression
								ORDER BY i.InscricaoId, ins.Nome";

        if ($sql->execute()) {
            return $sql->dataset();
        } else {
            $this->error = $sql->error;
            return null;
        }
    }
}

?>
