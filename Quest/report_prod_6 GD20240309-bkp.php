<?php

/**
 * 
 * RELATORIO DE CONDICOES FRACAS
 * 
 * */

require_once ('../Includes/fpdf_htmltable.php');
require_once ('../App_Code/Pesquisa.class.php');
require_once ('../App_Code/User.class.php');
require_once ('../App_Code/Questionario.class.php');
require_once ('../App_Code/Report_VulnerabilidadesFortalezas.class.php');
require_once ('../App_Code/CommonFunctions.php');
require_once ('../App_Code/FileHandler.class.php');
require_once ('report_prod_6.radarchart.php');
ob_clean();

class PDF extends FPDFWithHtmlTable
{
    private $clientelogofilename;
	
    function setClienteLogoFilename($pesquisaid)
    {
        $this->clientelogofilename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
    }

    function Capa($pesquisa)
    {
        $this->AddPage();

			//Logo
        $this->Image('../CSS/Images/logo_quest.jpg', $this->lMargin, 8, 37);
        //Cliente Logo
        if (file_exists($this->clientelogofilename)) //Identificacao
            $this->Image($this->clientelogofilename, null, 8, 0, 7, '', '', 'R');

        $this->Ln(36);
        //Logo report
        $pacoteTipoId = $pesquisa->modeloquestionario->tipo->id;
        $this->Image("ReportImages/report_intro_$pacoteTipoId"."_fraca.png", 45, null, 125);

			//Report title
		  	$this->SetFillColor(240);        
		  	$this->SetTextColor(99);
	  		$this->SetFont('Verdana', '', 12);
			$this->Cell(25);
			$this->Cell(125, 10, convertIsoUtf($this->title), 0, 0, 'C', true);	
			
        $this->Ln(24);
        $this->TableIdentificacao($pesquisa);
        $this->AddPage();
    }

    function Header()
    {
        //Primeira pag sem header
        if ($this->PageNo() == '1')
            return;

        global $title;

        //Logo
        $this->Image('../CSS/Images/logo_quest.jpg', $this->lMargin, 8, 37);
        //Cliente Logo
        if (file_exists($this->clientelogofilename)) //Identificacao
			$this->Image($this->clientelogofilename, null, 8, 0, 7, '', '', 'R');

			//title
			$this->SetFont('Verdana', '', 8);
			$this->Cell(55,4);
			$this->Cell(0,4,convertIsoUtf($this->title),0,0);

        //Bottom border
        $this->SetDrawColor(99, 99, 99);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, 20, $this->w - $this->rMargin, 20);

        //Line break
        $this->Ln(15);
    }

    function Footer()
    {
        //Primeira pag sem footer
        if ($this->PageNo() == '1')
            return;

        //Position in mm from bottom
        $this->SetY(-14);
        //Font
        $this->SetFont('Arial', 'I', 8);
        //Text color
        $this->SetTextColor(128);
        //Text background
        //$this->SetFillColor(250, 216, 156);
        //Page number and text
        $this->Cell(0, 6, convertIsoUtf('Sociedade Brasileira de Resiliência | www.sobrare.com.br'), 0,
            0, 'L', false, 'http://www.sobrare.com.br');
        $this->Cell(0, 6, $this->PageNo(), 0, 0, 'R');

        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->h - 14, $this->w - $this->rMargin, $this->h - 14);
    }

    function ChapterTitle($label, $subtitle = false, $addspaceafter = true, $addspacebefore = true, $align = 'L')
    {
        if (!$subtitle) {
            //Pre spacing
            if ($addspacebefore)
                $this->Ln(10);
            //Color and font
            //$this->SetTextColor(56, 130, 170);
            $this->SetTextColor(34, 35, 93);
            $this->SetFont('Verdana', 'B', 16);
        } else {
            //Pre spacing
            if ($addspacebefore)
                $this->Ln(8);
            //Color and font
            //$this->SetTextColor(31, 78, 123);
            $this->SetTextColor(34, 35, 93);
            $this->SetFont('Verdana', 'B', 14);
        }

        //Title
        $this->MultiCell(0, 6, convertIsoUtf($label), 0, $align, false);

        //Line break
        if ($addspaceafter)
            $this->Ln(3);
    }

    function ChapterBody($txt, $style = '')
    {
        //Font
        $this->SetFont('Arial', $style, 10);
        $this->SetTextColor(0);

        $txt = explode("\n", $txt);
        foreach ($txt as $t) {
            if (substr($t, 0, 3) == '-- ') {
                //Bullet paragraph
                $this->MultiCellBullet(0, 5, convertIsoUtf(str_replace('-- ', '', $t)));
                $this->Ln(2);
            } else {
                //Normal paragr
                $this->MultiCell(0, 5, convertIsoUtf($t));
                //Line break and spacing
                $this->Ln(4);
            }
        }
    }

    function ChapterNotes($txt)
    {
        //Font
        $this->SetFont('Times', '', 10);
        $this->SetTextColor(0);

        $txt = explode("\n", $txt);
        foreach ($txt as $t) {
            $this->MultiCell(0, 5, convertIsoUtf($t));
            //Line break and spacing
            $this->Ln(1);
        }
    }

    //MultiCell with bullet
    function MultiCellBullet($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        //Set bullet char
        $blt = chr(149);

        //Get bullet width including margins
        $blt_width = $this->GetStringWidth($blt) + $this->cMargin * 2;

        //Save x
        $bak_x = $this->x;

        //Output bullet
        $this->Cell($blt_width * 2);
        $this->Cell($blt_width, $h, $blt, 0, '', $fill);

        //Output text
        if ($w == 0) {
            $w = $this->w - $this->lMargin - $this->rMargin;
        }
        $this->MultiCell($w - $blt_width * 3, $h, $txt, $border, $align, $fill);

        //Restore x
        $this->x = $bak_x;
    }
	 
	  function TableIdentificacao($pesquisa) {
			global $pesquisa;
	     	
        $l = 32;

			/*
			//titulo
        $this->Cell($l);
        $this->SetFont('Verdana', '', 11.7);
        $this->SetTextColor(99);
        $this->Cell(85, 0, convertIsoUtf(strtoupper($this->title)), 0, 1);
 	 		$this->Ln(12);*/        
        
        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 5, 'PESQUISA', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->MultiCell(105, 5, convertIsoUtf($pesquisa->titulo), 0, 1);
    }    
}


