<?php
$pageTitle = 'SOBRARE Cockpit Admin | Associados';
include_once '../App_Code/User.class';
include_once '../App_Code/SqlHelper.class';
include_once '../App_Code/Associado.class';
include_once '../App_Code/CommonFunctions.php';
include_once 'admin.index.php';
include_once '../Controls/pagination.ctrl.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Admin', 'login.php');
include_once '../MasterPageCockpit.htm.php';


function Router() {	
	$associados = new Associados();
    
    //Filter. Deve estar aqui pelo action=exportar
	$filter = new Filter();
	$s_nome = $filter->addFromPost('nome', 'LIKE','s_nome');
	$s_email = $filter->addFromPost('email', 'LIKE', 's_email');	
	$s_status = getPost('s_status', 0); if ($s_status) {$filter->add('Ativo', '=', "0"); $_SESSION['s_status'] = "checked='checked'"; $s_status="checked='checked'";} else $s_status='';
					
    //get action and redirect
	$action = getPost('action');
	$id = getPost('id', 0);
	switch ($action) {			
		case 'delete':		
			if ($associados->Delete($id)) {
				$msg = 'Associado excluído com sucesso';
			} else {
				$msg = 'Erro ao excluir associado';
			};
			break;	
			
		case 'desativar':		
			if ($associados->Ativar($id, 0)) {
				$msg = 'Associado desativado com sucesso';
			} else {
				$msg = 'Erro ao desativar associado';
			};
			break;	
			
		case 'ativar':		
			if ($associados->Ativar($id, 1)) {
				$msg = 'Associado ativado com sucesso';
				if ($associados->error) $msg .= '&nbsp;<br />&nbsp;'.$associados->error;
			} else {
				$msg = 'Erro ao ativar associado';
			};
			break;
        
        case 'exportar':
            $lst = $associados->Items(1, 999999, "nome", $totalrows, $filter);        
            Exportar($lst);
            break;	           
	}


	echo "<div class='Buttons NavButtons'>";
				Button::RenderNav('Ir para Home', 'index.php', 'Ir para a página inicial', 'home'); echo "
			</div>";
				
	
	//Print any messages
	if (isset($msg)) MessageBox::Render($msg);
	
    //get data
	$page = getIntQueryString('page', 1, true);
	$pagesize = 10;
	$lst = $associados->Items($page, $pagesize, null, $totalrows, $filter);			
	
	if ($totalrows) $countmsg = "($totalrows itens encontrados)"; else $countmsg = '';
	
	echo "<h1>Cockpit do Admin</h1>
				<h2>Associados</h2>
				<fieldset>
					<legend>Filtros <span class='FieldsetMsg'>$countmsg</span></legend>					
					<form id='frm' name='frm' method='post' action='$_SERVER[REQUEST_URI]'>
						<input type='hidden' name='action' id='action' value='0' />
						<input type='hidden' name='id' id='id' value='0' />
						
						<table class='Form'>
							<tr class='Field'>
								<td>Nome</td>
								<td>E-mail</td>
								<td>Status</td>								
								<td rowspan='2' class='SearchButtonCell'>
									<div class='Buttons'>";
									   Button::Render(null, 'Pesquisar', '#', 'Pesquisa os itens conforme os filtros informados', 'search', true, 'regular', "Action(0, 0); return false;");
                                       Button::Render(null, 'Exportar', '#', 'Exportar associados', 'excel', true, 'regular', "Action(0, 'exportar'); return false;"); echo "
									</div>
								</td>
							</tr>
								<td><input type='text' id='s_nome' name='s_nome' value='$s_nome' size='30' /></td>
								<td><input type='text' id='s_email' name='s_email' value='$s_email' size='30' /></td>
								<td><input type='checkbox' $s_status name='s_status' value='1'/> Exibir somente desativados</td>
							</tr>
						</table>
					</form>
				</fieldset>";
				
	echo "<script type='text/javascript'>
					function Action(id, action) {
						document.getElementById('id').value = id;
						document.getElementById('action').value = action;
						
						document.getElementById('frm').submit();
					}
				</script>";
	
	if ($lst) {
		foreach ($lst as $p) {
			echo "<h3>".htmlspecialchars($p->nome)."</h3>
						<table class='Form'>
							<tr>
								<td class='Field' width='100px'>Email</td><td colspan='5'>".htmlspecialchars($p->email)."</td>
							</tr><tr>
								<td class='Field'>Profissão</td><td>".htmlspecialchars($p->profissao)."</td>
							
								<td class='Field'>Nível Acadêmico</td><td>".htmlspecialchars($p->nivelacademico)."</td>
                                
                                <td class='Field'>CPF</td><td>".htmlspecialchars($p->cpf)."</td>
							</tr><tr>
                            </tr><tr>	
								<td class='Field'>Endereço</td><td colspan='5'>";
									printf('%s - %s - %s - %s', htmlspecialchars($p->endereco), htmlspecialchars($p->bairro), htmlspecialchars($p->cidade), htmlspecialchars($p->uf));
			echo "		        </td>
							</tr><tr>
								<td class='Field'>Tel. Residencial</td><td width='150px'>".($p->telefoneresidencial ? htmlspecialchars($p->telefoneresidencial) : '-')."</td>
							
								<td class='Field'>Tel. Comercial</td><td width='150px'>".($p->telefonecomercial ? htmlspecialchars($p->telefonecomercial) : '-')."</td>
							
								<td class='Field'>Celular</td><td width='150px'>".($p->celular ? htmlspecialchars($p->celular) : '-')."</td>
							</tr><tr>
								<td class='Field'>Atividades</td><td colspan='5'>".htmlspecialchars($p->atividades)."</td>
							</tr><tr>
								<td class='Field'>Interesses</td><td colspan='5'>".htmlspecialchars($p->interesses)."</td>
							</tr><tr>
								<td class='Field'>Ativo</td><td colspan='5'>";
									if ($p->ativo == '1') echo "Sim"; else echo "<span class='Red'>Não</span>";
			echo "		</td>
							</tr><tr>
								<td class='Field'>Ações</td>
								<td colspan='5'>
									<div class='Buttons'>";
										if ($p->ativo == '1')
											Button::Render(null, 'Desativar', '#', 'Desativa o usuário, impossibilitando seu login no site', 'warning', true, 'negative', "if (confirm('Deseja realmente desativar este item?')) Action($p->id, 'desativar'); else return false;"); 
										else
											Button::Render(null, 'Ativar', '#', 'Ativa o usuário no site', 'change', true, 'positive', "if (confirm('Deseja realmente ativar este item?')) Action($p->id, 'ativar'); else return false"); 
										
										Button::Render(null, 'Excluir', '#', 'Exclui permanentemente o usuário do site', 'delete', true, 'negative', "if (confirm('Deseja realmente excluir este item? Essa ação não pode ser cancelada.')) Action($p->id, 'delete'); else return false;");
			echo "				</div>
								</td>
							</tr>
						</table>
						<br />";	
		}
		
		Pagination::Render('associados.php?page=%s', $totalrows, $pagesize, $page);
		
	} else {
		echo "<p><i>Nenhum item encontrado.</i></p>";
	}
}


