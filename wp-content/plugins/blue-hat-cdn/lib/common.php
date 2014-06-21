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

class BlueHatNetworkCommon
{
	private static $_getWebsiteOwnerEmailAddress;
	
	public static function isValidEmailAddress($emailAddress)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			return is_email($emailAddress);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			jimport('joomla.mail.helper');
			
			return JMailHelper::isEmailAddress($emailAddress);
		}
	}
	
	public static function getWebsiteOwnerEmailAddress()
	{
		if(!empty(self::$_getWebsiteOwnerEmailAddress)) return self::$_getWebsiteOwnerEmailAddress;
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			self::$_getWebsiteOwnerEmailAddress = get_bloginfo('admin_email');
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			$config =& JFactory::getConfig();
			
			if(method_exists($config, 'getValue'))
			{
				self::$_getWebsiteOwnerEmailAddress = $config->getValue('config.mailfrom');
			}
			else
			{
				self::$_getWebsiteOwnerEmailAddress = $config->get('mailfrom');
			}
		}
		
		return self::$_getWebsiteOwnerEmailAddress;
	}
	
	public static function mkdir($dirPath)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			return wp_mkdir_p($dirPath);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			jimport('joomla.filesystem.folder');
			
			return JFolder::create($dirPath);
		}
	}
	
	public static function humanReadableFilesize($size) 
	{
		$mod = 1024;
		
		$units = explode(' ', 'Bytes KB MB GB TB PB');
		
		for($i = 0; $size > $mod; $i++) 
		{
			$size /= $mod;
		}

		return round($size, 2).' '.$units[$i];
	}
	
	public static function getFileExt($file) 
	{
		return substr($file, strrpos($file, '.')+1);
	}
	
	public static function isWindows()
	{
		if(strncasecmp(PHP_OS, 'WIN', 3) == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function extractGzipFile($gzipFilePath)
	{
		if(!is_file($gzipFilePath)) return false;
		
		// Raising this value may increase performance
		$buffer_size = 4096; // read 4kb at a time
		$out_file_name = preg_replace('@\.gz$@i', '', $gzipFilePath, 1);

		// Open our files (in binary mode)
		$file = gzopen($gzipFilePath, 'rb');
		$out_file = fopen($out_file_name, 'wb');

		// Keep repeating until the end of the input file
		while(!gzeof($file)) 
		{
			// Read buffer-size bytes
			// Both fwrite and gzread and binary-safe
			fwrite($out_file, gzread($file, $buffer_size));
		}

		// Files are done, close files
		fclose($out_file);
		gzclose($file);
	}
}