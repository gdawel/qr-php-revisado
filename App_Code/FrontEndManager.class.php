<?php
include_once 'SqlHelper.class.php';
include_once 'Filter.class.php';

class ContentItem {
	var $id, $tipoid, $tipo, $texto, $title, $subtitle, $url, $urllabel, $index;
	
	function __construct() {}
    
    function renderDefaultView() {
        //if ($this->title) echo "<h1>$this->title</h1>";
        if ($this->subtitle) echo "<h2>$this->subtitle</h2>";
        
        if ($this->texto) echo "$this->texto";
        if ($this->url) {
            $label = ($this->urllabel) ? $this->urllabel : 'Sabia mais [+]';
            echo "<a href='$this->url' title='$label'>$label</a>";
        }
    }
    
    function renderDestaque($css = '') {
        $label = ($this->urllabel) ? $this->urllabel : 'Sabia mais [+]';
        $icon = ($this->subtitle) ? $this->subtitle : 'icon-book';

        echo "
            <div class='col-md-4 padding-top48'>
                <div class='inline-block services-icon static-icon'>
                    <i class='$icon'></i>
                </div>
                <div class='padding-top24'>
                    <h3 class='padding-bottom12'>$this->title</h3>
                    <p>$this->texto</p>
                    <a class='paralax-button retina-button letters-white dark-gray-bg margin-top12 text-center display-block' href='$this->url'>$label</a>
                </div>
            </div>
        ";
    }

    function renderBanner2() {
        global $app_path;

        $label = ($this->urllabel) ? $this->urllabel : 'Sabia mais [+]';

        echo "  <div class='slide'>
                    <a href='$this->url' title='$label'>
                        <img src='$app_path/Uploads/banner$this->index.png' alt='$this->title' />
                    </a>
                </div>";
    }

    function renderBanner() {
        global $app_path;

        $label = ($this->urllabel) ? $this->urllabel : 'Sabia mais [+]';

        echo "
            <li data-transition='fade' data-slotamount='1' data-masterspeed='300' data-thumb='' onclick=\"javascript:document.location = '$this->url'; return false;\">
                <img src='$app_path/Theme/images/general-bg/bg1.jpg' alt='' />

                <div class='caption fade stb'
                     data-x='-60'
                     data-y='80'
                     data-speed='800'
                     data-start='300'
                     data-easing='easeOutExpo'><img src='$app_path/Uploads/banner$this->index.jpg' alt='$this->title' /></div>

                <div class='caption very_big_white fade stl'
                     data-x='420'
                     data-y='80'
                     data-maxwidth='600'
                     data-speed='700'
                     data-start='700'
                     data-easing='easeOutExpo'>$this->title</div>

                <div class='caption very_small_white fade stl'
                     data-x='420'
                     data-y='180'
                     data-maxwidth='600'
                     data-speed='700'
                     data-start='800'
                     data-easing='easeOutExpo'>

                     $this->texto

                     <br /><br />
                     <a href='$this->url' title='$label'>$label</a>
                 </div>
            </li>
        ";
    }
}

define('CONTENT_DESTAQUE', 1);
define('CONTENT_HOME', 2);
define('CONTENT_BENEFICIO', 3);
define('CONTENT_HOME_BANNERS_SECUNDARIOS', 14);
define('CONTENT_QUEST_HISTORIA', 4);
define('CONTENT_SERVICOS', 5);
define('CONTENT_SERVICOS_MAPEAMENTO', 6);
define('CONTENT_SERVICOS_CONSULTORIA', 7);
define('CONTENT_SERVICOS_TREINAMENTO', 8);
define('CONTENT_SERVICOS_PESQUISAS_ACADEMICAS', 12);
define('CONTENT_SERVICOS_ESCOLAS', 13);
define('CONTENT_SERVICOS_COACHING', 15);
define('CONTENT_SERVICOS_DESTAQUE', 10);
define('CONTENT_PRODUTOS', 9);
define('CONTENT_QUEM_SOMOS', 11);


class FrontEndManager {
	var $error;
	
	function __construct() {}
    
