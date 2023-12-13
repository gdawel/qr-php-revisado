<?php
/*********************************
* RELATÓRIO GAMA GERAL
**********************************/

require_once ('../Includes/fpdf_htmltable.php');
require_once ('../App_Code/Pesquisa.class');
require_once ('../App_Code/Questionario.class');
require_once ('../App_Code/ModeloQuestionario.class');
require_once ('../App_Code/CommonFunctions.php');

ob_end_clean();

class PDF extends FPDFWithHtmlTable
{
    private $clientelogofilename;

    function setClienteLogoFilename($pesquisaid)
    {
        $this->clientelogofilename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
    }

    function Capa($quest)
    {
        global $pesquisa;

        $this->AddPage();

        //Logo
        $this->Image('../CSS/Images/logo_quest.jpg', $this->lMargin, 8, 37);
        //Logo cliente
        if (file_exists($this->clientelogofilename)) //Identificacao
            $this->Image($this->clientelogofilename, null, 8, 0, 7, '', '', 'R');

        $this->Ln(36);
        
        //Report logo
        $tipoModeloQuestionarioId = $pesquisa->modeloquestionario->tipo->id;
        $this->Image("ReportImages/report_intro_$tipoModeloQuestionarioId"."_basico.png", 45, null, 125);

		//Identificacao
        $this->Ln(24);
        $this->TableIdentificacao($quest->infos);
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
        $this->Line($this->lMargin, $this->h - 14, $this->w - $this->rMargin, $this->h -
            14);
    }

    function ChapterTitle($label, $subtitle = false, $addspaceafter = true, $addspacebefore = true)
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
        $this->Cell(0, 6, utf8_decode($label), 0, 1, 'L', false);

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

    function TableIdentificacao($data)
    {
        $l = 32;

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(30, 0, 'RESPONDENTE', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', 'B', 12);
        $this->SetTextColor(0);
        $this->Cell(75, 10, utf8_decode($data['Nome']), 0, 1);

        $this->Ln(3);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, 'E-MAIL', 0, 0);
        $this->Cell(30, 0, 'SEXO', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, utf8_decode($data['Email']), 0, 0);
        $this->Cell(30, 9, utf8_decode($data['Sexo']), 0, 1);

        $this->Ln(3);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, 'DATA DE NASCIMENTO', 0, 0);
        $this->Cell(30, 0, utf8_decode('CÓDIGO QUEST'), 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, utf8_decode(date('d/m/Y', strtotime($data['DataNascimento']))), 0, 0);
        $this->Cell(30, 9, utf8_decode($data['QuestionarioId']), 0, 1);

        $this->Ln(6);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 5, 'PESQUISA', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->MultiCell(105, 5, utf8_decode($data['Pesquisa']), 0, 1);
    }    
}

//Current user
$usr = Users::getCurrent();

//Get Quest
$quests = new Questionarios();
$questid = getIntQueryString('id', 0, true);
if (!$questid) {
    echo utf8_decode("Questionário não encontrado.");
    return;
}
$quest = $quests->item($questid);
if (!$quest) {
    echo utf8_decode("Questionário não encontrado.");
    return;
}
$modelos = new ModelosQuestionarios();
$modelo = $modelos->item($quest->modeloquestionarioid);


//Check permission
$pesquisaid = (getIntQueryString('aglutinadoraId', null)) ? 
						getIntQueryString('aglutinadoraId', 0) : $quest->pesquisaid; 

$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($pesquisaid);
if ((!$pesquisa) || ($pesquisa->isAccessDenied())) {
    echo utf8_decode("Acesso negado a este relatório.");
    return;
}

//Verifica se produto foi adquirido
if (!$pesquisa->isProdutoAdquirido(25)) {
	echo utf8_decode("Acesso negado a este relatório.");
   return;
}

//Verifica se quest está associado a pesquisa em questao. Usado principalmente para as 
//pesquisas aglutinadoras
if (!$pesquisa->hasQuest($quest->id)) {
	echo utf8_decode("Questionário não está associado a esta pesquisa.");
   return;
}

//Release objs
$pesquisas = null;
$modelos = null;
$quests = null;

//Start pdf
$pdf = new PDF();
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = 'Relatório Gama-Geral';
$pdf->SetTitle($title);
$pdf->SetAuthor('SOBRARE - Sociedade Brasileira de Resiliência');
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(15);

//Set logo
$pdf->setClienteLogoFilename($pesquisa->id);
$pesquisa->modeloquestionario = $modelo;

//Capa
$pdf->Capa($quest);
$pdf->AddPage();

//Instance final Chart
//$chart_dest = imagecreatefrompng('ReportImages/chart_base.png');


//Print Report Sections
$first_section = true;
if ($modelo->reportsections) {
    foreach ($modelo->reportsections as $section) {
        if ($section->posicao != 99) { //posicao 99 é notas de fim de report
            $pdf->ChapterTitle($section->title, !$first_section, $first_section, !$first_section);
            $pdf->ChapterBody($section->texto);

            $first_section = false;
        }
    }
}


//Print Fatores
$y_mult = 0;

foreach ($quest->fatores as $fator) {
    $pdf->AddPage();
    $pdf->ChapterTitle("Resultado da área $fator->nome ($fator->sigla)", false, true, false);
    //$pdf->ChapterTitle("$fator->nome ($fator->sigla)", true, true, false);
    //$pdf->ChapterBody($fator->descricao);

    //$pdf->Ln();
    //$pdf->ChapterBody('MCD característico do PC ' . $fator->valordescricao, 'B');
	// $pdf->ChapterBody("-- Posição Resiliente: $fator->classificacao");
	// $pdf->ChapterBody("-- $fator->classificacaodetalhada");
	 
	 //$pdf->Ln();	 
	 //$pdf->ChapterBody($fator->devolutiva);
    
   	$pdf->ChapterBody($fator->devolutivadetalhamento);  
}

//Notas finais
if (isset($modelo->reportsections['99'])) {
    //$pdf->Ln(12);
    $pdf->AddPage();
    $pdf->ChapterTitle($modelo->reportsections['99']->title, true, true, false);
    $pdf->ChapterNotes($modelo->reportsections['99']->texto);
}

$pdf->Output('Relatorio.pdf', 'D');
?>