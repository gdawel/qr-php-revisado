 //page Load
 $(document).ready(function(){

 });

function addModulo() {
	$('#templateModulo')
		.clone()
		.addClass('Modulo')
		.appendTo($('#tbModulos'));
	
	return false;
}