$pdf = new PDF();
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = 'Relatório das Condições de Fraca Resiliência na Equipe';
$pdf->SetTitle($title);
$pdf->SetAuthor('SOBRARE - Sociedade Brasileira de Resiliência');
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(15);

//Load data
$pesquisaid = getIntQueryString('id', 0, true);

//Check permission
$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($pesquisaid);
if ((!isset($pesquisa)) || ($pesquisa->isAccessDenied())) {
    echo convertIsoUtf("Acesso negado a este relatório.");
    return;
}

if (!$pesquisa->isProdutoAdquirido(6)) {
    echo convertIsoUtf("Acesso negado a este produto.");
    return;
}

//Generate chart
$report = new ReportGlobalCondicaoResiliencia($pesquisa);

//Capa
$pdf->Capa($pesquisa);

//Get report text sections
$reportsections = $pesquisa->modeloquestionario->getReportSections(REPORT_CONDICOES_FRACA_RESILIENCA);

//Intro
$first_section = true;
$section = $reportsections[1];
$pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);


//PC-P
$pdf->AddPage();
$first_section = true;
$section = $reportsections[2];
$pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);

$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
$pdf->Cell(85, 9, convertIsoUtf('Tabela: Condições de Fraca Resiliência na equipe no PC - P'), 0, 10);

$items = $report->getFracaResilienciaItems('P');
$items_count = 0;
//Cabecalho
$pdf->SetFillColor(240);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Verdana', 'B', 10);
$pdf->Cell(55, 8, convertIsoUtf('MCD'), 0, 0, 'C', true);
$pdf->Cell(15, 8, convertIsoUtf('Qtde'), 0, 0, 'C', true);
$pdf->Cell(105, 8, convertIsoUtf('Comentários'), 0, 0, 'C', true);
$pdf->Ln(8);

foreach ($items as $fator) {	
	$pdf->SetFillColor(245);        
  	$pdf->SetTextColor(0);
	$pdf->SetFont('Verdana', '', 10);
	$pdf->Cell(55, 8, convertIsoUtf($fator->nome), 0, 0, 'L', true);
	
	$pdf->SetFillColor(240);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('Verdana', '', 14);
	$pdf->Cell(15, 8, convertIsoUtf($fator->qtde), 0, 0, 'C', true);	
	
	$pdf->SetFillColor(250); 
  	$pdf->SetTextColor(99);
	$pdf->SetFont('Verdana', '', 8);
	if (strlen(convertIsoUtf($fator->descricaoFracaResilienciaPCI)) > 70) $line_height = 4; else $line_height = 8;
	$pdf->MultiCell(105, $line_height, convertIsoUtf($fator->descricaoFracaResilienciaPCI), 0, 'L', true);

	$pdf->Ln(1);
	$items_count += $fator->qtde;
}
$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
//$pdf->Cell(85, 4, convertIsoUtf("[N = $items_count]"), 0, 1);
$pdf->Cell(85, 4, convertIsoUtf("[N = $pesquisa->count_concluidos]"), 0, 1);
$pdf->SetFont('Verdana', '', 8);
$pdf->Cell(85, 4, convertIsoUtf("Fonte: Base de dados da SOBRARE"), 0, 1);
 

