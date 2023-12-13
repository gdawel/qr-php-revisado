<?php

/**
 * 
 * RELATORIO DE SITUACOES DE VULNERABILIDADE
 * 
 * */

require_once ('../Includes/fpdf_htmltable.php');
require_once ('../App_Code/Pesquisa.class');
require_once ('../App_Code/User.class');
require_once ('../App_Code/Questionario.class');
require_once ('../App_Code/Report_VulnerabilidadesFortalezas.class');
require_once ('../App_Code/CommonFunctions.php');
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
        $this->Image("ReportImages/report_intro_$pacoteTipoId"."_vuln.png", 45, null, 125);

			//Report title
		  	$this->SetFillColor(240);        
		  	$this->SetTextColor(99);
	  		$this->SetFont('Verdana', '', 12);
			$this->Cell(25);
			$this->Cell(125, 10, utf8_decode($this->title), 0, 0, 'C', true);	
			
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
			$this->Cell(0,4,utf8_decode($this->title),0,0);
			
							
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
        $this->Cell(0, 6, utf8_decode('Sociedade Brasileira de Resiliência | www.sobrare.com.br'), 0,
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
        $this->Cell(0, 6, utf8_decode($label), 0, 1, $align, false);

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
                $this->MultiCellBullet(0, 5, utf8_decode(str_replace('-- ', '', $t)));
                $this->Ln(2);
            } else {
                //Normal paragr
                $this->MultiCell(0, 5, utf8_decode($t));
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
            $this->MultiCell(0, 5, utf8_decode($t));
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
        $this->Cell(85, 0, utf8_decode(strtoupper($this->title)), 0, 1);
 	 		$this->Ln(12);*/        
        
        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 5, 'PESQUISA', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->MultiCell(110, 5, utf8_decode($pesquisa->titulo), 0, 1, false);
    }    
}


$pdf = new PDF();
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = 'Relatório das Situações de Vulnerabilidades na Equipe';
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
    echo utf8_decode("Acesso negado a este relatório.");
    return;
}

if (!$pesquisa->isProdutoAdquirido(7)) {
    echo utf8_decode("Acesso negado a este produto.");
    return;
}

$report = new ReportGlobalSituacoesVulnerabilidades($pesquisa);

//Capa
$pdf->Capa($pesquisa);

//Get report text sections
$reportsections = $pesquisa->modeloquestionario->getReportSections(REPORT_SITUACOES_VULNERABILIDADE);

//Intro
$first_section = true;
$section = $reportsections[1];
$pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);


//Vulneraveis
$first_section = false;
$section = $reportsections[2];
$pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);

$pdf->SetFont('Verdana', 'B', 10);
$pdf->SetTextColor(0);
$pdf->Cell(85, 9, utf8_decode('Respondentes na Situação de Vulnerabilidade Cognitiva'), 0, 1);


$pdf->SetFont('Verdana', '', 9);
$pdf->SetFillColor(245);        
$pdf->SetTextColor(0);

$items = $report->getDataItems();
$count = 0;
$items_count = 0;
if ($items) {
	foreach ($items as $q) {
		$count++;
		$items_count++;
			
		//Espaco para centralizar
		if ($count == 1) $pdf->Cell(25, 8, ' ', 0, 0, 'C');
	
		//print respondente
		$pdf->Cell(30, 8, utf8_decode($q->id), 0, 0, 'C', true);
		$pdf->Cell(1, 8, ' ', 0, 0, 'C', false);
	
		//nova linha
		if ($count == 4) {
			$pdf->Ln(10);
			$count = 0;
		} 
	}
	$pdf->Ln(10);
	$pdf->SetFont('Verdana', 'B', 10);
	$pdf->SetTextColor(0);
	//$pdf->Cell(85, 4, utf8_decode("[N = $items_count]"), 0, 1);
	$pdf->Cell(85, 4, utf8_decode("[N = $pesquisa->count_concluidos]"), 0, 1);
	$pdf->SetFont('Verdana', '', 8);
	$pdf->Cell(85, 4, utf8_decode("Fonte: Base de dados da SOBRARE"), 0, 1);
} else {
	$pdf->SetFont('Verdana', '', 9);
	$pdf->Cell(90, 9, utf8_decode('Nenhum respondente em situação de vulnerabilidade.'), 0, 1, 'L', true);	
}
 

//conclusao
$first_section = false;
$section = $reportsections[3];
$pdf->ChapterTitle('', !$first_section, $first_section, !$first_section);
$pdf->ChapterBody($section->texto);



//Notas finais
if (isset($reportsections['99'])) {
    //$pdf->Ln(12);
    $pdf->AddPage();
    $pdf->ChapterTitle($reportsections['99']->title, true, true, false);
    $pdf->ChapterNotes($reportsections['99']->texto);
}

$pdf->Output('Relatorio_Situacoes.pdf', 'D');
?>