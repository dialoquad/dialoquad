<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Home Page
 *
 * Note: You can overwrite home.php as well as any other Template in Child Theme.
 * Create the same file (name) include in /responsive-child-theme/ and you're all set to go!
 * @see            http://codex.wordpress.org/Child_Themes
 *
 * @file           home.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/home.php
 * @link           http://codex.wordpress.org/Template_Hierarchy
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>
 
<div class="wide-container">
  <div class="sectionDivider">
    <div id="dqnav-wrapper">
    <div class="icons-dqnav-box">
      <div id="dqnav-author" class="icons-dqnav">
         <a href="#author">Mt. Rushmore</a> 
      </div>
      </div>
      <div class="icons-dqnav-box"><div id="dqnav-headline" class="icons-dqnav"> <a href="#headline">DQ Headline</a> </div></div>
     <div class="icons-dqnav-box"> <div id="dqnav-random" class="icons-dqnav"> <a href="#random">Postlaroid</a> </div></div>
      <div class="icons-dqnav-box"><div id="dqnav-footer" class="icons-dqnav"> <a href="#wfooter">Athlete's footer</a> </div></div>
    </div>
  </div>
  <div id="featured" class="grid col-940"> 
    
    <!-- end of .col-460 -->
    
    <div id="featured-image" class="grid col-940 fit">
      <div class="wrapper">
        <div id="cn-slideshow" class="cn-slideshow lifted-shadow">
		  <div class="cn-loading">Loading...</div>
          <div class="cn-images">
            <?php	$count = 1;
		$recentPosts = new WP_Query( array ( 'orderby' => 'rand', 'posts_per_page' => '-1' ) );?>
            <?php while ($recentPosts->have_posts()) : $recentPosts->the_post(); ?>
            <?php if( $count>4 ) { break;}?>
            <?php if( has_post_thumbnail() ) { ?>
            <?php if ( has_post_thumbnail()) : ?>
            <?php $src1 = wp_get_attachment_image_src( get_post_thumbnail_id($recentPosts->ID), $size='banner-thumb');
   $src2 = wp_get_attachment_image_src( get_post_thumbnail_id($recentPosts->ID), $size='allpost-thumb');?>
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" > <img src="<?php echo $src1[0]?>" alt="image0<?php echo $count; ?>" title="<?php the_title(); ?>" data-thumb="<?php echo $src2[0]?>" <?php if($count==1){echo 'style="display:block;opacity:0.3;"';}else{echo 'style="display:none;"';}?> /> </a>
            <div class="cn-captions">
              <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" ><?php wpe_excerpt('wpe_excerptlength_banner', 'wpe_excerptmore'); ?></a>
              <p>by:
                <?php responsive_post_meta_data(); ?>
              </p>
            </div>
            <?php endif; $count++;?>
            <?php } ?>
            <?php endwhile; ?>
          </div>
          <!-- cn-images --> 
        </div>
        <!-- cn-slideshow --> 
      </div>
      <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery.tmpl.min.js"></script> 
      <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/jquery.slideshow.js"></script> 
    </div>
    <!-- end of #featured-image --> 
    
  </div>
  <!-- end of #featured --> 
</div>
<script type="text/javascript">
   	jQuery(function() {
		jQuery('#cn-slideshow').slideshow();
	});
</script> 
<!-- end of .wide-container -->
<a name="author"></a>
<div class="wide-container">
  <div class="sectionDivider">
    <h1> <span>Mt. Rushmore</span> </h1>
  </div>
  <div class="authoricon">
    <div class=" grid col-220 author">
      <div class="michael author-wrapper">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("葉展昀模組") ) : ?>
        <?php endif; ?>
      </div>
    </div>
    <div class=" grid col-220 author">
      <div class="david author-wrapper">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("蘇敬博模組") ) : ?>
        <?php endif; ?>
      </div>
    </div>
    <div class=" grid col-220 author">
      <div class="jade author-wrapper">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("傅宗玉模組") ) : ?>
        <?php endif; ?>
      </div>
    </div>
    <div class=" grid col-220 author fit">
      <div class="winston author-wrapper">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("蘇煒翔模組") ) : ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- end of .narrow-container -->

<a name="headline"></a>
<div class="wide-container">
  <div class="sectionDivider">
    <h1> <span>DQ Headline</span> </h1>
  </div>
  <div id="featured-content" class="grid col-940">
    <div id="inner-content">
      <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			   query_posts('showposts=1&paged='. $paged) ?>
      <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
      <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'responsive'), the_title_attribute('echo=0')); ?>">
          <?php the_title(); ?>
          </a></h1>
        <div class="post-meta">
          <?php responsive_post_meta_data(); ?>
          <?php if ( comments_open() ) : ?>
          <span class="comments-link"> <span class="mdash">&mdash;</span>
          <?php comments_popup_link(__('No Comments &darr;', 'responsive'), __('1 Comment &darr;', 'responsive'), __('% Comments &darr;', 'responsive')); ?>
          </span>
          <?php endif; ?>
        </div>
        <!-- end of .post-meta -->
         <iframe id="facebook-like" src="https://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink(get_the_ID()));?>&amp;share=true&amp;layout=standard&amp;width=390&amp;show_faces=false&amp;action=like&amp;colorscheme=light" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
        <div class="post-entry">
          <div id="article-content">
            <?php the_content(__('Read more &#8250;', 'responsive')); ?>
          </div>
          <!-- end of #article-content -->
          <?php wp_link_pages(array('before' => '<div class="pagination">' . __('Pages:', 'responsive'), 'after' => '</div>')); ?>
        </div>
        <!-- end of .post-entry -->
        <div class="navigation">
          <div class="previous">
            <?php previous_post_link( '&#8249; %link' ); ?>
          </div>
          <div class="next">
            <?php next_post_link( '%link &#8250;' ); ?>
          </div>
        </div>
        <!-- end of .navigation -->
        <div class="post-data">
          <?php the_tags(__('Tagged with:', 'responsive') . ' ', ', ', '<br />'); ?>
          <?php printf(__('Posted in %s', 'responsive'), get_the_category_list(', ')); ?> </div>
        <!-- end of .post-data -->
        
        <div class="post-edit">
          <?php edit_post_link(__('Edit', 'responsive')); ?>
        </div>
      </div>
      <!-- end of #post-<?php the_ID(); ?> -->
      
      <?php endwhile; ?>
      <?php if (false) { ?>
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
      <?php } ?>
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
      <?php wp_reset_query(); ?>
    </div>
    <!-- inner-content --> 
  </div>
  <!-- feature-content --> 
</div>
<!-- end of .wide-container -->

<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
