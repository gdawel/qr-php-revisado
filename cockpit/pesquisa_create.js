 function exibeProdutos(pacoteId) {
	$.ajax(
		{
		  type: "POST",
		  url: "../Controls/PacoteProdutosListItemPicker.ctrl.php",
		  data: "pacoteId=" + pacoteId,
		  beforeSend: function() {
 			// enquanto a função esta sendo processada, você
			// pode exibir na tela uma
			// msg de carregando
		  },
		  success: function(txt) {
 			// pego o id da div que envolve o select com
			// name="id_modelo" e a substituiu
			// com o texto enviado pelo php, que é um novo 
			//select com dados da marca x
			$('#divProdutos').html(txt);
		  },
		  error: function(txt) {		  	
		 	// em caso de erro você pode dar um alert('erro');
		 	alert('Erro!');
		  }
		}
	);
 }
 
 function exibeTipoDePesquisaFields($tipoId) {
 	if ($tipoId == '1') {
		$('#questsNormalLabel').show();
		$('#questsNormal').show();
		vld.enableValidation('qtde', true);
		
		$('#questsAglutinadoraLabel').hide();
		$('#questsAglutinadora').hide();
		vld.enableValidation('quests_ids', false);
	} else {
		$('#questsNormalLabel').hide();
		$('#questsNormal').hide();
		vld.enableValidation('qtde', false);
		
		$('#questsAglutinadoraLabel').show();
		$('#questsAglutinadora').show();
		vld.enableValidation('quests_ids', true);
	}
 } 
 
 
 $('document').ready(function(){
 	//onchange do Pacote
	$('#pacote').change(function(){
		exibeProdutos($('#pacote').val());
	});	
	exibeProdutos($('#pacote').val());
	
	//onchange do tipo da pesquisa
	$('#pesquisa_tipo').change(function(){
		exibeTipoDePesquisaFields($(this).val());
	})
	exibeTipoDePesquisaFields($('#pesquisa_tipo').val());
 });