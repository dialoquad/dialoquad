<?php
$quick_fix = ISSET($_GET['quickfix']);
$fix_applied = 0;
if($quick_fix != '')
{
if($quick_fix == 1)
{
$social_icon_array_order = array(0,1,2,3,4,5,6);  // Number Of Services
$social_icon_array_order = serialize($social_icon_array_order);
update_option('social_icon_array_order', $social_icon_array_order);
$fix_applied = 1;
}
}
$acx_installation_domain = $_SERVER['HTTP_HOST'];
$acx_installation_domain = str_replace("www.","",$acx_installation_domain);
$acx_installation_domain = str_replace(".","_",$acx_installation_domain);
if($acx_installation_domain == "") { $acx_installation_domain = "not_defined";}

if($_GET['page'] == "Acurax-Social-Icons-Expert-Support")
{
$acx_page_loaded = "_es";
} else
{
$acx_page_loaded = "";
}


echo "<div style='background: none repeat scroll 0% 0% white; height: 100%; display: inline-block; padding: 15px; width: 95%; margin-top: 15px; border-radius: 15px; min-height: 450px;'>";

if($fix_applied == 1)
{
echo "<div style='background: none repeat scroll 0% 0% lightgreen; width: 300px; text-align: center; margin-right: auto; margin-left: auto; padding: 7px 7px 5px; border-top-right-radius: 7px; border-top-left-radius: 7px; border-bottom: 2px solid green;'>Action Completed Successfully</div>";
}

echo "<h3 style='font-size: 20px; text-align: center; text-transform: capitalize; color: rgb(16, 177, 225);'>Do you need technical support services to get the best out of your WordPress site? </h3>";
echo "<p style='text-align:center;'><a href='http://www.acurax.com/services.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Acurax</a> offer a number of WordPress related services: From installing WordPress on your domain to offering support for existing WordPress sites.</p>";
echo "<ul id='acx_trouble_ul'><li><a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Troubleshoot WordPress site issues</a></li><li><a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Recommend & install plugins for improved WordPress performance</a></li><li><a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Create, modify, or customise, themes</a></li><li><a href='http://www.acurax.com/services/web-designing.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Explain errors and recommend solutions</a></li><li><a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Custom plugin development according to your needs</a></li><li><a href='http://www.acurax.com/services/wordpress-designing-experts.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Plugin Integration Support</a></li><li><a href='http://www.acurax.com/services.php?utm_source=fsmi&utm_medium=link&utm_campaign=trble" . $acx_page_loaded . "&ref=" . $acx_installation_domain . "' target='_blank'>Many more...</a></li></ul>";
echo "<p style='text-align: center; text-transform: capitalize; margin-top: 0px;'> We have extensive experience in WordPress troubleshooting, theme design & plugin development</p><hr />";
if($_GET['page'] == "Acurax-Social-Icons-Expert-Support")
{
$acx_installation_url = $_SERVER['HTTP_HOST'];
echo "<div id='acx_quick_request_form'>";
echo "<h2>We are dedicated to help you <br /> Submit Your Request Now!</h2>";
echo "<div class='acx_qr_label'>Name: <span>*</span></div><div class='acx_qr_field'><input type='input' name='acx_name' id='acx_name'></div>";
echo "<div class='acx_qr_label'>Email: <span>*</span></div><div class='acx_qr_field'><input type='input' name='acx_email' id='acx_email'></div>";
echo "<div class='acx_qr_label'>Phone: </div><div class='acx_qr_field'><input type='input' name='acx_phone' id='acx_phone'></div>";
echo "<div class='acx_qr_label'>Website URL: <span>*</span></div><div class='acx_qr_field'><input readonly type='input' name='acx_weburl' id='acx_weburl' value='" . $acx_installation_url . "'></div>";
echo "<div class='acx_qr_label'>Subject: <span>*</span></div><div class='acx_qr_field'><input type='input' name='acx_subject' id='acx_subject'></div>";
echo "<div class='acx_qr_label'>Question: <span>*</span></div><div class='acx_qr_field'><textarea name='acx_question' id='acx_question'></textarea></div>";
echo "<div class='acx_qr_label'></div><div class='acx_qr_field' style='width: 245px;'><div class='button' style='float:right;' onclick='acx_quick_request_submit();'>Submit Request</div></div>";
echo "</div>";
echo "<br /><br /><br /><br /><div style='font-size:12px;'>Its our pleasure to thank you for using our plugin and being with us, We always do our best to help you on your needs.If you like to hide this menu, you can do so at <a href='admin.php?page=Acurax-Social-Icons-Misc'>Misc</a> page which is under our plugin options.</div>";
?>
<script type="text/javascript">
var request_acx_form_status = 0;
function acx_quick_form_reset()
{
jQuery("#acx_subject").val('');
jQuery("#acx_question").val('');
}
acx_quick_form_reset();
function acx_quick_request_submit()
{
var acx_name = jQuery("#acx_name").val();
var acx_email = jQuery("#acx_email").val();
var acx_phone = jQuery("#acx_phone").val();
var acx_weburl = jQuery("#acx_weburl").val();
var acx_subject = jQuery("#acx_subject").val();
var acx_question = jQuery("#acx_question").val();
var order = '&action=acx_quick_request_submit&acx_name='+acx_name+'&acx_email='+acx_email+'&acx_phone='+acx_phone+'&acx_weburl='+acx_weburl+'&acx_subject='+acx_subject+'&acx_question='+acx_question; 
if(request_acx_form_status == 0)
{
request_acx_form_status = 1;
jQuery.post(ajaxurl, order, function(quick_request_acx_response)
{
if(quick_request_acx_response == 1)
{
alert('Your Request Submitted Successfully!');
acx_quick_form_reset();
request_acx_form_status = 0;
} else if(quick_request_acx_response == 2)
{
alert('Please Fill Mandatory Fields.');
request_acx_form_status = 0;
} else
{
alert('There was an error processing the request, Please try again.');
acx_quick_form_reset();
request_acx_form_status = 0;
}
});
} else
{
alert('A request is already in progress.');
}
}
</script>
<?php
} else
{
echo "<h2>Floating Social Media Icon Troubleshooting</h2>"; ?>
<p style="font-weight:bold;text-align:center;color:darkred;">IMPORTANT NOTE: Please do troubleshooting only if you got instructions from support or know what you are going to do.</p>

<p class="widefat" style="background: none repeat scroll 0% 0% menu; border-bottom: 2px dashed lavender; border-right: 2px dashed lavender; margin-bottom: 15px; margin-top: 8px; padding: 8px; width: 99%;">	<?php _e("1) Icon Selection Display Fix: " ); ?>
<?php _e("If you cant find any icons on the icon theme/style selection section, try this fix" ); ?>
<a href="admin.php?page=Acurax-Social-Icons-Troubleshooter&quickfix=1" class="acx_trouble_fixit">Click here to try this fix!</a>
</p>


<p style="text-align:center;">We will be adding more troubleshooting quick fix options according to requests</p>

<?php
}
echo "</div>";
?>