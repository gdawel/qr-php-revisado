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
    echo "<p>" . utf8_encode($row['post_content']) . "</p><br>";
}

?>
</div>
</html>