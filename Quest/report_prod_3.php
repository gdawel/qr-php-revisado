<?php

/**
 * 
 * TABELA DE ÍNDICES
 * 
 * */

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

    function Header()
    {
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
}

function IdentificacaoTable() {
	global $pdf, $pesquisa;
	$pesquisador = $pesquisa->Pesquisador();

	$pdf->ChapterTitle('Informações da Pesquisa', true, true, false);
	
	$pdf->SetFont('Verdana', '', 8);
	$pdf->SetTextColor(99);
	$pdf->Cell(25, 2, 'PESQUISA', 0, 0);	
	$pdf->SetFont('Verdana', 'B', 8);
	$pdf->SetTextColor(0);
	$pdf->Cell(75, 2, $pesquisa->id . ' - ' . utf8_decode($pesquisa->titulo), 0, 1);
	$pdf->Ln(3);
	
	$pdf->SetFont('Verdana', '', 8);
	$pdf->SetTextColor(99);
	$pdf->Cell(25, 2, utf8_decode('INSTITUIÇÃO'), 0, 0);	
	$pdf->SetFont('Verdana', 'B', 8);
	$pdf->SetTextColor(0);
	$pdf->Cell(75, 2, utf8_decode($pesquisador->instituicao), 0, 1);
	$pdf->Ln(3);
	
	$pdf->SetFont('Verdana', '', 8);
	$pdf->SetTextColor(99);
	$pdf->Cell(25, 2, 'PESQUISADOR', 0, 0);	
	$pdf->SetFont('Verdana', 'B', 8);
	$pdf->SetTextColor(0);
	$pdf->Cell(75, 2, utf8_decode($pesquisador->nome), 0, 1);
	$pdf->Ln(3);
}


$pdf = new PDF('L');
$pdf->AddFont('Verdana', 'B', '2baadfeddaf7cb6d8eb5b5d7b3dc2bfc_verdanab.php');
$pdf->AddFont('Verdana', '', 'e1cdac2412109fd0a7bfb58b6c954d9e_verdana.php');
$title = 'Tabela dos Índices nos Modelos de Crenças Determinantes';
$pdf->SetTitle($title);
$pdf->SetAuthor('SOBRARE - Sociedade Brasileira de Resiliência');
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(15);

//Load data
$pesquisaid = getIntQueryString('id', 0, true);

//Check permission
$pesquisas = new Pesquisas();
$pesquisa = $pesquisas->item($pesquisaid);
if ((!$pesquisa) || ($pesquisa->isAccessDenied())) {
    echo utf8_decode("Acesso negado a este relatório.");
    return;
}
if (!$pesquisa->isProdutoAdquirido(3)) {
    echo utf8_decode("Acesso negado a este relatório.");
    return;
}

//Logo
$pdf->setClienteLogoFilename($pesquisa->id);

//Title
$pdf->AddPage();
$pdf->ChapterTitle($title, false, true, false, "C");

//Print identificacao
IdentificacaoTable();

//Print table
$pdf->ChapterTitle('Tabela dos índices', true, true, true);
//$quests = $pesquisas->QuestListByPesquisaId($pesquisaid);
$quests = $pesquisa->getQuestionariosByStatus(QUESTIONARIO_STATUS_CONCLUIDO);

if ($quests) {
	$table = "<font face='arial' size='7'>
<table border='1'>
<tr>
<td width='80'>Núm. do Sujeito</td>";
	foreach ($quests[0]->fatores as $fator) {
		$table .= "<td width='".$pdf->getCellWidthByFatorId($fator->id)."' ALIGN='CENTER'>$fator->nome</td>";
	}
	$table .= "</tr>";
	
	foreach ($quests as $quest) {
		if ($quest->infos['StatusId'] == 3) {
	 		$table .= "<tr><td width='80' ALIGN='CENTER'>$quest->id</td>";
			foreach ($quest->fatores as $fator) $table .= "<td width='".$pdf->getCellWidthByFatorId($fator->id)."' ALIGN='CENTER'>$fator->valor</td>";
			$table .= "</tr>";
		}
	}
	$table .= "</table>
</font>
<br /><br />";
	$pdf->WriteHTML(utf8_decode($table));
	
} else {
	$pdf->ChapterBody('Nenhum questionário encontrado para esta pesquisa.');
}

//Notes
$pdf->ChapterNotes('Fonte: Sociedade Brasileira de Resiliência (SOBRARE) - CRPJ 3825/J');
$pdf->ChapterNotes('Nota do Autor: "Todos os dados numéricos extraídos dessa ferramenta poderão ser usados para alimentar pesquisas, preservando o sigilo do(a) respondente."');


$pdf->Output('Tabela_Indices.pdf', 'D');
?>