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

<!-- favicons -->
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />
<link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-iphone.png" /> 
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad.png" /> 
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-iphone-retina.png" />
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_stylesheet_directory_uri(); ?>/touch-icon-ipad-retina.png" />


<link href='http://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Asap:700italic' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>


<?php wp_enqueue_style('responsive-style', get_stylesheet_uri(), false, '1.8.8');?>

<?php if(!is_handheld()){	
	echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/style-desktop.css">';
}else{	
	echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/style-mobile.css">';
}?>

<!-- Start of custom header code -->

<!-- Metro bootstrap code -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/metro/iconFont.min.css">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/metro/metro-bootstrap.min.css">

<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery.dotdotdot.js"></script>

<!-- FishEye dock -->
<script type="text/javascript" language="javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/interface.js"></script>

<!-- Slide-Out Mmenu -->
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/hammer.js/1.0.5/jquery.hammer.min.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo get_stylesheet_directory_uri()?>/mmenu/jquery.mmenu.min.all.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo get_stylesheet_directory_uri()?>/mmenu/jquery.mmenu.dragopen.min.js"></script>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/mmenu/jquery.mmenu.all.css">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()?>/mmenu/jquery.mmenu.dragopen.css">
<script type="text/javascript">
<?php if( is_mobile()){ ?>
	$(function() {
		$('nav#mmenu').mmenu({
			searchfield: {
				add: true,
				search: false
			},
			dragOpen: {
				open: true,
				threshold: 180,
				maxStartPos: 512
			}
		});
	});
<?php } ?>
</script>
<?php if (!is_mobile() && ( is_category() || is_single() || is_search())) { ?>
<style type="text/css">
	#cssmenu { display: block !important; }
	#search-head #searchbox { display:block; }
</style>
<?php } ?>

<?php if ( is_page(274) ) { ?>
<?php require_once( get_template_directory() . '/head-category.php' );?>
<?php } ?>

<?php if ( is_page('tag-fog') ) { ?>
<?php } ?>

<?php if ( is_page() ) { ?>
<style type="text/css">
	.breadcrumb-list,.post-meta,#respond { 
		display: none !important; 
	}
</style>
<?php } ?>
<?php if(is_single()){ ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/bubble.js?ver=1.0"></script> 
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/speaker.js?ver=1.0"></script> 
<?php require_once( get_template_directory() . '/head-category.php' );?>
<?php } ?>
<?php if(is_home()): ?>
	<?php if(!is_mobile()){ ?>
		<style type="text/css">
			#search-head #searchbox { display:block; }
		</style>
	<?php }?>
<?php require_once( get_template_directory() . '/head-category.php' );?>
<?php require_once( get_template_directory() . '/head-home.php' );?>
<?php endif;?>


<!-- Document Ready scripts -->
<script type="text/javascript" language="javascript">
(function( window, $, undefined ) {
	$(document).ready(function(){

		/* Dotdotdot for windows resizing */
		$(window).bind("load resize", function(){
			$('.dotdotdot').dotdotdot({wrap:'letter'});

			$('.srp-post-title').dotdotdot({wrap:'letter'});
		});


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
		
		$("#logo .logo-img img").css('visibility','visible');

		<?php if (!is_handheld()) { ?>
	    
			/* Dock -- Left */
			$('#dock').Fisheye({
				maxWidth: 60,
				items: 'a',
				itemsText: 'span',
				container: '.dock-container',
				itemWidth: 40,
				itemPadding: 10,
				proximity: 80,
				halign : 'center'
			});

			/* Anchor slide */

			$("a[href*=#]").on('click', function(event){
				var href = $(this).attr("href");
				if ( /(#.*)/.test(href) ){
					var hash = href.match(/(#.*)/)[0];
					var path = href.match(/([^#]*)/)[0];

      				if (window.location.pathname == path || path.length == 0){
        				event.preventDefault();
						$link = $('[name="' + $.attr(this, 'href').substr(1) + '"]');
        				$('html,body').animate({scrollTop:$link.offset().top}, 250, "swing");
        				window.location.hash = hash;
      				}
    			}
			});

		<?php }?>

	});
})( window, jQuery );
</script>

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
	<a class="scroll-anchor" name="dqtop" data-text="Back to Top"></a>

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1&appId=493040587425313&version=v2.0";
		fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
	<?php responsive_container(); // before container hook ?>
	<div id="container" class="hfeed">

    	<?php responsive_header(); // before header hook ?>

    	<div id="header">
    		<div class="wide-container">
				<?php if(is_mobile()){ ?>
				<a href="#mmenu" id="mm-button"></a>
				<?php }?>
    			<?php if (has_nav_menu('top-menu', 'responsive')) { ?>
				<?php wp_nav_menu(array(
				'container'       => 'nav',
				'container_id'       => 'mmenu',
				'container_class'       => 'mm-white',
				'fallback_cb'	  =>  false,
				'menu_class'      => 'top-menu',
				'theme_location'  => 'top-menu')
				); 
				?>
				<?php } ?>

				<?php responsive_in_header(); // header hook ?>

				<?php if ( get_header_image() != '' ) : ?>

				<div id="logo">
    				<a href="<?php echo home_url('/'); ?>" class="logo-img" ><img src="<?php header_image(); ?>" width="<?php if(function_exists('get_custom_header')) { echo get_custom_header() -> width;} else { echo HEADER_IMAGE_WIDTH;} ?>" height="<?php if(function_exists('get_custom_header')) { echo get_custom_header() -> height;} else { echo HEADER_IMAGE_HEIGHT;} ?>" alt="<?php bloginfo('name'); ?>"/></a><a href="<?php echo home_url('/'); ?>" class="logo-caption">Dialoquad</a>
    			</div><!-- end of #logo -->

    			<?php endif; // header image was removed ?>

    			<?php if ( !get_header_image() ) : ?>

    			<div id="logo">
    				<span class="site-name"><a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></span>
    				<span class="site-description"><?php bloginfo('description'); ?></span>
    			</div><!-- end of #logo -->  

    			<?php endif; // header image was removed (again) ?>
			<div class="no-mobile"> 			
				<?php get_search_form(); ?>
			</div>
    		</div><!-- end of .narrow-container -->
    		<div class="landing-box">
    			<div class="wide-container">
    				<?php get_sidebar('top'); ?>
					<div id="cssmenu">
						<?php wp_nav_menu(array(
						'container'       => '',
						'theme_location'  => 'header-menu')
						); 
						?>
					</div>
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
