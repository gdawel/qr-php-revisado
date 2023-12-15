<?php
ob_start();
$pageTitle = 'SOBRARE Cockpit | Saldo de Créditos';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Creditos.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin,Gestor', 'login.php?returnurl=/cockpit/creditos_saldo.php');
include_once '../MasterPageCockpit.htm.php';

$msg = '';
$msg_style = 'Info';

function Router() {
	global $msg, $msg_style;
	$action = getPost('action', getQueryString('action'));	

	switch ($action) {
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg, $msg_style;
	
	$usr = Users::getCurrent();

	echo "<div class='Buttons NavButtons'>";
				if ($usr->isinrole('Admin')) Button::RenderNav('Créditos', 'creditos.php', 'Ir para Créditos');
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "	
			</div>";
				
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg, $msg_style);				
			
	echo "<h1>Meus Saldos</h1>";
			
	$creditos = new Creditos();
	$lst = $usr->isinrole('Admin') ? $creditos->getSaldo() : $creditos->getSaldo($usr->userid);
	
	if ($lst) {		
		$current_gestor = '';
		
		foreach ($lst as $credito) {
			if ($current_gestor != $credito->username) {
				//fecha tabela anterior
				if ($current_gestor != '')	echo "</table>";
				//atualiza cursor
				$current_gestor = $credito->username;
				
				echo "<h2>$credito->username</h2>";
				echo "<table class='List'>
							<tr>
								<th>Pacote</th>
								<th>Produtos</th>
								<th>Saldo</th>
							</tr>";
			}
			echo "<tr>";
			echo "	<td rowspan='".sizeof($credito->produtos)."' style=\"width:450px;\">$credito->pacote</td>";
							if ($credito->produtos) {
									foreach ($credito->produtos as $p) 
										echo "<td>$p->nome</td>
												<td style=\"width:50px;\" class='Center'>$p->porpacote</td></tr><tr>";
							} else {
								echo '<td>&nbsp;</td>
										<td>&nbsp;</td>';
							}
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "<p>Nenhum item encontrado.</p>";
	}
	
}
?>