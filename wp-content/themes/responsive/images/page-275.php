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
	?><br><div class="allpost-year"><?php
	the_time('Y'); echo '(' . $count . '篇文章)<br>';?>
    </div><?php
	
} 
if ($mcount == 1) {?>
<div class="allpost-month"><?php
	the_time('F'); echo  '<br>';?></div><?php
}
$ycount++;
$mcount++;?>
    <li><div class="allpost-posts"><a href="<?php the_permalink() ?>" rel="bookmark">
      <?php the_title(); ?>
      
      </a><div class="allpost-meta"><?php responsive_post_meta_data(); ?></div></div></li>
      
    <?php wp_reset_postdata();endwhile; ?>
  </ul>
  </div><!-- end of #inner-content --> 
  
</div>
<!-- end of #content-archive -->

<?php get_sidebar('home'); ?>
<?php get_footer(); ?>
