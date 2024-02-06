<?php
/*********************************
* RELATÓRIO ALFA: Coaching ($comentado == 1) E BETA: Promotor ($comentado == 0)
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

    function Capa($quest, $isRelatorioDetalhado)
    {
        global $pesquisa;

        $this->AddPage();

        //Logo
        $this->Image('../CSS/Images/logo_quest.jpg', $this->lMargin, 8, 37);
        //Logo cliente
        //if (file_exists($this->clientelogofilename)) //Identificacao
	    //    $this->Image($this->clientelogofilename, null, 8, 0, 7, '', '', 'R');
				
        $this->Ln(36);
        
        //Report logo
        $tipoModeloQuestionarioId = $pesquisa->modeloquestionario->tipo->id;
        //$this->Image("ReportImages/report_intro_$pacoteTipoId.png", 45, null, 125);
        if ($isRelatorioDetalhado)
        		$this->Image("ReportImages/report_intro_$tipoModeloQuestionarioId"."_detalhado.png", 45, null, 125);        
			else
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
        $rodapeSobrare = mb_convert_encoding('Sociedade Brasileira de Resiliência | www.sobrare.com.br', 'ISO-8859-1','UTF-8');
        $this->Cell(0, 6, $rodapeSobrare, 0,
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
        $this->Cell(0, 6, mb_convert_encoding($label, 'ISO-8859-1','UTF-8'), 0, 1, 'L', false);

        //Line break
        if ($addspaceafter)
            $this->Ln(1);
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
                $this->MultiCellBullet(0, 5, mb_convert_encoding(str_replace('-- ', '', $t), 'ISO-8859-1','UTF-8'));
                $this->Ln(2);
            } else {
                //Normal paragr
                $this->MultiCell(0, 5, mb_convert_encoding($t, 'ISO-8859-1','UTF-8'));
                //Line break and spacing
                $this->Ln(1);
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
            $this->MultiCell(0, 5, mb_convert_encoding($t, 'ISO-8859-1','UTF-8'));
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
        $this->Cell(75, 10, mb_convert_encoding($data['Nome'], 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Ln(3);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, 'E-MAIL', 0, 0);
        $this->Cell(30, 0, 'SEXO', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, mb_convert_encoding($data['Email'], 'ISO-8859-1','UTF-8'), 0, 0);
        $this->Cell(30, 9, mb_convert_encoding($data['Sexo'], 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Ln(3);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, 'DATA DE NASCIMENTO', 0, 0);
        $this->Cell(30, 0, mb_convert_encoding('CÓDIGO QUEST', 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, mb_convert_encoding(date('d/m/Y', strtotime($data['DataNascimento'])), 'ISO-8859-1','UTF-8'),
            0, 0);
        $this->Cell(30, 9, mb_convert_encoding($data['QuestionarioId'], 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Ln(6);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, 'PESQUISA', 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, mb_convert_encoding($data['Pesquisa'], 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Ln(6);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 8);
        $this->SetTextColor(99);
        $this->Cell(85, 0, mb_convert_encoding('QUESTIONÁRIO CONCLUÍDO EM:', 'ISO-8859-1','UTF-8'), 0, 1);

        $this->Cell($l);
        $this->SetFont('Verdana', '', 10);
        $this->SetTextColor(0);
        $this->Cell(85, 9, mb_convert_encoding(date('d/m/Y', strtotime($data['ConcluidoEm'])), 'ISO-8859-1','UTF-8'), 0, 1);
    }

    function ResultadoFatorTable($resultado)
    {
    		$destaque = 'BGCOLOR="#ff9933"';
        if ($resultado=='P - Tipo 1') $cp1 = $destaque; else $cp1 = '';
        if ($resultado=='P - Tipo 2') $cp2 = $destaque; else $cp2 = '';
        if ($resultado=='P - Tipo 3') $cp3 = $destaque; else $cp3 = '';
        if ($resultado=='P - Tipo 4') $cp4 = $destaque; else $cp4 = '';
        if ($resultado=='I - Tipo 1') $ci1 = $destaque; else $ci1 = '';
        if ($resultado=='I - Tipo 2') $ci2 = $destaque; else $ci2 = '';
        if ($resultado=='I - Tipo 3') $ci3 = $destaque; else $ci3 = '';
        if ($resultado=='I - Tipo 4') $ci4 = $destaque; else $ci4 = '';
        if ($resultado=='Excelente') $exc = $destaque; else $exc = '';
    
		/* DAWEL - Substituindo esse código exdrúxulo abaixo, 
        /*          por um mais coerente com a classe FPDF, que está sendo utilizada
        /*          nesta programação.
        /*          Isso dispensa o uso de mais um classe externa, a fpdf_htmltable.

        $table = "<font face='arial' size='8'>
                    <table border='1'>
                    <tr>
                    <td colspan='4' width='135'>Estilo Comportamental</td>
                    <td width='245' ALIGN='CENTER'>Passividade ao estresse</td>
                    <td width='70' ALIGN='CENTER'>Equilíbrio</td>
                    <td colspan='4' width='245' ALIGN='CENTER'>Intolerância ao estresse</td>
                    </tr>
                    <tr>
                    <td width='135' COLOR='#FFFFFF'>Tipologia do Índice</td>
                    <td width='90' ALIGN='CENTER'>4</td>
                    <td width='65' ALIGN='CENTER'>3</td>
                    <td width='45' ALIGN='CENTER'>2</td>
                    <td width='45' ALIGN='CENTER'>1</td>
                    <td width='70' ALIGN='CENTER'>-</td>
                    <td width='45' ALIGN='CENTER'>1</td>
                    <td width='45' ALIGN='CENTER'>2</td>
                    <td width='65' ALIGN='CENTER'>3</td>
                    <td width='90' ALIGN='CENTER'>4</td>
                    </tr>
                    <tr>
                    <td width='135' COLOR='#000000'>Condição de Resiliência</td>
                    <td width='90' ALIGN='CENTER' $cp4>Fraca</td>
                    <td width='65' ALIGN='CENTER' $cp3>Moderada</td>
                    <td width='45' ALIGN='CENTER' $cp2>Boa</td>
                    <td width='45' ALIGN='CENTER' $cp1>Forte</td>
                    <td width='70' ALIGN='CENTER' $exc>Excelente</td>
                    <td width='45' ALIGN='CENTER' $ci1>Forte</td>
                    <td width='45' ALIGN='CENTER' $ci2>Boa</td>
                    <td width='65' ALIGN='CENTER' $ci3>Moderada</td>
                    <td width='90' ALIGN='CENTER' $ci4>Fraca</td>
                    </tr>
                    <tr>
                    <td width='135'>Situação</td>
                    <td width='90' ALIGN='CENTER'>Vulnerabilidade</td>
                    <td width='65' ALIGN='CENTER'>&nbsp;</td>
                    <td width='45' ALIGN='CENTER'>&nbsp;</td>
                    <td width='45' ALIGN='CENTER'>&nbsp;</td>
                    <td width='70' ALIGN='CENTER'>Segurança</td>
                    <td width='45' ALIGN='CENTER'>&nbsp;</td>
                    <td width='45' ALIGN='CENTER'>&nbsp;</td>
                    <td width='65' ALIGN='CENTER'>&nbsp;</td>
                    <td width='90' ALIGN='CENTER'>Vulnerabilidade</td>
                    </tr>
                    </table>
                    </font>
                    <br /><br />";
        $this->WriteHTML(mb_convert_encoding($table));
        */

       
        $this->SetFont('Verdana','',8);

        $this->Cell(38,6,'Estilo Comportamental','1',0,'L');
        $this->Cell(59,6,'Passividade ao estresse','1',0,'C');
        $equilib = mb_convert_encoding('Equilíbrio', 'ISO-8859-1','UTF-8');
        $intoler = mb_convert_encoding('Intolerância ao estresse', 'ISO-8859-1','UTF-8');
        $this->Cell(18,6,$equilib,'1',0,'C');
        $this->Cell(59,6,$intoler,'1',0,'C');
        $this->Ln();
        $this->SetFont('Arial','',9);
        $tipologia = mb_convert_encoding('Tipologia do Índice', 'ISO-8859-1','UTF-8');
        $this->Cell(38,6,$tipologia,'1',0,'L');
        $this->Cell(21,6,'4','1',0,'C');
        $this->Cell(16,6,'3','1',0,'C');
        $this->Cell(10,6,'2','1',0,'C');
        $this->Cell(12,6,'1','1',0,'C');
        $this->Cell(18,6,'-','1',0,'C');
        $this->Cell(12,6,'1','1',0,'C');
        $this->Cell(10,6,'2','1',0,'C');
        $this->Cell(16,6,'3','1',0,'C');
        $this->Cell(21,6,'4','1',0,'C');
        $this->Ln();

        $this->SetFillColor(255,153,51);
        $resultResiliencia = 'P2';
        $p4 = false;
        $p3 = false;
        $p2 = false;
        $p1 = false;
        $e0 = false;
        $i1 = false;
        $i2 = false;
        $i3 = false;
        $i4 = false;
        switch ($resultado) {
            case 'P - Tipo 4':
                $p4 = true;
                break;
            case 'P - Tipo 3':
                $p3 = true;
                break;
            case 'P - Tipo 2':
                $p2 = true;
                break;
            case 'P - Tipo 1':
                $p1 = true;
                break;
            case 'Excelente':
                $e0 = true;
                break;
            case 'I - Tipo 1':
                $i1 = true;
                break;
            case 'I - Tipo 2':
                $i2 = true;
                break;
            case 'I - Tipo 3':
                $i3 = true;
                break;
            case 'I - Tipo 4':
                $i4 = true;
                break;
            
            default:
            break;
        }
        $condResili = mb_convert_encoding('Condição de Resiliência', 'ISO-8859-1','UTF-8');
        $this->Cell(38,6,$condResili,'1',0,'L');
        $this->SetFont('Arial','',8);
        $this->Cell(21,6,'Fraca','1',0,'C',$p4);
        $this->Cell(16,6,'Moderada','1',0,'C',$p3);
        $this->Cell(10,6,'Boa','1',0,'C',$p2);
        $this->Cell(12,6,'Forte','1',0,'C',$p1);
        $this->Cell(18,6,'Excelente','1',0,'C',$e0);
        $this->Cell(12,6,'Forte','1',0,'C',$i1);
        $this->Cell(10,6,'Boa','1',0,'C',$i2);
        $this->Cell(16,6,'Moderada','1',0,'C',$i3);
        $this->Cell(21,6,'Fraca','1',0,'C',$i4);
        $this->Ln();
        $situa = mb_convert_encoding('Situação', 'ISO-8859-1','UTF-8');
        $this->Cell(38,6,$situa,'1',0,'L');
        $this->Cell(21,6,'Vulnerabilidade','1',0,'L');
        $this->Cell(16,6,'','1',0,'C');
        $this->Cell(10,6,'','1',0,'C');
        $this->Cell(12,6,'','1',0,'C');
        $segur = mb_convert_encoding('Segurança', 'ISO-8859-1','UTF-8');
        $this->Cell(18,6,$segur,'1',0,'C');
        $this->Cell(12,6,'','1',0,'C');
        $this->Cell(10,6,'','1',0,'C');
        $this->Cell(16,6,'','1',0,'C');
        $this->Cell(21,6,'Vulnerabilidade','1',0,'C');
        $this->Ln(12);
    }
}

