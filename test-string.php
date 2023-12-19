<?php

function prepareDecimal($n) {
	   return ParseFloat($n);
	}

function ParseFloat($floatString){
    if (is_null($floatString))
        return 'NULL';

    $LocaleInfo = localeconv();
    /* Dawel: 14/12/2023: desabilitada a linha abaixo, pois estava fazendo o replace do
    /                     ponto decimal com base no locale de forma errada.
    /					  Dependendo do servidor, precisa voltar essa linha.
    */
    print_r($LocaleInfo); echo "<br><br>";
    $floatString = str_replace($LocaleInfo["mon_thousands_sep"] , "", $floatString); 
    $floatString = str_replace($LocaleInfo["mon_decimal_point"] , ".", $floatString); 
    return str_replace(",", ".", floatval($floatString)); 
} 
?>

<html>
<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
  Valor: <input type="text" name="valor">
  <input type="submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // collect value of input field
  $valor = $_POST['valor'];
  if (empty($valor)) {
    echo "Valor is empty";
  } else {
    //echo $valor;
  } echo "Valor da entrada: " . $valor . "<br><br>";

    $valorsaida = prepareDecimal($valor);
    echo "Valor da saída: " . $valorsaida . "<br><br>";
    }
?>

</body>
</html>



