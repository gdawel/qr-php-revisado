<?php
/*
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");
*/

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, "pt_BR", "pt_BR.utf-8", "portuguese", "pt-BR");
include_once('Filter.class.php');
define('MYSQL_BOTH',MYSQLI_BOTH);
define('MYSQL_NUM',MYSQLI_NUM);
define('MYSQL_ASSOC',MYSQLI_ASSOC);
define('AMBIENTE_PRODUCAO', 1);
define('AMBIENTE_HOMOLOGACAO', 2);
define('AMBIENTE_DESENVOLVIMENTO', 3);
if (!defined('JSON_PRETTY_PRINT'))
    define('JSON_PRETTY_PRINT', 128);

class SqlHelper {
	var $environment;
	var $db_server, $db_name, $db_user, $db_pass;
	var $db_conn, $db_query;
	var $command; 
	var $error; 
	var $debug;
	
	//TODO: como resolver essa questao do nested transaction sem o SESSION
	protected function isFirstTransactionLevel() {
		if (!isset($_SESSION['transactionLevel'])) $this->setInitialTransactionLevel();
		$level = $_SESSION['transactionLevel'];
		
		return ($level == 1);
	}
	
	protected function setInitialTransactionLevel() {
		$_SESSION['transactionLevel'] = 0;
		//echo "(set)Transaction Level: ".$_SESSION['transactionLevel'];
	}
	
	protected function raiseTransacationLevel() {
		if (!isset($_SESSION['transactionLevel'])) $this->setInitialTransactionLevel();
		$_SESSION['transactionLevel']++;
		
		//echo "(raise)Transaction Level: ".$_SESSION['transactionLevel'];
		return true;
	}
	protected function lowerTransactionLevel() {
		if (!isset($_SESSION['transactionLevel'])) $this->setInitialTransactionLevel();
		$_SESSION['transactionLevel']--;
		
		//echo "(lower)Transaction Level: ".$_SESSION['transactionLevel'];
		return true;
	}

	function __construct() {
		$this->debug = true;
		
		
		//$this->environment = AMBIENTE_DESENVOLVIMENTO;
		//$this->environment = AMBIENTE_HOMOLOGACAO;
        $this->environment = AMBIENTE_PRODUCAO;
		
		switch ($this->environment) {
			case  AMBIENTE_DESENVOLVIMENTO:
				$this->db_server='localhost';
				$this->db_user='root';
				$this->db_pass='';		
				$this->db_name='georgebarbosa5';			
				break;
			
			case AMBIENTE_HOMOLOGACAO:
            
				$this->db_server='mysql05.morsan.hospedagemdesites.ws';
				$this->db_user='morsan1';
				$this->db_pass='pass@word';
				$this->db_name='morsan1';
				break;
			
			case AMBIENTE_PRODUCAO:
                
				$this->db_server='localhost';
				$this->db_name='u265930598_georgebarbosa5';
				$this->db_user='u265930598_georgebarbosa5';
				$this->db_pass='EdurapaX4';			
				break;
			
			default:
				die('Ambiente do banco de dados inválido');
		}
		
		$this->db_conn = mysqli_connect($this->db_server, $this->db_user, $this->db_pass, $this->db_name)
			or die ('Erro na conexão com o banco de dados.');
		
		//Set char set
		mysqli_query($this->db_conn, "SET NAMES 'utf8';");
		mysqli_query($this->db_conn, "SET CHARACTER SET 'utf8';");
	}

	static function filter_escape_string($str, $quoteornull = false) {
		//$str = mysqli_real_escape_string($db_conn, $str);
		if ($quoteornull) {
			if (($str === null) || ($str == '')) {$str = 'NULL';} else {$str = "'$str'";}
		}
		return $str;
	}
	
    function escape_string($str, $quoteornull = false) {
        return SqlHelper::filter_escape_string($str, $quoteornull);    
    }
	function escape_id($id) {
		$id = intval($id);
		if (!$id) return 'NULL'; else return "$id";
	}
	
