<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header for Home Template
 */
?>

<script type="text/javascript" language="javascript">
var pos = {left:0};
(function( window, $, undefined ) {
	$(document).ready(function(){
		$.fn.toggleClick = function(){
			var functions = arguments;
			var iteration = 0;
			return this.click(function(){
				functions[iteration].apply(this,arguments);
				iteration = (iteration + 1) % functions.length;
			});
		};

		var fn1 = function(){
			pos.left = $(this).position().left;
			$(this).animate({'margin-left': -pos.left},'swing',null,fn3);
			$(this).parent().siblings().fadeToggle('swing');
		};

		var fn2 = function(){
			$(this).animate({'margin-left': pos.left},'swing',null,fn4);
			$(this).parent().children(".author-info").fadeToggle('swing');
		};

		var fn3 = function(){
			$(this).css('margin-left',0);
			$(this).parent().children(".author-info").fadeToggle('swing');
			//$(this).parent().siblings().toggle();
		};
		
		var fn4 = function(){
			$(this).css('margin-left',0);
			$(this).parent().siblings().fadeToggle('swing');
			//$(this).parent().siblings().toggle();
		};

		$('.author-img').toggleClick(fn1,fn2);
	});
})( window, jQuery );
</script>

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
