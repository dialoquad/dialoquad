<?php

/*
 Plugin Name: Add Linked Images To Gallery
 Plugin URI:  http://www.bbqiguana.com/wordpress-plugins/add-linked-images-to-gallery/
 Version: 1.4
 Description: Examines the text of a post and makes local copies of all the images linked though IMG tags, adding them as gallery attachments on the post itself.
 Author: Randy Hunt
 Author URI: http://www.bbqiguana.com/
 */

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$externimg_count = 0;

function externimg_find_imgs ($post_id) {

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	
	if (wp_is_post_revision($post_id)) return;
	
	if ($c=get_option('externimg_catlist')) {
		$catfound = false;
		$catlist = get_the_category($post_id);
		foreach ($catlist as $category) {
			if (in_array($category->cat_ID, explode(',', $c)))
				$catfound = true;
		}
		if (!$catfound) return;
	}
	$post = get_post($post_id);
	$a = get_option('externimg_authlist');
	if($a && !in_array($post->post_author, explode(',', $a))) return;
	
	$l = get_option('externimg_replacesrc');
	$k = get_option('externimg_custtagname');
	$processed = get_post_custom_values($k, $post_id);
	$replaced = false;
	$content = $post->post_content;
	$imgs = externimg_get_img_tags($post_id);
	global $externimg_count;
	
	for($i=0; $i<count($imgs); $i++) {
		if (!$processed || !in_array($imgs[$i], $processed)) {
			
			$parseurl = parse_url($imgs[$i]);
			$pathname = $parseurl['path'];
			$filename = substr(strrchr($pathname, '/'), 1);
			if (preg_match ('/(\.php|\.aspx?)$/', $filename) ) $filename .= '.jpg';
			$imgid   = externimg_sideload($imgs[$i], $filename, $post_id);
			$imgpath = wp_get_attachment_url($imgid);
			if (!is_wp_error($imgpath)) {
				if ($l=='custtag') {
					add_post_meta($post_id, $k, $imgs[$i], false);
				} else {
					$trans = preg_quote($imgs[$i], "/");
					$content = preg_replace('/(<img[^>]* src=[\'"]?)('.$trans.')/', '$1'.$imgpath, $content);
					$replaced = true;
				}
				$processed[] = $imgs[i];
				$externimg_count++;
			}
		}
	}
	if ($replaced) {
		$upd = array();
		$upd['ID'] = $post_id;
		$upd['post_content'] = $content;
		wp_update_post($upd);
	}
}

function externimg_getext ($file) {
	if (function_exists('mime_content_type'))
		$mime = mime_content_type($file);
	else return '';
	switch($mime) {
		case 'image/jpg':
		case 'image/jpeg':
			return '.jpg';
			break;
		case 'image/gif':
			return '.gif';
			break;
		case 'image/png':
			return '.png';
			break;
	}
	return '';
}

function externimg_sideload ($file, $url, $post_id) {
	if(!empty($file)){
		$file_array['tmp_name'] = download_url($file);
		if(is_wp_error($file_array['tmp_name'])) return;
		$file_array['name'] = basename($file);
		//$desc = @$desc;
		$desc = externimg_getext($file);

		$pathparts = pathinfo($file_array['tmp_name']);
		if (''==$pathparts['extension']) {
			$ext = externimg_getext($file_array['tmp_name']);
			rename($file_array['tmp_name'], $file_array['tmp_name'] . $ext);
			$file_array['name'] = basename($file_array['tmp_name']) . $ext;
			$file_array['tmp_name'] .= $ext;
		}

		$id = media_handle_sideload($file_array, $post_id, $desc);
		$src = $id;

		if(is_wp_error($id)) {
			@unlink($file_array['tmp_name']);
			return $id;
		}
	}
	if (!empty($src)) return $src;
	else return false;
}