//Current user
$usr = Users::getCurrent();

//Get Quest
$quests = new Questionarios();
$questid = getIntQueryString('id', 0, true);
if (!$questid) {
    echo mb_convert_encoding("Questionário não encontrado.", 'ISO-8859-1','UTF-8');
    return;
}
$quest = $quests->item($questid);
if (!$quest) {
    echo mb_convert_encoding("Questionário não encontrado.", 'ISO-8859-1','UTF-8');
    return;
}
$modelos = new ModelosQuestionarios();
$modelo = $modelos->item($quest->modeloquestionarioid);


//Check permission
$pesquisaid = (getIntQueryString('aglutinadoraId', null)) ? 
						getIntQueryString('aglutinadoraId', 0) : $quest->pesquisaid; 

$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($pesquisaid);
if ((!isset($pesquisa)) || ($pesquisa->isAccessDenied())) {
    echo mb_convert_encoding("Acesso negado a este relatório.", 'ISO-8859-1','UTF-8');
    return;
}

//Verifica se produto foi adquirido
if ((!isset($pesquisa->produtos[1])) && (!isset($pesquisa->produtos[2]))) {
	echo mb_convert_encoding("Acesso negado a este relatório.", 'ISO-8859-1','UTF-8');
   return;
}

//Verifica se quest está associado a pesquisa em questao. Usado principalmente para as 
//pesquisas aglutinadoras
if (!$pesquisa->hasQuest($quest->id)) {
	echo mb_convert_encoding("Questionário não está associado a esta pesquisa.", 'ISO-8859-1','UTF-8');
   return;
}

