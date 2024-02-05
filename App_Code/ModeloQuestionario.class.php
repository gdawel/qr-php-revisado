<?php
include_once('SqlHelper.class.php');
include_once('TipoModeloQuestionario.class.php');
include_once('Pergunta.class.php');
include_once('Fator.class.php');
include_once('Fator_ValorReferencia.class.php');
include_once('AutoExcludente.class.php');
include_once('AutoExcludente_Regra.class.php');

/*Reports*/
define('REPORT_INDIVIDUAL', 1);
define('REPORT_CONDICOES_FRACA_RESILIENCA', 2);
define('REPORT_SITUACOES_VULNERABILIDADE', 3);
define('REPORT_ANALISE_QUANTITATIVA', 4);
define('REPORT_CONDICOES_FORTALEZA', 5);
define('REPORT_MAPEAMENTO', 6);

class ModeloQuestionario {
	var $id, $nome, $enabled;
	var $tipoid, $tipo;
	var $perguntas;
    var $gruposperguntas;
	var $fatores;
	var $reportsections;
    var $isagrupado;
	
	function __construct() {
	}
	
	function getReportSections($reportId) {
		$sql = new SqlHelper();
		
		//Report Sections
		$sql->command = "SELECT r.* FROM modelosquestionarios_reportsections r
						 WHERE r.ReportId = $reportId AND r.ModeloQuestionarioId = $this->id
						 ORDER BY r.Posicao";
		$sql->execute();
		
        $lst = null;
		while ($r = $sql->fetch()) {
			$lst[$r['Posicao']] = new ReportSection($r['SectionId'], $r['ReportId'], $r['ModeloQuestionarioId'], $r['Title'], $r['Texto'], $r['Posicao'], $r['AddPageBreakBefore'], $r['FatorId']);
		}
		
		return $lst;
	}
	
	/**
	 * Retorna a quantidade das classificacoes dos fatores dos questionarios respondidos de toda a populacao-base do
	 * ModeloQuestionario. Utilizado principalmente no grafico de linhas do relatorio Analitico Quantitativo.
	 * 
	 * @return void
	 */
	function getCountPopulacaoBase() {
		$sql = new SqlHelper();
		$sql->command = "SELECT COUNT(*) as `Qtde`, qry.FatorId, qry.Fator, qry.ValorDescricao
								FROM (
								                      Select mdf.FatorId, f.Nome as `Fator`, f.Sigla,
																							(SELECT vr.Classificacao FROM modelosquestionarios_valoresreferencia vr
																							 WHERE vr.ModeloQuestionarioId = 2 AND vr.FatorId = mdf.FatorId
																										 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `Classificacao`,
																						 	(SELECT vr.Descricao FROM modelosquestionarios_valoresreferencia vr
																							 WHERE vr.ModeloQuestionarioId = 2 AND vr.FatorId = mdf.FatorId
																										 AND vr.LimiteSuperior >= qf.Valor AND vr.LimiteInferior < qf.Valor) AS `ValorDescricao`
																			FROM modelosquestionarios_fatores mdf
																			left join fatores f on f.FatorId = mdf.FatorId
																			left join questionarios_fatores qf on (mdf.FatorId = qf.FatorId)
								                      left join questionarios quest on quest.questionarioid = qf.questionarioid
																			where mdf.ModeloQuestionarioId = $this->id
								                            and quest.StatusId = 3
								      ) AS `qry`
        						group by FatorId, Fator, ValorDescricao
								order by Fator";
		$sql->execute();
		
		while ($r = $sql->fetch()) {			
			$this->fatores[$r['FatorId']]->qtdepopulacaobase[$r['ValorDescricao']] = $r['Qtde'];
		}
		
		return $this->fatores;						
	}
    
    function getValorReferenciaByDescricao($fatorId, $descricao) {
        if (!$this->fatores) return null;
        if (!$this->fatores[$fatorId]->valoresreferencia) return null;
        
        foreach ($this->fatores[$fatorId]->valoresreferencia as $vr) {
            if ($vr->descricao == $descricao) return $vr;   
        }        
        
        //if not found
        return null;
    }
}

class Report {
    var $id, $nome, $descricao, $path, $sections;
    