function externimg_get_img_tags ($post_id) {
	$post = get_post($post_id);
	$w = get_option('externimg_whichimgs');
	$s = get_option('siteurl');
	
	$result = array();
	//preg_match_all('/<img[^>]+src=\\\\?[\'"]?([^>\\\"\' ]+)/', $content, $matches);
	preg_match_all('/<img[^>]* src=[\'"]?([^>\'" ]+)/', $post->post_content, $matches);
	for ($i=0; $i<count($matches[0]); $i++) {
		$uri = $matches[1][$i];
		
		//only check FQDNs
		if (preg_match('/^http:\/\//', $uri)) {
			//make sure it's not external
			if ($s != substr($uri, 0, strlen($s)) ) {
				//only match Flickr images?
				if($w == 'All' || 
				   ($w == 'Flickr' && preg_match('/^http:\/\/[a-z0-9]+\.static\.flickr\.com\//', $uri)) ) {
					$result[] = $matches[1][$i];
				}
			}
		}
	}
	return $result;
}

function externimg_savefile ($file, $url, $post_id) {
	$time = null;
	
	$uploads = wp_upload_dir($time);
	$filename = wp_unique_filename( $uploads['path'], $url, $unique_filename_callback );
	$savepath = $uploads['path'] . "/$filename";
	
	if($fp = fopen($savepath, 'w')) {
		fwrite($fp, $file);
		fclose($fp);
	}
	
	$wp_filetype = wp_check_filetype( $savepath, $mimes );
	$type = $wp_filetype['type'];
	$title = $filename;
	$content = '';
	
	// Construct the attachment array
	$attachment = array(
						'post_mime_type' => $type,
						'guid' => $uploads['url'] . "/$filename",
						'post_parent' => $post_id,
						'post_title' => $title,
						'post_content' => $content
						);
	
	// Save the data
	$id = wp_insert_attachment($attachment, $savepath, $post_id);
	if ( !is_wp_error($id) ) {
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	} else return '';
	return $uploads['url'] . "/$filename";
}


//modified from code found at http://www.bin-co.com/php/scripts/load/
function externimg_loadimage ($url) {
	
	$url_parts = parse_url($url);
	$ch = false;
	$info = array(//Currently only supported by curl.
				  'http_code'    => 200
				  );
	$response = '';
	
	$send_header = array(
						 'Accept' => 'text/*',
						 'User-Agent' => 'Attach-Linked-Images WordPress Plugin (http://www.bbqiguana.com/)'
						 );
	
	
	
	///////////////////////////// Curl /////////////////////////////////////
	//If curl is available, use curl to get the data.
	if(function_exists("curl_init")) {  //$options['use'] == 'fsocketopen'))) { //Don't use curl if it is specifically stated to use fsocketopen in the options
		
		$page = $url;
		
		$ch = curl_init($url_parts['host']);
		
		curl_setopt($ch, CURLOPT_URL, $page) or die("Invalid cURL Handle Resouce");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Just return the data - not print the whole thing.
		curl_setopt($ch, CURLOPT_HEADER, true); //We need the headers
		curl_setopt($ch, CURLOPT_NOBODY, false); //Yes, get the body.
		
		//Set the headers our spiders sends
		curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); //The Name of the UserAgent we will be using ;)
		$custom_headers = array("Accept: " . $send_header['Accept'] );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		
		@curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/binget-cookie.txt"); //If ever needed...
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		@curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if(isset($url_parts['user']) and isset($url_parts['pass'])) {
			$custom_headers = array("Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		}
		
		$response = curl_exec($ch);
		$info = curl_getinfo($ch); //Some information on the fetch
		if('http://l.yimg.com/g/images/photo_unavailable.gif'==$info['url']) $body = '';
		curl_close($ch);  //If the session option is not set, close the session.
		
		//////////////////////////////////////////// FSockOpen //////////////////////////////
	} else { //If there is no curl, use fsocketopen - but keep in mind that most advanced features will be lost with this approch.
		if(isset($url_parts['query'])) {
			$page = $url_parts['path'] . '?' . $url_parts['query'];
		} else {
			$page = $url_parts['path'];
		}
		
		if(!isset($url_parts['port'])) $url_parts['port'] = 80;
		$fp = fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, 30);
		if ($fp) {
			$out = "GET $page HTTP/1.0\r\n"; //HTTP/1.0 is much easier to handle than HTTP/1.1
			$out .= "Host: $url_parts[host]\r\n";
			$out .= "Accept: $send_header[Accept]\r\n";
			$out .= "User-Agent: {$send_header['User-Agent']}\r\n";
			$out .= "Connection: Close\r\n";
			
			//HTTP Basic Authorization support
			if(isset($url_parts['user']) and isset($url_parts['pass'])) {
				$out .= "Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']) . "\r\n";
			}
			$out .= "\r\n";
			
			fwrite($fp, $out);
			while (!feof($fp)) {
				$response .= fgets($fp, 128);
			}
			fclose($fp);
		}
	}
	
	//Get the headers in an associative array
	$headers = array();
	
	if($info['http_code'] == 404 || $info['http_code'] == 400) {
		$body = '';
		$headers['Status'] = 404;
	} else {
		//Seperate header and content
		$header_text = substr($response, 0, $info['header_size']);
		$body = substr($response, $info['header_size']);
		
		foreach(explode("\n",$header_text) as $line) {
			$parts = explode(": ",$line);
			if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
		}
	}
	
	//if($options['return_info']) return array('body' => $body, 'info' => $info, 'curl_handle'=>$ch);
	return $body;
}

