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
	pRadar
};
   
function generateChart($items, $type='P') {
	/* Fetch data */
	foreach ($items as $i) {
		$data[] = $i->qtde;		
		$labels[] = convertIsoUtf($i->nome);
	}

  /* Create and populate the pData object */
 $myPicture = new pDraw(700, 500);   
 $myPicture->myData->addPoints($data,"DataPoints");  
 $myPicture->myData->setSerieDescription("DataPoints","Quantidade de Respondentes");
 $myPicture->myData->setPalette("DataPoints", new pColor(150,5,217));
 //$myPicture->myData->setPalette("DataPoints",array("R"=>0,"G"=>102,"B"=>153));
 
 /* Define the absissa serie */
 $myPicture->myData->addPoints($labels,"Labels");
 $myPicture->myData->setAbscissa("Labels");

 /* Overlay some gradient areas */ // GD: 10/03/2024 - Adicionado para testar erro SetColor undefined
//$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,["StartColor"=>new pColor(194,231,44,50),"EndColor"=>new pColor(43,107,58,50)]);
//$myPicture->drawGradientArea(0,0,700,20, DIRECTION_VERTICAL,["StartColor"=>new pColor(0),"EndColor"=>new pColor(50)]);

/* Add a border to the picture */
//$myPicture->drawRectangle(0,0,699,229,["Color"=>new pColor(0)]);

 /* Create the pChart object */
 // GD: 09/03/2024 $myPicture = new pImage(700,460,$myPicture->myData);

 /* Draw a solid background */
 $Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 //$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

 /* Overlay some gradient areas */
 //$Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 //$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
 // GD: 10/03/2024 - $myPicture->drawGradientArea(0,0,700,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,459,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>14));
 $myPicture->drawText(350,22,"Quantidade de Respondentes na Categoria FRACA no estilo PC - $type",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawLine(0,20,300,20,["Color" => new pColor(255)]);
 /* Set the default font properties */ 
 //$myPicture->setFontProperties(array("FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>14,"R"=>40,"G"=>40,"B"=>40));

 /* Enable shadow computing */ 
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 $SplitChart = new pRadar($myPicture);

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(25,40,690,440);
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
$myPicture->setFontProperties(array("FontName"=>"Includes/pChart/fonts/Abel-Regular.ttf","FontSize"=>11));
$Options = [
	
	"Layout"=>RADAR_LAYOUT_CIRCLE,
	"LabelPos"=>RADAR_LABELS_HORIZONTAL,
	
	"BackgroundGradient"=>["StartColor"=>new pColor(255,255,255,50),"EndColor"=>new pColor(139,237,247,30)],
	
];
 $SplitChart->drawRadar($Options);

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "_tmp/__".$usr->userid."_report_6_$type.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>