    function __construct() {}
}


class ReportSection {
	var $id, $reportid, $modeloquestionarioid, $modeloquestionario, $title, $texto, $posicao, $addpagebreakbefore, $fatorid;
	
	function __construct($id, $reportid, $modeloquestionarioid, $title, $texto, $posicao, $addpagebreakbefore, $fatorid) {
		$this->id = $id;
        $this->reportid = $reportid;
		$this->modeloquestionarioid = $modeloquestionarioid;
		$this->title = $title;
		$this->texto = $texto;
		$this->posicao = $posicao;
        $this->addpagebreakbefore = $addpagebreakbefore;
        $this->fatorid = $fatorid;
	}
}


class Reports {
    var $error; 
    
    function item($id) {
        $filter = new Filter();
        
        $filter->add("r.ReportId", '=', $id);
        $lst = $this->items($filter);
        if ($lst) {
            return $lst[$id];
        } else {
            return false;
        }
    }
    
    function items($filter) {
        if (!$filter) $filter = new Filter();
        
        $sql = new SqlHelper();
        $sql->command = "SELECT r.* 
                        FROM reports r
                        $filter->expression";
        
        $lst = null;
        
        if ($sql->execute()) {            
    		while ($r = $sql->fetch()) {
   		       $rep = new Report();
               $rep->id = $r['ReportId'];
               $rep->nome = $r['Nome'];
               $rep->descricao = $r['Descricao'];
               $rep->path = $r['Path'];
               
               $lst[$rep->id] = $rep;
    		}
            //get sections
            if ($lst) {
                $sql->command = "SELECT rs.*, md.Nome AS `ModeloQuestionario` 
                                FROM modelosquestionarios_reportsections rs
                                JOIN reports r ON rs.ReportId = r.ReportId
                                JOIN modelosquestionarios md on rs.ModeloQuestionarioId = md.ModeloQuestionarioId
                                $filter->expression
                                ORDER BY rs.ReportId, md.Nome, rs.Posicao";
                if ($sql->execute()) {
                    while ($r = $sql->fetch()) {
                        $section = new ReportSection($r['SectionId'], $r['ReportId'], $r['ModeloQuestionarioId'],
                                                      $r['Title'], $r['Texto'], $r['Posicao'], $r['AddPageBreakBefore'], $r['FatorId']);
                        $section->modeloquestionario = $r['ModeloQuestionario'];
                        
                        $lst[$r['ReportId']]->sections[$r['SectionId']] = $section;
                    }
                } else {
                    $this->error = $sql->error;
                    return false;
                }
            }
        } else {
            $this->error = $sql->error;
            return false;
        }
        
        if ($lst) return $lst; else return null;
    }//items()
    
    
    function saveSection($section) {
        $sql = new SqlHelper();
        if ($section->id) {
            $sql->command = "UPDATE modelosquestionarios_reportsections SET 
                                Title = ".$sql->escape_string($section->title, true).", 
                                Texto = ".$sql->escape_string($section->texto, true).",
                                Posicao = ".$sql->escape_id($section->posicao).",
                                AddPageBreakBefore = ".$sql->escape_string($section->addpagebreakbefore)."
                            WHERE SectionId = $section->id";
        } else {
            $sql->command = "INSERT INTO modelosquestionarios_reportsections (ReportId, ModeloQuestionarioId, Title, Texto, Posicao, AddPageBreakBefore)
                            VALUES (
                                $section->reportid,
                                $section->modeloquestionarioid,".
                                $sql->escape_string($section->title, true).",".
                                $sql->escape_string($section->texto, true).",
                                $section->posicao,
                                $section->addpagebreakbefore)";
        }
        
        if ($sql->execute()) {
            return true;
        } else {
            $this->error = $sql->error;
            return false;
        }
    }//savesection()
    
    
    function deletesection($section) {
        $sql = new SqlHelper();
        $sql->command = "DELETE FROM modelosquestionarios_reportsections WHERE SectionId = $section->id";
        
        echo $sql->command;
        
        if ($sql->execute()) {
            return true;
        } else {
            $this->error = $sql->error;
            return false;
        }
    }//deletesection()
}


class ModelosQuestionarios {
    function items($filter) {
        if (!$filter) $filter = new Filter();
        
        $sql = new SqlHelper();
        $sql->command = "SELECT mq.*, mqt.Nome as `TipoNome` 
                        FROM modelosquestionarios mq
                        LEFT JOIN pacotes_tipos mqt ON mq.TipoId = mqt.PacoteTipoId
                        $filter->expression";
        
        if ($sql->execute()) {            
    		while ($r = $sql->fetch()) {
   		       $m = new ModeloQuestionario();
               $m->id = $r['ModeloQuestionarioId'];
               $m->nome = $r['Nome'];
               $m->tipo = new TipoModeloQuestionario();
   			   $m->tipo->id = $r['TipoId'];
    		   $m->tipo->nome = $r['TipoNome'];
               $m->enabled = $r['Enabled'];
               
                $lst[$m->id] = $m;
    		}
        }
        
        if ($lst) return $lst; else return null;
    }
    
