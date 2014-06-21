<?php
/**
 * @package Blue Hat CDN
 * @version 2.9.4
 * @copyright (C) Copyright 2006-2014 Blue Hat Network, BlueHatNetwork.com. All rights reserved.
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.txt

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('BHN_PLUGIN_ADMIN_ROOT')) define('BHN_PLUGIN_ADMIN_ROOT', dirname(__FILE__));

if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
if(!class_exists('BlueHatCDNModelBlueHatCDN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdn.php';
if(!class_exists('BlueHatCDNController')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'controller.php';

if(BlueHatNetworkFactory::isWordPress() && !function_exists('blueHatTurboOptimizeFinalOutput'))
{
	function blueHatTurboOptimizeFinalOutput($buffer)
	{
		$blueHatTurboObj = new BlueHatCDNModelBlueHatCDN($buffer);
		
		if($blueHatTurboObj->hasOptimization())
		{
			return $blueHatTurboObj->optimize();
		}
		else
		{
			return $buffer;
		}
	}
	
	function blueHatTurboInitialize()
	{
		load_plugin_textdomain('blue-hat-cdn', false, dirname(plugin_basename(BHN_PLUGIN_ADMIN_ROOT_FILE)).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR);
		
		if((string)get_option('blue_hat_turbo_db_version') != (string)BlueHatCDNModelBlueHatCDN::BHN_VERSION) blueHatTurboDBInstall();
		
		ob_start('blueHatTurboOptimizeFinalOutput');
	}
	
	function blueHatTurboDBInstall()
	{
		if(!class_exists('BlueHatTurboDatabaseInstaller')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'install.bluehatcdn.php';
		
		BlueHatTurboDatabaseInstaller::setupDB();
		
		update_option('blue_hat_turbo_db_version', (string)BlueHatCDNModelBlueHatCDN::BHN_VERSION);
	}
	
	function blueHatTurboDisplayMainPage()
	{
		require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'bluehatcdn'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'default.php';
	}
	
	function blueHatTurboPrepHead($currentPage)
	{
		switch($currentPage)
		{
			case 'settings_page_blue-hat-turbo-mainpage':
				wp_enqueue_script('bhn-jquery', plugins_url('/assets/js/jquery-1.11.0.min.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn-jquery-migrate', plugins_url('/assets/js/jquery-migrate-1.2.1.min.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn', plugins_url('/assets/js/bhn.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn-jquery-ui', plugins_url('/assets/js/jquery-ui-1.10.4.custom.min.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn-jquery-jqgrid', plugins_url('/assets/jquery.jqGrid-4.6.0/js/jquery.jqGrid.min.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn-jquery-jqgrid-lang', plugins_url('/assets/jquery.jqGrid-4.6.0/js/i18n/grid.locale-'.substr(get_bloginfo('language'), 0, 2).'.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_script('bhn-jquery-jgrowl', plugins_url('/assets/jGrowl-master/jquery.jgrowl.min.js', BHN_PLUGIN_ADMIN_ROOT_FILE));
				
				wp_enqueue_style('bhn-jquery-ui', plugins_url('/assets/css/bluehatturbo_theme/jquery-ui-1.10.4.custom.min.css', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_style('bhn-jqgrid', plugins_url('/assets/jquery.jqGrid-4.6.0/css/ui.jqgrid.css', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_style('bhn-jquery-jgrowl', plugins_url('/assets/jGrowl-master/jquery.jgrowl.min.css', BHN_PLUGIN_ADMIN_ROOT_FILE));
				wp_enqueue_style('bhn-styles', plugins_url('/assets/css/styles.css', BHN_PLUGIN_ADMIN_ROOT_FILE));
				
				break;
		}
	}
	
	function blueHatTurboAdminMenu()
	{
		add_options_page( 'Blue Hat Turbo', 'Blue Hat Turbo', 'manage_options', 'blue-hat-turbo-mainpage', 'blueHatTurboDisplayMainPage');
		//add_menu_page('Blue Hat Turbo', 'Blue Hat Turbo', 'manage_options', 'blue-hat-turbo-mainpage', 'blueHatTurboDisplayMainPage', plugins_url('assets/images/icons/bluehatcdn-icon-16.png', BHN_PLUGIN_ADMIN_ROOT_FILE));
	}
	
	register_activation_hook(BHN_PLUGIN_ADMIN_ROOT_FILE, 'blueHatTurboDBInstall');
	
	add_action('plugins_loaded', 'blueHatTurboInitialize', 0);
	add_action('admin_menu', 'blueHatTurboAdminMenu');
	add_action('admin_enqueue_scripts', 'blueHatTurboPrepHead');
	
	add_action('wp_ajax_bhn_save_settings', array('BlueHatTurboController', 'save_settings'));
	add_action('wp_ajax_bhn_scan_sync_files', array('BlueHatTurboController', 'scan_sync_files'));
	add_action('wp_ajax_bhn_get_scan_sync_status', array('BlueHatTurboController', 'get_scan_sync_status'));
	add_action('wp_ajax_bhn_get_files', array('BlueHatTurboController', 'get_files'));
	add_action('wp_ajax_bhn_clear_index_data', array('BlueHatTurboController', 'clear_index_data'));
	add_action('wp_ajax_bhn_delete_selected_files_from_cdn', array('BlueHatTurboController', 'delete_selected_files_from_cdn'));
	add_action('wp_ajax_bhn_stop_scan_sync_process', array('BlueHatTurboController', 'stop_scan_sync_process'));
	add_action('wp_ajax_bhn_mesh_hash_compile', array('BlueHatTurboController', 'triggerMeshHashCompilerEvent'));
	add_action('wp_ajax_nopriv_bhn_mesh_hash_compile', array('BlueHatTurboController', 'triggerMeshHashCompilerEvent'));
}
elseif(BlueHatNetworkFactory::isJoomla())
{
	$controller	= new BlueHatCDNController();

	$controller->execute(JRequest::getCmd('task'));
	$controller->redirect();
}