<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 *
 * WARNING: Please do not edit this file in any way
 *
 * load the theme function files
 */
 if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => '葉展昀模組',
    'before_widget' => '<div class="widget-title">',
    'after_widget' => '</div>',
    'before_title' => '<h6>',
    'after_title' => '</h6>',
  )
);

 if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => '蘇敬博模組',
    'before_widget' => '<div class="widget-title">',
    'after_widget' => '</div>',
    'before_title' => '<h6>',
    'after_title' => '</h6>',
  )
);

 if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => '傅宗玉模組',
    'before_widget' => '<div class="widget-title">',
    'after_widget' => '</div>',
    'before_title' => '<h6>',
    'after_title' => '</h6>',
  )
);

 if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => '蘇煒翔模組',
    'before_widget' => '<div class="widget-title">',
    'after_widget' => '</div>',
    'before_title' => '<h6>',
    'after_title' => '</h6>',
  )
);

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
       add_image_size( 'allpost-thumb', 100, 100, true ); // Hard Crop Mode2
	   add_image_size( 'random-thumb', 369, 294, true ); // Soft Crop Mode3
	   add_image_size( 'banner-thumb', 1280, 800, true ); // Unlimited Height Mode
}

function wpe_excerptlength_category( $length ) {
    return 300;
}
function wpe_excerptlength_banner( $length ) {  
    return 120;
}

function wpe_excerptlength_random( $length ) {  
    return 75;
}

function wpe_excerptlength_search( $length ) {  
    return 400;
}

function wpe_excerptmore( $more ) {
    return '...';
}

register_sidebar(array(
	'name' => __('Home Widget 4', 'responsive'),
	'description' => __('Area 12 - sidebar-home.php', 'responsive'),
	'id' => 'home-widget-4',
	'before_title' => '<div id="widget-title-three" class="widget-title-home"><h3>',
	'after_title' => '</h3></div>',
	'before_widget' => '<div id="%1$s" class="widget-wrapper %2$s">',
	'after_widget' => '</div>'
));

function wpe_excerpt( $length_callback = '', $more_callback = '' ) {
    
    if ( function_exists( $length_callback ) ) {
        add_filter( 'excerpt_length', $length_callback);
	}
    
    if ( function_exists( $more_callback ) ) {
		remove_filter( 'the_content', 'responsive_auto_excerpt_more' );
        add_filter( 'excerpt_more', $more_callback);
	}
    
    $output = get_the_excerpt();
    $output = apply_filters( 'wptexturize', $output );
    $output = apply_filters( 'convert_chars', $output );
	$output = '<p>' . $output . '</p>'; // maybe wpautop( $foo, $br )
    echo $output;
}

//add_filter( 'widget_text', 'shortcode_unautop');
//add_filter( 'widget_text', 'do_shortcode');

function my_widget_tag_cloud_args( $args ) {
	$args['largest'] = 10;
	$args['smallest'] = 2;
	return $args;
}

add_filter( 'widget_tag_cloud_args', 'my_widget_tag_cloud_args' );

 
require ( get_template_directory() . '/includes/functions.php' );
require ( get_template_directory() . '/includes/theme-options.php' );
require ( get_template_directory() . '/includes/hooks.php' );
require ( get_template_directory() . '/includes/version.php' );
