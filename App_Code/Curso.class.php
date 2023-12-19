<?php
include_once 'SqlHelper.class.php';
include_once 'CommonFunctions.php';
include_once 'Filter.class.php';
include_once dirname(__FILE__)."/../Controls/events.ctrl.php";

define('CURSOS_TIPO_CURSO', 1);
define('CURSOS_TIPO_EVENTO', 2);
define('CURSOS_TIPO_FORMACAO', 3);

class Curso {
	var $id, $nome, $endereco, $local, $datainicio, $datatermino, $horario, $descricao, $inscricoesabertas,
        $tipoid, $tipo,
		$valor1, $datalimite1, $valor2, $datalimite2, /*Data Limite se refere ? inscricao*/ 
        $modulos, $inscricaotipoid_adicional, /*tipo de inscricao adicional, para estudantes, por exemplo*/
		$descontoassociado, $descontogrupo, $grupominimo, $descontomodulos, $modulosminimo,
        $historicoinfo, /*infos para exibicao do curso no historico*/
        $seokey;

	function __construct() {
       $this->historicoinfo = new stdClass();
	   $this->historicoinfo->imageurl = null;
       $this->historicoinfo->navigateurl = null;
       $this->historicoinfo->summary = null;
	}
	
	/*function fill($id, $nome, $endereco, $local, $datainicio, $datatermino, $horario, $descricao, $inscricoesabertas, $valor1=null, $datalimite1=null, $valor2=null,  $descontoassociado=null, $descontogrupo=null, $grupominimo=null) {
		$this->id = $id;
		$this->nome = $nome;
		$this->endereco = $endereco;
		$this->local = $local;
		$this->datainicio = $datainicio;
		$this->datatermino = $datatermino;
		$this->horario = $horario;
		$this->descricao = $descricao;
		$this->inscricoesabertas = $inscricoesabertas;
		$this->valor = $valor;
		$this->descontoassociado = $descontoassociado;
		$this->descontogrupo = $descontogrupo;
		$this->grupominimo = $grupominimo;
	}*/
	
	function uploadedFilename($folder = '../Uploads/') {
		return $folder."Curso_$this->id.jpg";
	}
	
	/**
	 * retorna o valor da inscricao do curso de acordo com a data de inscricao.
	 * 
	 * @return number
	 */
	function getValorInscricao() {
		$today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		if ($today <= strtotime($this->datalimite1)) return $this->valor1;
		if ($today <= strtotime($this->datalimite2)) return $this->valor2;
		return null;
	}
    
    /**
	 * retorna o valor da inscricao do curso e os modulos selecionados, de acordo com a data de inscricao.
	 * 
	 * @return number
	 */
    function getValor($selectedModulos) {
        $v = $this->getValorInscricao();
        
        if ($this->modulos) {
            foreach ($this->modulos as $m) {
                if (($selectedModulos) && (isset($selectedModulos[$m->id]))) {
                    $v += $m->getValor();
                }
            }
        }
        
        return $v;
    }
	    
	function getInscritosTotal() {
		$i = new Inscricoes();
		return $i->getInscritosCountByCursoId($this->id, false);
	}
    
    
    function getInscritosConfirmados() {
		$i = new Inscricoes();
		return $i->getInscritosCountByCursoId($this->id, true);
	}
}

define('CUPOM_TIPODESCONTO_PORCENTAGEM', 1);
define('CUPOM_TIPODESCONTO_VALOR', 2);

class Cupom {
    var $id, $cursoid, $codigo, $nome, $desconto, $tipodescontoid, $validade, $qtdemaximainscricoes, $acumulativo;
    
    function __construct() {}
    
    
    /**
     * Indica se o cupom ? valido para uso no momento atual.
     * 
     * @return true se cupom for valido. Caso contrario, false.
     */
    function isValido() {
        //validade
        $today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        if ($today > strtotime($this->validade)) return false;
        
        //qtde uso
        $cupons = new Cupons();
        $qtde = $cupons->getQtdeUsoCupom($this->id);
        if ($qtde >= $this->qtdemaximainscricoes) return false;
        
        return true;
    }
}

