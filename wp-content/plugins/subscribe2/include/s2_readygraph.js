jQuery(document).ready(function(){
	jQuery('#signin-submit').click(function(e){
		var emailempty = '{emailempty}'.replace('{emailempty}', objectL10n.emailempty);
		var passwordempty = '{passwordempty}'.replace('{passwordempty}', objectL10n.passwordempty);
		var email = jQuery('#signin-email').val();
		var password = jQuery('#signin-password').val();
		if (!email) {
			alert(emailempty);
			return;
		}
		if (!password) {
			alert(passwordempty);
			return;
		}
		jQuery.ajax({
			type: 'GET',
			url: 'https://readygraph.com/api/v1/wordpress-login/',
			data: {
				'email' : email,
				'password' : password
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var pathname = window.location.href;
					window.location = pathname + "&app_id="+response.data.app_id;
				} else {
					jQuery('#error').text(response.error);
					jQuery('#error').show();
				}
			}
		});
	});

	jQuery('#register-app-submit').click(function(e){
		var email = jQuery('#register-email').val();
		var site_url = jQuery('#register-url').val();
		var first_name = jQuery('#register-name').val();
		var password = jQuery('#register-password').val();
		var password2 = jQuery('#register-password1').val();
		var urlempty = '{urlempty}'.replace('{urlempty}', objectL10n.urlempty);
		var emailempty = '{emailempty}'.replace('{emailempty}', objectL10n.emailempty);
		var passwordmatch = '{passwordmatch}'.replace('{passwordmatch}', objectL10n.passwordmatch);
		if (!site_url) {
			alert(urlempty);
			return;
		}
		if (!email) {
			alert(emailempty);
			return;
		}
		if ( !password || password != password2 ) {
			alert(passwordmatch);
			return;
		}

		jQuery.ajax({
			type: 'POST',
			url: 'https://readygraph.com/api/v1/wordpress-signup/',
			data: {
				'email' : email,
				'site_url' : site_url,
				'first_name': first_name,
				'password' : password,
				'password2' : password2,
				'source' : 'subscribe2'
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var pathname = window.location.href;
					window.location = pathname + "&app_id="+response.data.app_id;
				} else {
					jQuery('#error').text(response.error);
					jQuery('#error').show();
				}
			}
		});
	});
});