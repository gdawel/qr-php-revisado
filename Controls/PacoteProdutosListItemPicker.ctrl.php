<?php
include_once 'list.ctrl.php';
include_once '../App_Code/CommonFunctions.php';

ob_clean(); //remove marcas de includes

$pacoteId = getPost('pacoteId', 0);
ListItemPicker::RenderCheckboxList('produtos', 'pacote_produtos_criacao_pesquisa', null, $pacoteId);
?>