<?php
include_once dirname(__FILE__)."/../App_Code/SqlHelper.class.php";

class ListItemPicker {
	
	static function getSqlCommand($type, $param = null) {
		//Define select statement
		switch ($type) {
			case 'simnao':
				$sql_command_text = "SELECT value, text FROM simnao ORDER BY 2";
				break;
				
			case 'sexo':
				$sql_command_text = "SELECT SexoId AS value, Sexo AS text FROM sexos ORDER BY Sexo";
				break;
			
			case 'uf':
				$sql_command_text = "SELECT UF AS value, UF AS text FROM ufs ORDER BY UF";
				break;
			
			//Criada para o export_pesquisa_legenda.php
			case 'uf_legenda':
				$sql_command_text = "SELECT Id AS value, UF AS text FROM ufs ORDER BY UF";
				break;
			
			case 'escolaridade':
				$sql_command_text = "SELECT EscolaridadeId AS value, Escolaridade AS text FROM escolaridades ORDER BY Escolaridade";
				break;
			
			case 'estadocivil':
				$sql_command_text = "SELECT EstadoCivilId AS value, EstadoCivil AS text FROM estadoscivis ORDER BY EstadoCivil";
				break;
			
			case 'religiao':
				$sql_command_text = "SELECT ReligiaoId AS value, Religiao AS text FROM religioes ORDER BY Religiao";
				break;
			
			case 'situacao_duracao':
				$sql_command_text = "SELECT SituacaoGraveDuracaoId AS value, Duracao AS text FROM situacoesgraves_duracao";
				break;
			
			case 'situacao_qdo':
				$sql_command_text = "SELECT SituacaGraveQuandoId AS value, Quando AS text FROM situacoesgraves_quando";
				break;
			
			case 'pessoas_dificuldades':
				$sql_command_text = "SELECT PessoaDificuldade AS value, Pessoa AS text FROM pessoasdificuldades";
				break;

			case 'publicacoes_tipos':
				$sql_command_text = "SELECT PublicacaoTipoId as value, PublicacaoTipo as text FROM publicacoes_tipos ORDER BY PublicacaoTipo";
				break;
				
			case 'pesquisas_tipos':
				$sql_command_text = "SELECT TipoId as value, Tipo as text FROM pesquisas_tipos ORDER BY 2";
				break;
			
			case 'pesquisas_status':
				//Somente Admin pode visualizar status CANCELADA
				$usr = Users::getCurrent();
				if (!$usr->isinrole('Admin'))
					$sql_command_text = "SELECT StatusId as value, Status as text
												FROM pesquisas_status
												WHERE StatusId <> 3 
												ORDER BY 2";
				else
					$sql_command_text = "SELECT StatusId as value, Status as text FROM pesquisas_status ORDER BY 2";
				break;
						
			case 'pacotes':
				$sql_command_text = "SELECT PacoteId as value, Nome as text FROM pacotes WHERE Enabled = 1 ORDER BY Nome";
				break;

			case 'pacote_produtos':
				$sql_command_text = "SELECT ProdutoId as value, Nome as text FROM produtos WHERE Enabled = 1 AND ProdutoId IN (SELECT ProdutoId FROM pacotes_produtos WHERE PacoteId = $param) ORDER BY Nome";
				break;	
			
                            case 'pacote_produtos_criacao_pesquisa':
				$sql_command_text = "SELECT ProdutoId as value, Nome as text FROM produtos "
                                    . "WHERE Enabled = 1 "
                                    . "AND ProdutoId IN (SELECT ProdutoId FROM pacotes_produtos WHERE PacoteId = $param) "
                                    . "AND ProdutoId NOT IN (3,4) " //Tabela de Categoria, Tabela de Indices
                                    . "ORDER BY Nome";
				break;
                            
			case 'pacotes_tipos':
				$sql_command_text = "SELECT PacoteTipoId as value, Nome as text FROM pacotes_tipos ORDER BY Nome";
				break;
			
			case 'produtos_faltantes_no_pacote':
				$sql_command_text = "SELECT ProdutoId as value, Nome as text FROM produtos WHERE Enabled = 1 AND ProdutoId NOT IN (SELECT ProdutoId FROM pacotes_produtos WHERE PacoteId = $param) ORDER BY Nome";
				break;
				
			case 'modelos_questionarios':
				$sql_command_text = "SELECT ModeloQuestionarioId as value, Nome as text FROM modelosquestionarios ORDER BY Nome";
				break;
			
			case 'roles':
				$sql_command_text = "SELECT Rolename as value, Rolename as text FROM roles ORDER BY 2";
				break;
			
			case 'users_status':
				$sql_command_text = "SELECT StatusId as value, Status as text FROM users_status ORDER BY 2";
				break;
				
			case 'gestores':
				if (!$param) $param = '0';
				$sql_command_text = "SELECT 
												u.UserId as value, 
												CASE u.Ativo 
													WHEN 0 THEN CONCAT(u.Nome, ' (Inativo)') 
													ELSE u.Nome 
												END as text 
											FROM users u
		  									WHERE (u.Ativo = 1 AND EXISTS(SELECT ur.Rolename from usersinroles ur WHERE u.userId = ur.UserId AND ur.Rolename = 'Gestor'))
		  											OR (u.UserId = $param)
											ORDER BY 2";
				break;

			case 'cursos_inscricoes_tipos':
                if (!$param) $param = '0';
            
				$sql_command_text = "SELECT InscricaoTipoId as value, InscricaoTipo as text 
                                     FROM cursos_inscricoes_tipos 
                                     WHERE InscricaoTipoId IN (1,2,3)
                                            OR InscricaoTipoId = $param
                                     ORDER BY 2";
				break;
			
			case 'cursos_inscricoes_status':
				$sql_command_text = "SELECT StatusId as value, Status as text FROM cursos_inscricoes_status ORDER BY 2";
				break;
			
			case 'cursos_condicoes_pagamento':
				$sql_command_text = "SELECT CondicaoPagamentoId as value, CondicaoPagamento as text FROM condicoespagamento ORDER BY 1";
				break;
			
            case 'cursos_tipos':
				$sql_command_text = "SELECT TipoId as value, Tipo as text FROM cursos_tipos ORDER BY 2";
				break;
                
            case 'contents_tipos':
				$sql_command_text = "SELECT TipoId as value, Tipo as text FROM contents_tipos ORDER BY 2";
				break;
                			
            case 'fontes_divulgacao':
				$sql_command_text = "SELECT FonteDivulgacaoId as value, Nome as text FROM fontesdivulgacao ORDER BY 2";
				break;

            case 'grupos_perguntas':
                $sql_command_text = "SELECT GrupoPerguntaId as value, concat(Posicao, '. ', left(Texto, 75), '...')  as text
                                     FROM modelosquestionarios_gruposperguntas
                                     WHERE ModeloQuestionarioId = $param
                                     ORDER BY Posicao";
                break;

			default:		
				$sql_command_text = '';
		}
		
		return $sql_command_text;
	}

