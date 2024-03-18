<?php
 /* pChart library inclusions */
 //include_once("../Includes/pChart2.1.1/class/pData.class.php");
 //include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 //include_once("../Includes/pChart2.1.1/class/pImage.class.php");


 /* pChart library inclusions */
require_once("../Includes/pChart/bootstrap.php");


use pChart\{
	pColor,
	pDraw,
	pCharts
};

/* CAT:Bar Chart - Distribuicao dos Indices no MCD */
   
function generateBarChart($dataValues, $fator) {
 

 //$MyPicture = new pDraw();  
 //order data by key
 ksort($dataValues);
 

/* Create and populate the pData object */
 $myPicture = new pDraw(700, 542);   
 $myPicture->myData->addPoints($dataValues,"DataPoints");  
 
 $myPicture->myData->setAxisProperties(0, ["Name" => "DataPoints", "Display" => AXIS_FORMAT_METRIC, "Format" => 1]);
 $myPicture->myData->setSerieDescription("DataPoints","Quant. Respondentes");
 $myPicture->myData->setPalette("DataPoints", new pColor(0,0,102)); // Côr: Azul Escuro
 $myPicture->myData->setAxisName(0,"Quantidade");

 /* Create the per bar palette */

 
/*  GD: 14/03/2024 - Testei criar um pallet de cores para cada série (barras) do gráfico: funcionou.
* 					 Porém, o cliente pediu azul.
$Palette = array("0"=>new pColor(188,224,46,100),
				 "1"=>new pColor(224,100,46,100),
				 "2"=>new pColor(224,214,46,100),
				 "3"=>new pColor(46,151,224,100),				 
				 "4"=>new pColor(176,46,224,100),
				 "5"=>new pColor(224,46,117,100),
				 "6"=>new pColor(92,224,46,100),
				 "7"=>new pColor(250,6,22,100),
				 "8"=>new pColor(224,176,46,100));

                 */

 //$$myPicture->myData->addPoints($dataValues,"Número de Respondentes no Índice");
 //$$myPicture->myData->setAxisName(0, "Número de Respondentes");
 //$$myPicture->myData->addPoints(array("PC-P\nCondição\nFRACA","PC-P\nCondição\nMODERADA","PC-P\nCondição\nBOA","PC-P\nCondição\nFORTE","PC-E\nEQUILÍBRIO","PC-I\nCondição\nFORTE","PC-I\nCondição\nBOA","PC-I\nCondição\nMODERADA","PC-I\nCondição\nFRACA"), "Labels");
 //$$myPicture->myData->setSerieDescription("Labels", "Classificação");
 //$$myPicture->myData->setAbscissa("Labels");
 //$$myPicture->myData->setPalette("Número de Respondentes no Índice",array("R"=>0,"G"=>102,"B"=>153));

 /* Define the absissa serie */
 $myPicture->myData->addPoints(array("PC-P Condição FRACA","PC-P Condição MODERADA","PC-P Condição BOA","PC-P Condição FORTE","PC-E EQUILÍBRIO","PC-I Condição FORTE","PC-I Condição BOA","PC-I Condição MODERADA","PC-I Condição FRACA"), "Labels");
 //$myPicture->myData->addPoints($labels,"Labels");
 $myPicture->myData->setSerieDescription("Labels","Classificação");
 $myPicture->myData->setAbscissa("Labels");

 /* Turn off Anti-aliasing */
 $myPicture->setAntialias(FALSE);
 
 /* Create the pChart object */
 //$myPicture = new pImage(700,490,$MyData);

 /* Write the chart title */ 
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>9]);
$myPicture->drawText(300,25,"Distribuição dos Índices no MCD '$fator->nome'",["FontSize"=>12,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE]);

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,530,["Color"=>new pColor(0)]);

/* Set the default font */
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>8]);

/* Define the chart area */
$myPicture->setGraphArea(180,70,620,500);

/* Draw the scale */
$myPicture->drawScale(["GridColor"=>new pColor(200),"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"Pos"=>SCALE_POS_TOPBOTTOM]);

/* Write the chart legend */
$myPicture->drawLegend(580,12,["Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL]);

/* Turn on shadow computing */ 
$myPicture->setShadow(TRUE,["X"=>1,"Y"=>1,"Color"=>new pColor(0,0,0,10)]);

/* Draw the chart */
(new pCharts($myPicture))->drawBarChart([
	"Gradient"=>TRUE,
	"GradientMode"=>GRADIENT_EFFECT_CAN,
	"DisplayPos"=>LABEL_POS_INSIDE,
	"Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL,
	"DisplayValues"=>TRUE,
	"DisplayColor"=>new pColor(255),
	"DisplayShadow"=>TRUE,
	"Interleave"=>1,
	"Surrounding"=>10
]);



/*
*
* DAWEL - PARTE ORIGINAL RETIRADA EM 14/03/2024
*/


/* Add a border to the picture */
 //$myPicture->drawRectangle(0,0,699,489,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>10));
 //$myPicture->drawGradientArea(1,1,698,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));
 //$myPicture->drawText(350,25,"Distribui��o dos �ndices no MCD '$fator->nome'",array("FontSize"=>16,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawText(350,55,$fatorNome,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 //$myPicture->setGraphArea(40,70,690,440);

 /* Draw the scale */
 //$scaleSettings = array("Mode"=>SCALE_MODE_START0,/*"Factors"=>array(1,2,5),"MinDivHeight"=>100,*/
 //								"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE);
 //$myPicture->drawScale($scaleSettings);

 /* Write the chart legend */
 //$myPicture->drawLegend(500,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on shadow computing */ 
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 //$settings = array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_MANUAL,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0,"Surrounding"=>20,"LabelPos"=>LABEL_POS_TOP);
 //$myPicture->drawBarChart($settings);

//
//
// =================================================================================

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_16_".$fator->id."_1.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>