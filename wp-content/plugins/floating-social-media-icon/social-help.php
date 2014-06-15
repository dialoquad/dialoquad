<div id="acx_help_page"><div style='background: none repeat scroll 0% 0% white; height: 100%; display: inline-block; padding: 8px; margin-top: 5px; border-radius: 15px; min-height: 450px; width: 98%;'>
<?php
$acx_si_fsmi_hide_advert = get_option('acx_si_fsmi_hide_advert');
if($acx_si_fsmi_hide_advert == "no")
{ ?>
<div id="acx_fsmi_premium">
<a style="margin: 8px 0px 0px 10px; float: left; font-size: 16px; font-weight: bold;" href="http://www.acurax.com/products/floating-social-media-icon-plugin-wordpress/?utm_source=plugin&utm_medium=highlight&utm_campaign=fsmi" target="_blank">Fully Featured - Premium Floating Social Media Icon</a>
<a style="margin: -14px 0px 0px 10px; float: left;" href="http://clients.acurax.com/floating-socialmedia.php?utm_source=plugin&utm_medium=highlight_yellow&utm_campaign=fsmi" target="_blank"><img src="<?php echo plugins_url('images/yellow.png', __FILE__);?>"></a>
</div> <!-- acx_fsmi_premium -->
<?php } ?>
<h2 style="text-align:center;">Floating Social Media Icon - Wordpress Plugin - Help/Support</h2>
<p style="text-align:center;">Thank you for using Floating Social Media Icon Plugin For Your Wordpress Social Media Profile Linking Need.</p>
<h3 style="text-align:center;"><a href="http://clients.acurax.com/link.php?id=8" target="_blank" class="button">Click here to open the FAQ and Help Page</a></h3>
<?php
if($acx_si_fsmi_hide_advert == "no")
{
socialicons_comparison(1);
acurax_optin(); 
}
?>
</div> <!-- acx_help_page -->
</div> 