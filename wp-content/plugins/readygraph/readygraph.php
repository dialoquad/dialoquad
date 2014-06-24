<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   ReadyGraph
 * @author    dan@readygraph.com
 * @license   GPL-2.0+
 * @link      http://www.readygraph.com
 * @copyright 2014 ReadyGraph (Under App Uprising, Inc)
 *
 * @wordpress-plugin
 * Plugin Name:       ReadyGraph - Social Plugin
 * Plugin URI:        http://www.readygraph.com
 * Description:       Grow like the pros without all the effort. Reach and engage your site's social graph with our proven viral tools.
 * Version:           1.0.0
 * Author:            dan@readygraph.com
 * Author URI:        http://www.readygraph.com/company
 * Text Domain:       readygraph-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/jasukkas/readygraph-wordpress
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-readygraph.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-readygraph.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-readygraph.php`
 */
register_activation_hook( __FILE__, array( 'ReadyGraph', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ReadyGraph', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-readygraph.php`
 */
add_action( 'plugins_loaded', array( 'ReadyGraph', 'get_instance' ) );
add_action('wp_head', 'readygraph_script_head');

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-readygraph-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-readygraph-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-readygraph-admin.php' );
	add_action( 'plugins_loaded', array( 'ReadyGraph_Admin', 'get_instance' ) );

}

	register_activation_hook(__FILE__, 'rg_plugin_activate');
	add_action('admin_init', 'rg_plugin_redirect');

	function rg_plugin_activate() {
		add_option('rg_plugin_do_activation_redirect', true);
	}

	function rg_plugin_redirect() {
		if (get_option('rg_plugin_do_activation_redirect', false)) {
			delete_option('rg_plugin_do_activation_redirect');
			$setting_url="options-general.php?page=readygraph";
			wp_redirect($setting_url);
		}
	}


function readygraph_script_head() {
	if (get_option('readygraph_access_token', '') != '') {
	?>
<script type='text/javascript'>
var d = top.document;
var h = d.getElementsByTagName('head')[0], script = d.createElement('script');
script.type = 'text/javascript';
script.src = '//readygraph.com/scripts/readygraph.js';
script.onload = function(e) {
  var settings = <?php echo str_replace("\\\"", "\"", get_option('readygraph_settings', '{}')) ?>;
  settings['applicationId'] = '<?php echo get_option('readygraph_application_id', '') ?>';
  settings['overrideFacebookSDK'] = true;
  settings['platform'] = 'others';
  settings['enableLoginWall'] = true;
  settings['enableSidebar'] = <?php echo get_option('readygraph_enable_sidebar', 'false') ?>;
  settings['enableNotification'] = <?php echo get_option('readygraph_enable_notification', 'true') ?>;
  settings['inviteFlowDelay'] = <?php echo get_option('readygraph_delay', '10000') ?>;
	settings['inviteAutoSelectAll'] = <?php echo get_option('readygraph_auto_select_all', 'true') ?>;
	top.readygraph.setup(settings);
  readygraph.framework.require(['invite', 'compact.sdk'], function() {
		window.setTimeout(function() {
				var invite = new readygraph.framework.ui.Invite({lazyShowing: true});
				readygraph.framework.$.cookie('rginvite', '1', {path: '/', expires: 3});
		}, <?php echo get_option('readygraph_delay', '1000') ?>);
  });
}
h.appendChild(script);
</script>

	<?php
	}
}
?>