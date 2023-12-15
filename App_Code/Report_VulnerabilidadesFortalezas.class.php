<?php
include_once 'Pesquisa.class.php';


/**
 * Classe que retorna os dados para os relat?rios das Condi?oes de Resili?ncia,
 * tando Fraca Resiliencia quanto Forte Resiliencia
 * 
 * @package SOBRARE
 * @author LTH Hermanos
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class ReportGlobalCondicaoResiliencia
{
    var $pesquisa;

    function __construct($pesquisa)
    {
        $this->pesquisa = $pesquisa;
        //recuperar somente questionarios concluidos para o relatorio
        $this->pesquisa->questionarios = $this->pesquisa->getQuestionariosByStatus(QUESTIONARIO_STATUS_CONCLUIDO);
    }
    
    function getFracaResilienciaItems($type = 'P') {
    	$type .= ' - Tipo 4'; //Somente MCD classificado com fraco
    	return $this->getDataItems($type);
    }
    
    function getForteResilienciaItems($type = 'P') {
    	$type .= ' - Tipo 1'; //Somente MCD classificado com forte
    	return $this->getDataItems($type);
    }
    
    function getBoaResilienciaItems($type = 'P') {
    	$type .= ' - Tipo 2'; //Somente MCD classificado com boa
    	return $this->getDataItems($type);
    }
    
    function getExcelenteResilienciaItems() {
    	$type = 'Excelente'; //Somente MCD classificado com excelente
    	return $this->getDataItems($type);
    }
    
    function getSegurancaItems($type = 'P') {
    	$forte = $this->getForteResilienciaItems($type);
    	$boa = $this->getBoaResilienciaItems($type);
        
    	//se nao houver quests concluidos...
    	if ((!$forte) && (!$boa)) return null;
		    	
    	//var para somatoria
    	$lst = ($forte) ? $forte : $boa; //somente para lista dos fatores do questionario
        $fatores = null; // para somatoria
        
    	foreach ($lst as $f) {
    		$qtde = 0;
    		if (isset($forte[$f->id])) {$qtde += $forte[$f->id]->qtde;}
    		if (isset($boa[$f->id])) {$qtde += $boa[$f->id]->qtde;}
            
            //consolida reulstado em outro objeto
            $fator = new Fator();
            $fator->id = $f->id;
            $fator->qtde = $qtde;
            $fatores[$fator->id] = $fator;
    	}
    	        
    	return $fatores;
    }
    
    private function getDataItems($type) {
        $quests = $this->pesquisa->questionarios; //neste caso, somente concluidos, de acordo com a __construct()
    	if (!$quests) return null;
    	
    	//obj base para sumarizacao
    	$fatores = null;
    	foreach ($quests[0]->fatores as $f) {
    	    $fator = new Fator();
            $fator->id = $f->id;
            $fator->nome = $f->nome;
            $fator->descricao = $f->descricao;
            $fator->descricaoAnaliseQuantitativa = $f->descricaoAnaliseQuantitativa;
            $fator->descricaoExcelente = $f->descricaoExcelente;
            $fator->descricaoFortalezaVisaoGeral = $f->descricaoFortalezaVisaoGeral;
            $fator->descricaoFracaResilienciaPCI = $f->descricaoFracaResilienciaPCI;
            $fator->descricaoFracaResilienciaPCP = $f->descricaoFracaResilienciaPCP;
            $fator->descricaoSegurancaPCI = $f->descricaoSegurancaPCI;
            $fator->descricaoSegurancaPCP = $f->descricaoSegurancaPCP;
            
            $fator->qtde = 0;
            $fatores[$fator->id] = $fator;
        }
    	
    	//Verifica se est? na condi??o pesquisada e incrementa contador para cada fator
    	foreach ($quests as $q) {
 			foreach ($q->fatores as $f) {
 				if ($f->valordescricao == $type) $fatores[$f->id]->qtde += 1;
 			}
    	}
    	    	
    	return $fatores;
    }
}

class ReportGlobalSituacoesVulnerabilidades {
	var $pesquisa;

    function __construct($pesquisa)
    {
        $this->pesquisa = $pesquisa;
        //selcionar somente os questionarios concluidos para o relatorio
        $this->pesquisa->questionarios = $this->pesquisa->getQuestionariosByStatus(QUESTIONARIO_STATUS_CONCLUIDO);
    }
    
    function getDataItems($type = 'P') {
		$type .= ' - Tipo 4'; //Somente MCD classificado com fraco
    	
    	//Select todos os questionarios concluidos
    	$quests = $this->pesquisa->questionarios; 
    	
    	if (!$quests) return null;
    	$lst = null;
		    	
    	//Verifica se est? na condi??o fraca e incrementa contador para cada fator
    	foreach ($quests as $q) {
    		if ($q->isVulneravel()) {
    			$lst[] = $q;
			}
    	}
    	
    	return $lst;
    }	
}
?>