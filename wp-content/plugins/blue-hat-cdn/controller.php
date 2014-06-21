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

if(!class_exists('BlueHatTurboController')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'controller_parent.php';

if(BlueHatNetworkFactory::isJoomla())
{
	if(class_exists('JControllerLegacy'))
	{
		class BlueHatCDNController extends JControllerLegacy
		{
			function display()
			{
				BlueHatTurboController::display($this);
			}

			function bhn_save_settings()
			{
				BlueHatTurboController::save_settings();
			}

			function bhn_scan_sync_files()
			{
				BlueHatTurboController::scan_sync_files();
			}

			function bhn_get_scan_sync_status()
			{
				BlueHatTurboController::get_scan_sync_status();
			}

			function bhn_get_files()
			{
				BlueHatTurboController::get_files();
			}

			function bhn_clear_index_data()
			{
				BlueHatTurboController::clear_index_data();
			}

			function bhn_delete_selected_files_from_cdn()
			{
				BlueHatTurboController::delete_selected_files_from_cdn();
			}

			function bhn_stop_scan_sync_process()
			{
				BlueHatTurboController::stop_scan_sync_process();
			}
		}
	}
	else
	{
		jimport('joomla.application.component.controller');

		class BlueHatCDNController extends JController
		{
			function display()
			{
				BlueHatTurboController::display($this);
			}

			function bhn_save_settings()
			{
				BlueHatTurboController::save_settings();
			}

			function bhn_scan_sync_files()
			{
				BlueHatTurboController::scan_sync_files();
			}
			
			function bhn_get_scan_sync_status()
			{
				BlueHatTurboController::get_scan_sync_status();
			}
			
			function bhn_get_files()
			{
				BlueHatTurboController::get_files();
			}

			function bhn_clear_index_data()
			{
				BlueHatTurboController::clear_index_data();
			}

			function bhn_delete_selected_files_from_cdn()
			{
				BlueHatTurboController::delete_selected_files_from_cdn();
			}

			function bhn_stop_scan_sync_process()
			{
				BlueHatTurboController::stop_scan_sync_process();
			}
		}
	}
}