function externimg_backcatalog () {
	global $externimg_count;
	$count = 0;
	$pp = get_posts( array( 'numberposts'=>-1 ) );
	foreach ($pp as $p) {
		try {
			//echo '<p>' . $p->ID . ': ' . $p->title . '</p>';
			echo '<p>[' . $p->ID . '] ' . $p->post_title . ': ';
			externimg_find_imgs($p->ID);
			echo '<em>' . $externimg_count . ' images processed.</em></p>';
		} catch (Exception $e) {
			echo '<em>an error occurred</em>.</p>';
		}
		$count += $externimg_count;
	}
}

function externimg_getauthors() {
	global $wpdb;
	$query = "SELECT $wpdb->users.* FROM $wpdb->users ORDER BY display_name;";
	$authors = $wpdb->get_results($query);
	return $authors;
}

function externimg_menu () {
	if ( function_exists('add_options_page') ) {
		add_options_page('Linked IMGs to Gallery', 'Linked IMGs', 8, 'externimg', 'externimg_options');
	}
}

function externimg_init () {
	//$plugin_dir = basename(dirname(__FILE__));
	//load_plugin_textdomain('externimg', 'wp-content/plugins/'.$plugin_dir, 'externimg');
	
	register_setting('externimg', 'externimg_whichimgs');
	register_setting('externimg', 'externimg_replacesrc');
	register_setting('externimg', 'externimg_custtagname');
	register_setting('externimg', 'externimg_cats');
	register_setting('externimg', 'externimg_auths');
	register_setting('externimg', 'externimg_catlist');
	register_setting('externimg', 'externimg_authlist');
}

function externimg_install () {
	//add default options
	$whichimgs   = get_option('externimg_whichimgs');
	$replacesrc  = get_option('externimg_replacesrc');
	$custtagname = get_option('externimg_custtagname');
	$catlist     = get_option('externimg_catlist');
	$authlist    = get_option('externimg_authlist');
	
	if(!$whichimgs)   update_option('externimg_whichimgs',   'All');
	if(!$replacesrc)  update_option('externimg_replacesrc',  'replace');
	if(!$custtagname) update_option('externimg_custtagname', 'externimg');
	if(!$catlist)     update_option('externimg_catlist',     '');
	if(!$authlist)    update_option('externimg_authlist',    '');
}