	static function Render($id, $type, $selected, $allownull = false, $param = null, $allownullvalue = 0, $allownulltext = 'Selecione...') {
		$sql = new SqlHelper();
		
		$sql->command = ListItemPicker::getSqlCommand($type, $param);
		$sql->execute();
		
		echo "<select name='$id' id='$id'>";
			
			if ($allownull) echo "<option value='$allownullvalue'>$allownulltext</option>";
		
			while ($r = $sql->fetch()) {
				$selected_option = ($r['value'] == $selected ? "selected='selected'" : '');
				echo "<option value='$r[value]' $selected_option>$r[text]</option>";
			}		
		echo "</select>";		
	}
	
	static function RenderRadioList($id, $type, $selected, $param = null, $onclick=null) {
		$sql = new SqlHelper();
		
		$sql->command = ListItemPicker::getSqlCommand($type, $param);
		$sql->execute();
				
		while ($r = $sql->fetch()) {
			$selected_option = ($r['value'] == $selected ? "checked='checked'" : '');
			if ($onclick) $onclickjs="onclick=\"javascript:$onclick\""; else $onclickjs='';
			echo "<input class='' type='radio' value='$r[value]' name='$id' $selected_option $onclickjs /><span class=''>$r[text]</span>";
		}				
	}
	
	static function RenderCheckboxList($id, $type, $selected, $param = null, $cssClass='') {
		$sql = new SqlHelper();
		
		$sql->command = ListItemPicker::getSqlCommand($type, $param);
		$sql->execute();
				
		while ($r = $sql->fetch()) {
			$selected_option = (in_array($r['value'], explode(',', $selected)) ? "checked='checked'" : '');
			echo "<div class='$cssClass'><input type='checkbox' value='$r[value]' name='".$id.'[]'."' $selected_option /><span class='radio'>$r[text]</span></div>";
		}				
	}
}

?>