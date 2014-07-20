<?php
/*
Plugin Name: Stop Pinging Yourself for WordPress
Plugin URI: http://thisismyurl.com/plugins/stop-pinging-yourself-for-wordpress/
Description: Stops a WordPress blog from pinging itself with pingbacks
Author: christopherross
Version: 1.0.0
Author URI: http://thisismyurl.com/
*/


/**
 * Stop Pinging Yourself for WordPress core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/stop-pinging-yourself-for-wordpress/
 *
 * @package 		Stop Pinging Yourself for WordPress
 * @copyright		Copyright (c) 2008, Chrsitopher Ross
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		Stop Pinging Yourself for WordPress 1.0
 */

function thisismyurl_no_self_ping( $links ) {

	foreach ( $links as $link_count => $link ) {
		if ( 0 === strpos( $link, get_option( 'home' ) ) )
			unset( $links[$link_count] );
	}

}
add_action( 'pre_ping', 'thisismyurl_no_self_ping' );
