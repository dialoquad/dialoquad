<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
 *Template Name: Categories 
 */

/**
* page-274 Template
*
*
* @file           page-274.php
* @package        Responsive 
* @author         Emil Uzelac 
* @copyright      2003 - 2013 ThemeID
* @license        license.txt
* @version        Release: 1.1
* @filesource     wp-content/themes/responsive/page-275.php
* @since          available since Release 1.0
*/
?>
<?php get_header(); ?>
<div class="wide-container">
	<div id="content-archive" class="grid col-940">
		<div id="inner-content">

			<?php if (has_nav_menu('category-menu', 'responsive')):?>
				<div id="category-menu">
					<?php wp_nav_menu(array(
					'container'       => '',
					'fallback_cb'	  =>  false,
					'menu_class'      => 'category-menu',
					'theme_location'  => 'category-menu')
					);?>
				</div>
			<?php endif ?>
		</div><!-- end of #inner-content --> 
	</div>
	<!-- end of #content-archive -->
</div><!-- end of .narrow-container -->
<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
