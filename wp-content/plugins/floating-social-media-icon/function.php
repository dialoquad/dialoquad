<?php
error_reporting('0');
//*************** Include style.css in Header ********
// Getting Option From DB *****************************	
$acx_si_theme = get_option('acx_si_theme');
$acx_si_credit = get_option('acx_si_credit');
$acx_si_display = get_option('acx_si_display');
$acx_si_twitter = get_option('acx_si_twitter');
$acx_si_facebook = get_option('acx_si_facebook');
$acx_si_youtube = get_option('acx_si_youtube');
$acx_si_linkedin = get_option('acx_si_linkedin');
$acx_si_gplus = get_option('acx_si_gplus');
$acx_si_pinterest = get_option('acx_si_pinterest');
$acx_si_feed = get_option('acx_si_feed');
$acx_si_icon_size = get_option('acx_si_icon_size');
$acx_si_fsmi_menu_highlight = get_option('acx_si_fsmi_menu_highlight');
$acx_si_fsmi_float_fix = get_option('acx_si_fsmi_float_fix');
$acx_si_fsmi_theme_warning_ignore = get_option('acx_si_fsmi_theme_warning_ignore');
$acx_si_fsmi_disable_mobile = get_option('acx_si_fsmi_disable_mobile');
// *****************************************************
// Check Credit Link
function enqueue_acx_si_style()
{
	wp_enqueue_style ( 'acx-si-style', plugins_url('style.css', __FILE__) );
}	add_action( 'wp_print_styles', 'enqueue_acx_si_style' );
// Options Value Checker
function acx_option_value_check($option_name,$yes,$no)
{ 	$acx_si_option_set = get_option($option_name);
	if ($acx_si_option_set != "") { echo $yes; } else { echo $no; }
}
function check_acx_credit($yes,$no)
{ 	$acx_si_credit = get_option('acx_si_credit');
	if($acx_si_credit != "no") { echo $yes; } else { echo $no; } 
}
function acurax_si_simple($theme = "") // Added Default "" // Updated << and V (alt added to Images Title Added to Links)
{
	// Getting Globals *****************************	
	global $acx_si_theme, $acx_si_credit, $acx_si_display , $acx_si_twitter, $acx_si_facebook, $acx_si_youtube, 		
	$acx_si_linkedin, $acx_si_gplus, $acx_si_pinterest, $acx_si_feed, $acx_si_icon_size;
	// *****************************************************
	if ($theme == "") { $acx_si_touse_theme = $acx_si_theme; } else { $acx_si_touse_theme = $theme; }
		//******** MAKING EACH BUTTON LINKS ********************
		if	($acx_si_twitter == "") { $twitter_link = ""; } else 
		{
			$twitter_link = "<a href='http://www.twitter.com/". $acx_si_twitter ."' target='_blank' title='Visit Us On Twitter'>" . "<img src=" . plugins_url('images/themes/'. $acx_si_touse_theme .'/twitter.png', __FILE__) . " style='border:0px;' alt='Visit Us On Twitter' /></a>";
		}
		if	($acx_si_facebook == "") { $facebook_link = ""; } else 
		{
			$facebook_link = "<a href='". $acx_si_facebook ."' target='_blank' title='Visit Us On Facebook'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/facebook.png', __FILE__) . " style='border:0px;' alt='Visit Us On Facebook' /></a>";
		}
		if	($acx_si_gplus == "") { $gplus_link = ""; } else 
		{
			$gplus_link = "<a href='". $acx_si_gplus ."' target='_blank' title='Visit Us On Google Plus'>" . "<img src=" . plugins_url('images/themes/'. $acx_si_touse_theme .'/googleplus.png', __FILE__) . " style='border:0px;' alt='Visit Us On Google Plus' /></a>";
		}
		if	($acx_si_pinterest == "") { $pinterest_link = ""; } else 
		{
			$pinterest_link = "<a href='". $acx_si_pinterest ."' target='_blank' title='Visit Us On Pinterest'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/pinterest.png', __FILE__) . " style='border:0px;' alt='Visit Us On Pinterest' /></a>";
		}
		if	($acx_si_youtube == "") { $youtube_link = ""; } else 
		{
			$youtube_link = "<a href='". $acx_si_youtube ."' target='_blank' title='Visit Us On Youtube'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/youtube.png', __FILE__) . " style='border:0px;' alt='Visit Us On Youtube' /></a>";
		}
		if	($acx_si_linkedin == "") { $linkedin_link = ""; } else 
		{
			$linkedin_link = "<a href='". $acx_si_linkedin ."' target='_blank' title='Visit Us On Linkedin'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/linkedin.png', __FILE__) . " style='border:0px;' alt='Visit Us On Linkedin' /></a>";
		}
		if	($acx_si_feed == "") { $feed_link = ""; } else 
		{
			$feed_link = "<a href='". $acx_si_feed ."' target='_blank' title='Check Our Feed'>" . "<img src=" . plugins_url('images/themes/' . $acx_si_touse_theme .'/feed.png', __FILE__) . " style='border:0px;' alt='Check Our Feed' /></a>";
		}
		$social_icon_array_order = get_option('social_icon_array_order');
	$social_icon_array_order = unserialize($social_icon_array_order);
	foreach ($social_icon_array_order as $key => $value)
	{
		if ($value == 0) { echo $twitter_link; } 
		else if ($value == 1) { echo $facebook_link; } 
		else if ($value == 2) { echo $gplus_link; } 
		else if ($value == 3) { echo $pinterest_link; } 
		else if ($value == 4) { echo $youtube_link; } 
		else if ($value == 5) { echo $linkedin_link; } 
		
		else if ($value == 6) { echo $feed_link; }
	}
} //acurax_si_simple()
function acx_theme_check_wp_head() {
	$template_directory = get_template_directory();
	// If header.php exists in the current theme, scan for "wp_head"
	$file = $template_directory . '/header.php';
	if (is_file($file)) {
		$search_string = "wp_head";
		$file_lines = @file($file);
		
		foreach ($file_lines as $line) {
			$searchCount = substr_count($line, $search_string);
			if ($searchCount > 0) {
				return true;
			}
		}
		
		// wp_head() not found:
		echo "<div class=\"highlight\" style=\"width: 99%; margin-top: 10px; margin-bottom: 10px; border: 1px solid darkred;\">" . "Your theme needs to be fixed for plugins to work (Especially Floating Social Media Icon). To fix your theme, use the <a href=\"theme-editor.php\">Theme Editor</a> to insert <code>&lt;?php wp_head(); ?&gt;</code> just before the <code>&lt;/head&gt;</code> line of your theme's <code>header.php</code> file." . "</div>";
	}
} // theme check 
if($acx_si_fsmi_theme_warning_ignore != "yes")
{
add_action('admin_notices', 'acx_theme_check_wp_head');
}
function acx_theme_check_wp_footer() {
	$template_directory = get_template_directory();
	
	// If footer.php exists in the current theme, scan for "wp_footer"
	$file = $template_directory . '/footer.php';
	if (is_file($file)) {
		$search_string = "wp_footer";
		$file_lines = @file($file);
		
		foreach ($file_lines as $line) {
			$searchCount = substr_count($line, $search_string);
			if ($searchCount > 0) {
				return true;
			}
		}
		
		// wp_footer() not found:
		echo "<div class=\"highlight\" style=\"width: 99%; margin-top: 10px; margin-bottom: 10px; border: 1px solid darkred;\">" . "Your theme needs to be fixed for plugins to work (Especially Floating Social Media Icon). To fix your theme, use the <a href=\"theme-editor.php\">Theme Editor</a> to insert <code>&lt;?php wp_footer(); ?&gt;</code> just before the <code>&lt;/body&gt;</code> line of your theme's <code>footer.php</code> file." . "</div>";
	}
}
if($acx_si_fsmi_theme_warning_ignore != "yes")
{
add_action('admin_notices', 'acx_theme_check_wp_footer');
}
function acurax_icons()
{
	global $acx_si_theme, $acx_si_credit, $acx_si_display , $acx_si_twitter, $acx_si_facebook, $acx_si_youtube, 		
	$acx_si_linkedin, $acx_si_gplus, $acx_si_pinterest, $acx_si_feed, $acx_si_icon_size;
			
	if($acx_si_twitter != "" || $acx_si_facebook != "" || $acx_si_youtube != "" || $acx_si_linkedin != ""  || 
	$acx_si_pinterest != "" || $acx_si_gplus != "" || $acx_si_feed != "")
	{
	//*********************** STARTED DISPLAYING THE ICONS ***********************
		echo "\n\n\n<!-- Starting Icon Display Code For Social Media Icon From Acurax International www.acurax.com -->\n";
		echo "<div id='divBottomRight' style='text-align:center;'>";
		acurax_si_simple();		
		echo "</div>\n";
		echo "<!-- Ending Icon Display Code For Social Media Icon From Acurax International www.acurax.com -->\n\n\n";
	//*****************************************************************************
	} // Chking null fields
	
} // Ending acurax_icons();
// Setting X Y values for javascript
$x = -170;
$y = -60;
function acx_load_floating_js()
{ 
	global $x;
	global $y;
	//*************** STARTING PUMBING JAVASCIRPT *******************************
	echo "\n\n\n<!-- Starting Javascript For Social Media Icon From Acurax International www.acurax.com -->\n";	
	$acx_si_icon_size = get_option('acx_si_icon_size');
	////////////////////////////////////////////////////////////////////////////
	//STARTING CROSS CHECK			    $count,$icon_size,$set_value  
	function acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value)
	{
		// Defining Values To Use
		$acx_si_icon_size = get_option('acx_si_icon_size'); // Getting Value From DB :)
		$acx_si_twitter = get_option('acx_si_twitter');
		$acx_si_facebook = get_option('acx_si_facebook');
		$acx_si_youtube = get_option('acx_si_youtube');
		$acx_si_linkedin = get_option('acx_si_linkedin');
		$acx_si_feed = get_option('acx_si_feed');
		$acx_si_gplus = get_option('acx_si_gplus');
		$acx_si_pinterest = get_option('acx_si_pinterest');
		$count_check = 0;
		$l1 = 0;
		$l2 = 0;
		$l3 = 0;
		$l4 = 0;
		$l5 = 0;
		$l6 = 0;
		$l7 = 0;
		if ($acx_si_twitter != "") { $l1 = 1; }
		if ($acx_si_facebook != "") { $l2 = 1; }
		if ($acx_si_youtube != "") { $l3 = 1; }
		if ($acx_si_linkedin != "") { $l4 = 1; }
		if ($acx_si_gplus != "") { $l5 = 1; }
		if ($acx_si_pinterest != "") { $l6 = 1; }
		if ($acx_si_feed != "") { $l7 = 1; }
		$count_check = $l1 + $l2 + $l3 + $l4 + $l5 + $l6 + $l7;
		if ($acx_si_icon_size == $icon_size && $count_check == $count)
		{
			global $x;
			global $y;
			$x = $set_x_value;
			$y = $set_y_value;
		}
	} // ENDING THE FUNCTION TO CROS CHECK
	/**************************************************************************
	CONDITIONS STARTING HERE  
	if X Decreases then move to Right
	If Y Decreases then Move to Down
	***************************************************************************/
	// Icon Size 16 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,16,-170,-35);
	acx_si_check_loaded_count(2,16,-170,-35);
	acx_si_check_loaded_count(3,16,-170,-35);
	acx_si_check_loaded_count(4,16,-170,-35);
	acx_si_check_loaded_count(5,16,-170,-35);
	acx_si_check_loaded_count(6,16,-170,-35);
	acx_si_check_loaded_count(7,16,-170,-35);
	// *********************************
	// Icon Size 25 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,25,-160,-50);
	acx_si_check_loaded_count(2,25,-160,-50);
	acx_si_check_loaded_count(3,25,-160,-50);
	acx_si_check_loaded_count(4,25,-160,-50);
	acx_si_check_loaded_count(5,25,-160,-50);
	acx_si_check_loaded_count(6,25,-180,-50);
	acx_si_check_loaded_count(7,25,-180,-50);
	// *********************************
	// Icon Size 32 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,32,-170,-55);
	acx_si_check_loaded_count(2,32,-170,-55);
	acx_si_check_loaded_count(3,32,-170,-55);
	acx_si_check_loaded_count(4,32,-170,-55);
	acx_si_check_loaded_count(5,32,-190,-70);
	acx_si_check_loaded_count(6,32,-160,-80);
	acx_si_check_loaded_count(7,32,-160,-80);
	// *********************************
	// Icon Size 40 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,40,-170,-65);
	acx_si_check_loaded_count(2,40,-170,-65);
	acx_si_check_loaded_count(3,40,-170,-65);
	acx_si_check_loaded_count(4,40,-170,-105);
	acx_si_check_loaded_count(5,40,-170,-105);
	acx_si_check_loaded_count(6,40,-170,-105);
	acx_si_check_loaded_count(7,40,-170,-145);
	// *********************************
	// Icon Size 48 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,48,-170,-75);
	acx_si_check_loaded_count(2,48,-170,-75);
	acx_si_check_loaded_count(3,48,-170,-75);
	acx_si_check_loaded_count(4,48,-170,-120);
	acx_si_check_loaded_count(5,48,-170,-120);
	acx_si_check_loaded_count(6,48,-170,-120);
	acx_si_check_loaded_count(7,48,-170,-175);
	// *********************************
	// Icon Size 55 Starts Here
	// acx_si_check_loaded_count($count,$icon_size,$set_x_value,$set_y_value);
	acx_si_check_loaded_count(1,55,-170,-80);
	acx_si_check_loaded_count(2,55,-170,-80);
	acx_si_check_loaded_count(3,55,-170,-135);
	acx_si_check_loaded_count(4,55,-170,-135);
	acx_si_check_loaded_count(5,55,-190,-135);
	acx_si_check_loaded_count(6,55,-190,-135);
	acx_si_check_loaded_count(7,55,-190,-200);
	// *********************************
	/**************************************************************************
	CONDITIONS ENDING HERE
	***************************************************************************/
	?>
	<script type="text/javascript">
	var ns = (navigator.appName.indexOf("Netscape") != -1);
	var d = document;
	var px = document.layers ? "" : "px";
	function JSFX_FloatDiv(id, sx, sy)
	{
		var el=d.getElementById?d.getElementById(id):d.all?d.all[id]:d.layers[id];
		window[id + "_obj"] = el;
		if(d.layers)el.style=el;
		el.cx = el.sx = sx;el.cy = el.sy = sy;
		el.sP=function(x,y){this.style.left=x+px;this.style.top=y+px;};
		el.flt=function()
		{
			var pX, pY;
			pX = (this.sx >= 0) ? 0 : ns ? innerWidth : 
			document.documentElement && document.documentElement.clientWidth ? 
			document.documentElement.clientWidth : document.body.clientWidth;
			pY = ns ? pageYOffset : document.documentElement && document.documentElement.scrollTop ? 
			document.documentElement.scrollTop : document.body.scrollTop;
			if(this.sy<0) 
			pY += ns ? innerHeight : document.documentElement && document.documentElement.clientHeight ? 
			document.documentElement.clientHeight : document.body.clientHeight;
			this.cx += (pX + this.sx - this.cx)/8;this.cy += (pY + this.sy - this.cy)/8;
			this.sP(this.cx, this.cy);
			setTimeout(this.id + "_obj.flt()", 40);
		}
		return el;
	}
	 JSFX_FloatDiv("divBottomRight", <?php echo $x; ?>, <?php echo $y; ?>).flt();
	</script>
	<?php echo "<!-- Ending Javascript Code For Social Media Icon From Acurax International www.acurax.com -->\n\n\n";
} 	if ($acx_si_display == "auto" || $acx_si_display == "both") 
	{
		add_action('wp_footer', 'acx_load_floating_js',101);
	}
