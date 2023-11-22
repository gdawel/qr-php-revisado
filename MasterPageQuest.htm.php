<?php
if (!session_id()) session_start();

/*set default page title*/
if (!isset($pageTitle)) $pageTitle = 'SOBRARE';
ob_clean();
$version = 35;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="pt-BR">
<head>
	<title><?php echo $pageTitle; ?></title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="author" content="SOBRARE - Sociedade Brasileira de Resili?ncia" />
	<meta name="keywords" content="sobrare, resiliencia, resili?ncia, pesquisa, teste" />
	<meta name="description" content="Site da Sociedade Brasileira de Resili?ncia. Saiba mais sobre resili?ncia, conhe?a os modelos de cren?as determinantes e capacite-se em nossos cursos sobre resili?ncia." />
	
	<link media="screen" href="../CSS/Grid8.css" rel="stylesheet" type="text/css" />
	<link media="screen" href="../CSS/StyleSheet.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />
	<link media="screen" href="../CSS/Buttons.css" rel="stylesheet" type="text/css" />
	<meta name="robots" content="all" />
	
	<script language="JavaScript" src="../Includes/gen_validatorv31.js" type="text/javascript"></script>
	<script type="text/javascript" src="../Includes/jquery.min.js"></script>
 	<script type="text/javascript" src="../Includes/jquery.meio.mask.js"></script>
 	<script type="text/javascript" src="../Includes/common.js?v=<?php echo $version; ?>"></script>
</head>

<body>
	<div id="container" class="container_8 Quest">
		<div id="header" class="Quest">			
			<div id="logo">
				<a href="../index.php" title="Ir para Home"><img src="../CSS/Images/logo2_small.png" /></a>
			</div>
			<div id="commands">
				<a href="../logout.php" title='Logout'>Sair</a>
			</div>
		</div>
		
		<div id="content" class="Quest">			
			<div id="main" class="grid_8">
				<?php Router(); ?>
			</div>
		</div>
		
		<hr class="clear" />
	</div>
	
	<script type="text/javascript">
	setTimeout(function(){var a=document.createElement("script");
	var b=document.getElementsByTagName("script")[0];
	a.src=document.location.protocol+"//script.crazyegg.com/pages/scripts/0031/6914.js?"+Math.floor(new Date().getTime()/3600000);
	a.async=true;a.type="text/javascript";b.parentNode.insertBefore(a,b)}, 1);
	</script>
</body>
</html>