class Modulo {
	var $id, $nome, $cursoid, $curso, $datainicio, $datatermino, $horario, $descricao,
		$valor1, $datalimite1, $valor2, $datalimite2, /*Data Limite se refere ? inscricao*/ 
        $opcional; 
	
	function __construct() {}
    
    /**
	 * retorna o valor do modulo de acordo com a data de inscricao.
	 * 
	 * @return number
	 */
	function getValor() {
		$today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		if ($today <= strtotime($this->datalimite1)) return $this->valor1;
		if ($today <= strtotime($this->datalimite2)) return $this->valor2;
		return null;
	}
    
    function IsInscricoesEncerradas() {
        $today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        if (($this->datalimite2) && ($today >= strtotime($this->datalimite2))) return true;
		if ($today >= strtotime($this->datalimite1)) return true;
		return false;
    }
}


class Cursos {
	var $error;

    function ItemsByTipoId($tipoid, $pageindex = 1, $pagesize = 10, $orderby='data', &$totalrows, $filter = null) {
        if (!$filter) $filter = new Filter();
        $filter->add('c.tipoid', '=', $tipoid);
        
        return $this->Items($pageindex, $pagesize, $orderby, $totalrows, $filter);
    }

	function Items($pageindex = 1, $pagesize = 10, $orderby='data', &$totalrows, $filter=null) {
        $lst = null;
		$sql = new SqlHelper();
		
		//Validate params
		if (!is_int($pageindex)) $pageindex = 1;
		if (!is_int($pagesize)) $pagesize = 10;
		if ($pagesize > 50) $pagesize = 50;
		$start = $pagesize * ($pageindex-1);		
		if (!$filter) $filter = new Filter();
		
		//Set orderby
		switch ($orderby) {
            case 'past-events':
                $orderby = 'DataInicio DESC';
                break;
			default:
				$orderby = 'DataInicio';
		}
		
		//Rowcount
		$sql->command = "SELECT Count(*) AS Rows FROM cursos c
											$filter->expression";		

		if ($sql->execute()) {
			if ($r = $sql->fetch()) $totalrows = $r['Rows'];
			if ($start > $totalrows) {$start = 0; $pageindex = 1;}
		} else {
			return null;
		}
		
		//Select
		$sql->command = "SELECT c.*, t.Tipo FROM cursos c
                         INNER JOIN cursos_tipos t ON c.TipoId = t.TipoId
						 $filter->expression 
						 ORDER BY $orderby LIMIT $start, $pagesize";		
		if ($sql->execute()) {
			while ($r = $sql->fetch()) {
				$c = new Curso();
				$c->id = $r['CursoId'];
                $c->tipoid = $r['TipoId'];
                $c->tipo = $r['Tipo'];
                $c->seokey = $r['SEOKey'];
				$c->nome = $r['Nome'];
				$c->endereco = $r['Endereco'];
				$c->local = $r['Local'];
				$c->datainicio = $r['DataInicio'];
				$c->datatermino = $r['DataTermino'];
				$c->horario = $r['Horario'];
				$c->descricao = $r['Descricao'];
				$c->inscricoesabertas = $r['InscricoesAbertas'];
				$c->valor1 = $r['Valor1'];
				$c->datalimite1 = $r['DataLimite1'];
				$c->valor2 = $r['Valor2'];
				$c->datalimite2 = $r['DataLimite2'];
			 	$c->descontoassociado = $r['DescontoAssociado'] ? $r['DescontoAssociado'] : 0;
				$c->descontogrupo = $r['DescontoGrupo'] ? $r['DescontoGrupo'] : 0;
				$c->grupominimo = $r['GrupoMinimo'] ? $r['GrupoMinimo'] : 0;
                $c->inscricaotipoid_adicional = $r['InscricaoTipoIdAdicional'];
                
                $c->historicoinfo->imageurl = $r['HistoricoImageUrl'];
                $c->historicoinfo->navigateurl = $r['HistoricoNavigateUrl'];
                $c->historicoinfo->summary = $r['HistoricoSummary'];
                
				$lst[$c->id] = $c;
			}
            
            //carrega os modulos de cada curso
            $sql->command = "SELECT m.* FROM cursos_modulos m
                             WHERE m.CursoId IN (SELECT c.CursoId FROM cursos c $filter->expression)";
            if ($sql->execute()) {
                while ($r = $sql->fetch()) {
                    $m = new Modulo();
                    $m->id = $r['ModuloId'];
                    $m->cursoid = $r['CursoId'];
                    $m->nome = $r['Nome'];
                    $m->descricao = $r['Descricao'];
                    $m->datainicio = $r['DataInicio'];
    				$m->datatermino = $r['DataTermino'];
    				$m->horainicio = $r['HoraInicio'];
    				$m->valor1 = $r['Valor1'];
    				$m->datalimite1 = $r['DataLimite1'];
    				$m->valor2 = $r['Valor2'];
    				$m->datalimite2 = $r['DataLimite2'];
                    $m->opcional = $r['Opcional'];
                    
                    //associa o modulo ao curso
                    $c = $lst[$m->cursoid];
                    $c->modulos[$m->id] = $m;
                }
            }
            
			if (isset($lst)) return $lst; else return null;
		} else {
			return null;
		}
	}
	
