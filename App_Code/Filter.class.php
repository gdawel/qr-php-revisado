<?php
include_once('CommonFunctions.php');

class Filter {
	var $fields, $values, $operators, $formats;
	var $expression;
	
	function __construct() {$this->expression = '';}
	
	function add($field, $operator, $value, $format = null) {	
		$this->fields[] = $field;
		$this->operators[] = $operator;
		$this->values[] = ($operator=='LIKE' ? "%$value%" : $value);
		$this->formats[] = $format;
		
		$this->generate();
	}
	
	function addFromPost($field, $operator, $varname, $format = null, $defaultvalue = null, $ignore_value = null) {
		if (isset($_SESSION[$varname]))
			$value = getPost($varname, @$_SESSION[$varname]); 
		else
			$value = getPost($varname, null);
		
		if (!$value) $value = $defaultvalue; 
		
		if (($value) && ($value != $ignore_value)) {
			$this->add($field, $operator, $value, $format); 
			$_SESSION[$varname] = $value; 
			return getPost($varname, $_SESSION[$varname], true);
		}	else {
			$_SESSION[$varname] = null;
			return null;
		}
	}
	
	function addFromQueryString($field, $operator, $varname, $format = null) {
		$value = getQueryString($varname, null); 
		
		if ($value) {
			$this->add($field, $operator, $value, $format); 
			$_SESSION[$varname] = $value; 
			return getQueryString($varname, null, true);
		} else {
			$_SESSION[$varname] = null;
			return null;
		}
	}
	
	function generate($insertwhere = true) {
		if (!$this->fields) {
			$e = '';	
		} else {			
			$i = 0;
			if ($insertwhere) $e = 'WHERE '; else $e = '';
			//$sql = new SqlHelper();
			
			foreach ($this->fields as $f) {
				if ($i > 0) $e .= ' AND ';
				if (!$this->formats[$i]) 
					$e .= "$f ".$this->operators[$i].' '.SqlHelper::filter_escape_string($this->values[$i], true);
				else 
					$e .= "$f ".$this->operators[$i].' '.sprintf($this->formats[$i], SqlHelper::filter_escape_string($this->values[$i], false));
				$i++;
			}
		}
		
		$this->expression = $e;
		return $e;
	}
}
?>