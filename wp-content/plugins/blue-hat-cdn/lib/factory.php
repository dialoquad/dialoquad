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

class BlueHatNetworkFactory
{
	private static $_getSiteRoot;
	private static $_getTmpPath;
	private static $_getSiteFrontEndBasePath;
	private static $_syncModelObject = null;
	
	public static function isWordPress()
	{
		if(class_exists('WP'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function isJoomla()
	{
		if(class_exists('JConfig'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function getTmpPath()
	{
		if(!empty(self::$_getTmpPath)) return self::$_getTmpPath;
		
		if(self::isJoomla())
		{
			if(method_exists('JFactory', 'getApplication'))
			{
				$mainframe =& JFactory::getApplication();
			}
			else
			{
				global $mainframe;
			}
			
			if(is_writable($mainframe->getCfg('tmp_path')))
			{
				$result = $mainframe->getCfg('tmp_path');
			}
			else
			{
				$result = sys_get_temp_dir();
			}
		}
		else
		{
			$result = sys_get_temp_dir();
		}
		
		self::$_getTmpPath = rtrim($result, DIRECTORY_SEPARATOR);
		
		return self::$_getTmpPath;
	}
	
	public static function getCachePath()
	{
		if(self::isWordPress())
		{
			$result = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'cache';
		}
		elseif(self::isJoomla())
		{
			$result = JPATH_SITE.DIRECTORY_SEPARATOR.'cache';
		}
		
		return $result;
	}
	
	public static function getSiteFrontEndBasePath()
	{
		if(!empty(self::$_getSiteFrontEndBasePath)) return self::$_getSiteFrontEndBasePath;
		
		if(self::isWordPress())
		{
			$result = get_home_path();
		}
		elseif(self::isJoomla())
		{
			$result = JPATH_SITE;
		}
		
		self::$_getSiteFrontEndBasePath = rtrim($result, DIRECTORY_SEPARATOR);
		
		return self::$_getSiteFrontEndBasePath;
	}
	
	public static function getSiteRoot($pathOnly=false)
	{
		if(self::isWordPress())
		{
			$result = site_url();
			
			if($pathOnly) $result = preg_replace('@^https?://[^/]+@', '', $result, 1);
		}
		elseif(self::isJoomla())
		{
			jimport('joomla.environment.uri');
			
			$result = JURI::root($pathOnly);
		}
		
		self::$_getSiteRoot = rtrim($result, '/'.DIRECTORY_SEPARATOR);
		
		return self::$_getSiteRoot;
	}
	
	public static function isWebsiteFrontend()
	{
		if(self::isWordPress())
		{
			if(!is_admin())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		elseif(self::isJoomla())
		{
			if(method_exists('JFactory', 'getApplication'))
			{
				$mainframe =& JFactory::getApplication();
			}
			else
			{
				global $mainframe;
			}
			
			if('site' == $mainframe->getName())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public static function exitNow()
	{
		if(self::isWordPress())
		{
			exit();
		}
		elseif(self::isJoomla())
		{
			jexit();
		}
		else
		{
			exit();
		}
	}
	
	public static function &getSyncModel($html='', $preferredCDN='')
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		if('' == $preferredCDN) $preferredCDN = BlueHatNetworkSetting::get('cdn_provider');
		
		switch($preferredCDN)
		{
			case 'bhn':
				if(!class_exists('BlueHatCDNModelBlueHatCDNBHN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnBHN.php';
			
				self::$_syncModelObject = new BlueHatCDNModelBlueHatCDNBHN($html);
			
				break;
			
			case 'aws':
				if(!class_exists('BlueHatCDNModelBlueHatCDNAmazon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnAmazon.php';
			
				self::$_syncModelObject = new BlueHatCDNModelBlueHatCDNAmazon($html);
			
				break;
			
			case 'rackspace_cloudfiles':
				if(!class_exists('BlueHatCDNModelBlueHatCDNRackspace')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnRackspace.php';
			
				self::$_syncModelObject = new BlueHatCDNModelBlueHatCDNRackspace($html);
			
				break;
			
			case 'rackspace_cloudfiles_uk':
				if(!class_exists('BlueHatCDNModelBlueHatCDNRackspace')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnRackspace.php';
			
				self::$_syncModelObject = new BlueHatCDNModelBlueHatCDNRackspace($html);
				self::$_syncModelObject->setUK();
				
				break;
			
			default:
				if(!class_exists('BlueHatCDNModelBlueHatCDN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdn.php';
			
				self::$_syncModelObject = new BlueHatCDNModelBlueHatCDN($html);
				
				break;
		}
		
		return self::$_syncModelObject;
	}
}