//Grafico PC-P
$pdf->AddPage();
$first_section = false;
$section = $reportsections[3];
$pdf->ChapterBody($section->texto);

$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
$pdf->Cell(85, 9, convertIsoUtf('Gráfico: Categoria FRACA no estilo PC - P'), 0, 1);

$imgWidth = 172;
$filename = generateChart($items, 'P');
$pdf->Image("../_tmp/$filename", $pdf->lMargin+($pdf->w - 2*$pdf->lMargin - $imgWidth )/2 + 2, null, $imgWidth );

$pdf->SetTextColor(0);
$pdf->SetFont('Verdana', '', 8);
$pdf->Cell(85, 4, convertIsoUtf("Fonte: Base de dados da SOBRARE"), 0, 1);



//PC-I
$pdf->AddPage();
$first_section = true;
$section = $reportsections[4];
$pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);

$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
$pdf->Cell(85, 9, convertIsoUtf('Tabela: Condições de Fraca Resiliência na equipe no PC - I'), 0, 1);


$items = $report->getFracaResilienciaItems('I');
$items_count = 0;
//Cabecalho
$pdf->SetFillColor(240);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Verdana', 'B', 10);
$pdf->Cell(55, 8, convertIsoUtf('MCD'), 0, 0, 'C', true);
$pdf->Cell(15, 8, convertIsoUtf('Qtde'), 0, 0, 'C', true);
$pdf->Cell(105, 8, convertIsoUtf('Comentários'), 0, 0, 'C', true);
$pdf->Ln(8);
foreach ($items as $fator) {	
	$pdf->SetFillColor(245);        
  	$pdf->SetTextColor(0);
	$pdf->SetFont('Verdana', '', 10);
	$pdf->Cell(55, 8, convertIsoUtf($fator->nome), 0, 0, 'L', true);
	
	$pdf->SetFillColor(240);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('Verdana', '', 14);
	$pdf->Cell(15, 8, convertIsoUtf($fator->qtde), 0, 0, 'C', true);
	
	$pdf->SetFillColor(250); 
  	$pdf->SetTextColor(99);
	$pdf->SetFont('Verdana', '', 8);
	if (strlen(convertIsoUtf($fator->descricaoFracaResilienciaPCP)) > 70) $line_height = 4; else $line_height = 8;
	$pdf->MultiCell(105, $line_height, convertIsoUtf($fator->descricaoFracaResilienciaPCP), 0, 'L', true);

	$pdf->Ln(1);
	$items_count += $fator->qtde;
}
$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
//$pdf->Cell(85, 4, convertIsoUtf("[N = $items_count]"), 0, 1);
$pdf->Cell(85, 4, convertIsoUtf("[N = $pesquisa->count_concluidos]"), 0, 1);
$pdf->SetFont('Verdana', '', 8);
$pdf->Cell(85, 4, convertIsoUtf("Fonte: Base de dados da SOBRARE"), 0, 1);


//Grafico PC-I
$pdf->AddPage();
$first_section = true;
$section = $reportsections[5];
$pdf->ChapterBody($section->texto);

$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
$pdf->Cell(85, 9, convertIsoUtf('Gráfico: Categoria FRACA no estilo PC - I'), 0, 1);

$filename = generateChart($items, 'I');
$pdf->Image("../_tmp/$filename", $pdf->lMargin+($pdf->w - 2*$pdf->lMargin - $imgWidth )/2 + 2, null, $imgWidth );

$pdf->SetTextColor(0);
$pdf->SetFont('Verdana', '', 8);
$pdf->Cell(85, 4, convertIsoUtf("Fonte: Base de dados da SOBRARE"), 0, 1);



//Notas finais
if (isset($reportsections['99'])) {
    //$pdf->Ln(12);
    $pdf->AddPage();
    $pdf->ChapterTitle($reportsections['99']->title, true, true, false);
    $pdf->ChapterNotes($reportsections['99']->texto);
}

$pdf->Output('Relatorio_Condicoes.pdf', 'D');
?>