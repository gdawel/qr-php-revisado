<?php

class BlogControls {
	static function LastsPosts($count = 2) {
        # INSTANTIATE CURL.
        $curl = curl_init();
        
        # CURL SETTINGS.
        curl_setopt($curl, CURLOPT_URL, "http://www.sobrare.com.br/blog/index.php/feed/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        
        # GRAB THE XML FILE.
        $xmlStr = curl_exec($curl);
        curl_close($curl);
        
        # SET UP XML OBJECT.
        $initPos = strpos($xmlStr, "<?xml");
        if ($initPos !== false)
            $xmlStr = substr($xmlStr, $initPos);
        $xmlStr = str_replace("<description><![CDATA[", '<description>', $xmlStr);
        $xmlStr = str_replace("]]></description>", '</description>', $xmlStr);
        $xmlStr = str_replace("<category><![CDATA[", '<category>', $xmlStr);
        $xmlStr = str_replace("]]></category>", '</category>', $xmlStr);
        $xmlStr = str_replace("slash:comments", 'slash_comments', $xmlStr);
        
        $xml = simplexml_load_string( $xmlStr );
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        return array_splice($array['channel']['item'], 0, $count);
        /*
        echo "<h2>Últimas Postagens</h2>";
        $tempCounter = 0;

        if ($array['channel']['item']) {
            foreach ( $array['channel']['item'] as $item )
            {
                if ( $tempCounter < $count )
                {
                    echo "<p class='event'>
                            <a href=\"$item[guid]\" class='title'><span>$item[title]</span></a>
                            <span class='date'>".date('d/m', strtotime($item['pubDate']))."&nbsp;|</span>
                            <span class='post_text'>".substr($item['description'], 0, 100)."[...]</span>
                         </p>";
                } else {
                    break;
                }
                $tempCounter += 1;
            }
        } else {
            echo "<p>Não foi possível carregar as últimas postagens.</p>";
        }
        */
	}
}
?>