// Starting Footer PBL
function pbl_footer() 
{
	global $acx_si_theme, $acx_si_credit, $acx_si_display , $acx_si_twitter, $acx_si_facebook, $acx_si_youtube, 		
	$acx_si_linkedin, $acx_si_gplus, $acx_si_pinterest, $acx_si_feed;
	//********** CHECKING CREDIT LINK STATUS ******************
	if($acx_si_twitter != "" || $acx_si_facebook != "" || $acx_si_youtube != "" || $acx_si_linkedin != ""  || $acx_si_pinterest != "" || $acx_si_gplus != "" || $acx_si_feed != "")
	{
		if($acx_si_credit == "yes") 
		{ 
			echo "<div style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>";
			$acx_get_url = "http://";
			$acx_get_url .= $_SERVER['HTTP_HOST'];
			$acx_get_url .= $_SERVER['REQUEST_URI'];
			$acx_installation_domain = $_SERVER['HTTP_HOST'];
			$acx_installation_domain = str_replace("www.","",$acx_installation_domain);
			$acx_installation_domain = str_replace(".","_",$acx_installation_domain);
			if($acx_installation_domain == "") { $acx_installation_domain = "not_defined";}
			$x = strlen($acx_get_url);
			if(($x % 10) == 0)
			{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Animated Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/blog-design.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Wordpress Development Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Wordpress Development Company</a>";
} else if(($x % 9) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Floating Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Floating Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/blog-design.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Blog Design Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Blog Designing Company</a>";
} else if(($x % 8) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Integration</a> Powered by <a href='http://www.acurax.com/services/blog-design.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Web Design Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Wordpress Theme Designers</a>";
} else if(($x % 7) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/web-designing.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Affordable Website Designer' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Website Design Expert</a>";
} else if(($x % 6) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='SocialMedia Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/web-development.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Web Development Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Web Development Company</a>";} else if(($x % 5) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/website-redesign.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Website Redesign Experts' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Website Redesign Experts</a>";} else if(($x % 4) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/social-media-marketing-optimization/social-profile-design.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Social Profile Design Experts' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Social Profile Design Experts</a>";
} else if(($x % 3) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/?ref=" . $acx_installation_domain . "' target='_blank' title='Wordpress Development Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Wordpress Development Company</a>";
} else if(($x % 2) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/web-designing.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Web Design Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Web Design Company</a>";
} else if(($x % 1) == 0)
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Animated Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Wordpress Development Company' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Wordpress Development Company</a>";
} else 
{
echo "<a href='http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?ref=" . $acx_installation_domain . "' target='_blank' title='Social Media Wordpress plugin' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Social Media Icons</a> Powered by <a href='http://www.acurax.com/services/online-store-design-development.php?utm_source=blink&utm_medium=link&utm_campaign=footer&ref=" . $acx_installation_domain . "' target='_blank' title='Ecommerce Design Expert' style='text-align:center;color:gray;font-family:arial;font-size:11px;text-decoration:none;'>Acurax Ecommerce Design Expert</a>";
}
			// Ending Crediting
			echo "</div>";
		} 
	}
} add_action('wp_footer', 'pbl_footer'); // pbl_footer
function extra_style_acx_icon() // updated added class acx_fsmi_float_fix support
{
	global $acx_si_icon_size, $acx_si_fsmi_float_fix, $acx_si_display, $acx_si_fsmi_disable_mobile;
	if($acx_si_fsmi_disable_mobile == "") { $acx_si_fsmi_disable_mobile = "no"; }
		echo "\n\n\n<!-- Starting Styles For Social Media Icon From Acurax International www.acurax.com -->\n<style type='text/css'>\n";
		echo "#divBottomRight img \n{\n";
		echo "width: " . $acx_si_icon_size . "px; \n}\n";
		
			if ($acx_si_display == "manual") 
			{
				echo "#divBottomRight \n{\n";
				echo "min-width:0px; \n";
				echo "position: static; \n}\n";
			}
			if ($acx_si_fsmi_float_fix == "yes") 
			{
				echo ".acx_fsmi_float_fix a \n{\n";
				echo "display:inline-block; \n}\n";
			}
			if($acx_si_fsmi_disable_mobile == "yes")
			{
				echo "@media only screen and (max-width:650px) \n{\n#divBottomRight \n{\n";
				echo "display:none !important; \n}\n}\n";
			}
			
		echo "</style>\n<!-- Ending Styles For Social Media Icon From Acurax International www.acurax.com -->\n\n\n\n";
}	add_action('admin_head', 'extra_style_acx_icon'); // ADMIN
	add_action('wp_head', 'extra_style_acx_icon'); // PUBLIC 
