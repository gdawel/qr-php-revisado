<?php
 /* pChart library inclusions */
 include_once("../Includes/pChart2.1.1/class/pData.class.php");
 include_once("../Includes/pChart2.1.1/class/pDraw.class.php");
 include_once("../Includes/pChart2.1.1/class/pImage.class.php");


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
 $MyData = new pData();  
 $MyData->addPoints($dataValues,"Populaчуo desta Pesquisa");
 $MyData->addPoints($dataValuesPopulacaoBase, "Populaчуo-Base");
 $MyData->setSerieWeight("Populaчуo desta Pesquisa",1);
 $MyData->setSerieWeight("Populaчуo-Base",1); 
 $MyData->setAxisUnit(0, '%');
 $MyData->setAxisName(0, "Nњmero de Respondentes");
 $MyData->addPoints(array("PC-P\nCondiчуo\nFRACA","PC-P\nCondiчуo\nMODERADA","PC-P\nCondiчуo\nBOA","PC-P\nCondiчуo\nFORTE","PC-E\nEQUILЭBRIO","PC-I\nCondiчуo\nFORTE","PC-I\nCondiчуo\nBOA","PC-I\nCondiчуo\nMODERADA","PC-I\nCondiчуo\nFRACA"), "Labels");
 $MyData->setSerieDescription("Labels", "Classificaчуo");
 $MyData->setAbscissa("Labels");
 $MyData->setPalette("Populaчуo desta Pesquisa",array("R"=>0,"G"=>102,"B"=>153));

 /* Create the pChart object */
 $myPicture = new pImage(700,490,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,489,array("R"=>204,"G"=>204,"B"=>204));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/Forgotte.ttf","FontSize"=>10));
 $myPicture->drawGradientArea(1,1,698,30,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>250,"EndG"=>250,"EndB"=>250,"Alpha"=>100));
 $myPicture->drawText(350,25,"Comparativo das populaчѕes no MCD '$fator->nome'",array("FontSize"=>16,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawText(350,55,$fatorNome,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(40,50,680,430);

 /* Draw the scale */ 
 //$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
 $scaleSettings = array(//"Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,
 								"Mode"=>SCALE_MODE_START0, 								
 								"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,
 								"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE); 
 $myPicture->drawScale($scaleSettings); 

 /* Turn on Antialiasing */ 
 $myPicture->Antialias = TRUE; 

 /* Draw the line chart */ 
 $myPicture->drawSplineChart(); 

 /* Write the chart legend */
 $myPicture->setFontProperties(array("FontName"=>"../Includes/pChart2.1.1/fonts/pf_arma_five.ttf","FontSize"=>8)); 
 $myPicture->drawLegend(430,50,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

 /* Render the picture */
 $usr = Users::getCurrent();
 $chartFilename = "../_tmp/__".$usr->userid."_report_16_".$fator->id."_2.png";
 $myPicture->render($chartFilename);
 return $chartFilename;
}
?>