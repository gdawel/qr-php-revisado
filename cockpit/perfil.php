<?php
$pageTitle = 'SOBRARE Associado | Meu perfil';
$mnuQuem = 'active';
include_once '../App_Code/User.class.php';
include_once '../App_Code/CommonFunctions.php';
include_once '../Controls/msgbox.ctrl.php';
include_once '../Controls/list.ctrl.php';
include_once '../Controls/button.ctrl.php';

Users::checkAuth('Associado', 'index.php');
include_once '../MasterPageCockpit.htm.php';

function Router() {	
	global $msg; 
	
	$action = getPost('action', '');	
	
	switch ($action) {
		case 'update':
			if (!isPageRefresh()) {
				if (!updatePerfil()) MessageBox::Render($msg, 'Error');
				else MessageBox::Render('Perfil atualizado com sucesso');
				RenderDefault();
			} else {
				RenderDefault();
			}
			break;
			
		default:
			RenderDefault();
	}
}

function RenderDefault() {
	global $msg;
	
	checkPageRefreshSessionVar();
	
	$users = new Users();
	$current_usr = Users::getCurrent();
	$usr = $users->item($current_usr->userid);
		
	echo "<div class='Buttons NavButtons'>";
			Button::RenderNav('Ir para Home', 'index.php', 'Voltar para a página inicial', 'home'); echo "
		</div>";


	echo "<h1>Meu Perfil</h1>";
					
	echo "<form action='perfil.php' method='post' name='frm' id='frm'>
		<input type='hidden' name='action' id='action' value='update' />
		<input type='hidden' name='userid' id='userid' value='$usr->userid' />
		<input type='hidden' name='PageRefreshChecker' id='PageRefreshChecker' value='$_SESSION[PageRefreshChecker]' />
		
		<div class='grid_8 alpha omega'>
			<h2>Perfil Restrito</h2>
			<p class='Note'>Neste cadastro, todos os campos são obrigatórios. Ele proporciona identificá-lo como associado na SOBRARE, fornecendo informações importante para contato.</p>
			
			<table class='Form'>				
				<tr class='Field'>
					<td colspan='2'>Nome <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='60' name='nome' id='nome' value='".getPost('nome', $usr->nome, true)."' maxlength='145' /></td>
				</tr>
				
				<tr class='Field'>
					<td>E-mail <span class='Red'>*</span></td>
                    <td>Data de Nascimento <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td><input type='text' size='40' name='email' id='email' value='".getPost('email', $usr->email, true)."' maxlength='45' /></td>
                    <td><input type='text' size='10' name='datanascimento' id='datanascimento' value='".getPost('datanascimento', format_date($usr->datanascimento), true)."' alt='date' /></td>
				</tr>
						
				
				<tr class='Field'>
					<td>Sexo <span class='Red'>*</span></td>
					<td>CPF <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td>"; ListItemPicker::Render('sexo', 'sexo', $usr->sexoid); echo "</td>
					<td><input type='text' size='14' name='cpf' id='cpf' value='".getPost('cpf', $usr->cpf, true)."' alt='cpf' /></td>
				</tr>			
						
				<tr class='Field'>
					<td>Endereço <span class='Red'>*</span></td>
                    <td>Número <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td><input type='text' size='40' name='endereco' id='endereco' value='".getPost('endereco', $usr->endereco, true)."' maxlength='145' /></td>
                    <td><input type='text' size='14' name='numero' id='numero' value='".getPost('numero', $usr->numero, true)."' /></td>
				</tr>                
                
				<tr class='Field'>
					<td colspan='2'>Complemento</td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='26' name='complemento' id='complemento' value='".getPost('complemento', $usr->complemento, true)."' maxlength='100' /></td>
				</tr>
				
				<tr class='Field'>
					<td>Bairro <span class='Red'>*</span></td>
					<td>CEP <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td><input type='text' size='26' name='bairro' id='bairro' value='".getPost('bairro', $usr->bairro, true)."' maxlength='45' /></td>
					<td><input type='text' size='14' name='cep' id='cep' alt='cep' value='".getPost('cep', $usr->cep, true)."' /></td>
				</tr>
				
				<tr class='Field'>
					<td>Cidade <span class='Red'>*</span></td>
					<td>UF <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td><input type='text' size='26' name='cidade' id='cidade' value='".getPost('cidade', $usr->cidade, true)."' maxlength='45' /></td>
					<td><input type='text' size='2' name='uf' id='uf' alt='aa' value='".getPost('uf', $usr->uf, true)."'  maxlength='2' /></td>
				</tr>
				
				<tr class='Field'>
					<td>País <span class='Red'>*</span></td>
					<td></td>
				</tr>
				<tr>
					<td><input type='text' size='26' name='pais' id='pais' value='".getPost('pais', $usr->pais, true)."' /></td>
					<td></td>
				</tr>
				
				<tr class='Field'>
					<td>Tel. Comercial <span class='Red'>*</span></td>
					<td>Tel. Residencial</td>
				</tr>
				<tr>
					<td><input type='text' size='14' name='com' id='com' alt='phone' value='".getPost('com', $usr->telefonecomercial, true)."' /></td>
					<td><input type='text' size='14' name='res' id='res' alt='phone' value='".getPost('res', $usr->telefoneresidencial, true)."' /></td>
				</tr>			
				
				<tr class='Field'>
					<td>Celular <span class='Red'>*</span></td>
					<td>
				</tr>
				<tr>
					<td><input type='text' size='14' name='celular' id='celular' alt='phone' value='".getPost('celular', $usr->celular, true)."' /></td>
					<td></td>
				</tr>
				
				<tr class='Field'>
					<td>Profissão <span class='Red'>*</span></td>
					<td></td>
				</tr>
				<tr>
					<td><input type='text' size='26' name='profissao' id='profissao' value='".getPost('profissao', $usr->profissao, true)."' maxlength='45' /></td>
					<td></td>
				</tr>			
			</table>
		</div>

		<div class='grid_8 alpha omega'>
			<h2>Perfil Público</h2>
			<p class='Note'>Este cadastro proporciona divulgar as informações que serão publicadas no site para que todos possam ter acesso.</p>
			
			<table class='Form'>
				<tr class='Field'>
					<td colspan='2'>E-mail de Divulgação <small><span class='Red'>(será publicado no site da SOBRARE)</span></small>
						<br /><small>Divulgue um e-mail para que outros associados possam entrar em contato com você</small>
					</td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='60' name='email2' id='email2' value='".getPost('email2', $usr->email2, true)."' maxlength='45' /></td>
				</tr>
				
				<tr class='Field'>
					<td colspan='2'>Endereço Eletrônico <small>(site, blog, twitter)</small></td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='60' name='url' id='url' value='".getPost('url', (($usr->url) ? $usr->url : 'http://'), true)."' maxlength='100' /></td>
				</tr>
				
				<tr class='Field'>
					<td colspan='2'>Instituição</td>
				</tr>
				<tr>
					<td colspan='2'><input type='text' size='60' name='instituicao' id='instituicao' value='".getPost('instituicao', $usr->instituicao, true)."' maxlength='150' /></td>
				</tr>			
				
				<tr class='Field'>
					<td>Nível Acadêmico</td>
					<td></td>
				</tr>
				<tr>
					<td><input type='text' size='26' name='nivel' id='nivel' value='".getPost('nivel', $usr->nivelacademico, true)."' maxlength='45' /></td>
					<td></td>
				</tr>			
				
				<tr class='Field'>
					<td colspan='2'>Atividades Desenvolvidas <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td colspan='2'><textarea type='text' cols='65' rows='4' name='atividades' id='atividades'>";echo getPost('atividades', $usr->atividades, true); echo "</textarea></td>
				</tr>
				
				<tr class='Field'>
					<td colspan='2'>Interesses <span class='Red'>*</span></td>
				</tr>
				<tr>
					<td colspan='2'>
						<textarea type='text' cols='65' rows='4' name='interesses' id='interesses'>";echo getPost('interesses', $usr->interesses, true); echo "</textarea>
					</td>
				</tr>			
			</table>
		</div>		
		
		<div class='Buttons'>";
			Button::RenderSubmit(null, 'Salvar', 'Salvar este item', 'save', 'positive');
			Button::Render(null, 'Voltar', 'index.php', 'Voltar para a página inicial', 'undo'); echo "
		</div>

		</form>
		
		<div id='frm_errorloc' class='Error'></div>
		
		<script language='JavaScript' type='text/javascript'>
			var vld  = new Validator('frm');
			
			vld.addValidation('nome','alpha', 'Somente letras são permitidas no campo Nome');
			vld.addValidation('nome', 'req','Nome obrigatório');
			vld.addValidation('nome', 'minlen', 'Preencha seu nome completo', 12);
			
			vld.addValidation('email', 'email', 'E-mail inválido');			
			vld.addValidation('email', 'req', 'E-mail obrigatório');
			vld.addValidation('email2', 'email', 'E-mail de Divulgação inválido');
            
            vld.addValidation('datanascimento', 'req', 'Data de Nascimento é obrgatória');
											
			vld.addValidation('cpf', 'req', 'CPF obrigatório');
			vld.addValidation('endereco', 'req', 'Endereço obrigatório');
            vld.addValidation('numero', 'req', 'Número é obrigatório');
			vld.addValidation('bairro', 'req', 'Bairro obrigatório');
			vld.addValidation('cidade', 'req', 'Cidade obrigatório');
			vld.addValidation('pais', 'req', 'País obrigatório');
			vld.addValidation('cep', 'req', 'CEP obrigatório');
			vld.addValidation('uf', 'req', 'UF obrigatório');
			vld.addValidation('com', 'req', 'Telefone Comercial obrigatório');
			vld.addValidation('celular', 'req', 'Celular obrigatório');			
			vld.addValidation('profissao', 'req', 'Profissão obrigatória');
			
			vld.addValidation('interesses', 'req', 'Campo \"Interesses\" obrigatório');
			vld.addValidation('atividades', 'req', 'Campo \"Atividades Desenvolvidas\" obrigatório');
			
			vld.EnableOnPageErrorDisplaySingleBox();
			vld.EnableMsgsTogether();
		</script>
	";
}

