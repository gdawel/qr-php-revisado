<?php
include_once '../App_Code/Pesquisa.class.php';
include_once '../App_Code/User.class.php';
include_once '../App_Code/Curso.class.php';
include_once '../App_Code/Inscricao.class.php';
include_once '../App_Code/CommonFunctions.php';

$usr = Users::getCurrent();
if (!$usr->isinrole('Admin')) {
	echo "Acesso negado.";
	exit;
}

Export();

function Export() {
	//get curso
	$cursoId = getIntQueryString('cursoId', 0);
	$cursos = new Cursos();
	$curso = $cursos->Item($cursoId);
	if (!$curso) {
		echo "<p>Curso inválido.</p>";
		return;
	}
	//filter
	$filter = new Filter();
	//status
	$s_status = getPost('s_status', '-1');
	if ($s_status != -1) $filter->add('i.StatusId', '=', $s_status);
	//curso
	if ($cursoId) $filter->add('i.CursoId', '=', $cursoId);
	
	//get data
	$inscricoes = new Inscricoes();
	$lst = $inscricoes->getExportDataInscritos($filter);
		
	//Start
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=Inscricoes_".getSEO($curso->nome).".xls");
										
	echo "<h1>$curso->nome</h1>";
	
	if ($lst) {
		$counter = 0;
		echo "<style>		
					.th {
						vertical-align:middle;
						background-color:#000;
						color:#FFF;
					}
					.thInscrito {
						vertical-align:middle;
						background-color:#003;
						color:#FFF;
					}
					td {
						border-color:#ccc;
						vertical-align:top;
					}					
					.inscrito {background-color:#E9F1FA;}
				</style>";
		echo "<table border='1'>
					<tr>
						<th class='th' rowspan='2'>#</th>
						<th class='th' rowspan='2'>Tipo</th>
						<th class='th' rowspan='2'>Status</th>	
						<th class='th' rowspan='2'>Data da Inscrição</th>
						<th class='th' rowspan='2'>Valor</th>
						<th class='th' rowspan='2'>Condição de Pagamento</th>
						<th class='thInscrito' colspan='4'>Inscritos</th>
						<th class='th' colspan='15'>Dados do Responsável</th>
					</tr>
					<tr>
					 	<th class='thInscrito'>Nome</th>
					 	<th class='thInscrito'>Associado?</th>
					 	<th class='thInscrito'>CPF</th>
					 	<th class='thInscrito'>Email</th>
					 	
						<th class='th'>Nome</th>
						<th class='th'>Associado?</th>
						<th class='th'>CPF</th>
                        <th class='th'>Data de Nascimento</th>
					 	<th class='th'>Sexo</th>
					 	<th class='th'>Email</th>
					 	<th class='th'>Endereço</th>
					 	<th class='th'>Bairro</th>
					 	<th class='th'>Cidade</th>
                        <th class='th'>CEP</th>
					 	<th class='th'>UF</th>
					 	<th class='th'>Telefone</th>
					 	<th class='th'>Celular</th>
					 	<th class='th'>Função</th>
					 	<th class='th'>Razão Social</th>
					 	<th class='th'>CNPJ</th>
					 	<th class='th'>IE</th>
					</tr>";
		foreach ($lst as $r) {
			$counter++;
			
			echo "<tr>";
			if ($counter == 1) { 
				echo "	<td rowspan='$r[QtdeInscritos]'>$r[InscricaoId]</td>
							<td rowspan='$r[QtdeInscritos]'>$r[InscricaoTipo]</td>
							<td rowspan='$r[QtdeInscritos]'>$r[Status]</td>
							<td rowspan='$r[QtdeInscritos]'>$r[CreatedDate]</td>
							<td rowspan='$r[QtdeInscritos]'>".$r['Valor']."</td>
							<td rowspan='$r[QtdeInscritos]'>$r[CondicaoPagamento]</td>";
			}
			
				echo " 	<td class='inscrito'>$r[InscritoNome]</td>
						 	<td class='inscrito'>".($r['InscritoAssociadoId']?'Sim':'Não')."</td>
						 	<td class='inscrito'>$r[InscritoCPF]</td>
						 	<td class='inscrito'>$r[InscritoEmail]</td>";
			
			if ($counter == 1) {
				echo "			 				
							<td rowspan='$r[QtdeInscritos]'>$r[Nome]</td>
							<td rowspan='$r[QtdeInscritos]'>".($r['AssociadoId']?'Sim':'Não')."</td>
							<td rowspan='$r[QtdeInscritos]'>$r[CPF]</td>
                            <td rowspan='$r[QtdeInscritos]'>".format_date($r['CPF'])."</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Sexo]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Email]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Endereco]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Bairro]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Cidade]</td>
                            <td rowspan='$r[QtdeInscritos]'>$r[CEP]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[UF]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Telefone]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Celular]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[Funcao]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[RazaoSocial]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[CNPJ]</td>
						 	<td rowspan='$r[QtdeInscritos]'>$r[IE]</td>";
			}
			
			echo "</tr>";
			
			if ($counter == $r['QtdeInscritos']) $counter = 0;
		}//foreach
		echo "</table>";
		
	} else {
		echo "Nenhum inscrito para este curso";
	}
}
?>