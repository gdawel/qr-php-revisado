<?php
function applicationPath() {
	$f=explode('/', $_SERVER['PHP_SELF']);
	//array_pop($f);
	//return implode('/', $f);
	if (is_array($f))
		return '/'.$f[1];
	else
		return '/';
}

function convertIsoUtf($texto) {

	$texto = mb_convert_encoding($texto, 'ISO-8859-1','UTF-8');
	return $texto;
}

function isPageRefresh() {
	$PageRefreshChecker = getPost('PageRefreshChecker', 0);
	checkPageRefreshSessionVar();
	return ($PageRefreshChecker != $_SESSION['PageRefreshChecker']);
}
function checkPageRefreshSessionVar() {
	//if (!session_id()) session_start();
	if (!isset($_SESSION['PageRefreshChecker'])) $_SESSION['PageRefreshChecker'] = 1;
}
function updatePageRefreshChecker() {$_SESSION['PageRefreshChecker'] += 1;}

/* Substituito pelo setlocale() no SqlHelper.class */
function decimal($val, $precision = 0) { 
    if ($val) : 
        $val = round((float) $val, (int) $precision);         
				if (count(explode('.', $val))>1) {list($a, $b) = explode('.', $val);} else {$a = $val; $b='';}
        if (strlen($b) < $precision) $b = str_pad($b, $precision, '0', STR_PAD_RIGHT); 
        return $precision ? "$a,$b" : $a; 
    else : // do whatever you want with values that do not have a float 
        return $val; 
    endif; 
} 

/**
 * Calcula a media de um vetor de numeros
 * @param array $a Vetor de numeros
 * @return number Retorna a media dos valores do vetor
 */
function media_aritmetica(array $a) {
    return array_sum($a) / count($a);
}

function mediana()
{
    $args = func_get_args();

    switch(func_num_args())
    {
        case 0:
            trigger_error('median() requires at least one parameter',E_USER_WARNING);
            return false;
            break;

        case 1:
            $args = array_pop($args);
            // fallthrough

        default:
            if(!is_array($args)) {
                trigger_error('median() requires a list of numbers to operate on or an array of numbers',E_USER_NOTICE);
                return false;
            }

            sort($args);
            
            $n = count($args);
            $h = intval($n / 2);

            if($n % 2 == 0) { 
                $median = ($args[$h] + $args[$h-1]) / 2; 
            } else { 
                $median = $args[$h]; 
            }

            break;
    }
    
    return $median;
}

function getPost($varname, $default = null, $convertspecialchars = false) {
	if (isset($_POST[$varname])) {
		if ($convertspecialchars) {
			return htmlspecialchars($_POST[$varname], ENT_QUOTES);
		} else {
			return $_POST[$varname];
		}
	} else {
		if ($convertspecialchars) {
			return htmlspecialchars($default, ENT_QUOTES);
		} else {
			return $default;
		}				
	}
}

function getPostIsSelected($varname, $index) {
    if (isset($_POST[$varname][$index])) {
        return "checked = 'checked'";
    } else {
        return "";
    }
}

function getIntPost($varname, $default = 0, $greaterthanzero = false) {
	if (!isset($_POST[$varname])) return intval($default);
	
	$r = intval($_POST[$varname]);
	if (($greaterthanzero) && ($r < 1)) $r = $default;
	return $r;
}

function getQueryString($varname, $default = null, $convertspecialchars = false) {
	if (!isset($_GET[$varname])) {
	   if ($convertspecialchars) {
            return htmlspecialchars($default, ENT_QUOTES);
        } else {
            return $default;   
        }  
	}
	
    if ($convertspecialchars) {
		return htmlspecialchars($_GET[$varname], ENT_QUOTES);
	} else {
		return $_GET[$varname];
	}
}

function getIntQueryString($varname, $default = 0, $greaterthanzero = false) {
	if (!isset($_GET[$varname])) return $default;
	
	$r = intval($_GET[$varname]);
	if (($greaterthanzero) && ($r < 1)) $r = $default;
	return $r;
}



/*
Get a color point form a gradient interval
*/
function get_gradient_point_color($start, $end, $percent) {
 
	$r1=hexdec(substr($start, 0, 2));
	$g1=hexdec(substr($start, 2, 2));
	$b1=hexdec(substr($start, 4, 2));
 
	$r2=hexdec(substr($end, 0, 2));
	$g2=hexdec(substr($end, 2, 2));
	$b2=hexdec(substr($end, 4, 2));
 
	$pc = $percent/100;
 
	$r = floor($r1+($pc*($r2-$r1)) + .5);
	$g = floor($g1+($pc*($g2-$g1)) + .5);
	$b = floor($b1+($pc*($b2-$b1)) + .5);
 
	return(sprintf('#%02X%02X%02X', $r, $g, $b));
}

function format_date($str) {
	if (!$str) return '';
	return date('d/m/Y', strtotime($str));
}
function format_datetime($str) {
	return date('d/m/Y H:i', strtotime($str));
}
function format_time($str) {
	return date('H:i', strtotime($str));
}

///gera uma string SEO friendly
function getSEO($titulo) {
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', remove_accent($titulo)), '-'));
}

///remove todos os acentos de uma string
function remove_accent($str) 
{ 
  $a = array('?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?'); 
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
  return str_replace($a, $b, $str); 
} 
?>