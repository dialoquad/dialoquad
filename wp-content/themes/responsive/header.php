<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header Template
 *
 *
 * @file           header.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.3
 * @filesource     wp-content/themes/responsive/header.php
 * @link           http://codex.wordpress.org/Theme_Development#Document_Head_.28header.php.29
 * @since          available since Release 1.0
 */
?>
<!doctype html>
<!--[if !IE]>      <html class="no-js non-ie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="no-js ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]--><head>

<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

<title><?php wp_title('&#124;', true, 'right'); ?></title>

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.png" />

<link href='http://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Asap:700italic' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>


<?php wp_enqueue_style('responsive-style', get_stylesheet_uri(), false, '1.8.8');?>

<!-- Start of custom header code -->

<!-- Metro bootstrap code -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/metro/iconFont.min.css">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/metro/metro-bootstrap.min.css">
<script src="<?php echo get_stylesheet_directory_uri()?>/metro/metro.min.js"></script>

<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery.dotdotdot.js"></script>
<script type="text/javascript" language="javascript">
	$(function() {
		$('.dotdotdot').dotdotdot({wrap:'letter'});
		$('.srp-post-title').dotdotdot({wrap:'letter'});
	});
</script> 

<script>
	$(document).ready(function(){
		$(".icon-search").click(function() {
			$("#searchbox").slideToggle('fast');
		});

		$(".icon-user-2").parent().mouseenter(function() {
			$("#searchbox:visible").slideToggle('fast');
		});

		$("div.input-control input").focus(function() {
			$(this).parents(".input-control").css('outline', '0').css('border-color', '#919191');
		});
		
		$("div.input-control input").blur(function() {
			$(this).parents(".input-control").css('border-color', '#d9d9d9');
		});

		$("a:contains('Joomla Turbo')").css('display','none');
	});
</script>
<?php if ( is_category() || is_single() || is_search()) { ?>
<style type="text/css">
	.menu { display: block !important; }
	#search-head #searchbox { display:block; }
</style>
<?php } ?>

<?php if( is_home()) {?>
<style type="text/css">
	#search-head #searchbox { display:block; }
</style>
<?php } ?>

<?php if ( is_page() ) { ?>
<style type="text/css">
	.breadcrumb-list,.post-meta,#respond { 
		display: none !important; 
	}
</style>
<?php } ?>
<?php if ( is_single() ) { ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/bubble.js?ver=1.0"></script> 
<?php } ?>
<?php if(is_single() || is_home()){ ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/speaker.js?ver=1.0"></script> 
<?php } ?>
<?php if(is_home()): ?>
<noscript>
    <style>
        .cn-images img{position: relative;display: block;border-bottom: 5px solid #d0ab47;} 
        .cn-slideshow{height: auto;}
    </style>
</noscript>
<script id="barTmpl" type="text/x-jquery-tmpl">
<div class="cn-bar">
    <div class="cn-nav">
	<a href="#" class="cn-nav-prev">
    <span>Previous</span>
	<div style="background-image:url(${prevSource});"></div> 
	</a>
	<a href="#" class="cn-nav-next">
    <span>Next</span>
	<div style="background-image:url(${nextSource});"></div>
	</a>
    </div><!-- cn-nav -->
    <div class="cn-nav-content">
    <div class="cn-nav-content-prev">
    <span>上一則</span>
    <h3>${prevTitle}</h3>
    </div>
    <div class="cn-nav-content-current">
    <span>現正觀看</span>
    <h2>${currentTitle}</h2>
    </div>
    <div class="cn-nav-content-next">
    <span>下一則</span>
    <h3>${nextTitle}</h3>
    </div>
    </div><!-- cn-nav-content -->
    </div><!-- cn-bar -->
	</script>
<?php endif;?>

<!-- End of custom header code -->

<?php wp_head(); ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-39738806-1', 'dialoquad.net');
ga('send', 'pageview');
</script>


</head>
<body <?php body_class(); ?>>

	<?php responsive_container(); // before container hook ?>
	<div id="container" class="hfeed">

    	<?php responsive_header(); // before header hook ?>

    	<div id="header">
    		<div class="wide-container">
        		<?php if (has_nav_menu('top-menu', 'responsive')) { ?>
<?php wp_nav_menu(array(
	'container'       => '',
	'fallback_cb'	  =>  false,
	'menu_class'      => 'top-menu',
	'theme_location'  => 'top-menu')
); 
?>
        		<?php } ?>

    			<?php responsive_in_header(); // header hook ?>

				<?php if ( get_header_image() != '' ) : ?>

        		<div id="logo">
            		<a href="<?php echo home_url('/'); ?>"><img src="<?php header_image(); ?>" width="<?php if(function_exists('get_custom_header')) { echo get_custom_header() -> width;} else { echo HEADER_IMAGE_WIDTH;} ?>" height="<?php if(function_exists('get_custom_header')) { echo get_custom_header() -> height;} else { echo HEADER_IMAGE_HEIGHT;} ?>" alt="<?php bloginfo('name'); ?>" /></a>
        		</div><!-- end of #logo -->

    			<?php endif; // header image was removed ?>

    			<?php if ( !get_header_image() ) : ?>

        		<div id="logo">
            		<span class="site-name"><a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></span>
            		<span class="site-description"><?php bloginfo('description'); ?></span>
        		</div><!-- end of #logo -->  

    			<?php endif; // header image was removed (again) ?>
    			<div id="search-head" class="widget-wrapper widget_search metro">
					<?php get_search_form(); ?>
				</div>
     		</div><!-- end of .narrow-container -->
     		<div class="landing-box">
     			<div class="wide-container">
    				<?php get_sidebar('top'); ?>

					<?php wp_nav_menu(array(
					'container'       => '',
					'theme_location'  => 'header-menu')
					); 
					?>

            		<?php if (has_nav_menu('sub-header-menu', 'responsive')) { ?>
					<?php wp_nav_menu(array(
					'container'       => '',
					'menu_class'      => 'sub-header-menu',
					'theme_location'  => 'sub-header-menu')
					); 
					?>
            		<?php } ?>
				</div><!-- end of .wide-container -->
    		</div><!-- end of .landing-box -->
    	</div>
    	<!-- end of #header -->

    	<?php responsive_header_end(); // after header hook ?>

		<?php responsive_wrapper(); // before wrapper ?>
    	<div id="wrapper" class="clearfix">
    		<?php responsive_in_wrapper(); // wrapper hook ?>
