<?php

$pageTitle = 'SOBRARE Cockpit Admin | Pasta de Arquivos';
include_once '../App_Code/User.class';
include_once '../App_Code/FileHandler.class';
include_once 'admin.index.php';
include_once '../Controls/msgbox.ctrl.php';
require_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {	
	global $msg;

	$action = getPost('action');
	switch ($action) {
		case 'upload':
			$fs = new FileHandler();
			if ($fs->uploadFiles()) $msg = 'Arquivo inserido com sucesso'; 
			else $msg = 'Erro ao inserir arquivo.';
			RenderDefault();
			break;
		
		case 'delete':
			$filename = getPost('id');
			$fs = new FileHandler();
			if ($fs->deleteFile($filename)) $msg = 'Arquivo excluído com sucesso';
			else $msg = "Erro ao excluir arquivo. $fs->error";
			RenderDefault();
			break;
			
		default:
			RenderDefault();
	}
	
}

function RenderDefault() {
	global $msg, $msg_class;
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg);
	
	$path = '../Uploads';
	$fs = new FileHandler();
	$lst = $fs->directory_list($path);
	
	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
			
	echo "<h1>Cockpit do Admin</h1>
			<h2>Pasta de Arquivos</h2>";
            
   

	echo "<form action='briefcase.php' method='POST' id='frm'>
				<input type='hidden' value='delete' name='action' id='action' />
				<input type='hidden' value='0' name='id' id='id' />
			</form>
			<script type='text/javascript'>
				function deleteFile(filename) {
					var ctrl_frm = document.getElementById('frm');
					var ctrl_id = document.getElementById('id');
					if (ctrl_frm) {
						ctrl_id.value = filename;
						ctrl_frm.submit();
					}	
					return false;
				}
			</script>
	
	
			<h3>Inserir Novo Arquivo</h3>
			
			<form enctype='multipart/form-data' method='POST' action='briefcase.php'>
				 <input type='hidden' name='action' value='upload' />
			    <input type='hidden' name='MAX_FILE_SIZE' value='300000' />
			    Selecione o arquivo: <input name='userfiles[]' type='file' />				    
				 <div class='Buttons'>";
					Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive'); echo "
				</div>		
			</form> 
			
			<hr class='clear' />";
			
    echo "
			<h3>Arquivos disponíveis</h3>";
			if ($lst) {
				echo "<table class='List'>
							<tr>
								<th></th>
								<th>Nome do arquivo</th>
							</tr>";	
				foreach ($lst as $filename) {
					$filename = htmlentities($filename);
			   	echo "<tr>
			   				<td><a href=\"javascript:if (confirm('Deseja realmente excluir este arquivo?')) deleteFile('$filename');\"><img src='../Images/icon-delete.png' alt='Clique para excluir este arquivo.' /></a></td>
								<td><a href='$path/$filename' alt='Clique para abrir este arquivo' target='_blank'>".$filename."</a></td>
							</tr>";
				}
				echo "</table>";
			} else {
				echo "<p>Nenhum arquivo encontrado</p>";
			}		
}

?>