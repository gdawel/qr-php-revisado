<?php
include_once dirname(__FILE__)."/../App_Code/CommonFunctions.php";

class Button {	

	static function Render($id, $caption = 'Text', $href = '#', $tooltip = '', $imgType = null, $enabled = true, $cssClass = 'regular', $onclick = null, $target = '_self') {
		$dir = applicationPath();
		
		echo "<a href='$href' title='$tooltip' class='$cssClass'".((!$enabled)?' disabled="disabled"':'').(($onclick)?" onclick=\"javascript:$onclick\"":'')." target='$target'>";
			if ($imgType) echo "<img src='../Images/icon-$imgType.png' alt='$tooltip' />";
			echo "<span>$caption</span>";		
		echo "</a>";			
	}
	
	/**
	 * Renderiza o botao de Submit do form.
	 * 
	 * @param mixed $id
	 * @param string $caption
	 * @param string $tooltip
	 * @param mixed $imgType
	 * @param string $cssClass
	 * @param mixed $frmId. ID do form a ser enviado. Influencia na forma como o bot�o � renderizado.
	 * @return void
	 */
	static function RenderSubmit($id, $caption = 'Text', $tooltip = '', $imgType = null, $cssClass = 'regular', $frmId = null) {
		
		if (is_null($frmId)) {	
			
			$dir = applicationPath();
			
			echo "<button type='submit' title='$tooltip' class='$cssClass'>";
			if (isset($imgType)) echo "<img src='../Images/icon-$imgType.png' alt='$tooltip' />";
				echo "<span>$caption</span>";		
				echo "</button>";
		} else {
			Button::Render($id, $caption, '#', $tooltip, $imgType, true, $cssClass, "document.getElementById('$frmId').submit(); return false;");
		}
	}
	
	static function RenderNav($caption = 'Text', $href='#', $tooltip = '', $imgType = null) {
		Button::Render(null, $caption, $href, $tooltip, $imgType, true, 'regular', null);
	}
}

?>