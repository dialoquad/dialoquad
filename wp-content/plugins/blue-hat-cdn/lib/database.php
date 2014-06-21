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

class BlueHatNetworkDatabase
{
	public static $dbObj = null;
	private static $_tablePrefix = null;
	private static $_joomlaVersionObj = null;
	
	public static function quote($str)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			return '\''.esc_sql($str).'\'';
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(empty(self::$dbObj)) self::$dbObj =& JFactory::getDBO();
			
			return self::$dbObj->Quote($str);
		}
	}
	
	public static function escape($str)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			return esc_sql($str);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(empty(self::$dbObj)) self::$dbObj =& JFactory::getDBO();
			
			if(method_exists(self::$dbObj, 'getEscaped'))
			{
				return self::$dbObj->getEscaped($str);
			}
			else
			{
				return self::$dbObj->escape($str);
			}
		}
	}
	
	public static function getTablePrefix()
	{
		if(self::$_tablePrefix) return self::$_tablePrefix;
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(empty(self::$dbObj)) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			self::$_tablePrefix = self::$dbObj->prefix.'bhn_v2';
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			self::$_tablePrefix = '#__bhn_v2';
		}
		
		return self::$_tablePrefix;
	}
	
	public static function getRow($sql)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->get_row($sql, ARRAY_A);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			self::$dbObj->setQuery($sql);
			
			return self::$dbObj->loadAssoc();
		}
	}
	
	public static function getResults($sql)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->get_results($sql, ARRAY_A);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			self::$dbObj->setQuery($sql);
			
			return self::$dbObj->loadAssocList();
		}
	}
	
	public static function getResult($sql)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->get_var($sql);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			self::$dbObj->setQuery($sql);
			
			return self::$dbObj->loadResult();
		}
	}
	
	public static function insertData($tableName, $dataObj)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->insert($tableName, (array)$dataObj);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			return self::$dbObj->insertObject($tableName, $dataObj);
		}
	}
	
	public static function getLastInsertId()
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->insert_id;
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			return self::$dbObj->insertid();
		}
	}
	
	public static function query($sql)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			if(!self::$dbObj) 
			{
				global $wpdb;
				
				self::$dbObj =& $wpdb;
			}
			
			return self::$dbObj->query($sql);
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			if(!self::$dbObj) self::$dbObj =& JFactory::getDBO();
			
			self::$dbObj->setQuery($sql);
			
			if(!self::$_joomlaVersionObj)
			{
				jimport('joomla.version');
				
				self::$_joomlaVersionObj = new JVersion();
			}
			
			if(version_compare(self::$_joomlaVersionObj->getShortVersion(), '3.0', '>='))
			{
				return self::$dbObj->execute();
			}
			else
			{
				return self::$dbObj->query();
			}
		}
	}
}