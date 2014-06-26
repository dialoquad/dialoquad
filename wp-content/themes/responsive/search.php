<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Search Template
 *
 *
 * @file           search.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/search.php
 * @link           http://codex.wordpress.org/Theme_Development#Search_Results_.28search.php.29
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>
<div class="wide-container">
        <div id="content-search" class="grid col-940">
		<div id="featured-content">
<div id="inner-content">
<?php if (have_posts()) : ?>

    <h6><?php printf(__('Search results for: %s', 'responsive' ), '<span>' . get_search_query() . '</span>'); ?></h6>

		<?php while (have_posts()) : the_post(); ?>
        
        
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <h1 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'responsive'), the_title_attribute('echo=0')); ?>"><?php the_title(); ?></a></h1>
                
                
                <div class="post-entry">
                    <?php if ( has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
                    <?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
                        </a>
                    <?php endif; ?><div class="post-meta">
                <?php responsive_post_meta_data(); ?>
                
				    <?php if ( comments_open() ) : ?>
                        <span class="comments-link">
                        <span class="mdash">&mdash;</span>
                    <?php comments_popup_link(__('No Comments &darr;', 'responsive'), __('1 Comment &darr;', 'responsive'), __('% Comments &darr;', 'responsive')); ?>
                        </span>
                    <?php endif; ?> 
                </div><!-- end of .post-meta -->
                <iframe id="facebook-like" src="https://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink(get_the_ID()));?>&send=false&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
					<?php wpe_excerpt('wpe_excerptlength_search', 'wpe_excerptmore');?>
                    <?php wp_link_pages(array('before' => '<div class="pagination">' . __('Pages:', 'responsive'), 'after' => '</div>')); ?>
                </div><!-- end of .post-entry -->
                           

            <div class="post-edit"><?php edit_post_link(__('Edit', 'responsive')); ?></div>               
            </div><!-- end of #post-<?php the_ID(); ?> -->
            
        <?php endwhile; ?> 
        
        <?php if (  $wp_query->max_num_pages > 1 ) : ?>
        <div class="navigation">
			<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive' ) ); ?></div>
            <div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive' ) ); ?></div>
		</div><!-- end of .navigation -->
        <?php endif; ?>

	    <?php else : ?>

        <h3 class="title-404"><?php printf(__('Your search for %s did not match any entries.', 'responsive' ), get_search_query() ); ?></h3>
                    
        <p><?php _e('Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive'); ?></p>
                  
        <h6><?php printf( __('You can return %s or search for the page you were looking for.', 'responsive'),
	            sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
		            esc_url( get_home_url() ),
		            esc_attr__('Home', 'responsive'),
		            esc_attr__('&larr; Home', 'responsive')
	                )); 
			 ?></h6>
                    
        <div id="noresult">  
			<?php get_search_form(); ?>
        </div><!-- end of #noresult -->

<?php endif; ?>  
      </div><!-- end of #inner-content -->
      </div><!-- end of #featured-content -->
        </div><!-- end of #content-search -->
</div><!-- end of .narrow-container -->
<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
