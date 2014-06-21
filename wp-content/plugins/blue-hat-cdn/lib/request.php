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

class BlueHatNetworkRequest
{
	public static function getVar($name, $defaultValue=null, $hash='method', $type='none', $mask=0)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			switch(strtolower($hash))
			{
				case 'get':
					if(isset($_GET[$name])) $defaultValue = $_GET[$name];
					
					break;
				case 'post':
					if(isset($_POST[$name])) $defaultValue = $_POST[$name];
					
					break;
				
				case 'server':
					if(isset($_SERVER[$name])) $defaultValue = $_SERVER[$name];
					
					break;
				
				case 'method':
					if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0)
					{
						if(isset($_POST[$name])) $defaultValue = $_POST[$name];
					}
					else
					{
						if(isset($_GET[$name])) $defaultValue = $_GET[$name];
					}
					
					break;
			}
			
			if($type == 'int') $defaultValue = (int)$defaultValue;
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			$defaultValue = JRequest::getVar($name, $defaultValue, $hash, $type, $mask);
		}
		
		return $defaultValue;
	}
	
	public static function getInt($name, $defaultValue=null, $hash='get')
	{
		return self::getVar($name, $defaultValue, $hash, 'int');
	}
	
	public static function isHTTPS()
	{
		if(strcasecmp(self::getVar('HTTPS', 'off', 'server'), 'on') == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}