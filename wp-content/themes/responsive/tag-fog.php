<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
 * Template Name: Tag-Fog 
 *
 * Tag-Fog Template
 *
 * @file           tag-fog.php
 */

?>
<?php get_header(); ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/tag-fog.js"></script>

<div class="narrow-container">
	<div id="featured-content" class="grid col-940">
        <div id="inner-content">
  			<?php if (have_posts()) : ?>
  			<?php while (have_posts()) : the_post(); ?>
  			<?php $options = get_option('responsive_theme_options'); ?>
  			<?php if ($options['breadcrumb'] == 0): ?>
  			<?php echo responsive_breadcrumb_lists(); ?>
  			<?php endif; ?>
  			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    			<h1 class="post-title">
      				<?php the_title(); ?>
    			</h1>
    			<?php if ( comments_open() ) : ?>
    			<div class="post-meta">
      				<?php responsive_post_meta_data(); ?>
      				<?php if ( comments_open() ) : ?>
      				<span class="comments-link"> <span class="mdash">&mdash;</span>
      					<?php comments_popup_link(__('No Comments &darr;', 'responsive'), __('1 Comment &darr;', 'responsive'), __('% Comments &darr;', 'responsive')); ?>
      				</span>
      				<?php endif; ?>
    			</div>
    			<!-- end of .post-meta -->
    			<?php endif; ?>
    			<div class="post-entry">
				<p class="brownian"><a>Brownian Motion</a></p>
				<p class="fog-restore"><a>Restore</a></p>
				<div class="tagcloud">
      				<?php the_content(__('Read more &#8250;', 'responsive')); ?>
<canvas id="canvas" width="600" height="400" style="background:transparent;" ></canvas>
				</div>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/box2d.js"></script>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/box2d-helpers.js"></script>
      				<?php wp_link_pages(array('before' => '<div class="pagination">' . __('Pages:', 'responsive'), 'after' => '</div>')); ?>
    			</div>
    			<!-- end of .post-entry -->

    			<?php if ( comments_open() ) : ?>
    			<div class="post-data">
      				<?php the_tags(__('Tagged with:', 'responsive') . ' ', ', ', '<br />'); ?>
      				<?php the_category(__('Posted in %s', 'responsive') . ', '); ?>
    			</div>
    			<!-- end of .post-data -->
    			<?php endif; ?>
    			<div class="post-edit">
      				<?php edit_post_link(__('Edit', 'responsive')); ?>
    			</div>
  			</div>
  			<!-- end of #post-<?php the_ID(); ?> -->

			<a class="scroll-anchor" name="discuss" style="height:1px;display:block" data-text="Dialoguer"></a>
			<div class="sectionDivider">
    			<h1> <span>Dialoguer</span> </h1>
			</div> 
			<div class="comment-wrapper">
				<div class="fb-comment-wrapper">
					<div class="fb-comments" data-href="<?php echo get_permalink(get_the_ID());?>" data-width="450" data-numposts="5" data-colorscheme="light"></div>
				</div>
				<div class="respond-wrapper">
					<?php comments_template( '', true ); ?>
    			</div>
			</div><!-- end of .comment-wrapper -->
  			<?php endwhile; ?>
  			<?php if (  $wp_query->max_num_pages > 1 ) : ?>
  			<div class="navigation">
    			<div class="previous">
      				<?php next_posts_link( __( '&#8249; Older posts', 'responsive' ) ); ?>
    			</div>
    			<div class="next">
      				<?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive' ) ); ?>
    			</div>
  			</div>
  			<!-- end of .navigation -->
  			<?php endif; ?>
  			<?php else : ?>
  			<h1 class="title-404">
    			<?php _e('404 &#8212; Fancy meeting you here!', 'responsive'); ?>
  			</h1>
  			<p>
    		<?php _e('Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive'); ?>
  			</p>
  	  			<h6><?php printf( __('You can return %s or search for the page you were looking for.', 'responsive'),
	      			sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
		  			esc_url( get_home_url() ),
		  			esc_attr__('Home', 'responsive'),
		  			esc_attr__('&larr; Home', 'responsive')
	  			)); 
?></h6>
  			<?php get_search_form(); ?>
  			<?php endif; ?>
		</div><!-- end of #inner content-->

	</div>
	<!-- end of #content-archive -->
</div><!-- end of .wide-container -->
<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
