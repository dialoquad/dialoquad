(function( window, $, undefined ) {
	
	$.Slideshow 				= function( options, element ) {
	
		this.$el			= $( element );
		
		this.$preloader		= this.$el.find('div.cn-loading');
		
		// images
		this.$images		= this.$el.find('div.cn-images img').hide();
		this.$captions		= this.$el.find('div.cn-captions').hide();
		
		// total number of images
		this.imgCount		= this.$images.length;
		
		
		this.isAnimating	= false;
		
		this._init( options );
		
		
	};
	
	$.Slideshow.defaults 		= {
		current			: 0
    };
	
	$.Slideshow.prototype 		= {
		_init 				: function( options ) {
			
			this.options 		= $.extend( true, {}, $.Slideshow.defaults, options );
			
			// validate options
			this._validate();
			
			this.current		= this.options.current;
			
			//this.$preloader.appendTo( this.$el );
			
			var instance		= this; 
			
			this._preloadImages( function() {
				
				
				instance.$images.eq( instance.current ).show();
				//instance.$captions.eq( instance.current ).show();
				var $topmar1  = -1*((instance.$images.eq(instance.current).height()-360)/2);
				instance.$images.eq( instance.current ).removeAttr("style").css('width', '100%').css('top', $topmar1).css('display', 'block');
				
				instance.bar	= new $.NavigationBar( instance.imgCount, instance._getStatus() );
				
				instance.bar.getElement().appendTo( instance.$el );
				
				instance._initEvents();
			
				instance.$preloader.hide();
			});
			
			var $curImg;
			var owidth;
			var oheight;
			var opostition;
			
			$(window).bind("resize", function(){
				var $topmar1  = -1*((instance.$images.eq(instance.current).height()-360)/2);
				instance.$images.eq( instance.current ).removeAttr("style").css('width', '100%').css('top', $topmar1).css('display', 'block');
			});			

			$('div.cn-images').hover(function() {
				var zoom = 1.2;
				//Set the width and height according to the zoom percentage
				$curImg = instance.$images.eq( instance.current );
				var width = $curImg.width() * zoom;
				var height = $curImg.height() * zoom;
				owidth = $curImg.width();
				oheight = $curImg.height();
				oposition = $curImg.position();
				//otop = $curImg.position().top;
				//oleft = $curImg.postition().left;
				var xmove = -0.1*$curImg.width();
				var ymove = -0.1*$curImg.height();
				
				//alert(width + " " +  height +" "+ owidth +" "+ oheight + " " + oposition.top + " " + oposition.left);
				//Move and zoom the image
				$curImg.stop(false,true).animate({'width':width, 'height':height, 'top':oposition.top+ymove, 'left':xmove}, {duration:200});
				
				//Display the caption
				instance.$captions.eq( instance.current ).stop(false,true).fadeIn(200);
			},
			function() {
				//Reset the image
				$curImg.stop(false,true).animate({'width':owidth, 'height':oheight, 'top':oposition.top, 'left':0}, {duration:100});	
		
				//Hide the caption
				instance.$captions.eq( instance.current ).fadeOut(200);
			});
			

		},
		_preloadImages		: function( callback ) {
			
			var loaded	= 0, instance = this;
			
			this.$images.each( function(i) {
			
				var $img	= $(this);
					
				// large image
				$('<img/>').load( function() {
					++loaded;
					if( loaded === instance.imgCount * 2 ) callback.call();
				}).attr( 'src', $img.attr('src') );
				// thumb
				$('<img/>').load( function() {
					++loaded;
					if( loaded === instance.imgCount * 2 ) callback.call();
				}).attr( 'src', $img.data('thumb') );
				
			
			});
			
		},
		_validate			: function() {
		
			if( this.options.current < 0 || this.options.current >= this.imgCount )
				this.options.current = 0;
		
		},
		_getStatus			: function() {
			
			var $currentImg	= this.$images.eq( this.current ), $nextImg, $prevImg;
			
			( this.current === 0 ) ? $prevImg = this.$images.eq( this.imgCount - 1 ) : $prevImg = $currentImg.parent().prev().prev().children();
			( this.current === this.imgCount - 1 ) ? $nextImg = this.$images.eq( 0 ) : $nextImg = $currentImg.parent().next().next().children();
			
			
			return {
				prevSource		: $prevImg.data( 'thumb' ),
				nextSource		: $nextImg.data( 'thumb' ),
				prevTitle		: $prevImg.attr( 'title' ),
				currentTitle	: $currentImg.attr( 'title' ),
				nextTitle		: $nextImg.attr( 'title' )
			};
			
		},
		_initEvents			: function() {
			
			
			var instance	= this;
			
			this.bar.$navPrev.bind('click.slideshow', function( event ) {
				instance._navigate( 'prev' );
				return false;
				
			});
			
			this.bar.$navNext.bind('click.slideshow', function( event ) {
				
				instance._navigate( 'next' );
				return false;
				
			});
			
			var refreshIntervalId;
			refreshIntervalId =  setInterval(function() { 
			
				instance._navigate( 'next' );
				return false;
			} , 4000);
			
			
			$('div.cn-images').bind("mouseenter", (function(){ 
				clearInterval(refreshIntervalId); 
				return false;
			}));
			
			$('div.cn-images').bind("mouseleave", (function(){ 
				
				refreshIntervalId =  setInterval(function() { 
					instance._navigate( 'next' );
					return false;
				} , 4000);
			}));
			
			$("div.cn-bar").live("mouseenter", function() {
				clearInterval(refreshIntervalId); 
				return false;
			});
			
			$("div.cn-bar").live("mouseleave", function() {
				refreshIntervalId =  setInterval(function() { 
					instance._navigate( 'next' );
					return false;
				} , 4000);
			});
			
		},
		_navigate			: function( dir ) {
			
			if( this.isAnimating ) return false;
			
			this.isAnimating	= true;
			
			var $curr			= this.$images.eq( this.current ).css( 'z-index' , 998 ),
				instance		= this,
				$currcap		= this.$captions.eq( this.current ).css( 'z-index' , 998 );
			
			( dir === 'prev') 
				? ( this.current === 0 ) ? this.current = this.imgCount - 1 : --this.current
				: ( this.current === this.imgCount - 1 ) ? this.current = 0 : ++this.current;
			
			this.$images.eq( this.current ).show();
			//this.$captions.eq( this.current ).show();
			var $topmar  = -1*((this.$images.eq(this.current).height()-360)/2);
			
			this.$images.eq( this.current ).css('top', $topmar);
			
			$curr.fadeOut( 400, function() {
			
				$(this).css( 'z-index' , 1 );
				
				instance.isAnimating	= false;
			
			});
			
			$currcap.fadeOut( 400, function() {
			
				$(this).css( 'z-index' , 1 );
				
				instance.isAnimating	= false;
			
			});
			
			
			this.bar.set( this._getStatus() );
			
		}
	};
	
	$.NavigationBar				= function( imgCount, status ) {
		
		this._init( imgCount, status );
		
	};
	
	$.NavigationBar.prototype 	= {
	
		_init 				: function( imgCount, status ) {
			
			this.$el 			= $('#barTmpl').tmpl( status );
			
			// navigation
			this.$navPrev		= this.$el.find('a.cn-nav-prev');
			this.$thumbPrev		= this.$navPrev.children('div');
			
			this.$navNext		= this.$el.find('a.cn-nav-next');
			this.$thumbNext		= this.$navNext.children('div');
			
			// navigation status
			this.$statusPrev	= this.$el.find('div.cn-nav-content-prev > h3');
			this.$statusCurrent	= this.$el.find('div.cn-nav-content-current > h2');
			this.$statusNext	= this.$el.find('div.cn-nav-content-next > h3');
			
			// just show current image description if only one image
			if( imgCount <= 1) {
			
				this.$navPrev.hide();
				this.$navNext.hide();
				this.$statusPrev.parent().hide();
				this.$statusNext.parent().hide();
				
			}
			
		},
		getElement			: function() {
		
			return this.$el;
		
		},
		// set the current, previous and next descriptions, and also the previous and next thumbs
		set					: function( status ) {
			
			this.$thumbPrev.css( 'background-image', 'url(' + status.prevSource + ')' );
			this.$thumbNext.css( 'background-image', 'url(' + status.nextSource + ')' );
			this.$statusPrev.text( status.prevTitle );
			this.$statusCurrent.text( status.currentTitle );
			this.$statusNext.text( status.nextTitle );
			
		}
	
	};
	
	var logError 				= function( message ) {
		
		if ( this.console ) {
			
			console.error( message );
			
		}
		
	};
	
	$.fn.slideshow 				= function( options ) {
	
		if ( typeof options === 'string' ) {
		
			var args = Array.prototype.slice.call( arguments, 1 );

			this.each(function() {
			
				var instance = $.data( this, 'slideshow' );
				
				if ( !instance ) {
					logError( "cannot call methods on slideshow prior to initialization; " +
					"attempted to call method '" + options + "'" );
					return;
				}
				
				if ( !$.isFunction( instance[options] ) || options.charAt(0) === "_" ) {
					logError( "no such method '" + options + "' for slideshow instance" );
					return;
				}
				
				instance[ options ].apply( instance, args );
			
			});
		
		} 
		else {
		
			this.each(function() {
				var instance = $.data( this, 'slideshow' );
				if ( !instance ) {
					$.data( this, 'slideshow', new $.Slideshow( options, this ) );
				}
			});
		
		}
		
		return this;
		
	};
	
})( window, jQuery );