function Exportar($ds) {
    ob_clean();
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: inline; filename=Associados.xls");
									
    echo "<h1>Associados</h1>";
   	
       echo utf8_decode("<table border='1'>
				<tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Email</th>
                    <th>Email2</th>
                    <th>UF</th>
                    <th>País</th>
                    <th>Cidade</th>
                    <th>Endereço</th>
                    <th>Bairro</th>
					<th>CEP</th>
                    <th>Telefone Residencial</th>
                    <th>Telefone Comercial</th>
                    <th>Celular</th>
                    <th>Nível Acadêmico</th>
                    <th>Área Ocupação</th>
                    <th>Instituição</th>
                    <th>Profissão</th>
                    <th>Atividades</th>
                    <th>Interesses</th>
                    <th>URL</th>
                    <th>Ativo?</th>
                </tr>");
                
	   foreach ($ds as $a) 
        echo utf8_decode("<tr>            
                <td>$a->id</td>
				<td>$a->nome</td>
                <td>$a->cpf</td>
				<td>$a->email</td>
				<td>$a->email2</td>
				<td>$a->uf</td>
				<td>$a->pais</td>
				<td>$a->cidade</td>
				<td>$a->endereco</td>
				<td>$a->bairro</td>
				<td>$a->cep</td>
				<td>$a->telefoneresidencial</td>
				<td>$a->telefonecomercial</td>
				<td>$a->celular</td>				
				<td>$a->nivelacademico</td>
				<td>$a->areaocupacao</td>
				<td>$a->instituicao</td>
				<td>$a->profissao</td>
				<td>$a->atividades</td>
				<td>$a->interesses</td>
				<td>$a->url</td>
				<td>$a->ativo</td>
            </tr>");
	
    echo "</table>";
    
    echo utf8_decode("<p>Relatório extraído em ".date('d/m/Y H:m').".</p>");
    exit();  
} //Exportar()
?>