	function item($id) {
		$sql = new SqlHelper();
				
		$sql->command = "SELECT mq.*, mqt.Nome as `TipoNome` 
											FROM modelosquestionarios mq 
											INNER JOIN pacotes_tipos mqt ON mq.TipoId = mqt.PacoteTipoId
											WHERE mq.ModeloQuestionarioId = $id";
		$sql->execute();
		
		if ($r = $sql->fetch()) {			
			$item = new ModeloQuestionario();
			$item->id = $r['ModeloQuestionarioId'];
			$item->nome = $r['Nome'];
			$item->enabled = $r['Enabled'];
			
			$item->tipo = new TipoModeloQuestionario();
			$item->tipo->id = $r['TipoId'];
			$item->tipo->nome = $r['TipoNome'];

			//Report Sections
			$sql->command = "SELECT r.* FROM modelosquestionarios_reportsections r
											 WHERE r.ReportId = 1 AND r.ModeloQuestionarioId = $id
											 ORDER BY r.Posicao";
			$sql->execute();
			
			while ($r = $sql->fetch()) {
				$item->reportsections[$r['Posicao']] = new ReportSection($r['SectionId'], $r['ReportId'], $r['ModeloQuestionarioId'], $r['Title'],
																		 $r['Texto'], $r['Posicao'], $r['AddPageBreakBefore'], $r['FatorId']);
			}

			//Perguntas
			$sql->command = "SELECT p.PerguntaId, p.Texto, p.Posicao, p.PosicaoGrupo, p.GrupoPerguntaId,
												(SELECT group_concat(' ',a.texto, ' (', a.valor, ')') FROM modelosquestionarios_gruposalternativas m 
												 INNER JOIN modelosquestionarios_alternativas a ON m.Alternativaid=a.AlternativaId
												 WHERE m.GrupoAlternativasId = p.GrupoAlternativasId
												 ORDER BY m.Posicao) AS `Alternativas`
												FROM modelosquestionarios_perguntas p
												WHERE p.ModeloQuestionarioId = $id
												ORDER BY p.Posicao";
			$sql->execute();
			
			while ($r = $sql->fetch()) {
				$p = new Pergunta();
				
				$p->id = $r['PerguntaId'];
				$p->texto = $r['Texto'];
				$p->posicao = $r['Posicao'];
                $p->posicaogrupo = $r['PosicaoGrupo'];
                $p->grupoperguntaid = $r['GrupoPerguntaId'];
				$p->alternativas = $r['Alternativas'];
				//TODO: finalizar fill
				
				$item->perguntas[$p->id] = $p;
			}

            //Grupos Perguntas
            $sql->command = "SELECT g.GrupoPerguntaId, g.ModeloQuestionarioId, g.Texto, g.Posicao
                                FROM modelosquestionarios_gruposperguntas g
                                WHERE g.ModeloQuestionarioId = $id
                                ORDER BY g.Posicao";
            $sql->execute();

            while ($r = $sql->fetch()) {
                $g = new GrupoPergunta();

                $g->id = $r['GrupoPerguntaId'];
                $g->texto = $r['Texto'];
                $g->posicao = $r['Posicao'];
                $g->modeloquestionarioid = $r['ModeloQuestionarioId'];

                $item->gruposperguntas[$g->id] = $g;
            }

            //Fatores
			$item->fatores = $this->getFatores($item->id);
			
			
			//Valores Ref & Auto-excludente
			if ($item->fatores) {
                foreach ($item->fatores as $f) {
    			
    				//Valor Ref
    				$sql->command = "SELECT * FROM modelosquestionarios_valoresreferencia vr
    													WHERE vr.ModeloQuestionarioId = $id AND FatorId = $f->id
    													ORDER BY vr.LimiteSuperior DESC";
    				$sql->execute();
    				
    				while ($r = $sql->fetch()) {
    					$vr = New Fator_ValorReferencia();
    					
    					$vr->id = $r['ValorReferenciaId'];
                        $vr->descricao = $r['Descricao'];
    					$vr->limitesuperior = $r['LimiteSuperior'] == 999.99 ? null : $r['LimiteSuperior'];
    					$vr->limiteinferior = $r['LimiteInferior'];
    					$vr->classificacao = $r['Classificacao'];
    					$vr->classificacaodetalhada = $r['ClassificacaoDetalhada'];
    					$vr->devolutiva = $r['Devolutiva'];
                        $vr->devolutivadetalhamento = $r['DevolutivaDetalhamento'];
    					$vr->estilo = $r['Estilo'];
                        $vr->objetivoscapacitacao = $r['ObjetivosCapacitacao'];
                        
    					$f->valoresreferencia[$vr->id] = $vr;
    				}
    				
    				//Auto-excludente
    				$sql->command = "SELECT m.AutoExcludenteId, m.FatorId, m.ValorCorrecao
    													FROM modelosquestionarios_autoexcludentes m
    													WHERE m.ModeloQuestionarioId = $id AND m.FatorId = $f->id";
    				$sql->execute();
    				
    				while ($r = $sql->fetch()) {
    					$a = new AutoExcludente();									
    				
    					$a->id = $r['AutoExcludenteId'];
    					$a->fator = $f;
    					$a->valorcorrecao = $r['ValorCorrecao'];
    					
    					$f->autoexcludentes[] = $a;
    				}
    				
    				//Regras Auto-Excludente
    				if ($f->autoexcludentes) {
    					foreach ($f->autoexcludentes as $a) {
    						$sql->command = "SELECT * FROM modelosquestionarios_autoexcludentes_regras
    															WHERE AutoExcludenteId = $a->id";
    						$sql->execute();
    					
    						while ($r = $sql->fetch()) {
    							$re = new AutoExcludente_Regra();
    							
    							$re->id = $r['RegraId'];
    							$re->operador = $r['Operador'];
    							$re->valorreferencia = $r['ValorReferencia'];
    							$re->pergunta = $item->perguntas[$r['PerguntaId']];
    								
    							$a->regras[] = $re;
    						}
    					}
    				}
    			}			
            }
		}
		
		if (isset($item)) {return $item;} else {return null;}
	}
	
