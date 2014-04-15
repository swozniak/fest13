<?php
	$title = ( is_home() ) ? get_bloginfo( 'description' ) : 'PRE-FEST 2' . wp_title( '&raquo;', false );
	$desc = 'The Fest is an annual music festival in Gainesville, Florida, organized by No Idea Records.';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<title><?php echo $title; ?></title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo $desc; ?>" />

	<link rel="shortcut icon" type="image/x-icon" href="<?php bloginfo( 'template_directory '); ?>/img/favicon.ico" />

	<link href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" rel="stylesheet" media="screen, projection" />

	<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>"/>
	<meta property="og:title" content="<?php echo $title; ?>" />
	<meta property="og:image" content="<?php bloginfo( 'template_directory' ); ?>/img/og-image.jpg" />
	<meta property="og:url" content="http://<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>" />
	<meta property="og:description" content="<?php echo $desc; ?>" />

	<meta name="twitter:card" content="summary">
	<meta name="twitter:site" content="@thefestfl">
	<meta name="twitter:title" content="<?php echo $title; ?>">
	<meta name="twitter:description" content="<?php echo $desc; ?>">
	<meta name="twitter:creator" content="@thefestfl">
	<meta name="twitter:image" content="<?php bloginfo( 'template_directory' ); ?>/img/og-image.jpg">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Open+Sans:400,300,700,800' rel='stylesheet' type='text/css'>


	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-48284793-1', 'thefestfl.com');
	  ga('send', 'pageview');

	</script>
	<?php wp_head(); ?>
  </head>
 
  <body>
	<div class="container">
	  <header>
		<div class="row" id="fest-tier-one-sponsors">
		  <a target="_blank" href="http://www.bandsonabudget.com/" title="Bands on a Budget"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/boab.png" alt="Bands on a Budget" /></a>
		  <a target="_blank" href="http://www.cwpress.com/" title="Commonwealth Press"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/cwp.png" alt="Commonwealth Press" /></a>
		  <a target="_blank" href="http://www.orangeamps.com/" title="Orange Amplification"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/orange.png" alt="Orange Amplification" /></a>
		  <a target="_blank" href="http://store.noidearecords.com/" title="No Idea Records"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/noidea.png" alt="No Idea Records" /></a>
		  <a target="_blank" href="http://www.sjcdrums.com/" title="SJC Drums"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/sjc.png" alt="SJC Drums" /></a>
		  <a target="_blank" href="http://www.pabstblueribbon.com/" title="Pabst Blue Ribbon"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_1/pbr.png" alt="Pabst Blue Ribbon" /></a>
		</div>
		<div class="row" id="fest-splash">
		  <a href="<?php bloginfo( 'url' ); ?>/prefest"><img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/prefest_splash.jpg" /></a>
		</div>

		<nav class="navbar navbar-default" id="fest-nav" role="navigation">
		  <div class="container-fluid">
			<!-- Mobile nav display -->
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#fest-nav-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <a class="navbar-brand" href="#"></a>
			</div>

			<!-- Full width display -->
			<div class="collapse navbar-collapse" id="fest-nav-collapse">
			  <?php wp_nav_menu( array( 'theme_location' => 'prefest-nav-1', 'menu_id' => 'fest-nav-1', 'menu_class' => 'nav navbar-nav navbar-right', 'depth'=> 3, 'container'=> false, 'walker'=> new Bootstrap_Walker_Nav_Menu ) ); ?>
			  <?php wp_nav_menu( array( 'theme_location' => 'prefest-nav-2', 'menu_id' => 'fest-nav-2', 'menu_class' => 'nav navbar-nav navbar-right', 'depth'=> 3, 'container'=> false, 'walker'=> new Bootstrap_Walker_Nav_Menu ) ); ?>
			  <?php wp_nav_menu( array( 'theme_location' => 'prefest-nav-3', 'menu_id' => 'fest-nav-3', 'menu_class' => 'nav navbar-nav navbar-right', 'depth'=> 3, 'container'=> false, 'walker'=> new Bootstrap_Walker_Nav_Menu ) ); ?>
			</div>
		  </div>
		</nav>
	  </header>

	  <div class="content-container">
		<div class="row" id="content">