function updatePerfil() {
	global $msg;
	
	updatePageRefreshChecker();					
					
	$a = new User();
	
	$a->userid = getPost('userid', '');
	$a->nome = getPost('nome', '');
	$a->email = getPost('email', '');
	$a->email2 = getPost('email2', '');
	$a->cpf = getPost('cpf', '');
    $a->datanascimento = getPost('datanascimento', null);
	$a->sexoid = getIntPost('sexo', 1);
	$a->endereco = getPost('endereco', '');
    $a->complemento = getPost('complemento', '');
    $a->numero = getPost('numero', '');
	$a->bairro = getPost('bairro', '');
	$a->cidade = getPost('cidade', '');
	$a->pais = getPost('pais', '');
	$a->uf = getPost('uf', '');
	$a->cep = getPost('cep', '');
	$a->celular = getPost('celular', '');
	$a->telefonecomercial = getPost('com', '');
	$a->telefoneresidencial = getPost('res', '');
	$a->instituicao = getPost('instituicao', '');
	$a->nivelacademico = getPost('nivel', '');
	$a->profissao = getPost('profissao', '');
	$a->areaocupacao = getPost('areaocupacao', '');
	$a->atividades = getPost('atividades', '');
	$a->interesses = getPost('interesses', '');
	$a->url = getPost('url', '');
	//formata url
	if ($a->url == 'http://') $a->url = null;
	//if (substr($a->url, 0, 8) != 'http://') $a->url = "http://$a->url";
		
	$users = new Users();
	if (!$users->update($a)) {
		$msg = $users->error;
		return false;
	} else {
		return true;
	}
}

?>