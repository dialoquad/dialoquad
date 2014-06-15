<?php 
if($_POST['acurax_social_icon_hidden'] == 'Y') 
{	//Form data sent
$acx_si_fsmi_menu_highlight = $_POST['acx_si_fsmi_menu_highlight'];
update_option('acx_si_fsmi_menu_highlight', $acx_si_fsmi_menu_highlight);
$acx_si_fsmi_theme_warning_ignore = $_POST['acx_si_fsmi_theme_warning_ignore'];
update_option('acx_si_fsmi_theme_warning_ignore', $acx_si_fsmi_theme_warning_ignore);
$acx_si_fsmi_acx_service_banners = $_POST['acx_si_fsmi_acx_service_banners'];
update_option('acx_si_fsmi_acx_service_banners', $acx_si_fsmi_acx_service_banners);
$acx_si_fsmi_float_fix = $_POST['acx_si_fsmi_float_fix'];
update_option('acx_si_fsmi_float_fix', $acx_si_fsmi_float_fix);
$acx_si_fsmi_hide_advert = $_POST['acx_si_fsmi_hide_advert'];
update_option('acx_si_fsmi_hide_advert', $acx_si_fsmi_hide_advert);
$acx_si_fsmi_disable_mobile = $_POST['acx_si_fsmi_disable_mobile'];
update_option('acx_si_fsmi_disable_mobile', $acx_si_fsmi_disable_mobile);
$acx_si_fsmi_hide_expert_support_menu = $_POST['acx_si_fsmi_hide_expert_support_menu'];
update_option('acx_si_fsmi_hide_expert_support_menu', $acx_si_fsmi_hide_expert_support_menu);


?>
<div class="updated"><p><strong><?php _e('Acurax Floating Social Icons Misc Settings Saved!.' ); ?></strong></p></div>
<?php
}
else
{	//Normal page display
$acx_si_fsmi_menu_highlight = get_option('acx_si_fsmi_menu_highlight');
$acx_si_fsmi_theme_warning_ignore = get_option('acx_si_fsmi_theme_warning_ignore');
$acx_si_fsmi_acx_service_banners = get_option('acx_si_fsmi_acx_service_banners');
$acx_si_fsmi_float_fix = get_option('acx_si_fsmi_float_fix');
$acx_si_fsmi_hide_advert = get_option('acx_si_fsmi_hide_advert');
$acx_si_fsmi_disable_mobile = get_option('acx_si_fsmi_disable_mobile');
$acx_si_fsmi_hide_expert_support_menu = get_option('acx_si_fsmi_hide_expert_support_menu');

// Setting Defaults
if ($acx_si_fsmi_menu_highlight == "") {	$acx_si_fsmi_menu_highlight = "yes"; }
if ($acx_si_fsmi_theme_warning_ignore == "") {	$acx_si_fsmi_theme_warning_ignore = "no"; }
if ($acx_si_fsmi_acx_service_banners == "") {	$acx_si_fsmi_acx_service_banners = "yes"; }
if ($acx_si_fsmi_float_fix == "") {	$acx_si_fsmi_float_fix = "no"; }
if ($acx_si_fsmi_hide_advert == "") {	$acx_si_fsmi_hide_advert = "no"; }
if ($acx_si_fsmi_disable_mobile == "") {	$acx_si_fsmi_disable_mobile = "no"; }
if ($acx_si_fsmi_hide_expert_support_menu == "") {	$acx_si_fsmi_hide_expert_support_menu = "no"; }
} //Main else
?>
<div class="wrap">
<div style='background: none repeat scroll 0% 0% white; height: 100%; display: inline-block; padding: 8px; margin-top: 5px; border-radius: 15px; min-height: 450px; width: 100%;'>
<?php
$acx_si_fsmi_acx_service_banners = get_option('acx_si_fsmi_acx_service_banners');
if ($acx_si_fsmi_acx_service_banners != "no") { ?>
<div id="acx_ad_banners_fsmi">
<a href="http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=plugin-page&utm_medium=banner&utm_campaign=fsmi" target="_blank" class="acx_ad_fsmi_1">
<div class="acx_ad_fsmi_title">Wordpress Expert Support</div> <!-- acx_ad_fsmi_title -->
<div class="acx_ad_fsmi_desc">Troubleshoot WordPress site issues</div> <!-- acx_ad_fsmi_desc -->
</a> <!--  acx_ad_fsmi_1 -->

<a href="http://www.acurax.com/services/website-redesign.php?utm_source=plugin-page&utm_medium=banner&utm_campaign=fsmi" target="_blank" class="acx_ad_fsmi_1">
<div class="acx_ad_fsmi_title">Custom Theme Design</div> <!-- acx_ad_fsmi_title -->
<div class="acx_ad_fsmi_desc acx_ad_fsmi_desc2" style="padding-top: 4px; height: 41px; font-size: 13px; text-align: center;">Create, modify, or customise, themes</div> <!-- acx_ad_fsmi_desc -->
</a> <!--  acx_ad_fsmi_1 -->

<a href="http://www.acurax.com/services/web-development.php?utm_source=plugin-page&utm_medium=banner&utm_campaign=fsmi" target="_blank" class="acx_ad_fsmi_1">
<div class="acx_ad_fsmi_title">Plugin Development</div> <!-- acx_ad_fsmi_title -->
<div class="acx_ad_fsmi_desc acx_ad_fsmi_desc3" style="padding-top: 4px; height: 41px; font-size: 13px; text-align: center;">Custom plugin development according to your needs</div> <!-- acx_ad_fsmi_desc -->
</a> <!--  acx_ad_fsmi_1 -->

<a href="http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=plugin-page&utm_medium=banner&utm_campaign=fsmi" target="_blank" class="acx_ad_fsmi_1">
<div class="acx_ad_fsmi_title">Quick Support</div> <!-- acx_ad_fsmi_title -->
<div class="acx_ad_fsmi_desc acx_ad_fsmi_desc4" style="padding-top: 4px; height: 41px; font-size: 13px; text-align: center;">Explain errors and recommend solutions</div> <!-- acx_ad_fsmi_desc -->
</div> <!--  acx_ad_fsmi_1 -->
</a> <!--  acx_ad_banners_fsmi -->
<?php } else { ?>
<p class="widefat" style="padding:8px;width:99%;">
<b>Acurax Services >> </b>
<a href="http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=plugin-page&utm_medium=banner_link&utm_campaign=fsmi" target="_blank">Wordpress Expert Support</a> | 
<a href="http://www.acurax.com/services/website-redesign.php?utm_source=plugin-page&utm_medium=banner_link&utm_campaign=fsmi" target="_blank">Custom Theme Design</a> | 
<a href="http://www.acurax.com/services/web-development.php?utm_source=plugin-page&utm_medium=banner_link&utm_campaign=fsmi" target="_blank">Plugin Development</a> | 
<a href="http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=plugin-page&utm_medium=banner_link&utm_campaign=fsmi" target="_blank">Quick Support</a>
</p>
<?php } ?>
<?php if($acx_si_fsmi_hide_advert == "no")
{ ?>
<div id="acx_fsmi_premium">
<a style="margin: 8px 0px 0px 10px; float: left; font-size: 16px; font-weight: bold;" href="http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?utm_source=plugin&utm_medium=highlight&utm_campaign=fsmi" target="_blank">Fully Featured - Premium Floating Social Media Icon</a>
<a style="margin: -14px 0px 0px 10px; float: left;" href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin&utm_medium=highlight_yellow&utm_campaign=fsmi" target="_blank"><img src="<?php echo plugins_url('images/yellow.png', __FILE__);?>"></a>
</div> <!-- acx_fsmi_premium -->
<?php } ?>
<?php echo "<h2>" . __( 'Acurax Social Icons Misc Settings', 'acx_si_config' ) . "</h2>"; ?>
<form name="acurax_si_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="acurax_social_icon_hidden" value="Y">
<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Menu Highlight: " ); ?>
<select name="acx_si_fsmi_menu_highlight">
<option value="yes"<?php if ($acx_si_fsmi_menu_highlight == "yes") { echo 'selected="selected"'; } ?>>Yes, Highlight Plugin Menu </option>
<option value="no"<?php if ($acx_si_fsmi_menu_highlight == "no") { echo 'selected="selected"'; } ?>>No, Dont Highlight Plugin Menu </option>
</select>
<?php _e("If you don't like the plugin menu highlighting in green, you can set this to NO" ); ?>
</p>
<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Acurax Service Banners: " ); ?>
<select name="acx_si_fsmi_acx_service_banners">
<option value="yes"<?php if ($acx_si_fsmi_acx_service_banners == "yes") { echo 'selected="selected"'; } ?>>Yes, Show Them </option>
<option value="no"<?php if ($acx_si_fsmi_acx_service_banners == "no") { echo 'selected="selected"'; } ?>>No, Hide Them </option>
</select>
<?php _e("Show Acurax Service Banners On Plugin Settings Page?" ); ?>
</p>
<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Hide Premium Version Adverts: " ); ?>
<select name="acx_si_fsmi_hide_advert">
<option value="yes"<?php if ($acx_si_fsmi_hide_advert == "yes") { echo 'selected="selected"'; } ?>>Yes </option>
<option value="no"<?php if ($acx_si_fsmi_hide_advert == "no") { echo 'selected="selected"'; } ?>>No </option>
</select>
<?php _e("Would you like to hide the feature comparison advertisement of basic and premium version from plugin settings pages?" ); ?>
</p>