	function nextevents($tipoid = null, $interval = null, $lag = 2, $maxRows = 30) {
        $totalrows = 0;
        
        if (!$interval) $interval = EventControls::defaultInterval();
	    $filter = new Filter();
		$filter->add('datediff(c.DataInicio, now())', 'BETWEEN ', $interval, "-$lag AND %d ");
		//filtrar por tipo, se solicitado
        if ($tipoid)
            $filter->add('c.tipoid', '=', $tipoid);
        
		return $this->Items(1, $maxRows, 'data', $totalrows, $filter);
	}

    function pastevents($tipoid = null, $interval = null, $lag = 2) {
        $totalrows = 0;
        
        if (!$interval) $interval = EventControls::defaultInterval();
	    $filter = new Filter();
		$filter->add('datediff(c.DataInicio, now())', 'BETWEEN ', $interval, "-%d AND -$lag ");
        $filter->add('NOT c.HistoricoNavigateUrl', 'IS ', '');
		//filtrar por tipo, se solicitado
        if ($tipoid)
            $filter->add('c.tipoid', '=', $tipoid);
        
		return $this->Items(1, 30, 'past-events', $totalrows, $filter);
	}
    
    function ItemBySEO($t) {
        $sql = new SqlHelper();
		
		$sql->command = "SELECT c.* FROM cursos c
	                     WHERE SEOKey = ".$sql->escape_string($t, true);
        
        if ($sql->execute()) {
            if ($r = $sql->fetch())
                return $this->Item($r['CursoId']);
            else
                return null;
        } else {
            return null;
        }
		
    }

	function Item($id) {
	   $rowscount = 0;
		$filter = new Filter();
        $filter->add('c.CursoId', '=', $id);
        
        $lst = $this->Items(1, 1, "data", $rowscount, $filter);
        if ($lst) {
            return $lst[$id];
        } else {
            return null;
        }
	}
	
