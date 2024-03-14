<?php
if (!session_id()) session_start();

/*set default page title*/
if (!isset($pageTitle)) $pageTitle = 'SOBRARE Cockpit';
/*set emprty additional header code*/
if (!isset($header)) $header = '';
ob_clean();
$version = 56;
?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="pt-BR">
<head>
	<title><?php echo $pageTitle; ?></title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="author" content="SOBRARE - Sociedade Brasileira de Resiliência" />
	<meta name="keywords" content="sobrare, resiliencia, resiliência, pesquisa, teste" />
	<meta name="description" content="Site da Sociedade Brasileira de Resiliência. Saiba mais sobre resiliência, conheça os modelos de crenças determinantes e capacite-se em nossos cursos sobre resiliência." />
	
	<link media="screen" href="../CSS/Grid16.css" rel="stylesheet" type="text/css" />
	<link media="screen" href="../CSS/StyleSheet.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />
	<link media="screen" href="../CSS/Buttons.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />
	<link media="screen" href="../Includes/jQueryCSS/redmond/jquery-ui-1.8.14.custom.css" rel="stylesheet" type="text/css" />
	<meta name="robots" content="all" />
	
	<script language="JavaScript" src="../Includes/gen_validatorv31.js" type="text/javascript"></script>
	<script type="text/javascript" src="../Includes/jquery.min.js"></script>
	<script type="text/javascript" src="../Includes/jquery.meio.mask.js"></script>
	<script type="text/javascript" src="../Includes/jquery-ui-1.8.7.custom.min.js"></script>
	<script type="text/javascript" src="../Includes/common.js?v=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="../Includes/animatedcollapse.js"></script>

	<?php echo $header; ?>
</head>

<body>
	<div id="container" class="container_16 Cockpit">
		<div id="header" class="Cockpit">
			<div id="logo">
				<a href="index.php" title="Ir para Home da SOBRARE"><img src="../CSS/Images/logo2_small.png" /></a>
			</div>
			
			<div id="commands">
				<a href="config.php" title='Opções e configurações do Cockpit'>Mudar Senha</a>
				&nbsp;
				<a href="../logout.php" title='Logout'>Sair</a>
			</div>
		</div>
		
		<div id="content">				
			<?php
					echo '<div id="main" class="grid_16"><div class="padding_0">';
									Router();
					echo '</div></div>';
			?>
		</div>
		
		<hr class="clear" />
		<div id="pre-footer" class="Cockpit"></div>
	</div>
		
	<div id="footer" class="container_12 Cockpit">
		<p>QUEST_Resiliência © Todos os direiros reservados</p>
		<p>SOBRARE Sociedade Brasileira de Resiliência - CRPJ 3825/J</p>
	</div>	
	
	<script type="text/javascript">
	setTimeout(function(){var a=document.createElement("script");
	var b=document.getElementsByTagName("script")[0];
	a.src=document.location.protocol+"//script.crazyegg.com/pages/scripts/0031/6914.js?"+Math.floor(new Date().getTime()/3600000);
	a.async=true;a.type="text/javascript";b.parentNode.insertBefore(a,b)}, 1);
	</script>
</body>
</html>