<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
* page-275 Template
*
*
* @file           page-275.php
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
  			<ul>
    			<?php
    			$recentPosts = new WP_Query( );
    			$recentPosts->query('posts_per_page=-1');
				?>
    			<?php while ($recentPosts->have_posts()) : $recentPosts->the_post(); ?>
    			<?php if (mysql2date("Y", $post->post_date) != $year) {
				$year = mysql2date("Y", $post->post_date);
				$ycount=1;
				}
				if (mysql2date("F", $post->post_date) != $month) {
				$month = mysql2date("F", $post->post_date);
				$mcount=1;
				}

				if ($ycount == 1) {
				$posts = get_posts('year=' . $year . '&posts_per_page=-1');
				$count = count($posts);
				$ytmp = get_the_time('Y');
				?><br><div class="allpost-year"><h1><a href="<?php echo get_year_link($ytmp); ?>"><?php
							the_time('Y'); echo '(' . $count . ' Articles)<br>';?></a></h1>
    			</div><?php

				} 
				if ($mcount == 1) {
				$ytmp = get_the_time('Y');
				$mtmp = get_the_time('m');
				?>
				<div class="allpost-month"><a href="<?php echo get_month_link( $ytmp, $mtmp ); ?>"><?php	the_time('F'); echo  '<br>';?></a></div><?php
				}
				$ycount++;
				$mcount++;?>
    			<li><div class="allpost-posts"><div class="allpost-thumb">
    					<?php if ( has_post_thumbnail()) : ?>
   						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
   							<?php the_post_thumbnail('allpost-thumb'); ?>
   						</a>
 						<?php endif; ?></div><div class="allpost-content">
    					<div class="allpost-thepost">
    						<a href="<?php the_permalink() ?>" rel="bookmark">
      							<?php the_title(); ?>

      					</a></div><div class="allpost-meta"><?php responsive_post_meta_data(); ?></div>
						<div class="fb-like" id="facebook-like" data-href=<?php echo get_site_url();?> data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
      				</div>

      			</div></li>

    			<?php wp_reset_postdata();endwhile; ?>
  			</ul>
  		</div><!-- end of #inner-content --> 

	</div>
	<!-- end of #content-archive -->
</div><!-- end of .narrow-container -->
<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