function acx_si_admin_style()  // Adding Style For Admin
{
global $acx_si_fsmi_menu_highlight;
	echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('style_admin.css', __FILE__). '">';
	if ($acx_si_fsmi_menu_highlight != "no") {
	echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('dynamic_admin_style.css', __FILE__). '">';
	} // updated
}	add_action('admin_head', 'acx_si_admin_style'); // ADMIN
	$acx_si_display = get_option('acx_si_display');
if	($acx_si_display == "auto" || $acx_si_display == "both") 
{
	add_action('wp_footer', 'acurax_icons',100);
}
$acx_si_sc_id = 0; // Defined to assign shortcode unique id
function DISPLAY_ACURAX_ICONS_SC($atts)
{
	global $acx_si_display, $acx_si_icon_size, $acx_si_sc_id;
	extract(shortcode_atts(array(
	"theme" => '',
	"size" => $acx_si_icon_size,
	"autostart" => 'false'
	), $atts));
	if (!is_numeric($theme)) { $theme = ""; } else {
	if ($theme > ACX_SOCIALMEDIA_TOTAL_THEMES) { $theme = ""; }
	}
	if (!is_numeric($size)) { $size = $acx_si_icon_size; } else {
	if ($size > 55) { $size = $acx_si_icon_size; }
	}
	if ($acx_si_display != "auto" || $acx_si_display == "both") 
	{
		$acx_si_sc_id = $acx_si_sc_id + 1;
		ob_start();
		echo "<style>\n";
		echo "#short_code_si_icon img \n {";
		echo "width:" . $size . "px; \n}\n";
		echo ".scid-" . $acx_si_sc_id . " img \n{\n";
		echo "width:" . $size . "px !important; \n}\n";
		echo "</style>";
		echo "<div id='short_code_si_icon' style='text-align:center;' class='acx_fsmi_float_fix scid-" . $acx_si_sc_id . "'>"; // updated
		acurax_si_simple($theme);
		echo "</div>";
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	} 
	else echo "<!-- Select Display Mode as Manual To Show The Acurax Social Media Icons -->";
} // DISPLAY_ACURAX_ICONS_SC
function DISPLAY_ACURAX_ICONS()
{
		global $acx_si_display, $acx_si_icon_size;;
	if ($acx_si_display != "auto" || $acx_si_display == "both") 
	{
		echo "\n\n\n<!-- Starting Styles For Social Media Icon (PHP CODE) From Acurax International www.acurax.com 
		-->\n<style 
		type='text/css'>\n";
		echo "#short_code_si_icon img \n{\n";
		echo "width: " . $acx_si_icon_size . "px; \n}\n";
		echo "</style>\n<!-- Ending Styles For Social Media Icon (PHP CODE) From Acurax International www.acurax.com 
		-->\n\n\n\n";
		echo "<div id='short_code_si_icon' style='text-align:center;'>";
		acurax_si_simple();
		echo "</div>";
	} 
	else echo "<!-- Select Display Mode as Manual To Show The Acurax Social Media Icons -->";
} // DISPLAY_ACURAX_ICONS
			
