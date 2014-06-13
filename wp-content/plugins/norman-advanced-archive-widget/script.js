$(document).ready(function() {
	jQuery('li.norman-adv-archive-year a.icon').click(function() {
		if (jQuery(this).hasClass('more')) {
			jQuery(this).removeClass('more');
			jQuery(this).addClass('less');
		} else {
			jQuery(this).removeClass('less');
			jQuery(this).addClass('more');
		}
		jQuery(this).parent().children('ul').slideToggle('fast');
		return false;
	});	
});