    function getObjectFromReader($r) {
        $i = new ContentItem();
        $i->id = $r['ContentId'];
        $i->tipoid = $r['TipoId'];
        $i->tipo = $r['Tipo'];
        $i->texto = $r['Texto'];
        $i->title = $r['Title'];
        $i->subtitle = $r['SubTitle'];
        $i->url = $r['Url'];
        $i->urllabel = $r['UrlLabel'];
        $i->index = $r['Index'];
        
        return $i;
    }
    
    /*
    function getItemByTipoId($tipoid = CONTENT_HOME) {
        $sql = new SqlHelper();
        
        $sql->command = "SELECT c.*, t.Tipo FROM contents c
                            INNER JOIN contents_tipos t ON c.TipoId = t.TipoId
                            WHERE c.TipoId = $tipoid
                            ORDER BY `Index` LIMIT 0, 1";
        if ($sql->execute()) {
            $r = $sql->fetch();
            $i = $this->getObjectFromReader($r);
        } else {
            $i = null;
            $this->error = $sql->error;
        }
        
        return $i;
    }*/
    
    function getItems($filter = null, $count = null) {
        if (!$filter) $filter = new Filter();
        
        $sql = new SqlHelper();
        
        if (is_int($count)) $limit = "LIMIT 0, $count"; else $limit = '';
        
        $sql->command = "SELECT c.*, t.Tipo 
                            FROM contents c
                            INNER JOIN contents_tipos t ON c.TipoId = t.TipoId
                            $filter->expression
                            ORDER BY t.Tipo, c.Index
                            $limit";
        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $i = $this->getObjectFromReader($r);
                //print_r($r);
                $lst[] = $i;
            }
        } else {
            $lst = null;
            $this->error = $sql->error;
        }
        
        return isset($lst) ? $lst : null;
    }
    
    function getItem($id) {
        $filter = new Filter();
        $filter->add('c.ContentId', '=', $id);
        $r = $this->getItems($filter);
        if ($r) return $r[0]; else return null;
    }
    
    function getItemsByTipoId($tipoid, $count = 10) {
        /*$sql = new SqlHelper();
        
        $sql->command = "SELECT c.*, t.Tipo 
                            FROM contents c
                            INNER JOIN contents_tipos t ON c.TipoId = t.TipoId
                            WHERE c.TipoId = $tipoid
                            ORDER BY `Index` LIMIT 0, $count";
        if ($sql->execute()) {
            while ($r = $sql->fetch()) {
                $i = $this->getObjectFromReader($r);
                $lst[] = $i;
            }
        } else {
            $lst = null;
            $this->error = $sql->error;
        }
        
        return isset($lst) ? $lst : null;*/
        $filter = new Filter();
        $filter->add('c.TipoId', '=', $tipoid);
        return $this->getItems($filter, $count);
    }
  
    function Save(&$c) {
		$sql = new SqlHelper();
		
		if ($c->id) {
			$sql->command = "UPDATE contents SET title=".$sql->escape_string($c->title, true).',
    												subtitle='.$sql->escape_string($c->subtitle, true).',
    												url='.$sql->escape_string($c->url, true).',
    												urllabel='.$sql->escape_string($c->urllabel, true).',
    												texto='.$sql->escape_string($c->texto, true).',
    												tipoid='.$sql->escape_string($c->tipoid, true).',
    												`index`='.$sql->escape_string($c->index, true)."
												WHERE ContentId = $c->id";
		} else {
			$sql->command = "INSERT INTO contents (title, subtitle, url, urllabel, texto, tipoid, `index`) VALUES (".
												$sql->escape_string($c->title, true).', '.
												$sql->escape_string($c->subtitle, true).','.
												$sql->escape_string($c->url, true).','.
												$sql->escape_string($c->urllabel, true).','.
												$sql->escape_string($c->texto, true).','.
												$sql->escape_string($c->tipoid, true).','.
												$sql->escape_string($c->index, true).')';
		}
		
		if (!$ret = $sql->execute()) 
            $this->error = $sql->error;
		else {
			if (!$c->id) $c->id = $sql->getInsertId();
		}
		return $ret;
	}
	
	function Delete($id) {
		$sql = new SqlHelper();
		
		$sql->command = "DELETE FROM contents WHERE ContentId = $id";
		return $sql->execute();
	}  
}
?>