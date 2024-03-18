<?php
 /* pChart library inclusions */
 include_once("../Includes/pChart2.1.1/class/pData.class.php");
 include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 include_once("../Includes/pChart2.1.1/class/pImage.class.php");


/* CAT:Bar Chart - Distribuicao dos Indices no MCD */
   
function generateBarChart($dataValues, $fator) {
 /* Create and populate the pData object */
 $MyData = new pData();  
 //order data by key
 ksort($dataValues);
 $MyData->addPoints($dataValues,"Nъmero de Respondentes no Нndice");
 $MyData->setAxisName(0, "Nъmero de Respondentes");
 $MyData->addPoints(array("PC-P\nCondiзгo\nFRACA","PC-P\nCondiзгo\nMODERADA","PC-P\nCondiзгo\nBOA","PC-P\nCondiзгo\nFORTE","PC-E\nEQUILНBRIO","PC-I\nCondiзгo\nFORTE","PC-I\nCondiзгo\nBOA","PC-I\nCondiзгo\nMODERADA","PC-I\nCondiзгo\nFRACA"), "Labels");
 $MyData->setSerieDescription("Labels", "Classificaзгo");
 $MyData->setAbscissa("Labels");
 $MyData->setPalette("Nъmero de Respondentes no Нndice",array("R"=>0,"G"=>102,"B"=>153));

 /* Create the pChart object */
 $myPicture = new pImage(700,490,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,489,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>10));
 $myPicture->drawGradientArea(1,1,698,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));
 $myPicture->drawText(350,25,"Distribuiзгo dos Нndices no MCD '$fator->nome'",array("FontSize"=>16,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawText(350,55,$fatorNome,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(40,70,690,440);

 /* Draw the scale */
 $scaleSettings = array("Mode"=>SCALE_MODE_START0,/*"Factors"=>array(1,2,5),"MinDivHeight"=>100,*/
 								"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);

 /* Write the chart legend */
 //$myPicture->drawLegend(500,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $settings = array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_MANUAL,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0,"Surrounding"=>20,"LabelPos"=>LABEL_POS_TOP);
 $myPicture->drawBarChart($settings);

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "../_tmp/__".$usr->userid."_report_16_".$fator->id."_1.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>