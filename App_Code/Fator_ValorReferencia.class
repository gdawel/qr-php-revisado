<?php

class Fator_ValorReferencia {
    var $id;
	var $modeloquestionarioid, $modeloquestionario, $fatorid, $fator, $descricao;
	var $limitesuperior, $limiteinferior;
	var $classificacao, $classificacaodetalhada;
	var $devolutiva, $devolutivadetalhamento;
    var $estilo, $objetivoscapacitacao;
	
	function __construct() {}
	
	function faixa() {
		if ($this->limitesuperior) {
			return "Entre $this->limiteinferior e $this->limitesuperior";
		} else {
			return "Maior que $this->limiteinferior";
		}
			
	}
}

?>