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

class BlueHatNetworkLanguage
{
	public static function _($translationKey)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			return __($translationKey, 'blue-hat-cdn');
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			return JText::_($translationKey);
		}
	}
	
	public static function sprintf()
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$args = func_get_args();
		
		if(BlueHatNetworkFactory::isJoomla())
		{
			return call_user_func_array('JText::sprintf', $args);
		}
		else
		{
			if(!empty($args)) 
			{
				$args[0] = self::_($args[0]);
				
				return call_user_func_array('sprintf', $args);
			}
		}
		
		return $args[0];
	}
}