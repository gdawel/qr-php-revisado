 function exibeProdutos(pacoteId) {
	$.ajax(
		{
		  type: "POST",
		  url: "../Controls/PacoteProdutosListItemPicker.ctrl.php",
		  data: "pacoteId=" + pacoteId,
		  beforeSend: function() {
 			// enquanto a função está sendo processada
		  },
		  success: function(txt) {
			$('#divProdutos').html(txt);
		  },
		  error: function(txt) {		  	
		 	alert('Erro!');
		  }
		}
	);
 }
 
 
 //page Load
 $('document').ready(function(){
	$('#pacote').change(function(){
		exibeProdutos($('#pacote').val());
	});
	exibeProdutos($('#pacote').val());
 });