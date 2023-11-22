<?php
class Fator {
	var $id, $modeloquestionarioid, $nome, $sigla, $formacalculo, $result, $valor, $valordescricao, $classificacao, $classificacaodetalhada;
	var $valoresreferencia, $autoexcludentes;
	var $valorrefmin, $valorrefmax; //valores de ref da faixa do resultado atual
	var $descricao, $devolutiva, $devolutivadetalhamento;
	var $descricaoFracaResilienciaPCP, $descricaoFracaResilienciaPCI, $descricaoAnaliseQuantitativa;
	var $descricaoFortalezaVisaoGeral, $descricaoSegurancaPCP, $descricaoSegurancaPCI, $descricaoExcelente;
	var $qtde; //para relatorios GlobalMCD
	var $qtdepopulacaobase; //para relatorio Analitico Quantitativo
	
	function __construct() {}
	
	/*Retorno uma porcentagem do valor do resultado do fator em relacao ? faixa de referencia
	|      Forte     |   
	0%             +100%  */
	function valorrelativo() {
		$faixa = ($this->valorrefmax - $this->valorrefmin);
		
		$rel = (($this->valor - $this->valorrefmin) / $faixa);
		
		//limita valores de borda, a fim de melhorar a leitura do grafico
		if ($rel <= 0.09) $rel = 0.09;
		if ($rel > 0.92) $rel = 0.92;
		return $rel;
	}
}
?>