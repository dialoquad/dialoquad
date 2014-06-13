<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
* Home Widgets Template
*
*
* @file           sidebar-home.php
* @package        Responsive 
* @author         Emil Uzelac 
* @copyright      2003 - 2013 ThemeID
* @license        license.txt
* @version        Release: 1.0
* @filesource     wp-content/themes/responsive/sidebar-home.php
* @link           http://codex.wordpress.org/Theme_Development#Widgets_.28sidebar.php.29
* @since          available since Release 1.0
*/
?>  

<a name="random"></a>
<div class="wide-container">
	<div class="sectionDivider">
		<h1>
			<span>Postlaroid</span>
		</h1>
	</div>
	<div id="random-x6">

		<?php	$count = 1;
		$recentPosts = new WP_Query( array ( 'orderby' => 'date', 'posts_per_page' => '-1' ) );?>



		<?php while ($recentPosts->have_posts()) : $recentPosts->the_post(); ?>
		<?php if( $count>6 ) { break;}?>
		<?php if( has_post_thumbnail() ) { ?>
		<div class="grid col-300<?php if($count%3 == 0){echo ' fit';}?>">
			<div class="random-wrapper" id="widgets">
				<div class="random">
					<?php if ( has_post_thumbnail()) : ?>
   					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
   						<?php the_post_thumbnail('random-thumb'); ?>
   					</a>
 					<?php endif;?>
					<h1 class="widget-title">
						<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'responsive'), the_title_attribute('echo=0')); ?>"><?php the_title(); ?></a></h1>

					<?php wpe_excerpt('wpe_excerptlength_random', 'wpe_excerptmore');  $count++;?>
					<iframe id="facebook-like" src="//www.facebook.com/plugins/like.php?href=<?php echo get_site_url();?>/?p=<?php echo get_the_ID();?>&send=false&layout=button_count&width=450&show_faces=true&action=like&colorscheme=light&font&height=80" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
				</div>


		</div></div>
		<?php } ?>


		<?php endwhile; ?>


	</div>
</div><!-- end of .narrow-container -->

<a name="wfooter"></a>
<div class="wide-container">
	<div class="sectionDivider">
		<h1>
			<span>Athlete's footer</span>
		</h1>
	</div>
	<div id="widget-footer">
    	<div id="widgets" class="home-widgets">
        	<div class="grid col-220 footerdeco">
        		<?php responsive_widgets(); // above widgets hook ?>

        		<?php if (!dynamic_sidebar('home-widget-1')) : ?>
        		<div class="widget-wrapper">

        			<div class="widget-title-home"><h3><?php _e('Home Widget 1', 'responsive'); ?></h3></div>
        			<div class="textwidget"><?php _e('This is your first home widget box. To edit please go to Appearance > Widgets and choose 6th widget from the top in area 6 called Home Widget 1. Title is also manageable from widgets as well.','responsive'); ?></div>

				</div><!-- end of .widget-wrapper -->
				<?php endif; //end of home-widget-1 ?>

        		<?php responsive_widgets_end(); // responsive after widgets hook ?>
        	</div><!-- end of .col-300 -->

        	<div class="grid col-220 footerdeco">
        		<?php responsive_widgets(); // responsive above widgets hook ?>

				<?php if (!dynamic_sidebar('home-widget-2')) : ?>
        		<div class="widget-wrapper">

        			<div class="widget-title-home"><h3><?php _e('Home Widget 2', 'responsive'); ?></h3></div>
        			<div class="textwidget"><?php _e('This is your second home widget box. To edit please go to Appearance > Widgets and choose 7th widget from the top in area 7 called Home Widget 2. Title is also manageable from widgets as well.','responsive'); ?></div>

				</div><!-- end of .widget-wrapper -->
				<?php endif; //end of home-widget-2 ?>

        		<?php responsive_widgets_end(); // after widgets hook ?>
        	</div><!-- end of .col-300 -->

        	<div class="grid col-220 footerdeco foot3">
        		<?php responsive_widgets(); // above widgets hook ?>

				<?php if (!dynamic_sidebar('home-widget-3')) : ?>
        		<div class="widget-wrapper">

        			<div class="widget-title-home"><h3><?php _e('Home Widget 3', 'responsive'); ?></h3></div>
        			<div class="textwidget"><?php _e('This is your third home widget box. To edit please go to Appearance > Widgets and choose 8th widget from the top in area 8 called Home Widget 3. Title is also manageable from widgets as well.','responsive'); ?></div>

				</div><!-- end of .widget-wrapper -->
				<?php endif; //end of home-widget-3 ?>

        		<?php responsive_widgets_end(); // after widgets hook ?>
        	</div><!-- end of .col-300 -->

        	<div class="grid col-220 footerdeco fit">

        		<?php responsive_widgets(); // responsive above widgets hook ?>

				<?php if (!dynamic_sidebar('home-widget-4')) : ?>
        		<div class="widget-wrapper">

        			<div class="widget-title-home"><h3><?php _e('Home Widget 4', 'responsive'); ?></h3></div>
        			<div class="textwidget"><?php _e('This is your second home widget box. To edit please go to Appearance > Widgets and choose 7th widget from the top in area 7 called Home Widget 4. Title is also manageable from widgets as well.','responsive'); ?></div>

				</div><!-- end of .widget-wrapper -->
				<?php endif; //end of home-widget-2 ?>

        		<?php responsive_widgets_end(); // after widgets hook ?>
        	</div><!-- end of .col-300 fit -->


    	</div><!-- end of #widgets -->
    </div><!-- end of widget-footer -->
</div>
<!-- end of .wide-container -->
