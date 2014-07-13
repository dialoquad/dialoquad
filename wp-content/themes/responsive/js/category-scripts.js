
jQuery(document).ready(function() {
   jQuery(".menu a").hover(function(){
            jQuery(this).fadeTo('fast', 0.5);
			jQuery(this).animate({left:30}, {duration:'fast'});
        })
        
        jQuery(".menu a").mouseleave(function(){
            jQuery(this).fadeTo('fast', 1);
			jQuery(this).animate({left:0}, {duration:'fast'});
        })
});
