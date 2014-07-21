<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header for Home Template
 */
?>

<style type="text/css">
	#search-head #searchbox { display:block; }
</style>

<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri()?>/js/speaker.js?ver=1.0"></script> 

<noscript>
    <style>
        .cn-images img{position: relative;display: block;border-bottom: 5px solid #d0ab47;} 
        .cn-slideshow{height: auto;}
    </style>
</noscript>
<script id="barTmpl" type="text/x-jquery-tmpl">
<div class="cn-bar">
    <div class="cn-nav">
	<a href="#" class="cn-nav-prev">
    <span>Previous</span>
	<div style="background-image:url(${prevSource});"></div> 
	</a>
	<a href="#" class="cn-nav-next">
    <span>Next</span>
	<div style="background-image:url(${nextSource});"></div>
	</a>
    </div><!-- cn-nav -->
    <div class="cn-nav-content">
    <div class="cn-nav-content-prev">
    <span>上一則</span>
    <h3>${prevTitle}</h3>
    </div>
    <div class="cn-nav-content-current">
    <span>現正觀看</span>
    <h2>${currentTitle}</h2>
    </div>
    <div class="cn-nav-content-next">
    <span>下一則</span>
    <h3>${nextTitle}</h3>
    </div>
    </div><!-- cn-nav-content -->
    </div><!-- cn-bar -->
	</script>