    function getValorReferencia($id) {
        $sql = new SqlHelper();
		$sql->command = "SELECT vr.*, m.Nome AS `ModeloQuestionario`, f.Nome as `Fator`
                         FROM modelosquestionarios_valoresreferencia vr
                         INNER JOIN modelosquestionarios m ON vr.ModeloQuestionarioId = m.ModeloQuestionarioId
                         INNER JOIN fatores f ON f.FatorId = vr.FatorId
						 WHERE vr.ValorReferenciaId = $id";
		
        if ($sql->execute()) {		
    		if ($r = $sql->fetch()) {
    			$vr = New Fator_ValorReferencia();
    			
    			$vr->id = $r['ValorReferenciaId'];
                $vr->modeloquestionarioid = $r['ModeloQuestionarioId'];
                $vr->modeloquestionario = $r['ModeloQuestionario'];
                $vr->fatorid = $r['FatorId'];
                $vr->fator = $r['Fator'];
                $vr->descricao = $r['Descricao'];
    			$vr->limitesuperior = $r['LimiteSuperior'] == 999.99 ? null : $r['LimiteSuperior'];
    			$vr->limiteinferior = $r['LimiteInferior'];
    			$vr->classificacao = $r['Classificacao'];
    			$vr->classificacaodetalhada = $r['ClassificacaoDetalhada'];
    			$vr->devolutiva = $r['Devolutiva'];
                $vr->devolutivadetalhamento = $r['DevolutivaDetalhamento'];
                $vr->estilo = $r['Estilo'];
                $vr->objetivoscapacitacao = $r['ObjetivosCapacitacao'];
                
                return $vr;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    function SaveFator(&$f) {
		$sql = new SqlHelper();
		
		if ($f->id) {
			$sql->command = "UPDATE modelosquestionarios_fatores SET 
                                descricao=".$sql->escape_string($f->descricao, true).',
								descricaoFracaResilienciaPCI='.$sql->escape_string($f->descricaoFracaResilienciaPCI, true).',
								descricaoFracaResilienciaPCP='.$sql->escape_string($f->descricaoFracaResilienciaPCP, true).',
								descricaoAnaliseQuantitativa='.$sql->escape_string($f->descricaoAnaliseQuantitativa, true).',
								descricaoFortalezaVisaoGeral='.$sql->escape_string($f->descricaoFortalezaVisaoGeral, true).',
								descricaoSegurancaPCP='.$sql->escape_string($f->descricaoSegurancaPCP, true).',
                                descricaoSegurancaPCI='.$sql->escape_string($f->descricaoSegurancaPCI, true).',
                                descricaoExcelente='.$sql->escape_string($f->descricaoExcelente, true)."
			                 WHERE FatorId = $f->id AND ModeloQuestionarioId = $f->modeloquestionarioid";
		} else {
			$sql->command = "INSERT INTO modelosquestionarios_fatores (descricao, descricaoFracaResilienciaPCI, descricaoFracaResilienciaPCP, descricaoAnaliseQuantitativa,
                                                                        descricaoFortalezaVisaoGeral, descricaoSegurancaPCP, descricaoSegurancaPCI, descricaoExcelente) VALUES (".
												$sql->escape_string($f->descricao, true).', '.
												$sql->escape_string($f->descricaoFracaResilienciaPCI, true).','.
												$sql->escape_string($f->descricaoFracaResilienciaPCP, true).','.
												$sql->escape_string($f->descricaoAnaliseQuantitativa, true).','.
												$sql->escape_string($f->descricaoFortalezaVisaoGeral, true).','.
                                                $sql->escape_string($f->descricaoSegurancaPCP, true).','.
                                                $sql->escape_string($f->descricaoSegurancaPCI, true).','.
                                                $sql->escape_string($f->descricaoExcelente, true).                                         
                                                ')';
		}
        
		if (!$ret = $sql->execute()) {
		  	$this->error = $sql->error; 
		} else {
			if (!$f->id) $f->id = $sql->getInsertId();
		}
		return $ret;
	}
    
    
    function SaveValorReferencia(&$vr) {
		$sql = new SqlHelper();
		
		if ($vr->id) {
			$sql->command = "UPDATE modelosquestionarios_valoresreferencia SET 
                                descricao=".$sql->escape_string($vr->descricao, true).',
								classificacao='.$sql->escape_string($vr->classificacao, true).',
								classificacaodetalhada='.$sql->escape_string($vr->classificacaodetalhada, true).',
								devolutiva='.$sql->escape_string($vr->devolutiva, true).',
								devolutivadetalhamento='.$sql->escape_string($vr->devolutivadetalhamento, true).',
                                estilo='.$sql->escape_string($vr->estilo, true).',
                                objetivoscapacitacao='.$sql->escape_string($vr->objetivoscapacitacao, true).',
								limiteinferior='.$sql->prepareDecimal($vr->limiteinferior, true).',
								limitesuperior='.$sql->prepareDecimal($vr->limitesuperior, true)."
			                 WHERE ValorReferenciaId = $vr->id";
		} else {
			$sql->command = "INSERT INTO cursos (descricao, classificacao, classificacaodetalhada, devolutiva, devolutivadetalhamento, estilo, objetivoscapacitacao, limiteinferior, limitesuperior) VALUES (".
												$sql->escape_string($vr->descricao, true).', '.
												$sql->escape_string($vr->classificacao, true).','.
												$sql->escape_string($vr->classificacaodetalhada, true).','.
												$sql->escape_string($vr->devolutiva, true).','.
												$sql->escape_string($vr->devolutivadetalhamento, true).','.
                                                $sql->escape_string($vr->estilo, true).','.
                                                $sql->escape_string($vr->objetivoscapacitacao, true).','.
												$sql->prepareDecimal($vr->limiteinferior, true).','.
												$sql->prepareDecimal($vr->limitesuperior, true).')';
		}
        
		if (!$ret = $sql->execute()) {
		  $this->error = $sql->error;
		} else {
			if (!$vr->id) $vr->id = $sql->getInsertId();
		}
		return $ret;
	}

    function SavePergunta(&$p) {
		$sql = new SqlHelper();
		
		if ($p->id) {
			$sql->command = "UPDATE modelosquestionarios_perguntas SET 
                                texto = ".$sql->escape_string($p->texto, true).",
                                grupoperguntaid = ".$sql->escape_id($p->grupoperguntaid).",
                                posicaogrupo = ".$sql->escape_id($p->posicaogrupo)."
			                 WHERE PerguntaId = $p->id";
		} else {
			/*$sql->command = "INSERT INTO cursos (descricao, classificacao, classificacaodetalhada, devolutiva, devolutivadetalhamento, limiteinferior, limitesuperior) VALUES (".
												$sql->escape_string($vr->descricao, true).', '.
												$sql->escape_string($vr->classificacao, true).','.
												$sql->escape_string($vr->classificacaodetalhada, true).','.
												$sql->escape_string($vr->devolutiva, true).','.
												$sql->escape_string($vr->devolutivadetalhamento, true).','.
												$sql->prepareDecimal($vr->limiteinferior, true).','.
												$sql->prepareDecimal($vr->limitesuperior, true).')';
		  */
          $this->error = "N?o ? poss?vel incluir nova pergunta.";
          return false;
        }
        
		if (!$ret = $sql->execute()) {
		  $this->error = $sql->error;
		} else {
			if (!$p->id) $p->id = $sql->getInsertId();
		}
		return $ret;
	}


    function SaveGrupoPergunta(&$g) {
        $sql = new SqlHelper();

        if ($g->id) {
            $sql->command = "UPDATE modelosquestionarios_gruposperguntas SET
                                texto = ".$sql->escape_string($g->texto, true).",
                                posicao = ".$sql->escape_id($g->posicao)."
			                 WHERE GrupoPerguntaId = $g->id";
        } else {
            /*$sql->command = "INSERT INTO cursos (descricao, classificacao, classificacaodetalhada, devolutiva, devolutivadetalhamento, limiteinferior, limitesuperior) VALUES (".
                                                $sql->escape_string($vr->descricao, true).', '.
                                                $sql->escape_string($vr->classificacao, true).','.
                                                $sql->escape_string($vr->classificacaodetalhada, true).','.
                                                $sql->escape_string($vr->devolutiva, true).','.
                                                $sql->escape_string($vr->devolutivadetalhamento, true).','.
                                                $sql->prepareDecimal($vr->limiteinferior, true).','.
                                                $sql->prepareDecimal($vr->limitesuperior, true).')';
          */
            $this->error = "N?o ? poss?vel incluir novo grupo de pergunta.";
            return false;
        }

        if (!$ret = $sql->execute()) {
            $this->error = $sql->error;
        } else {
            if (!$g->id) $g->id = $sql->getInsertId();
        }
        return $ret;
    }
	
	function DeleteValorReferencia($id) {
		$sql = new SqlHelper();
		
		$sql->command = "DELETE FROM modelosquestionarios_valoresreferencia WHERE ValorReferenciaId = $id";
		return $sql->execute();
	}
    
	function getFatores($ModeloQuestionarioId) {
		$sql = new SqlHelper();
		
		$sql->command = "SELECT mqf.FatorId, f.Nome, f.Sigla , mqf.FormaCalculo, mqf.Descricao, mqf.DescricaoFracaResilienciaPCP, mqf.DescricaoFracaResilienciaPCI,
										mqf.DescricaoAnaliseQuantitativa, mqf.DescricaoFortalezaVisaoGeral, mqf.DescricaoSegurancaPCP, mqf.DescricaoSegurancaPCI,
										mqf.DescricaoExcelente
												FROM modelosquestionarios_fatores mqf 
												INNER JOIN fatores f ON mqf.FatorId = f.FatorId
											 WHERE mqf.ModeloQuestionarioId = $ModeloQuestionarioId";

		if ($sql->execute()) {		
			while ($r = $sql->fetch()) {
				$f = new Fator();
				
				$f->id = $r['FatorId'];
				$f->nome = $r['Nome'];
				$f->sigla = $r['Sigla'];
                $f->descricao = $r['Descricao'];
				$f->descricaoFracaResilienciaPCP = $r['DescricaoFracaResilienciaPCP'];
				$f->descricaoFracaResilienciaPCI = $r['DescricaoFracaResilienciaPCI'];
				$f->descricaoAnaliseQuantitativa = $r['DescricaoAnaliseQuantitativa'];
				$f->descricaoFortalezaVisaoGeral = $r['DescricaoFortalezaVisaoGeral'];
				$f->descricaoSegurancaPCP = $r['DescricaoSegurancaPCP'];
				$f->descricaoSegurancaPCI = $r['DescricaoSegurancaPCI'];
				$f->descricaoExcelente = $r['DescricaoExcelente'];
				$f->formacalculo = $r['FormaCalculo'];
				
				$lst[$f->id] = $f;
			}
			
            
			//Auto-excludentes
            if (isset($lst)) {
    			foreach ($lst as $f) {
    				$sql->command = "SELECT a.AutoExcludenteId, a.FatorId, a.ValorCorrecao 
    													FROM modelosquestionarios_autoexcludentes a
    													WHERE a.FatorId = $f->id AND a.ModeloQuestionarioId = $ModeloQuestionarioId";
    				
    				if ($sql->execute()) {
    					while ($r = $sql->fetch()) {
    						$ae = new AutoExcludente();
    						$ae->id = $r['AutoExcludenteId'];
    						$ae->fatorid = $r['FatorId'];
    						$ae->valorcorrecao = $r['ValorCorrecao'];
    						$f->autoexcludentes[$ae->id] = $ae;
    					}
    				}
    			}
			}
            
			//Regras das auto-excludentes
			if (isset($lst)) {
                foreach ($lst as $f) {
    				if ($f->autoexcludentes) {
    					foreach ($f->autoexcludentes as $ae) {
    						$sql->command = "SELECT m.RegraId, m.AutoExcludenteId, p.Posicao, m.Operador, m.ValorReferencia
    															FROM modelosquestionarios_autoexcludentes_regras m
    															inner join modelosquestionarios_perguntas p on m.PerguntaId = p.PerguntaId
    														 WHERE m.AutoExcludenteId = $ae->id";
    						if ($sql->execute()) {
    							while ($r = $sql->fetch()) {	
    								$regra = new AutoExcludente_Regra();
    								$regra->id = $r['RegraId'];
    								$regra->perguntaposicao = $r['Posicao'];
    								$regra->operador = $r['Operador'];
    								$regra->valorreferencia = $r['ValorReferencia'];
    								
    								$ae->regras[$regra->id] = $regra;
    							}
    						}
    					}
    				}
    			}
			}
            
			if (isset($lst)) {return $lst;} else {return null;}
			
		} else {
			//TODO: error
			return null;
		}
	}
	function getFatoresByQuestionarioId($QuestionarioId) {
		$sql = new SqlHelper();
		
		//Get ModeloId
		$sql->command = "SELECT pac.ModeloQuestionarioId 
                         FROM pesquisas p
                         INNER JOIN pacotes pac ON p.PacoteId = pac.PacoteId 
											WHERE p.PesquisaId = (SELECT q.PesquisaId FROM questionarios q WHERE q.QuestionarioId = $QuestionarioId)";
		$sql->execute();
		
		if ($r = $sql->fetch()) {
			$id = $r['ModeloQuestionarioId'];
		} else {
			return null;
		}
		$sql = null;
		
		return $this->getFatores($id);
	}
}

?>