function externimg_options () {
	$_cats  = '';
	$_auths = '';
	echo '<div class="wrap">';
	echo '<h2>Linked IMGs to Gallery Attachments</h2>';

	if ($_POST['action']=='backcatalog') {
		externimg_backcatalog();
		echo '<div id="message" class="updated fade" style="background-color:rgb(255,251,204);"><p>Finished processing past posts!</p></div>';
	}

	if ($_POST['action']=='update') {
		//check_admin_referer('externimg_update-action');
		//			$_cats  = implode(',', $_POST['externimg_cats']);
		//			$_auths = implode(',', $_POST['externimg_auths']);
		update_option('externimg_whichimgs',   $_POST['externimg_whichimgs'] );
		update_option('externimg_replacesrc',  $_POST['externimg_replacesrc'] );
		update_option('externimg_custtagname', $_POST['externimg_custtagname'] );
		if($_POST['externimg_catlist'])  update_option('externimg_catlist',  $_cats );
		update_option('externimg_catlist',  ($_POST['externimg_catlist'] ) ? implode(',', $_POST['externimg_cats'] ) : '');
		update_option('externimg_authlist', ($_POST['externimg_authlist']) ? implode(',', $_POST['externimg_auths']) : '');
		//			if($_POST['externimg_authlist']) update_option('externimg_authlist', $_auths);
		//			update_option('externimg_catlist',     $_POST['externimg_catlist'] );
		//			update_option('externimg_authlist',    $_POST['externimg_authlist'] );
		echo '<div id="message" class="updated fade" style="background-color:rgb(255,251,204);"><p>Settings updated.</p></div>';
	}
	echo '<big>Options</big>';
	echo '<form name="externimg-options" method="post" action="">';
	settings_fields('externimg');
	echo '<table class="form-table"><tbody>';
	echo '<tr valign="top"><th scope="row"><strong>Which external IMG links to process:</strong></th>';
	echo '<td><label for="myradio1"><input id="myradio1" type="radio" name="externimg_whichimgs" value="All" ' . (get_option('externimg_whichimgs')!='Flickr'?'checked="checked"':'') . '/> All images</label><br/>';
	echo '<label for="myradio2"><input id="myradio2" type="radio" name="externimg_whichimgs" value="Flickr" ' . (get_option('externimg_whichimgs')=='Flickr'?'checked="checked"':'') . ' /> Only Flickr images</label><br/>';
	echo '<p>By default, all external images are processed.  This can be set to only apply to Flickr.</p>';
	echo '</td></tr>';
	echo '<tr valign="top"><th scope="row"><strong>What to do with the images:</th>';
	echo '<td><label for="myradio3"><input id="myradio3" type="radio" name="externimg_replacesrc" value="replace" ' . (get_option('externimg_replacesrc')!='custtag'?'checked="checked"':'') . ' /> Replace SRC attribute with local copy</label><br/>';
	echo '<label for="myradio4"><input id="myradio4" type="radio" name="externimg_replacesrc" value="custtag" ' . (get_option('externimg_replacesrc')=='custtag'?'checked="checked"':'') . ' /> Use custom tag:</label> ';
	echo '<input type="text" size="20" name="externimg_custtagname" value="' . get_option('externimg_custtagname') . '" /><br/>';
	echo '<p>Replacing the SRC attribute will convert the external IMG link to a local link, pointed at the local copy downloaded by this plugin. If the SRC attribute is not replaced, the plugin needs to mark the IMG as having been processed somehow, so this is done by tracking processed images in custom_tag values.  You can change the name of the custom tag.</p></td></tr>';
	//	echo '<tr valign="top"><th scope="row">This site name:</th><td>' . get_option('siteurl') . '</td></tr>';
	
	echo '<tr align="top"><th scope="row"><strong>Apply to these categories:</strong></th>';
	echo '<td><label for="myradio5"><input type="radio" id="myradio5" name="externimg_catlist" value="" ' . (get_option('externimg_catlist')==''?'checked="checked"':'') . ' /> All categories</label><br/>';
	echo '<label for="myradio6"><input type="radio" id="myradio6" name="externimg_catlist" value="Y" ' . (get_option('externimg_catlist')!=''?'checked="checked"':'') . ' /> Selected categories</label><br/>';
	
	$_cats = explode(',', get_option('externimg_catlist'));
	$chcount = 0;
	$cats = get_categories();
	foreach ($cats as $cat) {
		$chcount++;
		echo '<label for="mycheck'.$chcount.'"><input type="checkbox" id="mycheck'.$chcount.'" name="externimg_cats[]" value="' . $cat->cat_ID . '" '.(in_array($cat->cat_ID, $_cats)?'checked="checked"':'').' /> ' . $cat->cat_name . '</label><br/>';
	}
	echo '</td></tr>';
	echo '<tr align="top"><th scope="row"><strong>Apply to these authors:</strong></th>';
	echo '<td><label for="myradio7"><input type="radio" id="myradio7" name="externimg_authlist" value="" ' . (get_option('externimg_authlist')==''?'checked="checked"':'') . ' /> All authors</label><br/>';
	echo '<label for="myradio8"><input type="radio" id="myradio8" name="externimg_authlist" value="Y" ' . (get_option('externimg_authlist')!=''?'checked="checked"':'') . ' /> Selected authors</label><br/>';
	
	$_auths = explode(',', get_option('externimg_authlist'));
	$auths = externimg_getauthors();
	foreach ($auths as $auth) {
		$chcount++;
		echo '<label for="mycheck'.$chcount.'"><input type="checkbox" id="mycheck'.$chcount.'" name="externimg_auths[]" value="' . $auth->ID . '" '.(in_array($auth->ID, $_auths)?'checked="checked"':'').'/> ' . $auth->display_name . '</label><br/>';
	}
	echo '</td></tr>';
	
	echo '</tbody></table>';
	echo '<div class="submit">';
	//echo '<input type="hidden" name="externimg_update" value="action" />';
	echo '<input type="submit" name="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</div>';
	echo '</form>';

	echo '<form name="externimg-backcatalog" method="post" action="">';
	echo '<div class="wrap">';
	echo '<big>Process all posts</big>';
	echo '<p>Use this function to apply the plugin to all previous posts. The settings specified above will still be respected.</p>';
	echo '<p><em>Please note that this can take a long time for sites with a lot of posts.</em></p>';
	echo '<div class="submit">';
	echo '<input type="hidden" name="action" value="backcatalog">';
	echo '<input type="submit" class="button-primary" value="' . __('Process') . '" />';
	echo '</div>';
	echo '<p>&nbsp;</p>';
	echo '</div>';
	echo '</form>';

	echo '<div class="wrap">';
	echo '<big>Donate</big>';
	echo '<p>If you like this plugin consider donating a small amount to the author using PayPal to support further plugin development.</p>';
	echo '<div align="center"><form name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="business" value="bbqiguana@gmail.com"><input type="hidden" name="item_name" value="Donations for WP-Externimage Plugin"><input type="hidden" name="currency_code" value="USD"><input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!"></form></div>';
	echo '<p>If you think donating money is somehow impersonal you could also choose items from my <a href="http://www.amazon.com/registry/wishlist/18LMHOMRM49P8/ref=cm_wl_act_vv">Amazon.com wishlist</a>.</p>';
	echo '</div>';
	echo '';
	echo '</div>';
}

if ( is_admin() ) { // admin actions
	add_action('admin_menu', 'externimg_menu');
	add_action('admin_init', 'externimg_init');
}
register_activation_hook(__FILE__, 'externimg_install');

add_action('save_post', 'externimg_find_imgs');

?>
