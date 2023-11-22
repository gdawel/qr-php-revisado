<?php

class MessageBox {

	static function Render ($msg, $style = 'alert alert-info') {
		echo "<div id='MsgBox' class='$style'>
					$msg
				</div>";
	}
}

?>