<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Hide Expert Support Menu?: " ); ?>
<select name="acx_si_fsmi_hide_expert_support_menu">
<option value="yes"<?php if ($acx_si_fsmi_hide_expert_support_menu == "yes") { echo 'selected="selected"'; } ?>>Yes </option>
<option value="no"<?php if ($acx_si_fsmi_hide_expert_support_menu == "no") { echo 'selected="selected"'; } ?>>No </option>
</select>
<?php _e("Would you like to hide the expert support sub menu?" ); ?>
</p>


<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Social Media Widget Theme Conflict Fix: " ); ?>
<select name="acx_si_fsmi_float_fix">
<option value="yes"<?php if ($acx_si_fsmi_float_fix == "yes") { echo 'selected="selected"'; } ?>>Enable </option>
<option value="no"<?php if ($acx_si_fsmi_float_fix == "no") { echo 'selected="selected"'; } ?>>Disable </option>
</select>
<?php _e("If your widget/shortcode icons are in Vertical, then enable this to make it Horizontal" ); ?>
</p>

<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Ignore Theme Warning?" ); ?>
<select name="acx_si_fsmi_theme_warning_ignore">
<option value="yes"<?php if ($acx_si_fsmi_theme_warning_ignore == "yes") { echo 'selected="selected"'; } ?>>Yes </option>
<option value="no"<?php if ($acx_si_fsmi_theme_warning_ignore == "no") { echo 'selected="selected"'; } ?>>No </option>
</select>
<?php _e("If everything is working properly and still the plugin shows theme warning, you can set this to Yes" ); ?>
</p>

