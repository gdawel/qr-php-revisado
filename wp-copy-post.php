<?php
header('Content-Type: text/html; charset=utf-8'); ?>

<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<div class="container">

<?php

$dsn = 'mysql:dbname=georgebarbosa12;host=mysql13.georgebarbosa.com.br';
$user = 'georgebarbosa12';
$password = 'Sbr4952@@Aris';

$pdo = new PDO($dsn, $user, $password);

if(isset($_GET['ini'])) { $ini = $_GET['ini']; } else { $ini = 0; };
if(isset($_GET['fim'])) { $fim = $_GET['fim']; } else { $fim = 99999; };


$data = $pdo->query("SELECT * FROM wp_posts 
                        WHERE ID > $ini AND ID < $fim 
                        AND post_type = 'post' ORDER BY ID")->fetchAll();
$count = 0;
// and somewhere later:
foreach ($data as $row) {
    
    echo "<h3> Post ID: " . $row['ID'] . 
                " / Título: " . utf8_encode($row['post_title']) . "<br>" .
                " -> Status: " . $row['post_status'] . 
                " / Data Publicação: " . $row['post_date'] . "</h3>";
    $limpar = 0;
    $rest = $row['post_content'];
    do {
        
        $pos1 = strpos($rest, "[");
        $pos2 = strpos($rest, "]");
        //echo "pos1=".$pos1." / pos2=".$pos2."<br>";
        $tamanho = strlen($rest);
        if ($pos2 === false) {
            $limpar = 1;
        } else {
            $limpar = 0;
            if ($pos1 == 0) {
                    $rest = substr($rest, $pos2+1, $tamanho);
                } else {
                    $rest = substr($rest, 0, $pos1) . substr($rest, $pos2+1, $tamanho);
                }
            }

    } while ($limpar == 0);

    echo "================ Início do Conteúdo do Post ID: " . $row['ID'] . " ================<br><br>";
    //echo "<p>" . utf8_encode($row['post_content']) . "</p><br>";
    echo "<p>" . utf8_encode($rest) . "</p><br>";
    echo "================ Fim do Conteúdo do Post ID: " . $row['ID'] . " ================";
}

?>
</div>
</html>