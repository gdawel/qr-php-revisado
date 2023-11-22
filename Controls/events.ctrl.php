<?php
include_once dirname(__FILE__)."/../App_Code/SqlHelper.class";
include_once dirname(__FILE__)."/../App_Code/Curso.class";

class EventControls {
	public static function defaultInterval() {return 360;}
	
	public static function NextEvents($renderSubMenuContainer=false) {
		$cursos = new Cursos();
		$interval = EventControls::defaultInterval();
		$lst = $cursos->nextevents(null, $interval);
		
		if ($lst) {
            if ($renderSubMenuContainer) echo "<div class='submenu_item'>";
    		echo "<h2 class='cursos'>Programação</h2>";
            
			foreach ($lst as $c) {
				echo "<p class='event'>
								<a href='/cursos.php?t=$c->seokey' class='title'><span>$c->nome</span></a>
								<span class='date'>".date('d/m', strtotime($c->datainicio))."&nbsp;|</span>
								<span class='local'>$c->local</span>
							</p>";
			}
            
            if ($renderSubMenuContainer) echo "</div>";
            
		} else {
			//echo "</p><i>Nenhum evento previsto.</i></p>";
		}  
	}
	
    public static function AgendaItems() {
        
    	echo '<h2>Agenda</h2>
            <ul class="servicos_list">
                <li><a href="cursos.php?t=cursos">Cursos</a></li>
                <li><a href="cursos.php?t=eventos">Eventos</a></li>
                <li><a href="cursos.php?t=formacao">Formação</a></li>
            </ul>';
    }
    
    public static function FaleConosco() {       
        echo '<h2 class="contato">Fale Conosco</h2>
    	       <p>Clique <a href="contato.php" title="Fale conosco!">aqui</a> e solicite mais informações  de como
                contratar nossos serviços para sua equipe ou empresa.</p>
                
                <p><small>Ligue hoje para mais informações:</small><br />
                    <span class="SubMenuDestaque">(+55 11) 5549-2943</span></p>';
    }
    
	public static function RenderCursoInfo($c, $showDetalhesButton = true) {
		echo "<div class='curso'>						
			    <p class='info'>
	               <span class='date'>".date('d/m', strtotime($c->datainicio))."&nbsp;$c->horario</span>
	               <span class='local'>$c->local</span>
	    		   <span class='endereco'>$c->endereco</span>
	    				<ul class='valor'>
	    					<li class='Hidden'>R$ ".number_format($c->valor1, 0, ',', '.')." para inscrições realizadas até ".date('d/m', strtotime($c->datalimite1))."</li>";
	    					if (($c->valor2) && ($c->valor2 > 0)) echo "<li class='Hidden'>R$ ".number_format($c->valor2, 2, ',', '.')." para inscrições realizadas até ".date('d/m', strtotime($c->datalimite2))."</li>";
	    					if (($c->descontoassociado) && ($c->descontoassociado > 0)) echo "<li>Associados terão $c->descontoassociado% de desconto</li>";
	    					if (($c->descontogrupo) && ($c->descontogrupo > 0)) echo "<li>Grupos com, no mínimo, $c->grupominimo pessoas terão $c->descontogrupo% de desconto acumulativo</li>";
	    echo "		</ul>
	            </p>
					";
					if ($showDetalhesButton) {
						//echo "<a href='cursos.php?id=$c->id'><img src='Images/button-detalhes-small.jpg' title='Clique para exibir destalhes deste curso' /></a>";
						echo "<div class='Buttons'>";
							Button::Render(null, 'Inscreva-se!', "cursos_inscricao.php?cursoId=$c->id", 'Faça sua inscrição neste curso', '', true, 'positive');
							Button::Render(null, 'Saiba Mais', "cursos.php?t=$c->seokey", 'Sabia mais sobre este curso', 'list');
						echo "</div>";
					}						
		echo "	</div>";
				
	}	
}
?>