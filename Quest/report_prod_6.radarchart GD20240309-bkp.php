<?php
 /* pChart library inclusions */
 include_once("../Includes/pChart2.1.1/class/pData.class.php");
 include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 include_once("../Includes/pChart2.1.1/class/pImage.class.php");
 include_once("../Includes/pChart2.1.1/class/pRadar.class.php");

/* CAT:Radar Chart */
   
function generateChart($items, $type='P') {
	/* Fetch data */
	foreach ($items as $i) {
		$data[] = $i->qtde;		
		$labels[] = utf8_decode($i->nome);
	}

  /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints($data,"DataPoints");  
 $MyData->setSerieDescription("DataPoints","Quantidade de Respondentes");
 $MyData->setPalette("DataPoints",array("R"=>0,"G"=>102,"B"=>153));
 
 /* Define the absissa serie */
 $MyData->addPoints($labels,"Labels");
 $MyData->setAbscissa("Labels");

 /* Create the pChart object */
 $myPicture = new pImage(700,460,$MyData);

 /* Draw a solid background */
 $Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 //$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

 /* Overlay some gradient areas */
 //$Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 //$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
 $myPicture->drawGradientArea(0,0,700,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,459,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>16));
 $myPicture->drawText(350,22," Quantidade de Respondentes na Categoria FRACA no estilo PC - $type",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>14,"R"=>40,"G"=>40,"B"=>40));

 /* Enable shadow computing */ 
 //$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 $SplitChart = new pRadar();

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(25,40,690,440);
 $Options = array("Factors"=>array(2,5),"Weight"=>1,
						"Layout"=>RADAR_LAYOUT_CIRCLE,"LabelPos"=>RADAR_LABELS_HORIZONTAL,
						//"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>20,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>5),
						"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>30,"EndR"=>150,"EndG"=>150,"EndB"=>150,"EndAlpha"=>10), 
						"FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf",
						"FontSize"=>6);
 $SplitChart->drawRadar($myPicture,$MyData,$Options);

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "../_tmp/__".$usr->userid."_report_6_$type.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>