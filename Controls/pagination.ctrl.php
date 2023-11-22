<?php

class Pagination {
	
	static function Render($urlformatstring, $totalrows, $pagesize, $currentpage) {
		$totalpages = intval($totalrows / $pagesize) + ($totalrows % $pagesize > 0 ? 1:0);
		
		if ($totalpages > 1) {
			echo "<div class='my-pagination'>";
			
            //verificar se precisa fazer um folding, se houver muitas paginas
            $maxdisplayed = 17;
            
            $isFolded = false;           
            if ($totalpages > $maxdisplayed) {
                $firstpagenumber = ($currentpage - intval($maxdisplayed/2) < 1) ? 1 : $currentpage - intval($maxdisplayed/2);
                $lastpagenumber = ($currentpage + intval($maxdisplayed/2) > $totalpages) ? $totalpages : $currentpage + intval($maxdisplayed/2);
                $isFolded = true;
            } else {
                $firstpagenumber = 1;
                $lastpagenumber = $totalpages;
            }
            
            //show link for first page, if folded
            if ($isFolded && $firstpagenumber != 1) {
                Pagination::RenderItem($urlformatstring, 1, '');
                echo "<span>...<span>";
            }
            
            //render pagenumbers
			for ($p = $firstpagenumber; $p <= $lastpagenumber; $p++) {
				if ($p == $currentpage) $class='current'; else $class='';
				Pagination::RenderItem($urlformatstring, $p, $class);
			}
			
            //show link for last page, if folded
            if ($isFolded && $lastpagenumber != $totalpages) {
                echo "<span>...<span>";
                Pagination::RenderItem($urlformatstring, $totalpages, '');
            }
            
			echo "</div>";
		}
	}
    
    static function RenderItem($urlformatstring, $pageNumber, $class) {
        echo "<a href='";
		printf($urlformatstring, $pageNumber);
		echo "' class='$class'>$pageNumber</a>";
    }
	
}