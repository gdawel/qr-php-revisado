<?php
 
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
 $myPicture = new pDraw(700, 542);   
 $myPicture->myData->addPoints($data,"DataPoints");  
 $myPicture->myData->setAxisProperties(0, ["Name" => "DataPoints", "Display" => AXIS_FORMAT_METRIC, "Format" => 0]);
 $myPicture->myData->setSerieDescription("DataPoints","Quant. Respondentes");
 $myPicture->myData->setPalette("DataPoints", new pColor(19,111,16));
 $myPicture->myData->setAxisName(0,"Quantidade");
 //$myPicture->myData->setPalette("DataPoints",array("R"=>0,"G"=>102,"B"=>153));

 //$myPicture->drawGradientArea(0,0,500,500,DIRECTION_HORIZONTAL,["StartColor"=>new pColor(219,231,139,50),"EndColor"=>new pColor(1,138,68,50)]);
 
 /* Define the absissa serie */
 $myPicture->myData->addPoints($labels,"Labels");
 $myPicture->myData->setSerieDescription("Labels","Quantidade por MCD");
 $myPicture->myData->setAbscissa("Labels");

 /* Turn off Anti-aliasing */
 $myPicture->setAntialias(FALSE);
// ===========================================================================================================
/* Write the chart title */ 
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>9]);
$myPicture->drawText(300,25,"Quantidade de Respondentes por MCD",["FontSize"=>14,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE]);


/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,530,["Color"=>new pColor(0)]);

/* Set the default font */
$myPicture->setFontProperties(["FontName"=>"Includes/pChart/fonts/Cairo-Regular.ttf","FontSize"=>8]);

/* Define the chart area */
$myPicture->setGraphArea(150,70,620,500);

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
	"Surrounding"=>10
]);
// ===========================================================================================================
 

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_6_$type.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>