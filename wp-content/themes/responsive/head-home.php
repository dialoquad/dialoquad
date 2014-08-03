<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header for Home Template
 */
?>

<script type="text/javascript" language="javascript">
var pos = {left:0,index:0};
(function( window, $, undefined ) {
	$(document).ready(function(){
		<?php if(! is_handheld()){
			echo 'var animation = "swing";';
		}else{
			echo 'var animation = 0;';
		}
		?>
		var fn1 = function(){
			$(this).parent().siblings().andSelf().children('.author-img').unbind('click');
			pos.left = $(this).position().left;
				$(this).animate({'margin-left': -pos.left},animation,null,fn3);
			$(this).parent().siblings(".author-avatar").slice(pos.index,pos.index+3).fadeToggle(animation);
			$(this).parent().siblings(".nav").toggle(animation);
		};

		var fn2 = function(){
			$(this).unbind('click');
				$(this).css('position','absolute').animate({'margin-left': pos.left},animation,null,fn4);
			$(this).parent().children(".author-info").css('margin-left',240).fadeToggle(animation);
		};

		var fn3 = function(){
			$(this).css('margin-left',0);
			$(this).parent().children(".author-info").css('margin-left',0).fadeToggle(null,animation,function(){
				$(this).siblings(".author-img").click(fn2);
			});
			//$(this).parent().siblings().toggle();
		};
		
		var fn4 = function(){
			$(this).css({'margin-left':'0','position':'initial'});
			$(this).parent().siblings(".author-avatar").slice(pos.index,pos.index+3).fadeToggle(animation);
			$(this).parent().siblings().andSelf().children('.author-img').click(fn1);
			$(this).parent().siblings(".nav").toggle(animation);
			//$(this).parent().siblings().toggle();
		};

		$('.author-avatar').slice(4).toggle();

		$('.authoricon .widget-avatar .nav.previous').click(function(){
			if(pos.index > 0){
				$('.author-avatar').eq(pos.index - 1).toggle();
				$('.author-avatar').eq(pos.index + 3).toggle();
				pos.index--;
			}	
		});
		
		$('.authoricon .widget-avatar .nav.next').click(function(){
			var $items = $('.author-avatar');
			if(pos.index < $items.size() - 4){
				$items.eq(pos.index).toggle();
				$items.eq(pos.index + 4).toggle();
				pos.index++;
			}		
		});
		
		$('.author-img').click(fn1);
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
