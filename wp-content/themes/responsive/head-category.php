<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Header for Categories Template (page-id-274)
 */
?>

<script type="text/javascript" language="javascript">
(function( window, $, undefined ) {
	$(document).ready(function(){

		/* Category Navigation bar */
		var timer = [];
		var unhover = function(myvar){
			myvar.is(".dqarrow") ? myvar.css('width','0px') : myvar.prev().css('width','0px');
		};

		$("*[id^=category-menu]> ul> li> .dqarrow").click(function() {
			$(this).siblings("ul").slideToggle('fast');
		});

		$("*[id^=category-menu]> ul> li> a, *[id^=category-menu]> ul> li> .dqarrow").hover(function() {
			var n = $(this).parent().index();
			window.clearTimeout(timer[n]);
			$(this).is(".dqarrow") ? $(this).css('width',$(this).css('height')) : $(this).prev().css('width',$(this).css('height'));
		},function(){
			var myvar = $(this);
			var n = $(this).parent().index();
			<?php if(!is_handheld()){ ?>
				timer[n] = setTimeout(function(){unhover(myvar);},200);
			<?php }?>
		});
	});
})( window, jQuery );
</script>

