<?php

if (!defined('ALLOW_PAGSEGURO_CONFIG')) { die('NOT ALLOWED'); }

$PagSeguroConfig = array();

$PagSeguroConfig['enviroment'] = array();
$PagSeguroConfig['enviroment']['enviroment'] = "production"; //production, development


$PagSeguroConfig['credentials'] = array();
$PagSeguroConfig['credentials']['email'] = "financeiro@sobrare.com.br";
$PagSeguroConfig['credentials']['token'] = "056A3AEDDCFE4562AE5415AB3B4F094C";

$PagSeguroConfig['application'] = array();
$PagSeguroConfig['application']['charset'] = "UTF-8"; // UTF-8, ISO-8859-1

$PagSeguroConfig['log'] = array();
$PagSeguroConfig['log']['active'] = TRUE;
$PagSeguroConfig['log']['fileLocation'] = "C:\Users\eduardo.moralles\Web Projects\SOBRARE\Uploads\PagSeguro.log";
//$PagSeguroConfig['log']['fileLocation'] = "E:\home\morsan.outsys.net\wwwroot\sobrare\Uploads\PagSeguro.log"; //MORSAN.com.br
//$PagSeguroConfig['log']['fileLocation'] = "E:\home\georgebarbosa\Web\sobrare\Uploads\PagSeguro.log";

?>