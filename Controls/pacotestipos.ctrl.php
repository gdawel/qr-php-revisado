<?php
include_once dirname(__file__) . "/../App_Code/SqlHelper.class";
include_once dirname(__file__) . "/../App_Code/Produto.class";

class PacotesTipos
{
    public static function Render()
    {        
        $produtos = new Produtos();
        $pacotes_tipos = $produtos->getPacotesTipos();
        
        if ($pacotes_tipos) {
            foreach ($pacotes_tipos as $tipo) {
                /*echo "
                     <div class='pacote PacoteTipo$tipo->id '>
								<a href='quest.php?tipo=$tipo->id' title='Clique para saber mais'><img src='Images/box-quest-$tipo->id.png' alt='$tipo->nome' /></a>
								<p class='PacoteDescription'>$tipo->introducao</p>
							</div>
                ";*/
                echo "<div class='grid_12 alpha omega'><div class='ProdutoDestaque'>";
                    echo "<img src='/sobrare/CSS/Images/box-quest-$tipo->id.png' alt='$tipo->nome' class='FloatRight' />";
                    if ($tipo->nome) echo "<h2>$tipo->nome</h2>";                    
                    if ($tipo->introducao) echo "$tipo->introducao";
                    //if ($d->url) {
                        $label = 'Sabia mais [+]';
                        echo "<div class='url'>
                                <a href='quest.php?tipo=$tipo->id' title='$tipo->nome - $label'>$label</a>
                              </div>";
                    //}
                echo "</div></div>";
            }
        } else {
            echo "<p>Nenhum item encontrado.</p>";
        }
		
        echo "<hr class='clear' />";
    }
}

class Servicos {
    public static function RenderSubMenu() {
        $manager = new FrontEndManager();
        $lst = $manager->getItemsByTipoId(CONTENT_SERVICOS_DESTAQUE);
        echo "<h2>Serviços</h2>";

                if ($lst) {
                    echo "<ul class='servicos_list'>";
                    //Button::Render(null, 'Mapeamento', 'quest.php?sectionId=6', 'Saiba mais', 'servico-mapeamento');
                    //Button::Render(null, 'Consultoria', 'quest.php?sectionId=7', 'Saiba mais', 'servico-consultoria');
                    //Button::Render(null, 'Treinamento e Assessoria', 'quest.php?sectionId=8', 'Saiba mais', 'servico-treinamento');
                    foreach ($lst as $d) {
                        echo "<li><a href='$d->url' title='$d->title'>$d->title</a></li>";
                    }
                        
                    echo "</ul>";
                }

    }
    
    public static function RenderDestaque() {
        $manager = new FrontEndManager();
        $lst = $manager->getItemsByTipoId(CONTENT_SERVICOS_DESTAQUE);
        
        if ($lst) {
            echo '
            <div class="tabs margin-top24 gray-border-bott">
					<ul class="nav nav-tabs text-center">';
                        $css = 'active';
                        foreach ($lst as $d) {
                            echo "<li class='$css'><a href='#tab$d->id' data-toggle='tab'>$d->title</a></li>";
                            $css = '';
                        }
			echo '
                    </ul>
					<div class="tab-content">
            ';
            $css = 'active';
            foreach ($lst as $d) {
                echo "<div class='tab-pane $css' id='tab$d->id'>
                        <div class='simple-post'>
                            $d->texto
                        </div>
                      </div>";
                $css = '';
            }

            echo '
                    </div> <!--tab-content-->
            ';
        }
    }
}
?>