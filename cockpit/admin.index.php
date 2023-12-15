<?php
include_once '../App_Code/Publicacao.class.php';
include_once '../App_Code/Associado.class.php';

function AdminIndex() {
	$interval = 15;

	$publicacoes = new Publicacoes();
	$pend = $publicacoes->getPendentesCount();
	switch ($pend) {
		case 0:
			$pend_text='Nenhuma publicação pendente de liberação.';
			break;
		case 1:
			$pend_text='1 publicação pendente de liberação.';
			break;
		default:
			$pend_text="$pend publicações pendentes de liberação.";
	}
	
	$newpub = $publicacoes->NewItemsCountByPeriod($interval);
	switch ($newpub) {
		case 0:
			$newpub_text="Nenhuma nova publicação nos últimos $interval dias.";
			break;
		case 1:
			$newpub_text="1 publicação nos últimos $interval dias.";
			break;
		default:
			$newpub_text="$newpub novas publicações nos últimos $interval dias.";
	}
	$publicacoes = null;
	
	$associados = new Associados();
	$pend = $associados->getDesativadosCount();
	switch ($pend) {
		case 0:
			$pend_text_assoc='Nenhum associado pendente de ativação.';
			break;
		case 1:
			$pend_text_assoc='1 associado pendente de ativação.';
			break;
		default:
			$pend_text_assoc="$pend associados pendentes de ativação.";
	}
	
	$newassoc = $associados->NewItemsCountByPeriod($interval);
	switch ($newassoc) {
		case 0:
			$newassoc_text="Nenhuma novo associado nos últimos $interval dias.";
			break;
		case 1:
			$newassoc_text="1 novo associado nos últimos $interval dias.";
			break;
		default:
			$newassoc_text="$newassoc novos associados nos últimos $interval dias.";
	}
	
	echo "<h1>Área do Admin</h1>
	
				<table class='List CenterVertical'>
					<tr>
						<td width='80px'><a href='publicacoes.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td width='300px'>
							<h2>Publicações
							</h2>
						</td><td>
							<ul>
								<li>$pend_text</li>
								<li>$newpub_text</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td><a href='associados.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Associados</h2>
						</td><td>
							<ul>
								<li>$pend_text_assoc</li>
								<li>$newassoc_text</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td><a href='cursos.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Cursos</h2>
						</td><td>
							Lista de cursos disponíveis no site
						</td>
					</tr>
					
					<tr>
						<td><a href='briefcase.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Pasta de Arquivos</h2>
						</td><td>
							Pasta de arquivos disponíveis online
						</td>
					</tr>					
					
					<tr>
						<td><a href='pesquisa_create.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Criar Pesquisa</h2>
						</td><td>
							Criar pesquisas ad hoc
						</td>
					</tr>
					
                    
					<tr>
						<td><a href='modeloquestionario.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Modelos de Questionários</h2>
						</td><td>
							Administrar Modelos de Questionários do QUEST_Resiliência.
						</td>
					</tr>
                    
					<tr>
						<td><a href='produto.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Produtos</h2>
						</td><td>
							Produtos disponíveis para compra no site
						</td>
					</tr>
                    
					<tr>
						<td><a href='pacote.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Pacotes</h2>
						</td><td>
							Pacotes e produtos disponíveis para compra no site
						</td>
					</tr>
					
                    <tr>
						<td><a href='reports.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Relatórios</h2>
						</td><td>
							Administrar textos e seções dos relatórios do QUEST_Resiliência.
						</td>
					</tr>
                    
					<tr>
						<td><a href='users.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Usuários</h2>
						</td><td>
							Administrar usuários e suas permissões
						</td>
					</tr>
					
					<tr>
						<td><a href='creditos.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Créditos</h2>
						</td><td>
							Administrar créditos de questionários dos gestores
						</td>
					</tr>
                    
					<tr>
						<td><a href='frontendmanager.php'><img src='../Images/button-detalhes-small.jpg' /></a></td>
						<td>				
							<h2>Conteúdo do Site</h2>
						</td><td>
							Administrar textos do site da SOBRARE
						</td>
					</tr>
				</table>";
}
?>