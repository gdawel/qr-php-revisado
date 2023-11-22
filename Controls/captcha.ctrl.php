<?php
include_once 'App_Code/CommonFunctions.php';

class  Captcha {
	public static function render() {
		$values[1] = 'pulasas';
		$values[2] = 'muthipic';
		$values[3] = 'etsans';
		$values[4] = 'licit';
		
		//Choose captcha
		$captcha = rand(1, 4);
		$_SESSION['captcha'] = $values[$captcha];
        
		echo "<img src='../Images/captcha$captcha.jpg' title='Captcha' />
		      <input type='text' size='15' maxlength='15' name='captcha' id='captcha' />"; 
	}
	
	public static function check() {        
		$user_value = getPost('captcha', null);
		$captcha = isset($_SESSION['captcha']) ? $_SESSION['captcha'] : 'N/A';
		
		return ($user_value == $captcha); 
	}	
}
?>