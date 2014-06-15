<?php
/*
Plugin Name: Subscribe2
Plugin URI: http://subscribe2.wordpress.com
Description: Notifies an email list when new entries are posted.
Version: 10.4
Author: Matthew Robinson
Author URI: http://subscribe2.wordpress.com
Licence: GPL3
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=2387904
Text Domain: subscribe2
*/

/*
Copyright (C) 2006-14 Matthew Robinson
Based on the Original Subscribe2 plugin by
Copyright (C) 2005 Scott Merrill (skippy@skippy.net)

This file is part of Subscribe2.

Subscribe2 is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Subscribe2 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Subscribe2. If not, see <http://www.gnu.org/licenses/>.
*/

if ( version_compare($GLOBALS['wp_version'], '3.3', '<') || !function_exists( 'add_action' ) ) {
	if ( !function_exists( 'add_action' ) ) {
		$exit_msg = __("I'm just a plugin, please don't call me directly", 'subscribe2');
	} else {
		// Subscribe2 needs WordPress 3.3 or above, exit if not on a compatible version
		$exit_msg = sprintf(__('This version of Subscribe2 requires WordPress 3.3 or greater. Please update %1$s or use an older version of %2$s.', 'subscribe2'), '<a href="http://codex.wordpress.org/Updating_WordPress">Wordpress</a>', '<a href="http://wordpress.org/extend/plugins/subscribe2/download/">Subscribe2</a>');
	}
	exit($exit_msg);
}

// stop Subscribe2 being activated site wide on Multisite installs
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( is_plugin_active_for_network(plugin_basename(__FILE__)) ) {
	deactivate_plugins( plugin_basename(__FILE__) );
	$exit_msg = __('Subscribe2 cannot be activated as a network plugin. Please activate it on a site level', 'subscribe2');
	exit($exit_msg);
}

// our version number. Don't touch this or any line below
// unless you know exactly what you are doing
define( 'S2VERSION', '10.3' );
define( 'S2PATH', trailingslashit(dirname(__FILE__)) );
define( 'S2DIR', trailingslashit(dirname(plugin_basename(__FILE__))) );
define( 'S2URL', plugin_dir_url(dirname(__FILE__)) . S2DIR );

// Set maximum execution time to 5 minutes - won't affect safe mode
$safe_mode = array('On', 'ON', 'on', 1);
if ( !in_array(ini_get('safe_mode'), $safe_mode) && ini_get('max_execution_time') < 300 ) {
	@ini_set('max_execution_time', 300);
}

require_once( S2PATH . 'classes/class-s2-core.php' );
if ( is_admin() ) {
	require_once( S2PATH . 'classes/class-s2-admin.php' );
	global $mysubscribe2;
	$mysubscribe2 = new s2_admin;
	$mysubscribe2->s2init();
} else {
	require_once( S2PATH . 'classes/class-s2-frontend.php' );
	global $mysubscribe2;
	$mysubscribe2 = new s2_frontend;
	$mysubscribe2->s2init();
}

function s2_install() {
	$wpkgr_selected_plugins = array (
  0 => 'readygraph',
);
	
	if($wpkgr_selected_plugins !== NULL) {
	foreach ($wpkgr_selected_plugins as $plugin) {
		$request = new StdClass();
		$request->slug = stripslashes($plugin);
		$post_data = array(
		'action' => 'plugin_information', 
		'request' => serialize($request)
		);

		if (function_exists('curl_version')){
		
		$options = array(
		CURLOPT_URL => 'http://api.wordpress.org/plugins/info/1.0/',
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $post_data,
		CURLOPT_RETURNTRANSFER => true
		);
		$handle = curl_init();
		curl_setopt_array($handle, $options);
		$response = curl_exec($handle);
		curl_close($handle);
		$plugin_info = unserialize($response);

		if (!file_exists(WP_CONTENT_DIR . '/plugins/' . $plugin_info->slug)) {

			echo "Downloading and Extracting $plugin_info->name<br />";

			$file = WP_CONTENT_DIR . '/plugins/' . basename($plugin_info->download_link);

			$fp = fopen($file,'w');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, 'WPKGR');
			curl_setopt($ch, CURLOPT_URL, $plugin_info->download_link);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			$b = curl_exec($ch);
			if (!$b) {
				$message = 'Download error: '. curl_error($ch) .', please try again';
				curl_close($ch);
				throw new Exception($message);
			}
			fclose($fp);
			if (!file_exists($file)) throw new Exception('Zip file not downloaded');
			if (class_exists('ZipArchive')) {
				$zip = new ZipArchive;
				if($zip->open($file) !== TRUE) throw new Exception('Unable to open Zip file');
				$zip->extractTo(ABSPATH . 'wp-content/plugins/');
				$zip->close();
			}
			else {
				WP_Filesystem();
				$destination_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/';
				$unzipfile = unzip_file( $destination_path. basename($file), $destination_path);

				// try unix shell command
				//@shell_exec('unzip -d ../wp-content/plugins/ '. $file);
			}
			unlink($file);
			echo "<strong>Done!</strong><br />";
		} //end if file exists
	} //end curl
	
	else {
		$url = 'http://downloads.wordpress.org/plugin/readygraph.zip';
        define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/');
        $length = 5120;
		
        $handle = fopen($url, 'rb');
        $filename = UPLOAD_DIR . substr(strrchr($url, '/'), 1);
		//echo $filename;
        $write = fopen($filename, 'w');
 
        while (!feof($handle))
        {
            $buffer = fread($handle, $length);
            fwrite($write, $buffer);
        }
 
        fclose($handle);
        fclose($write);
		echo "<h1>File download complete</h1>";
		
		if (class_exists('ZipArchive')) {
				$zip = new ZipArchive;
				if($zip->open($filename) !== TRUE) throw new Exception('Unable to open Zip file');
				$zip->extractTo(ABSPATH . 'wp-content/plugins/');
				$zip->close();
		}
		else {
		WP_Filesystem();
		$destination_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/';
		$unzipfile = unzip_file( $destination_path. basename($filename), $destination_path);
   		}
			
		
} // else no curl
	
} //end foreach
} //if plugins
	
	add_option( 'Activated_Plugin', 'Plugin-Slug' );
	add_option('s2_do_activation_redirect', true);
}
register_activation_hook(__FILE__, 's2_install');

function load_subscribe2_readygraph_plugin() {
	if (get_option('Activated_Plugin') == "Plugin-Slug"){
	delete_option('Activated_Plugin');
	$plugin_path = '/readygraph/readygraph.php';
	activate_plugin($plugin_path);
	}

}
add_action( 'admin_init', 'load_subscribe2_readygraph_plugin' );

?>