<?php
 /* pChart library inclusions */
 //include_once("../Includes/pChart2.1.1/class/pData.class.php");
 //include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 //include_once("../Includes/pChart2.1.1/class/pImage.class.php");
 //include_once("../Includes/pChart2.1.1/class/pRadar.class.php");

/* pChart library inclusions */
require_once("../Includes/pChart/bootstrap.php");

use pChart\pColor;
use pChart\pDraw;
use pChart\pCharts;


 /* CAT:Bar Chart */
  
function generateChart($fator, $data, $labels) {
  /* Create and populate the pData object */
 $myPicture = new pDraw(700, 420);   

 $myPicture->myData->addPoints($data,"DataPoints");  
 $myPicture->myData->setAxisProperties(0, ["Name" => "DataPoints", "Display" => AXIS_FORMAT_METRIC, "Format" => 0]);
 $myPicture->myData->setSerieDescription("DataPoints","Quantidade de Respondentes");
 $myPicture->myData->setPalette("DataPoints", new pColor(0,51,0));  //Côr: Verde Escuro
 $myPicture->myData->setAxisName(0,"Respondentes");

 /* Define the absissa serie */
 $myPicture->myData->addPoints($labels,"Labels");
 $myPicture->myData->setSerieDescription("Labels","Quantidade de Respondentes");
 $myPicture->myData->setAbscissa("Labels");

 /* Write the picture title */ 
 $myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>12]);
 $myPicture->drawText(350,22,"Respondentes nas Condições de Fortaleza - MCD $fator->nome",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));

 /* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,400,["Color"=>new pColor(0)]);

/* Set the default font */
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>8]);

/* Define the chart area */
$myPicture->setGraphArea(150,70,620,350);

/* Draw the scale */
$myPicture->drawScale(["GridColor"=>new pColor(200),"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"Pos"=>SCALE_POS_TOPBOTTOM]);

/* Write the chart legend */
$myPicture->setFontProperties(["FontSize"=>10]);
$myPicture->drawLegend(250,380,["Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL,"Family"=>LEGEND_FAMILY_BOX]);

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
	"Surrounding"=>10
]);



 // GD: 16/03/2024 Linhas antigas daqui para baixo.
 //$myPicture->myData->addPoints($data,"DataPoints");  
 //$myPicture->myData->setSerieDescription("DataPoints","Quantidade de Respondentes");
 //$myPicture->myData->setPalette("DataPoints",array("R"=>0,"G"=>102,"B"=>153));
 
 /* Define the absissa serie */
 //$myPicture->myData->addPoints($labels,"Labels");
 //$myPicture->myData->setAbscissa("Labels");

 

 /* Create the pChart object */
 //$myPicture = new pImage(700,460,$MyData);

 /* Draw a solid background */
 //$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 //$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

 /* Overlay some gradient areas */
 //$Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 //$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
 //$myPicture->drawGradientArea(0,0,700,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));

 /* Add a border to the picture */
 //$myPicture->drawRectangle(0,0,699,459,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>16));
 //$myPicture->drawText(350,22,"Respondentes nas Condi��es de Fortaleza - MCD $fator->nome",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));

 /* Set the default font properties */ 
 //$myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>14,"R"=>40,"G"=>40,"B"=>40));

 /* Enable shadow computing */ 
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 //$SplitChart = new pRadar();

 /* Draw a radar chart */ 
 //$myPicture->setGraphArea(25,50,680,450);
 //$Options = array("AxisRotation"=>30, "Factors"=>array(2,5),"Weight"=>1,
//						"Layout"=>RADAR_LAYOUT_CIRCLE, "LabelPos"=>RADAR_LABELS_HORIZONTAL,
//						//"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>20,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>5),
//						"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>30,"EndR"=>150,"EndG"=>150,"EndB"=>150,"EndAlpha"=>10), 
//						"FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf",
//						"FontSize"=>6);
 //$SplitChart->drawRadar($myPicture,$MyData,$Options);

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_26_f$fator->id.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>