<p class="widefat" style="padding:8px;width:99%;margin-top:8px;">	<?php _e("Disable Floating Social Media Icon on Mobile Devices?" ); ?>
<select name="acx_si_fsmi_disable_mobile">
<option value="yes"<?php if ($acx_si_fsmi_disable_mobile == "yes") { echo 'selected="selected"'; } ?>>Yes </option>
<option value="no"<?php if ($acx_si_fsmi_disable_mobile == "no") { echo 'selected="selected"'; } ?>>No </option>
</select>
<?php _e("If the floating icon making issues on mobile devices, then you can hide the floating icons on mobile devices by setting this value to Yes" ); ?>
</p>

<p class="submit">
<input type="submit" name="Submit" class="button" value="<?php _e('Save Settings', 'acx_si_config' ) ?>" />
</p>
</form>
<hr/>
<?php 
if($acx_si_fsmi_hide_advert == "no")
{
socialicons_comparison(1);
} ?> 
<br>
<p class="widefat" style="padding:8px;width:99%;">
Something Not Working Well? Have a Doubt? Have a Suggestion? - <a href="http://www.acurax.com/contact.php" target="_blank">Contact us now</a> | Need a Custom Designed Theme For your Blog or Website? Need a Custom Header Image? - <a href="http://www.acurax.com/contact.php" target="_blank">Contact us now</a>
</p>
</div>
</div>