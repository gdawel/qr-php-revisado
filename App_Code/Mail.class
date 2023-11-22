<?php
class Email {
	var $to, $cc, $subject, $message, $headers, $from;

    var $adminAddress = 'SOBRARE <faleconosco@sobrare.com.br>';
	
	function __construct() {
		//$this->from = 'SOBRARE <faleconosco@sobrare.com.br>';
		$this->from = 'Fale Conosco SOBRARE <noreply@sobrare.com.br>'; //senha Pass2word
		//$this->from = 'MORSAN <morsan@morsan.com.br>';
		
		// To send HTML mail, the Content-type header must be set
		$this->headers  = 'MIME-Version: 1.1' . "\r\n";
		$this->headers .= 'Content-type: text/html; charset=utf-8' . "\r\n"; //charset=utf-8

		// Additional headers
		$this->headers .= "From: $this->from" . "\r\n";		
		$this->headers .= "Return-Path: $this->from" . "\r\n"; // return-path
        $this->headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        //$this->headers .= 'Reply-To: faleconosco@sobrare.com.br' . "\r\n" .
		//$this->headers .= 'Cc: eduardomoralles@gmail.com' . "\r\n";
		//$this->headers .= 'Bcc: eduardomoralles@yahoo.com' . "\r\n";
	}
	
	function send() {
		try {
			return @mail($this->to, utf8_decode($this->subject), utf8_decode($this->message), $this->headers);
		
		} catch (Exception $e) {
			return false;
		}		
	}
}
?>