function acx_not_auto()
{
	echo "<!-- Select Display Mode as Manual To Show The Acurax Social Media Icons -->";
}
if	($acx_si_display != "auto" || $acx_si_display == "both") 
{
	add_shortcode( 'DISPLAY_ACURAX_ICONS', 'DISPLAY_ACURAX_ICONS_SC' ); // Defining Shortcode to show Social Media Icon
}
else
{
	add_shortcode( 'DISPLAY_ACURAX_ICONS', 'acx_not_auto' ); // Defining Shortcode to show Select Manual
}
function acx_si_custom_admin_js()
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
}	add_action( 'admin_enqueue_scripts', 'acx_si_custom_admin_js' );
// wp-admin Notices >> Finish Upgrade
function acx_si_pluign_upgrade_not_finished()
{
    echo '<div class="error">
		  <p><b>Thanks for updating Floating Social Media Icon plugin... You need to visit <a href="admin.php?page=Acurax-Social-Icons-Settings">Plugin\'s Settings Page</a> to Complete the Updating Process - <a href="admin.php?page=Acurax-Social-Icons-Settings">Click Here Visit Social Icon Plugin Settings</a></b></p>
		  </div>';
}
$total_arrays = 7; // Number Of Services
$social_icon_array_order = get_option('social_icon_array_order');
$social_icon_array_order = unserialize($social_icon_array_order);
$social_icon_array_count = count($social_icon_array_order); 
if ($social_icon_array_count < $total_arrays) 
{
	add_action('admin_notices', 'acx_si_pluign_upgrade_not_finished',1);
}
function acx_fsmi_si_pluign_finish_version_update()
{
    echo '<div id="message" class="updated">
		  <p><b>Thanks for updating Floating Social Media Icon plugin... You need to visit <a href="admin.php?page=Acurax-Social-Icons-Settings&status=updated#updated">Plugin\'s Settings Page</a> to Complete the Updating Process - <a href="admin.php?page=Acurax-Social-Icons-Settings&status=updated#updated">Click Here Visit Social Icon Plugin Settings</a></b></p>
		  </div>';
}
$acx_fsmi_si_current_version = get_option('acx_fsmi_si_current_version');
if($acx_fsmi_si_current_version != '1.3.5') // Current Version
{
if (get_option('social_icon_array_order') != "")
{
	add_action('admin_notices', 'acx_fsmi_si_pluign_finish_version_update',1);
}
}
// wp-admin Notices >> Plugin not configured
function acx_si_pluign_not_configured()
{
    echo '<div class="error">
	<p><b>Floating Social Media Icon Plugin is not configured. You need to configure your social media profile URL\'s 
		  to start showing the Floating Social Media Icons - <a href="admin.php?page=Acurax-Social-Icons-Settings">Click 
		  here to configure</a></b></p>
		  </div>';
}
if ($social_icon_array_count == $total_arrays) 
{
if ($acx_si_twitter == "" && $acx_si_facebook == "" && $acx_si_youtube == "" && $acx_si_linkedin == ""  && $acx_si_pinterest == "" && $acx_si_gplus == "" && $acx_si_feed == "")
{
	add_action('admin_notices', 'acx_si_pluign_not_configured',1);
} // Chking If Plugin Not Configured
} // Chking $social_icon_array_count == $total_arrays
// wp-admin Notices >> Plugin not configured
function acx_si_pluign_promotion()
{
$acx_tweet_text_array = array
						(
						"I Use Floating SocialMedia wordpress plugin from @acuraxdotcom and you should too",
						"Floating Social Media wordpress Plugin from @acuraxdotcom is Awesome",
						"Thanks @acuraxdotcom for developing such a wonderful social media wordpress plugin",
						"Actually i am looking for a social media Plugin like this. Thanks @acuraxdotcom",
						"Its very nice to use Floating Social media wordpress Plugin from @acuraxdotcom",
						"I installed Floating Social Media.. from @acuraxdotcom,  It works wonderful",
						"The floating social media icon wordpress plugin looks soo nice.. thanks @acuraxdotcom", 
						"It awesome to use Floating Social Media wordpress plugin from @acuraxdotcom",
						"Floating Social Media wordpress Plugin that i use Looks awesome and works terrific",
						"I am using Floating Social Media Icon wordpress Plugin from @acuraxdotcom I like it!",
						"The socialmedia plugin from @acuraxdotcom Its simple looks good and works fine",
						"Ive been using this social media plugin for a while now and it is attractive",
						"Floating Social Media Icon wordpress plugin is Fantastic Plugin",
						"Floating Social Media Icon wordpress plugin was easy to use and works great. thank you!",
						"Good and flexible wp socialmedia plugin especially for beginners.",
						"Easily the best socialmedia wordpress plugin of the type I have used ! THANKS! @acuraxdotcom",
						);
$acx_tweet_text = array_rand($acx_tweet_text_array, 1);
$acx_tweet_text = $acx_tweet_text_array[$acx_tweet_text];
// echo $acx_tweet_text;
    echo '<div id="acx_td" class="error" style="background: none repeat scroll 0pt 0pt infobackground; border: 1px solid inactivecaption; padding: 5px;line-height:16px;">
	<p>It looks like you have been enjoying using Floating Social Media Icon plugin from <a href="http://www.acurax.com?utm_source=plugin&utm_medium=thirtyday&utm_campaign=fsmi" title="Acurax Web Designing Company" target="_blank">Acurax</a> for atleast 30 days.Would you consider upgrading to <a href="http://clients.acurax.com/floating-socialmedia.php/?utm_source=plugin&utm_medium=thirtyday_yellow&utm_campaign=fsmi" title="Premium Floating Social Media Icon" target="_blank">premium version</a> to enjoy more features and help support continued development of the plugin? - Spreading the world about this plugin. Thank you for using the plugin</p>
	<p>
	<a href="http://wordpress.org/support/view/plugin-reviews/floating-social-media-icon/" class="button" style="color:black;text-decoration:none;padding:5px;margin-right:4px;" target="_blank">Rate it 5★\'s on wordpress</a>
	<a href="https://twitter.com/share?url=http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/&text='.$acx_tweet_text.' -" class="button" style="color:black;text-decoration:none;padding:5px;margin-right:4px;" target="_blank">Tell Your Followers</a>
	<a href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin&utm_medium=thirtyday&utm_campaign=fsmi" class="button" style="color:black;text-decoration:none;padding:5px;margin-right:4px;" target="_blank">Order Premium Version</a>
	<a href="admin.php?page=Acurax-Social-Icons-Premium&td=hide" class="button" style="color:black;text-decoration:none;padding:5px;margin-right:4px;margin-left:20px;">Don\'t Show This Again</a>
</p>
		  
		  </div>';
}
$acx_si_installed_date = get_option('acx_si_installed_date');
if ($acx_si_installed_date=="") { $acx_si_installed_date = time();}
if($acx_si_installed_date < ( time() - 2952000 ))
{
if (get_option('acx_si_td') != "hide")
{
add_action('admin_notices', 'acx_si_pluign_promotion',1);
}
}
// Starting Widget Code
class Acx_Social_Icons_Widget extends WP_Widget
{
    // Register the widget
    function Acx_Social_Icons_Widget() 
	{
        // Set some widget options
        $widget_options = array( 'description' => 'Allow users to show Social Media Icons From Floating Social Media Icon 
		Plugin', 'classname' => 'acx-social-icons-desc' );
        // Set some control options (width, height etc)
        $control_options = array( 'width' => 300 );
        // Actually create the widget (widget id, widget name, options...)
        $this->WP_Widget( 'acx-social-icons-widget', 'Acx Social Icons', $widget_options, $control_options );
    }
    // Output the content of the widget
    function widget($args, $instance) 
	{
        extract( $args ); // Don't worry about this
        // Get our variables
        $title = apply_filters( 'widget_title', $instance['title'] );
		$icon_size = $instance['icon_size'];
		$icon_theme = $instance['icon_theme'];
		$icon_align = $instance['icon_align'];
        // This is defined when you register a sidebar
        echo $before_widget;
        // If our title isn't empty then show it
        if ( $title ) 
		{
            echo $before_title . $title . $after_title;
        }
		echo "<style>\n";
		echo "." . $this->get_field_id('widget') . " img \n{\n";
		echo "width:" . $icon_size . "px; \n } \n";
		echo "</style>";
		echo "<div id='acurax_si_simple' class='acx_fsmi_float_fix " . $this->get_field_id('widget') . "'"; // updated
		if($icon_align != "") { echo " style='text-align:" . $icon_align . ";'>"; } else { echo " style='text-align:center;'>"; }
		acurax_si_simple($icon_theme);
		echo "</div>";
        // This is defined when you register a sidebar
        echo $after_widget;
    }
	// Output the admin options form
	function form($instance) 
	{
		$total_themes = ACX_SOCIALMEDIA_TOTAL_THEMES;
		$total_themes = $total_themes + 1;
		// These are our default values
		$defaults = array( 'title' => 'Social Media Icons','icon_size' => '32' );
		// This overwrites any default values with saved values
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
				value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('icon_size'); ?>"><?php _e('Icon Size:'); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('icon_size'); ?>" id="<?php echo $this->get_field_id('icon_size'); ?>">
<option value="16"<?php if ($instance['icon_size'] == "16") { echo 'selected="selected"'; } ?>>16px X 16px </option>
<option value="25"<?php if ($instance['icon_size'] == "25") { echo 'selected="selected"'; } ?>>25px X 25px </option>
<option value="32"<?php if ($instance['icon_size'] == "32") { echo 'selected="selected"'; } ?>>32px X 32px </option>
<option value="40"<?php if ($instance['icon_size'] == "40") { echo 'selected="selected"'; } ?>>40px X 40px </option>
<option value="48"<?php if ($instance['icon_size'] == "48") { echo 'selected="selected"'; } ?>>48px X 48px </option>
<option value="55"<?php if ($instance['icon_size'] == "55") { echo 'selected="selected"'; } ?>>55px X 55px </option>
</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('icon_theme'); ?>"><?php _e('Icon Theme:'); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('icon_theme'); ?>" id="<?php echo $this->get_field_id('icon_theme'); ?>">
<option value=""<?php if ($instance['icon_theme'] == "") { echo 'selected="selected"'; } ?>>Default Theme Design</option>
<?php
for ($i=1; $i < $total_themes; $i++)
{
?>
<option value="<?php echo $i; ?>"<?php if ($instance['icon_theme'] == $i) { echo 'selected="selected"'; } ?>>Theme Design <?php echo $i; ?> </option>
<?php
}	?>
</select>
			</p>
<p>
	<label for="<?php echo $this->get_field_id('icon_align'); ?>"><?php _e('Icon Align:'); ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name('icon_align'); ?>" id="<?php echo $this->get_field_id('icon_align'); ?>">
	<option value=""<?php if ($instance['icon_align'] == "") { echo 'selected="selected"'; } ?>>Default </option>
	<option value="left"<?php if ($instance['icon_align'] == "left") { echo 'selected="selected"'; } ?>>Left </option>
	<option value="center"<?php if ($instance['icon_align'] == "center") { echo 'selected="selected"'; } ?>>Center </option>
	<option value="right"<?php if ($instance['icon_align'] == "right") { echo 'selected="selected"'; } ?>>Right </option>
	</select>
</p>
		<?php
	}
	// Processes the admin options form when saved
	function update($new_instance, $old_instance) 
	{
		// Get the old values
		$instance = $old_instance;
		// Update with any new values (and sanitise input)
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['icon_size'] = strip_tags( $new_instance['icon_size'] );
		$instance['icon_theme'] = strip_tags( $new_instance['icon_theme'] );
		$instance['icon_align'] = strip_tags( $new_instance['icon_align'] );
		return $instance;
	}
} add_action('widgets_init', create_function('', 'return register_widget("Acx_Social_Icons_Widget");'));
// Ending Widget Codes
function acurax_optin()
{ 
echo "";
} 
function socialicons_comparison($ad=2)
{
$ad_1 = '
</hr>
<a name="compare"></a><div id="ss_middle_wrapper"> 
		<div id="ss_middle_center"> 
			<div id="ss_middle_inline_block"> 
			
				<div class="middle_h2_1"> 
					<h2>Limited on Features ?</h2>
					<h3>Compare and Decide</h3>
				</div><!-- middle_h2_1 -->
				
<div id="ss_features_table"> 
				
					<div id="ss_table_header"> 
						<div class="tb_h1"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
							<div class="tb_h2"> <h3>Features</h3> </div><!-- tb_h2 -->
							<div class="tb_h3"> <div class="ss_download"> </div><!-- ss_download --> </div><!-- tb_h3 -->
						<div class="tb_h4 fsmi_tb_h4"> <a href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin_fsmi_settings_table&utm_medium=link&utm_campaign=compare_buynow" target="_blank"><div class="ss_buy_now"> </div><!-- ss_buy_now --></a> </div><!-- tb_h4 -->
					</div><!-- ss_table_header -->
						
					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 197px;"> Display </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>More Sharp Quality Icons</li>
									<li>20+ Icon Theme/Style</li>
										<li>Can Choose Icon Theme/Style</li>
											<li>Can Choose Icon Size</li>
												<li>Automatic/Manual Integration</li>
													<li>Set MouseOver text for each icon in Share Mode</li>
												<li>Set MouseOver text for each icon in Profile Link Mode</li>
											<li>Option to HIDE Invididual Share Icon</li>
										<li><strong>Set Floating Icons in Vertical</strong></li>
									<li><strong>Define how many icons in 1 row</strong></li>
								<li class="ss_last_one"><strong>Add Custom Icons</strong></li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
										<div class="ss_yes"> </div><!-- ss_yes -->
											<div class="ss_yes"> </div><!-- ss_yes -->
												<div class="ss_no"> </div><!-- ss_no -->
											<div class="ss_no"> </div><!-- ss_no -->
										<div class="ss_no"> </div><!-- ss_no -->
									<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
										<div class="ss_yes"> </div><!-- ss_yes -->
											<div class="ss_yes"> </div><!-- ss_yes -->
												<div class="ss_yes"> </div><!-- ss_yes -->
											<div class="ss_yes"> </div><!-- ss_yes -->
										<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->
					
					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 30px;"> Icon Function </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Link to Social Media Profile</li>
								<li class="ss_last_one"><strong>Share On Social Media</strong></li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->			

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 30px;"> Animation </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Fly Animation</li>
								<li class="ss_last_one"><strong>Mouse Over Effects</strong></li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->	

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 84px;"> Fly Animation Repeat Interval</div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Based On Time in Seconds</li>
									<li><strong>Based On Time in Minutes</strong></li>
										<li>Based On Time in Hours</li>
									<li>Based on Page Views</li>
								<li class="ss_last_one">Based On Page Views and Time</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
									<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->	

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 30px;"> Multiple Fly Animation<br/></div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Can Choose Fly Start Position</li>
								<li class="ss_last_one">Can Choose Fly End Position</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->				

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 52px;">Easy to Configure</div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Ajax Based Settings Page</li>
									<li>Drag & Drop Reorder Icons</li>
								<li class="ss_last_one">Easy to Configure</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder"> 
							<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->			

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 106px;">Widget Support </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Multiple Widgets</li>
									<li>Separate Icon Style/Theme For Each</li>
										<li>Separate Icon Size For Each</li>
										<li>Set whether the icons to Link Profiles/SHARE</li>
									<li><strong>Separate Mouse Over Multiple Animation for Each</strong></li>
								<li class="ss_last_one">Separate Default Opacity for Each</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder">
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->	

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 106px;">Shortcode Support </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Multiple Instances</li>
									<li>Separate Icon Style/Theme For Each</li>
										<li><strong>Separate Icon Size For Each</strong></li>
										<li>Set whether the icons to Link Profiles/SHARE</li>
									<li>Separate Mouse Over Multiple Animation for Each</li>
								<li class="ss_last_one">Separate Default Opacity for Each</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder">
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->	

					<div class="ss_column_holder"> 
					
						<div class="tb_h1 mini"> <h3>Feature Group</h3> </div><!-- tb_h1 -->
						<div class="ss_feature_group" style="padding-top: 126px;">PHP Code Support </div><!-- -->
						<div class="tb_h1 mini"> <h3>Features</h3> </div><!-- tb_h1 -->
						<div class="ss_features"> 
							<ul>
								<li>Multiple Instances</li>
									<li>Use Outside Loop</li>
										<li>Separate Icon Style/Theme For Each</li>
											<li>Separate Icon Size For Each</li>
										<li><strong>Set whether the icons to Link Profiles/SHARE</strong></li>
									<li>Separate Mouse Over Multiple Animation for Each</li>
								<li class="ss_last_one">Separate Default Opacity for Each</li>
							</ul>
						</div><!-- ss_features -->
						
						<div class="tb_h1 mini"> <h3>FREE &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <span style="color: #ffe400;">PREMIUM</span></h3> </div><!-- tb_h1 -->
						<div class="ss_y_n_holder">
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
										<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_no"> </div><!-- ss_no -->
								<div class="ss_no"> </div><!-- ss_no -->
							<div class="ss_no ss_last_one"> </div><!-- ss_no -->
						</div><!-- ss_y_n_holder -->
						
						<div class="ss_y_n_holder"> 
							<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
										<div class="ss_yes"> </div><!-- ss_yes -->
									<div class="ss_yes"> </div><!-- ss_yes -->
								<div class="ss_yes"> </div><!-- ss_yes -->
							<div class="ss_yes ss_last_one"> </div><!-- ss_yes -->
						</div><!-- ss_y_n_holder -->						
						
					</div><!-- column_holder -->						
					
				
					
				</div><!-- ss_features_table -->		

			<div id="ad_fsmi_2_button_order" style="float: left; width: 100%;">
<a href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin_fsmi_settings&utm_medium=banner&utm_campaign=plugin_yellow_order" target="_blank"><div id="ad_fsmi_2_button_order_link"></div></a></div> <!-- ad_fsmi_2_button_order --></div></div></div>';
$ad_2='<div id="ad_fsmi_2"> <a href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin_fsmi_settings&utm_medium=banner&utm_campaign=plugin_enjoy" target="_blank"><div id="ad_fsmi_2_button"></div></a> </div> <!-- ad_fsmi_2 --><br>
<div id="ad_fsmi_2_button_order">
<a href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin_fsmi_settings&utm_medium=banner&utm_campaign=plugin_yellow_order" target="_blank"><div id="ad_fsmi_2_button_order_link"></div></a></div> <!-- ad_fsmi_2_button_order --> ';
if($ad=="" || $ad == 2) { echo $ad_2; } else if ($ad == 1) { echo $ad_1; } else { echo $ad_2; } 
} // Updated
function acx_fsmi_saveorder_callback()
{
	global $wpdb;
$social_icon_array_order = $_POST['recordsArray'];
if (current_user_can('manage_options')) {
	$social_icon_array_order = serialize($social_icon_array_order);
	update_option('social_icon_array_order', $social_icon_array_order);
	echo "<div id='acurax_notice' align='center' style='width: 420px; font-family: arial; font-weight: normal; font-size: 22px;'>";
	echo "Social Media Icon's Order Saved";
	echo "</div><br>";
}
	die(); // this is required to return a proper result
} add_action('wp_ajax_acx_fsmi_saveorder', 'acx_fsmi_saveorder_callback');
function acx_quick_request_submit_callback()
{
	$acx_name =  $_POST['acx_name'];
	$acx_email =  $_POST['acx_email'];
	$acx_phone =  $_POST['acx_phone'];
	$acx_weburl =  $_POST['acx_weburl'];
	$acx_subject =  $_POST['acx_subject'];
	$acx_question =  $_POST['acx_question'];
if($acx_name == "" || $acx_email == "" || $acx_weburl == "" || $acx_subject == "" || $acx_question == "")
{
echo 2;
} else
{
$current_user_acx = wp_get_current_user();
$current_user_acx = $current_user->user_email;
if($current_user_acx == "")
{
$current_user_acx = $acx_email;
}
$headers[] = 'From: ' . $acx_name . ' <' . $current_user_acx . '>';
$headers[] = 'Content-Type: text/html; charset=UTF-8'; 
$message = "Name: ".$acx_name . "\r\n <br>";
$message = $message . "Email: ".$acx_email . "\r\n <br>";
if($acx_phone != "")
{
$message = $message . "Phone: ".$acx_phone . "\r\n <br>";
}
// In case any of our lines are larger than 70 characters, we should use wordwrap()
$acx_question = wordwrap($acx_question, 70, "\r\n <br>");
$message = $message . "Website: ".$acx_weburl . "\r\n <br>";
$message = $message . "Question: ".$acx_question . "\r\n <br>";
$emailed = wp_mail( 'info@acurax.com', $acx_subject, $message, $headers );
if($emailed)
{
echo 1;
} else
{
echo 0;
}
}
	die(); // this is required to return a proper result
}add_action('wp_ajax_acx_quick_request_submit','acx_quick_request_submit_callback');
?>