/*
 * Scroll bubble
 * based on http://jsfiddle.net/michaelhue/7NAvm/7/light/
 */
(function( window, $, undefined ) {
	$(window).load(function() {
		var	scrollTimer = null,
				touch = 'ontouchstart' in window,
				totalScroll = 0,
				previousScroll = 0,
				viewportHeight = $(window).height(),
				documentHeight = $(document).height(),
				bubble = $('.speaker img'),
				picwidth = [],
				scrollTimer = null,
				post = $("#article-content");
		if(bubble.length > 0)
			$('#article-content').css('overflow', 'hidden');
				//bubbleText = $('#scrollBubbleText');
		picwidth.push(bubble.eq(0).width());
		picwidth.push(bubble.eq(1).width());
		// calculate total reading time
		//total_reading_time.setTime(timeToRead(post.children())); 
		//alert(total_reading_time);
		//alert(post.children().size());
		// show initial bubble
		//staticBubble(total_reading_time.firstString());
	
	
		/* event listeners */
	
		// detect starts and stops to evaluate if we need to show the bubble or not
		$(window).bind('scrollstart', function(e) {
			previousScroll = null;
		});
	
		$(window).bind('scrollstop', function(e) {
			totalScroll = 0;
		});
	
		if(touch)
			bubble.css('webkit-transition', '-webkit-transform 0.2s ease-out');
		$(window).scroll(function () {
			if (scrollTimer) {
				clearTimeout(scrollTimer);   // clear any previous pending timer
			}
			scrollTimer = setTimeout(handleScroll, 10);   // set new timer
		});
		// main scroll function
		function handleScroll() {
			$('.speaker img').each( function(index){
				//alert($(document).scrollTop() + viewportHeight - $(this).height() + ' ' + post.offset().top); 
				if($(document).scrollTop() < post.offset().top - viewportHeight + $(this).height()) { 
				
					// post start
					//staticBubble(total_reading_time.firstString());
					$(this).removeAttr("style").css('position', 'absolute').css('top', 'auto');
				} else if(((post.offset().top + post.height() - viewportHeight) - $(document).scrollTop()) < 0) {
					// post end
					//staticBubble(total_reading_time.lastString());
					$(this).removeAttr("style").css('position', 'absolute').css('top', (post.height() - $(this).height()) + 'px').css('bottom', 'initial') ;
					
		
				} else if(!touch && $(document).scrollTop() > 100) { 
					$(this).removeAttr("style").css('position', 'fixed').css('top', 'auto').css('bottom', 0 +'px').css('width', picwidth[index] + 'px');
					// normal cases
					//scrollBubble();
				} else {
					$(this).removeAttr("style").css('position', 'absolute').css('top', 'auto');
				}
			});
		}
	});
})( window, jQuery );