	function Save(&$c) {
		$sql = new SqlHelper();
		
        $c->seokey = getSEO($c->nome);
        
		if ($c->id) {
			$sql->command = "UPDATE cursos SET nome=".$sql->escape_string($c->nome, true).',
												seokey='.$sql->escape_string($c->seokey, true).',
                                                tipoid='.$sql->escape_string($c->tipoid, true).',
                                                endereco='.$sql->escape_string($c->endereco, true).',
												local='.$sql->escape_string($c->local, true).',
												datainicio='.$sql->prepareDate($c->datainicio).',
												datatermino='.$sql->prepareDate($c->datatermino).',
												horario='.$sql->escape_string($c->horario, true).',
												descricao='.$sql->escape_string($c->descricao, true).',
												valor1='.$sql->prepareDecimal($c->valor1, true).',
												datalimite1='.$sql->prepareDate($c->datalimite1).',
												valor2='.$sql->prepareDecimal($c->valor2, true).',
												datalimite2='.$sql->prepareDate($c->datalimite2).',
												descontoassociado='.$sql->prepareDecimal($c->descontoassociado, true).',
												descontogrupo='.$sql->prepareDecimal($c->descontogrupo, true).',
												grupominimo='.$sql->escape_string($c->grupominimo, true).',
                                                historicoimageurl='.$sql->escape_string($c->historicoinfo->imageurl, true).',
                                                historiconavigateurl='.$sql->escape_string($c->historicoinfo->navigateurl, true).',
                                                historicosummary='.$sql->escape_string($c->historicoinfo->summary, true).',
												inscricoesabertas='.$sql->escape_string($c->inscricoesabertas, true)." 
												WHERE CursoId = $c->id";
		} else {
			$sql->command = "INSERT INTO cursos (nome, seokey, tipoid, endereco, local, datainicio, datatermino, horario, inscricoesabertas, 
															valor1, datalimite1, valor2, datalimite2, descontoassociado, descontogrupo, grupominimo, descricao) VALUES (".
												$sql->escape_string($c->nome, true).', '.
                                                $sql->escape_string($c->seokey, true).', '.
                                                $sql->escape_string($c->tipoid, true).', '.
												$sql->escape_string($c->endereco, true).','.
												$sql->escape_string($c->local, true).','.
												$sql->prepareDate($c->datainicio).','.
												$sql->prepareDate($c->datatermino).','.
												$sql->escape_string($c->horario, true).','.
												$sql->escape_string($c->inscricoesabertas, true).','.
												$sql->prepareDecimal($c->valor1, true).','.
												$sql->prepareDate($c->datalimite1).','.
												$sql->prepareDecimal($c->valor2, true).','.
												$sql->prepareDate($c->datalimite2).','.
												$sql->escape_string($c->descontoassociado, true).','.
												$sql->escape_string($c->descontogrupo, true).','.
												$sql->escape_string($c->grupominimo, true).','.
												$sql->escape_string($c->descricao, true).')';
		}
		
		if (!$ret = $sql->execute()) $this->error = $sql->error;
		else {
			if (!$c->id) $c->id = $sql->getInsertId();
		}
		return $ret;
	}
	
	function Delete($id) {
		$sql = new SqlHelper();
		
		$sql->command = "DELETE FROM cursos WHERE CursoId = $id";
		return $sql->execute();
	}
}

class Cupons {
    var $error;
    
    function ItemByCodigo($codigo) {
        $sql = new SqlHelper();
        
        $filter = new Filter();
        $filter->add('c.Codigo', '=', $codigo);
        
        //Select
		$sql->command = "SELECT c.* FROM cursos_cuponsdescontos c
						$filter->expression
                        LIMIT 0,1";
                        		
		if ($sql->execute()) {
			if ($r = $sql->fetch()) {
				$c = new Cupom();
                $c->id = $r['CupomId'];
				$c->cursoid = $r['CursoId'];
                $c->nome = $r['Nome'];
                $c->codigo = $r['Codigo'];
				$c->validade = $r['Validade'];
				$c->desconto = $r['Desconto'];
                $c->tipodescontoid = $r['TipoDescontoId'];
                $c->acumulativo = $r['Acumulativo'];
			 	$c->qtdemaximainscricoes = $r['QtdeMaximaInscricoes'];
                return $c;
			} else {
			     $this->error = 'Cupom n?o encontrado.';
                return null;
			}
        } else {
            $this->error = $sql->error;
            return null;
        }      
    }
    
    /**
     * Retorna quantas vezes um cupom j? foi utilizado em inscricoes ativas.
     * @var int id ID (e nao o codigo) do cupom.
     * */
    function getQtdeUsoCupom($id) {
        $sql = new SqlHelper();
        $filter = new Filter();
        $filter->add('c.CupomId', '=', $id);
        $filter->add('c.StatusId', 'IN', '(1,2,3,4,5)', "%s"); //somente inscricoes ativas
        
        //Select
		$sql->command = "SELECT COUNT(*) AS Qtde 
                        FROM cursos_inscricoes c
						$filter->expression";
                        		
		if ($sql->execute()) {
			if ($r = $sql->fetch()) {
                $qtde = $r['Qtde'];
            } else {
                $qtde = 0;
            }
            
            return $qtde;
		} else {
            $this->error = "Erro ao executar getQtdeUsoCupom()";
            return false;
		}
    }
}
?>