//Verifica se solicitou relatório comentado
$show_report_comentado = ( (($usr->isinrole('Admin')) || (isset($pesquisa->produtos[2]))) 
									&& (getQueryString('comentado', null) == '1') );

//Release objs
$pesquisas = null;
$modelos = null;
$quests = null;

//Start pdf
$pdf = new PDF();
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = ($show_report_comentado) ? 'Relatório Alfa-Coaching' : 'Relatório Beta-Promotor';
$pdf->SetTitle($title);
$pdf->SetAuthor('SOBRARE - Sociedade Brasileira de Resiliência');
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(15);

//Set logo
$pdf->setClienteLogoFilename($pesquisa->id);
$pesquisa->modeloquestionario = $modelo;

//Capa
$pdf->Capa($quest, $show_report_comentado);
$pdf->AddPage();

//Instance final Chart
$chart_dest = imagecreatefrompng('ReportImages/chart_base.png');


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


//Print Chart Fatores
$y_mult = 0;

foreach ($quest->fatores as $fator) {
    //Gauge
    switch ($fator->valordescricao) {
        case 'P - Tipo 1':
            //$gauge_file = 'gauge_cp_forte.png';
            $x_mult = 3;
            break;
        case 'P - Tipo 2':
            //$gauge_file = 'gauge_cp_boa.png';
            $x_mult = 2;
            break;
        case 'P - Tipo 3':
            //$gauge_file = 'gauge_cp_moderada.png';
            $x_mult = 1;
            break;
        case 'P - Tipo 4':
            //$gauge_file = 'gauge_cp_fraca.png';
            $x_mult = 0;
            break;
        case 'I - Tipo 1':
            //$gauge_file = 'gauge_ci_forte.png';
            $x_mult = 5;
            break;
        case 'I - Tipo 2':
            //$gauge_file = 'gauge_ci_boa.png';
            $x_mult = 6;
            break;
        case 'I - Tipo 3':
            //$gauge_file = 'gauge_ci_moderada.png';
            $x_mult = 7;
            break;
        case 'I - Tipo 4':
            //$gauge_file = 'gauge_ci_fraca.png';
            $x_mult = 8;
            break;
        default:
            //$gauge_file = 'gauge_excelente.png';
            $x_mult = 4;
    }

    //Atualizar chart resumo final
    $sigla = strtolower($fator->sigla);
    $chart_src = imagecreatefrompng("ReportImages/chart_$sigla" . "_d.png");
    //$x = 56 * ($x_mult) + (13 * $fator->valorrelativo());
    $x = 55 * ($x_mult - 0.5 + $fator->valorrelativo()) - 0;
    $y = 75 + (53 * $y_mult);
    imagecopy($chart_dest, $chart_src, $x, $y, 0, 0, 57, 50);
    $y_mult += 1;
}
// Output chart resumo
$chart_file = "../_tmp/chart_quest_$questid.png";
imagepng($chart_dest, $chart_file);
$pdf->AddPage();
$pdf->ChapterTitle('Resumo dos resultados das áreas', false, true, false);
$pdf->Ln(3);
$pdf->Image($chart_file, 20, null, 174);
//  Legenda
$pdf->SetTextColor(99);
$pdf->SetFont('Verdana', 'B', 9);
$pdf->Cell(0, 5, 'Legenda', 0, 1);
$pdf->SetTextColor(128);
$nl = 0;
foreach ($quest->fatores as $fator) {
    $pdf->SetFont('Verdana', 'B', 7);
    $pdf->Cell(9, 5, mb_convert_encoding("$fator->sigla:", 'ISO-8859-1','UTF-8'), 0, 0);
    $pdf->SetFont('Verdana', '', 7);
    $pdf->Cell(70, 5, mb_convert_encoding($fator->nome, 'ISO-8859-1','UTF-8'), 0, $nl);
    if ($nl == 0)
        $nl = 1;
    else
        $nl = 0;
}


//print fatores
foreach ($quest->fatores as $fator) {
    $pdf->AddPage();
    $pdf->ChapterTitle("Resultado da área $fator->nome ($fator->sigla)", false, true, false);
    $pdf->ChapterBody($fator->descricao);

    if ($show_report_comentado) {
        $pdf->Ln();
        $pdf->ChapterBody('MCD característico do PC ' . $fator->valordescricao, 'B');
        $pdf->ChapterBody($fator->devolutiva);
    
        //Imprime tabela
        $pdf->Ln();
        $pdf->ResultadoFatorTable($fator->valordescricao);
    } else {
        $pdf->ChapterBody($fator->devolutiva);
    }
    
    //Print Detalhamento, se foi adquirido
    if ($show_report_comentado) 
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

//Release chart memory
imagedestroy($chart_dest);
imagedestroy($chart_src);
?>