	function strtotime_nonus($date) {
			if(is_null($date) or ($date == ""))
					return False;
			// break out components with '/', maximum of 3 elements
			$components = explode("/", $date, 3);
			$count = count($components);
			if($count > 1) // There is a slash
			{
					$tmp = $components[0]; // Swap first and second components
					$components[0] = $components[1];
					$components[1] = $tmp;
			}
			return strtotime(implode("/",$components)); // Put back together
	}

	function prepareDate($date) {
		if (($timestamp = $this->strtotime_nonus($date)) === false) {
			return 'NULL';
		} else {
			return "'".date('Y-m-d H:i', $timestamp)."'";
		}
	}
	
	function prepareDecimal($n) {
	   return $this->ParseFloat($n);
	}

    function ParseFloat($floatString){
        if (is_null($floatString))
            return 'NULL';

        $LocaleInfo = localeconv();
		/* Dawel: 14/12/2023: desabilitada as duaslinha abaixo, pois estava fazendo o replace do
		/                     ponto decimal com base no locale de forma errada.
		/					  Dependendo do servidor, precisa voltar essa linha.
		*/
        //$floatString = str_replace($LocaleInfo["mon_thousands_sep"] , "", $floatString); 
		//$floatString = str_replace($LocaleInfo["mon_decimal_point"] , ".", $floatString); 
        return str_replace(",", ".", floatval($floatString)); 
    } 

	function __destruct() {
		$this->close();
	}

	function execute() {
		try {
			$this->db_query = mysqli_query($this->db_conn, $this->command);
			
			if ($this->db_query) {
				return true;
			} else {
				$this->error = "Erro ao executar query! \n Erro: <i>". mysqli_error($this->db_conn)."</i><br /><br /> <code>SQL Statement: <i>$this->command</i></code>";
				if ($this->debug) echo $this->error;
				return false;
			}
		} catch (Exception $e) {
			$this->error = "Erro ao executar query! \n Erro: <i>". mysqli_error($this->db_conn)."</i><br /><br /> <code>SQL Statement: <i>$this->command</i></code>";
			if ($this->debug) {
				echo $this->error;
				throw $e;
			}
			return false;
		}			
	}

	function hasrows() {
		return (mysqli_num_rows($this->db_query) > 0);
	}

	function rowscount() {
		return mysqli_num_rows($this->db_query);
	}

	function fetch(){
        
		return mysqli_fetch_assoc($this->db_query);
 
	}
	
	function fetchrow(){
		return mysqli_fetch_row($this->db_query);
	}

    function getInsertId() {
        return mysqli_insert_id($this->db_conn);
    }

	function dataset() {
		while ($r = $this->fetch()) {
			$lst[] = $r;
		}
		
		if (isset($lst)) {return $lst;} else {return null;}
	}
	
	function close(){
		//mysqli_free_result($this->db_query);
		mysqli_close($this->db_conn);
	}
	
	function begin() {
		$this->raiseTransacationLevel();
		
		if ($this->isFirstTransactionLevel()) {
            mysqli_autocommit($this->db_conn, FALSE);
		} else {
			//keep using existing transaction
		}
	}
	
	function commit() {
		if ($this->isFirstTransactionLevel()) {
			$this->setInitialTransactionLevel();

            $r = mysqli_commit($this->db_conn);
            mysqli_autocommit($this->db_conn, TRUE);

            return $r;
		} else {
			$this->lowerTransactionLevel();
			return true;
		}
	}
	
	function rollback() {
		if ($this->isFirstTransactionLevel()) {
			$this->setInitialTransactionLevel();

            $r = mysqli_rollback($this->db_conn);
            mysqli_autocommit($this->db_conn, TRUE);

            return $r;
		} else {
			$this->lowerTransactionLevel();
			return true;
		}
	}
}
?>