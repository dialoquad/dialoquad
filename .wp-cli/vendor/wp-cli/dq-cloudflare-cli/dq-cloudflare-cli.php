<?php
/*
Plugin Name: DQ CloudFlare CLI
Version: 1.0
Description: A CLI interface for the WP Super Cache plugin
Author: MegaDog
Author URI: http://github.com/wp-cli
Plugin URI: http://github.com/wp-cli/wp-super-cache-cli
License: MIT
*/

function dq_cloudflare_cli_init() {
	if ( defined('WP_CLI') && WP_CLI ) {
		include dirname(__FILE__) . '/cli.php';
	}
}
add_action( 'plugins_loaded', 'dq_cloudflare_cli_init' );

