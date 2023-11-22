<?php
include_once dirname(__FILE__)."/../App_Code/SqlHelper.class";
include_once dirname(__FILE__)."/../App_Code/CommonFunctions.php";
include_once dirname(__FILE__)."/../Controls/button.ctrl.php";

class PesquisaImage {
	var $error;
	
	public static function Render($pesquisaid) {
		$filename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
		
		echo "<table>
					<tr><td class='Center'>";
		if (file_exists($filename)) {
			echo "<img src='$filename' alt='Logo da Pesquisa' height='60px' />";
		} else {
			echo "<img src='../Uploads/Clientes/no_logo.jpg' alt='Logo da Pesquisa' />";
		}
		
		echo "</td></tr>
				<tr>
					<td class='Center'>
						<input type='hidden' name='MAX_FILE_SIZE' value='300000' />
						<input name='pesquisa_image_file' type='file' class='FileInput' />
				    	<div class='Buttons'>";
						  Button::Render(null, 'Alterar Logo', '#', 'Alterar logo desta pesquisa', 'photo', true, 'regular', "submitUpdatePesquisaForm('Image');");
		echo "
						</div>
					</td>
				</tr>
			</table>";
	}
	
	public static function Delete($pesquisaid) {
		$filename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
		if (file_exists($filename)) @unlink($filename);
	}
	
	public function Save($pesquisaid){		
		$filename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
		
		if (!empty($_FILES)) {
			$file_extension = pathinfo($_FILES["pesquisa_image_file"]["name"], PATHINFO_EXTENSION);
			
			if (strtoupper($file_extension) != 'JPG') {
				$this->error = 'O arquivo deve ser do formato JPG';
				return false;
			}
			
			$ret = move_uploaded_file($_FILES["pesquisa_image_file"]["tmp_name"], $filename);
			if ($ret) {
				//chdir('../Uploads/Clientes/'); 
				//chown("logo_cliente_$pesquisaid.jpg", 666);
				chmod("../Uploads/Clientes/logo_cliente_$pesquisaid.jpg", 0644);
			}		
			return $ret;
		} else {
			$this->error = 'Nenhum arquivo selecionado para upload';
			return false;
		}
	}
}

?>