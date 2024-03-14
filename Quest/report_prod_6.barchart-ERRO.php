<?php
 /* pChart library inclusions */
 // GD: 09/03/2024 include_once("../Includes/pChart2.1.1/class/pData.class.php");
 // GD: 09/03/2024 include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 // GD: 09/03/2024 include_once("../Includes/pChart2.1.1/class/pImage.class.php");
 // GD: 09/03/2024 include_once("../Includes/pChart2.1.1/class/pRadar.class.php");

/* CAT:Radar Chart */

/* pChart library inclusions */
require_once("../Includes/pChart/bootstrap.php");

use pChart\{
	pColor,
	pDraw,
	pCharts
};
   
function generateChart($items, $type='P') {
	/* Fetch data */
	foreach ($items as $i) {
		$data[] = $i->qtde;		
		$labels[] = convertIsoUtf($i->nome);
	}

/* Create and populate the pData object */
 $myPicture = new pDraw(700, 700);   
 $myPicture->myData->addPoints($data,"DataPoints");  
 $myPicture->myData->setSerieDescription("DataPoints","Quantidade de Respondentes");
 $myPicture->myData->setPalette("DataPoints", new pColor(150,5,217));
 //$myPicture->myData->setPalette("DataPoints",array("R"=>0,"G"=>102,"B"=>153));
 
 /* Define the absissa serie */
 $myPicture->myData->addPoints($labels,"Labels");
 $myPicture->myData->setSerieDescription("Labels","Quantidade por Fator");
 $myPicture->myData->setAbscissa("Labels");

 /* Turn off Anti-aliasing */
 $myPicture->setAntialias(FALSE);

 /* Overlay some gradient areas */ // GD: 10/03/2024 - Adicionado para testar erro SetColor undefined
//$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,["StartColor"=>new pColor(194,231,44,50),"EndColor"=>new pColor(43,107,58,50)]);
//$myPicture->drawGradientArea(0,0,700,20, DIRECTION_VERTICAL,["StartColor"=>new pColor(0),"EndColor"=>new pColor(50)]);

/* Add a border to the picture */
//$myPicture->drawRectangle(0,0,699,229,["Color"=>new pColor(0)]);

 /* Create the pChart object */
 // GD: 09/03/2024 $myPicture = new pImage(700,460,$myPicture->myData);

 /* Draw a solid background */
 //$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 //$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

 /* Overlay some gradient areas */
 //$Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 //$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
 // GD: 10/03/2024 - $myPicture->drawGradientArea(0,0,700,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));

 /* Add a border to the picture */
// GD 11/03/2024 $myPicture->drawRectangle(0,0,650,459,array("R"=>204,"G"=>204,"B"=>204));

 /* Draw the scale */
$myPicture->drawScale(["GridColor"=>new pColor(200),"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE]);

 /* Write the picture title */ 
 // GD 11/03/2024 $myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>14]);
 // GD 11/03/2024 $myPicture->drawText(350,22,"Quantidade de Respondentes na Categoria FRACA no estilo PC - $type",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawLine(0,20,300,20,["Color" => new pColor(255)]);
 /* Set the default font properties */ 
 //$myPicture->setFontProperties(array("FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>14,"R"=>40,"G"=>40,"B"=>40));

 /* Enable shadow computing */ 
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 //$SplitChart = new pRadar($myPicture);

// GD 11/03/2024 =========== Novos parâmentros
 /* Draw the background */
$myPicture->drawFilledRectangle(0,0,700,230,["Color"=> new pColor(170,183,87), "Dash"=>TRUE, "DashColor"=>new pColor(190,203,107)]);

/* Overlay with a gradient */
$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,["StartColor"=>new pColor(219,231,139,50),"EndColor"=>new pColor(1,138,68,50)]);
$myPicture->drawGradientArea(0,0,700,20, DIRECTION_VERTICAL,["StartColor"=>new pColor(0,0,0,80),"EndColor"=>new pColor(50,50,50,80)]);

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,229,["Color"=>new pColor(0)]);

/* Write the picture title */ 
$myPicture->setFontProperties(["FontName"=>"../Includes/pChart/fonts/PressStart2P-Regular.ttf","FontSize"=>6]);
$myPicture->drawText(10,15,"drawBarChart() - draw a bar chart",["Color"=>new pColor(255)]);

/* Write the chart title */ 
$myPicture->setFontProperties(["FontName"=>"../Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>9]);
$myPicture->drawText(250,55,"Average temperature",["FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE]);



 $pCharts = new pCharts($myPicture);




 /* Draw a radar chart */ 
 //$myPicture->setGraphArea(25,40,650,440);
 /* GD: 09/03/2024 - Retirado Opções misturadas para verificar se há algum erro. 
 *						Colada Opções de exemplo.radar
 $Options = [
			//"Factors"=>array(2,5),"Weight"=>1,
			"Layout"=>RADAR_LAYOUT_CIRCLE,"LabelPos"=>RADAR_LABELS_HORIZONTAL,
			//"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>20,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>5),
			//"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>30,"EndR"=>150,"EndG"=>150,"EndB"=>150,"EndAlpha"=>10), 
			"BackgroundGradient"=>["StartColor"=>new pColor(255,255,255,50),"EndColor"=>new pColor(32,109,174,30)],
			"FontName"=>"fonts/Cairo-Regular.ttf","FontSize"=>7
			
			];
			*/
//$myPicture->setFontProperties(array("FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>11));
/*
$Options = [
	
	"Layout"=>RADAR_LAYOUT_CIRCLE,
	"LabelPos"=>RADAR_LABELS_HORIZONTAL,
	
	"BackgroundGradient"=>["StartColor"=>new pColor(255,255,255,50),"EndColor"=>new pColor(139,237,247,30)],
	
];
*/

/* Draw the scale and the 1st chart */
$myPicture->setGraphArea(60,60,600,390);
$myPicture->drawFilledRectangle(60,60,450,300,["Color"=>new pColor(255,255,255,10),"Surrounding"=>-200]);
$myPicture->drawScale(["DrawSubTicks"=>TRUE]);
$myPicture->setShadow(TRUE,["X"=>1,"Y"=>1,"Color"=>new pColor(0,0,0,10)]);
$myPicture->setFontProperties(["FontName"=>"../Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>7]);
$pCharts->drawBarChart(["DisplayValues"=>TRUE,"DisplayType"=>DISPLAY_AUTO,"Rounded"=>TRUE,"Surrounding"=>30]);


 //$pCharts->drawBarChart();

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_6_$type.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>