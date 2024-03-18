<?php
 /* pChart library inclusions */
 //include_once("../Includes/pChart2.1.1/class/pData.class.php");
 //include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 //include_once("../Includes/pChart2.1.1/class/pImage.class.php");


/* pChart library inclusions */
require_once("Includes/pChart/bootstrap.php");

use pChart\pColor;
use pChart\pDraw;
use pChart\pCharts;

/* CAT:Line Chart - Distribuicao dos Indices no MCD */
   
function generateLineChart($dataValues, $dataValuesPopulacaoBase, $fator) {
 //order data by key
 ksort($dataValues);
 ksort($dataValuesPopulacaoBase);
 
 /* Calcular valores relativos da pesquisa (%) */
 $total = array_sum($dataValues);
 foreach ($dataValues as &$v) $v = $v / $total * 100; 
	
 /* Calcular valores relativos da populacao base (%) */
 $total = array_sum($dataValuesPopulacaoBase);
 foreach ($dataValuesPopulacaoBase  as &$v) $v = $v / $total * 100;
 	
 /* Create and populate the pData object */
 $myPicture = new pDraw(700, 542);  
 //$myPicture->myData->addPoints($dataValues,"Populaçãoo desta Pesquisa");
 //$myPicture->myData->addPoints($dataValuesPopulacaoBase, "População-Base");
 //$myPicture->myData->setSerieWeight("Popula��o desta Pesquisa",1);
 //$myPicture->myData->setSerieWeight("Popula��o-Base",1); 
 //$myPicture->myData->setAxisUnit(0, '%');
 //$myPicture->myData->setAxisName(0, "N�mero de Respondentes");
 //$myPicture->myData->addPoints(array("PC-P\nCondi��o\nFRACA","PC-P\nCondi��o\nMODERADA","PC-P\nCondi��o\nBOA","PC-P\nCondi��o\nFORTE","PC-E\nEQUIL�BRIO","PC-I\nCondi��o\nFORTE","PC-I\nCondi��o\nBOA","PC-I\nCondi��o\nMODERADA","PC-I\nCondi��o\nFRACA"), "Labels");
 //$myPicture->myData->setSerieDescription("Labels", "Classifica��o");
 //$myPicture->myData->setAbscissa("Labels");
 //$myPicture->myData->setPalette("Popula��o desta Pesquisa",array("R"=>0,"G"=>102,"B"=>153));

 /*  GD: 15/03/2024 - Definição do gráfico no novo padrão do pChart
 /
 /*
/* Populate the pData object */
$myPicture->myData->addPoints($dataValues,"Esta-Pesquisa");
$myPicture->myData->setPalette("Esta-Pesquisa",new pColor(220,60,20));
$myPicture->myData->addPoints($dataValuesPopulacaoBase, "População-Base");
$myPicture->myData->setPalette("População-Base",new pColor(20,0,153));
//$myPicture->myData->addPoints([-4,VOID,VOID,12,8,3],"Probe 1");
//$myPicture->myData->addPoints([3,12,15,8,5,-5],"Probe 2");
//$myPicture->myData->addPoints([2,7,5,18,19,22],"Probe 3");
$myPicture->myData->setSerieProperties("Esta-Pesquisa", ["Ticks" => 4, "Weight" => 1]);
$myPicture->myData->setSerieProperties("População-Base", ["Weight" => 2]);
$myPicture->myData->setAxisName(0,"Número de Respondentes");
$myPicture->myData->addPoints(array("PC-P\nCondição\nFRACA","PC-P\nCondição\nMODERADA","PC-P\nCondição\nBOA","PC-P\nCondição\nFORTE","PC-E\nEQUILÍBRIO","PC-I\nCondição\nFORTE","PC-I\nCondição\nBOA","PC-I\nCondição\nMODERADA","PC-I\nCondição\nFRACA"), "Labels");
//$myPicture->myData->addPoints(["Jan","Feb","Mar","Apr","May","Jun"],"Labels");
$myPicture->myData->setSerieDescription("Labels","MCD's");
$myPicture->myData->setAbscissa("Labels");

/* Turn off Anti-aliasing */
$myPicture->setAntialias(FALSE);


/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,530,["Color"=>new pColor(0)]);

/* Write the chart title */ 
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>11]);
$myPicture->drawText(350,35,"Comparativo das populações no MCD",["FontSize"=>16,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE]);

/* Set the default font */
$myPicture->setFontProperties(["FontSize"=>7]);

/* Define the chart area */
$myPicture->setGraphArea(60,40,650,400);

/* Draw the scale */
$myPicture->drawScale(["XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridColor"=>new pColor(200),"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE]);

/* Turn on Anti-aliasing */
$myPicture->setAntialias(TRUE);

$scaleSettings = array(//"Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,
								"Mode"=>SCALE_MODE_START0, 								
								"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,
								"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE); 
$myPicture->drawScale($scaleSettings); 

/* Write the chart legend */
$myPicture->setFontProperties(["FontSize"=>10]);
$myPicture->drawLegend(250,500,["Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL,"Family"=>LEGEND_FAMILY_LINE]);

/* Draw the line chart */
$pCharts = new pCharts($myPicture);

$pCharts->drawsPLineChart();



/* Write the chart legend */
//$myPicture->drawLegend(540,20,["Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL]);



 /*  GD: 15/03/2024 - Linhas Abaixo Desabilitadas no novo gráfico
 /
 /*

 
 /* Create the pChart object */
 //$myPicture = new pImage(700,490,$MyData);

 /* Turn of Antialiasing */
 //$myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 //$myPicture->drawRectangle(0,0,699,489,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>10));
 //$myPicture->drawGradientArea(1,1,698,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));
 //$myPicture->drawText(350,25,"Comparativo das popula��es no MCD '$fator->nome'",array("FontSize"=>16,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawText(350,55,$fatorNome,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 //$myPicture->setGraphArea(40,50,680,430);

 /* Draw the scale */ 
 //$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
 //$scaleSettings = array(//"Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,
 //								"Mode"=>SCALE_MODE_START0, 								
 //								"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,
 //								"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE); 
 //$myPicture->drawScale($scaleSettings); 

 /* Turn on Antialiasing */ 
 //$myPicture->Antialias = TRUE; 

 /* Draw the line chart */ 
 //$myPicture->drawSplineChart(); 

 /* Write the chart legend */
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>8)); 
 //$myPicture->drawLegend(430,50,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_16_".$fator->id."_2.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>