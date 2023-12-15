<?php
class Pergunta {
	var $id, $texto, $posicao, $grupoalternativas, $alternativas, $modeloquestionarioid, 
			$resposta, $respostavalor, $questionarioid, $grupoperguntaid, $posicaogrupo;
	
	function __construct() {}
}

class GrupoPergunta {
    var $id, $texto, $posicao, $modeloquestionarioid, $perguntas;

    function __construct() {}
}

class Alternativa {
	var $id, $texto, $valor, $posicao;
	
	function __construct() {}
}
?>