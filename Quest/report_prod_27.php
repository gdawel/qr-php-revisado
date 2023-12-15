<?php

/*********************************
* Relatório de Mapeamento – Trabalho (Produto #27)
* Relatório de Mapeamento – Acadêmico (Produto #28)
**********************************/

require_once ('../Includes/fpdf_htmltable.php');
require_once ('../App_Code/Pesquisa.class.php');
require_once ('../App_Code/Questionario.class.php');
require_once ('../App_Code/ModeloQuestionario.class.php');
require_once ('../App_Code/CommonFunctions.php');

ob_end_clean();

class PDF extends FPDFWithHtmlTable
{
    private $clientelogofilename;

    function setClienteLogoFilename($pesquisaid)
    {
        $this->clientelogofilename = "../Uploads/Clientes/logo_cliente_$pesquisaid.jpg";
    }

    function Capa()
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
        $this->Image("ReportImages/report_intro_$tipoModeloQuestionarioId"."_general.png", 45, null, 125);

        //Identificacao
        $this->Ln(24);
        $this->TableIdentificacao();
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
        $this->Line($this->lMargin, $this->h - 14, $this->w - $this->rMargin, $this->h - 14);
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
        //$this->Cell(0, 6, utf8_decode($label), 0, 1, 'L', false);
        $this->MultiCell(0, 6, utf8_decode($label), 0, 'L', false);

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

    function TableIdentificacao()
    {
        global $pesquisa;
        
        $l = 32;
        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 5, 'PESQUISA', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->MultiCell(110, 5, utf8_decode($pesquisa->titulo), 0);
    }
}


function RenderTabelaIdentificacaoDetalhada($fatorId) {
    global $pdf, $pesquisa;
    
    //for header
    $pdf->multiCellTableAligns = array('C', 'C', 'C');
    $pdf->multiCellTableWidths = array(200, 245, 250);
    $pdf->multiCellLineHeight = 4;
    $pdf->SetFillColor(235, 235, 235);
    $pdf->MutiCellTableRow(array('Estilo', 'Respondentes', utf8_decode('Objetivos da Capacitação')), true);
    
    //for rows
    $pdf->multiCellTableAligns = array('L', 'C', 'L');
    
    //ordem dos estilos
    $estilos = array('Excelente', 
                        'P - Tipo 1', 'I - Tipo 1', 
                        'P - Tipo 2', 'I - Tipo 2',
                        'P - Tipo 3', 'I - Tipo 3',
                        'P - Tipo 4', 'I - Tipo 4');
    
    //render them
    foreach ($estilos as $e) {
        $vr = $pesquisa->modeloquestionario->getValorReferenciaByDescricao($fatorId, $e);
        if ($vr)
            $pdf->MutiCellTableRow(array(utf8_decode($vr->estilo), getQuestionarioListByClassificacao($fatorId, $e),  utf8_decode($vr->objetivoscapacitacao))); 
    }
}


/**
 * Retorna uma string dos IDs dos questionarios que atendem à descricao de determinado fator.
 * 
 * @param mixed $fatorId ID do Fator MCD.
 * @param mixed $fatorDescricao A Classificação a ser renderizada. Por exemplo, 'Excelente', 'P - Tipo 4', 'I - Tipo 3', etc...
 * @return
 */
function getQuestionarioListByClassificacao($fatorId, $fatorDescricao) {
    global $pesquisa;
    
    $concluidos = $pesquisa->getQuestionariosConcluidos();
    
    //if no quests
    if (!$concluidos) return " ";
    
    foreach ($concluidos as $q) {
        if ($q->fatores[$fatorId]->valordescricao == $fatorDescricao)
            $lst[] = $q->id;
    } 
    
    return (isset($lst)) ? join(', ', $lst) : " ";
}

//Current user
$usr = Users::getCurrent();

//Get PesquisaId and check permission
$pesquisaid = (getIntQueryString('aglutinadoraId', null)) ? 
						getIntQueryString('aglutinadoraId', 0) : getIntQueryString('id', 0); 

$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($pesquisaid);
if ((!isset($pesquisa)) || ($pesquisa->isAccessDenied())) {
    echo utf8_decode("Acesso negado a este relatório.");
    return;
}

if (!$usr->isinrole('Admin')) {//admin pode acessar relatório
    if (!$pesquisa->isProdutoAdquirido(27) && !$pesquisa->isProdutoAdquirido(28)) {
        echo utf8_decode("Acesso negado a este relatório. Produto não adquirido.");
        return;
    }
}

//Release DataAccess objs
$pesquisas = null;
$modelos = null;
$quests = null;

//Start pdf
$pdf = new PDF();
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = 'Relatório de Mapeamento';
$pdf->SetTitle($title);
$pdf->SetAuthor('SOBRARE - Sociedade Brasileira de Resiliência');
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(15);

//Set logo
$pdf->setClienteLogoFilename($pesquisa->id);

//Capa
$pdf->Capa();
$pdf->AddPage();

//Print Report Sections
$reportsections = $pesquisa->modeloquestionario->getReportSections(REPORT_MAPEAMENTO);

$first_section = true;
if ($reportsections) {
    foreach ($reportsections as $section) {
        if ($section->posicao != 99) { //posicao 99 é notas de fim de report
            //add page, if requested
            if ($section->addpagebreakbefore) {
                $pdf->AddPage();
                $first_section = true;
            }
            
            $pdf->ChapterTitle($section->title, false, true, !$first_section);
            $pdf->ChapterBody($section->texto);

            //Renderiza a tabela se for uma seçao relacionada a um MCD
            if ($section->fatorid)
                RenderTabelaIdentificacaoDetalhada($section->fatorid);
            
            $first_section = false;
        }
    }
}


//Notas finais
if (isset($reportsections['99'])) {
    //$pdf->Ln(12);
    $pdf->AddPage();
    $pdf->ChapterTitle($reportsections['99']->title, true, true, false);
    $pdf->ChapterNotes($reportsections['99']->texto);
}

$pdf->Output('Relatorio_Mapeamento.pdf', 'D');
?>