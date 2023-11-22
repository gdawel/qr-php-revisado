<?php

//* AQUI COMEÇA A BRINCADEIRA COM A GERAÇÃO DE PDF 

require("./Includes/fpdf.php");

class PDF extends FPDF
{
// Load data
function LoadData($file)
{
    // Read file lines
    $lines = file($file);
    $data = array();
    foreach($lines as $line)
        $data[] = explode(';',trim($line));
    return $data;
}

// Simple table
function BasicTable($header, $data)
{
    // Header
    foreach($header as $col)
        $this->Cell(40,7,$col,1);
    $this->Ln();
    // Data
    foreach($data as $row)
    {
        foreach($row as $col)
            $this->Cell(40,6,$col,1);
        $this->Ln();
    }
}

// Better table
function ImprovedTable($header, $data)
{
    // Column widths
    $w = array(40, 35, 40, 45);
    // Header
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C');
    $this->Ln();
    // Data
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR');
        $this->Cell($w[1],6,$row[1],'LR');
        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
        $this->Ln();
    }
    // Closing line
    $this->Cell(array_sum($w),0,'','T');
}

// Colored table
function FancyTable($header, $data)
{
    // Colors, line width and bold font
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    // Header
    $w = array(40, 35, 40, 45);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
    $this->Ln();
    // Color and font restoration
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    // Data
    $fill = false;
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
        $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
        $this->Ln();
        $fill = !$fill;
    }
    // Closing line
    $this->Cell(array_sum($w),0,'','T');
}
}

$pdf = new PDF();
// Column headings
$header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
// Data loading
$data = $pdf->LoadData('countries.txt');
//print_r($data);
$pdf->SetFont('Arial','',10);
//$pdf->AddPage();
//$pdf->BasicTable($header,$data);
//$pdf->AddPage();
//$pdf->ImprovedTable($header,$data);
$pdf->AddPage();

//$pdf->FancyTable($header,$data);
$pdf->SetFont('Verdana','',8);

$pdf->Cell(40,6,'Estilo Comportamental','1',0,'L');
$pdf->Cell(63,6,'Passividade ao estresse','1',0,'C');
$equilib = mb_convert_encoding('Equilíbrio', 'ISO-8859-2','UTF-8');
$intoler = mb_convert_encoding('Intolerância ao estresse', 'ISO-8859-2','UTF-8');
$pdf->Cell(18,6,$equilib,'1',0,'C');
$pdf->Cell(63,6,$intoler,'1',0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',9);
$tipologia = mb_convert_encoding('Tipologia do Índice', 'ISO-8859-2','UTF-8');
$pdf->Cell(40,6,$tipologia,'1',0,'L');
$pdf->Cell(21,6,'4','1',0,'C');
$pdf->Cell(18,6,'3','1',0,'C');
$pdf->Cell(10,6,'2','1',0,'C');
$pdf->Cell(14,6,'1','1',0,'C');
$pdf->Cell(18,6,'-','1',0,'C');
$pdf->Cell(14,6,'1','1',0,'C');
$pdf->Cell(10,6,'2','1',0,'C');
$pdf->Cell(18,6,'3','1',0,'C');
$pdf->Cell(21,6,'4','1',0,'C');
$pdf->Ln();

$pdf->SetFillColor(255,153,51);
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
switch ($resultResiliencia) {
    case 'P4':
        $p4 = true;
        break;
    case 'P3':
        $p3 = true;
        break;
    case 'P2':
        $p2 = true;
        break;
    case 'P1':
        $p1 = true;
        break;
    case 'E0':
        $e0 = true;
        break;
    case 'I1':
        $i1 = true;
        break;
    case 'I2':
        $i2 = true;
        break;
    case 'I3':
        $i3 = true;
        break;
    case 'I4':
        $i4 = true;
        break;
    
    default:
      break;
  }
$condResili = mb_convert_encoding('Condição de Resiliência', 'ISO-8859-1', 'UTF-8');
$pdf->Cell(40,6,$condResili,'1',0,'L');
$pdf->SetFont('Arial','',8);
$pdf->Cell(21,6,'Fraca','1',0,'C',$p4);
$pdf->Cell(18,6,'Moderada','1',0,'C',$p3);
$pdf->Cell(10,6,'Boa','1',0,'C',$p2);
$pdf->Cell(14,6,'Forte','1',0,'C',$p1);
$pdf->Cell(18,6,'Excelente','1',0,'C',$e0);
$pdf->Cell(14,6,'Forte','1',0,'C',$i1);
$pdf->Cell(10,6,'Boa','1',0,'C',$i2);
$pdf->Cell(18,6,'Moderada','1',0,'C',$i3);
$pdf->Cell(21,6,'Fraca','1',0,'C',$i4);
$pdf->Ln();
$situa = mb_convert_encoding('Situação', 'ISO-8859-1','UTF-8');
$pdf->Cell(40,6,$situa,'1',0,'L');
$pdf->Cell(21,6,'Vulnerabilidade','1',0,'L');
$pdf->Cell(18,6,'','1',0,'C');
$pdf->Cell(10,6,'','1',0,'C');
$pdf->Cell(14,6,'','1',0,'C');
$segur = mb_convert_encoding('Segurança', 'ISO-8859-2','UTF-8');
$pdf->Cell(18,6,$segur,'1',0,'C');
$pdf->Cell(14,6,'','1',0,'C');
$pdf->Cell(10,6,'','1',0,'C');
$pdf->Cell(18,6,'','1',0,'C');
$pdf->Cell(21,6,'Vulnerabilidade','1',0,'C');
//$pdf->Ln(12);

//$teste = mb_convert_encoding('Resiliência', 'ISO-8859-2','UTF-8');
//$pdf->Cell(160,6,$teste,'1',0,'C');
$pdf->Output();
?>