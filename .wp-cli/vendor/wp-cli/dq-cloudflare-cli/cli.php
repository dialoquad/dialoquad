<?php

/**
 * Manages the WP Super Cache plugin
 */
class WPCloudFlare_Command extends WP_CLI_Command {

	/**
	 * Clear something from the cache.
	 *
	 * @synopsis <mode> [--permalink=<permalink>]
	 */
	
	function dev( $args = array(), $assoc_args = array() ){
    	global $cloudflare_zone_name, $cloudflare_api_key, $cloudflare_api_email;
		
    	load_cloudflare_keys();
		
		if( $args[0] == "on" ){
			set_dev_mode(esc_sql($cloudflare_api_key), esc_sql($cloudflare_api_email), $cloudflare_zone_name, 1);
			WP_CLI::success( 'CloudFlare Development Mode On' );
		}else if( $args[0] == "off" ){

			$result = set_dev_mode(esc_sql($cloudflare_api_key), esc_sql($cloudflare_api_email), $cloudflare_zone_name, 0);
			WP_CLI::success( 'CloudFlare Development Mode Off' );
		}else if( $args[0] == "status" ){
			$dev_mode = get_dev_mode_status($cloudflare_api_key, $cloudflare_api_email, $cloudflare_zone_name);
			switch( $dev_mode  ){
				case "on":
					WP_CLI::success( 'CloudFlare Development Mode is currently On' );
					break;
				case "off":
					WP_CLI::success( 'CloudFlare Development Mode is currently Off' );
					break;
				default:
					WP_CLI::error( 'No such command for status!!' );
					break;
			}
		}else{
			WP_CLI::error( 'No such command for cloudflare cli!!' );
		}
	}
}

WP_CLI::add_command( 'cloudflare', 'WPCloudFlare_Command' );

