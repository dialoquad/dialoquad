<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Single Posts Template
 *
 *
 * @file           single.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/single.php
 * @link           http://codex.wordpress.org/Theme_Development#Single_Post_.28single.php.29
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>

<div class="wide-container">
    <div id="featured-content" class="grid col-940">
        <div id="scrollbubble"><span id="scrollBubbleText"></span></div>

        <div id="inner-content">
			<?php if (have_posts()) : ?>

			<?php while (have_posts()) : the_post(); ?>

        	<?php $options = get_option('responsive_theme_options'); ?>
			<?php if ($options['breadcrumb'] == 0): ?>
			<?php echo responsive_breadcrumb_lists(); ?>
        	<?php endif; ?> 

            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <h1 class="post-title"><?php the_title(); ?></h1>

                <div class="post-meta">
                	<?php responsive_post_meta_data(); ?>

				    <?php if ( comments_open() ) : ?>
                    <span class="comments-link">
                        <span class="mdash">&mdash;</span>
                    	<?php comments_popup_link(__('No Comments &darr;', 'responsive'), __('1 Comment &darr;', 'responsive'), __('% Comments &darr;', 'responsive')); ?>
                    </span>
                    <?php endif; ?> 
                </div><!-- end of .post-meta -->
         		<iframe id="facebook-like" src="https://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink(get_the_ID()));?>&amp;share=true&amp;layout=standard&amp;width=390&amp;show_faces=false&amp;action=like&amp;colorscheme=light" scrolling="no" frameborder="0" allowTransparency="true"></iframe>

                <div class="post-entry">
                	<div id="article-content">
                    	<?php the_content(__('Read more &#8250;', 'responsive')); ?>
                    </div><!-- end of #article-content -->
                    <?php if ( get_the_author_meta('description') != '' ) : ?>

                    <div id="author-meta">
                    	<?php if (function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), '80' ); }?>
                        <div class="about-author"><?php _e('About','responsive'); ?> <?php the_author_posts_link(); ?></div>
                        <p><?php the_author_meta('description') ?></p>
                    </div><!-- end of #author-meta -->

                    <?php endif; // no description, no author's meta ?>

                    <?php wp_link_pages(array('before' => '<div class="pagination">' . __('Pages:', 'responsive'), 'after' => '</div>')); ?>

                </div><!-- end of .post-entry -->

                <div class="navigation">
			        <div class="previous"><?php previous_post_link( '&#8249; %link' ); ?></div>
                    <div class="next"><?php next_post_link( '%link &#8250;' ); ?></div>
		        </div><!-- end of .navigation -->

                <div class="post-data">
				    <?php the_tags(__('Tagged with:', 'responsive') . ' ', ', ', '<br />'); ?> 
					<?php printf(__('Posted in %s', 'responsive'), get_the_category_list(', ')); ?> 
                </div><!-- end of .post-data -->             

            	<div class="post-edit"><?php edit_post_link(__('Edit', 'responsive')); ?></div>             
            </div><!-- end of #post-<?php the_ID(); ?> -->
            <div class="comment-wrapper">
            	<script src="http://connect.facebook.net/zh_TW/all.js#xfbml=1"></script>
				<div class="fb-comment-wrapper">
					<fb:comments  id="facebook-comment" href="<?php echo get_permalink(get_the_ID());?>" num_posts="2"></fb:comments>
				</div>
				<div class="respond-wrapper">
					<?php comments_template( '', true ); ?>
            	</div>
            </div><!-- end of .comment-wrapper -->
        	<?php endwhile; ?> 

        	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
        	<div class="navigation">
				<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive' ) ); ?></div>
            	<div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive' ) ); ?></div>
			</div><!-- end of .navigation -->
        	<?php endif; ?>

	    	<?php else : ?>

        	<h1 class="title-404"><?php _e('404 &#8212; Fancy meeting you here!', 'responsive'); ?></h1>

        	<p><?php _e('Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive'); ?></p>

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

    </div><!-- end of #content -->
</div><!-- end of .narrow-container -->
<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
