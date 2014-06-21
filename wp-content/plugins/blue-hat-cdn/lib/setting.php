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

class BlueHatNetworkSetting 
{
	private static $_allSettings = array();
	
	private static function &_getSettingObject($useCache=true)
	{
		if(!$useCache || array() == self::$_allSettings)
		{
			if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
			if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
			
			$tmpResults = BlueHatNetworkDatabase::getResults(
				'SELECT HIGH_PRIORITY SQL_CACHE 
					setting_name,
					setting_value 
				FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting;'
			);
			
			self::$_allSettings = array();
			
			if(!empty($tmpResults))
			{
				foreach($tmpResults as $key => $row)
				{
					self::$_allSettings[$row['setting_name']] = $row['setting_value'];
					
					if(!empty(self::$_allSettings[$row['setting_name']]) && (self::$_allSettings[$row['setting_name']][0] == '{' || self::$_allSettings[$row['setting_name']][0] == '[')) self::$_allSettings[$row['setting_name']] = (array)BlueHatNetworkSerializer::decode(self::$_allSettings[$row['setting_name']]);
				}
			}
		}
		
		return self::$_allSettings;
	}
	
	public static function get($settingName, $defaultValue=null, $useCache=true)
	{
		$allSettings =& self::_getSettingObject();
		
		if(!$useCache)
		{
			$allSettings[$settingName] = BlueHatNetworkDatabase::getResult(
				'SELECT HIGH_PRIORITY SQL_NO_CACHE 
					setting_value 
				FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting 
				WHERE setting_name = '.BlueHatNetworkDatabase::quote($settingName).' 
				LIMIT 1;'
			);
			
			if(!empty($allSettings[$settingName]) && ($allSettings[$settingName][0] == '{' || $allSettings[$settingName][0] == '[')) $allSettings[$settingName] = (array)BlueHatNetworkSerializer::decode($allSettings[$settingName]);
		}
		
		if(array_key_exists($settingName, $allSettings))
		{
			return $allSettings[$settingName];
		}
		else
		{
			return $defaultValue;
		}
	}
	
	public static function set($settingName, $newValue=null)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		if(is_array($newValue) || is_object($newValue)) $newValue = BlueHatNetworkSerializer::encode($newValue);
		
		if($newValue === null)
		{
			$newValue = 'null';
		}
		else
		{
			$newValue = BlueHatNetworkDatabase::quote((string)$newValue);
		}
		
		$nowTimestamp = (string)BlueHatNetworkDateTime::getCurrentUnixStamp();
		
		return BlueHatNetworkDatabase::query(
			'INSERT HIGH_PRIORITY INTO '.BlueHatNetworkDatabase::getTablePrefix().'_setting 
			( 
				setting_name,
				setting_value,
				setting_cdate,
				setting_mdate
			) 
			VALUES 
			(
				'.BlueHatNetworkDatabase::quote($settingName).', 
				'.$newValue.', 
				'.$nowTimestamp.',
				'.$nowTimestamp.' 
			) ON DUPLICATE KEY UPDATE 
				setting_value = VALUES(setting_value), 
				setting_mdate = VALUES(setting_mdate);'
		);
	}
	
	public static function deleteSetting($settingName)
	{
		if(empty($settingName)) return false;
		
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		if(is_array($settingName))
		{
			$inClause = '';
			
			if(!empty($settingName))
			{
				foreach($settingName as $settingNameToDelete)
				{
					$inClause .= BlueHatNetworkDatabase::quote($settingNameToDelete).',';
				}
				
				$inClause = rtrim($inClause, ',');
			}
			
			if('' != $inClause) return BlueHatNetworkDatabase::query('DELETE FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting WHERE setting_name IN ('.$inClause.');');
		}
		else
		{
			return BlueHatNetworkDatabase::query(
				'DELETE FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting 
				WHERE setting_name = '.BlueHatNetworkDatabase::quote($settingName).';'
			);
		}
	}
	
	public static function getTmp($settingName, $defaultValue=null)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		$result = BlueHatNetworkDatabase::getResult(
			'SELECT SQL_NO_CACHE tmp_setting_value FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting_tmp 
			WHERE tmp_setting_name = '.BlueHatNetworkDatabase::quote($settingName).';'
		);
		
		if(empty($result)) $result = $defaultValue;
		
		return $result;
	}
	
	public static function setTmp($settingName, $newValue)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		return BlueHatNetworkDatabase::query(
			'INSERT INTO '.BlueHatNetworkDatabase::getTablePrefix().'_setting_tmp 
			( 
				tmp_setting_name, 
				tmp_setting_value 
			) 
			VALUES 
			(
				'.BlueHatNetworkDatabase::quote($settingName).', 
				'.BlueHatNetworkDatabase::quote($newValue).' 
			) ON DUPLICATE KEY UPDATE 
				tmp_setting_value = VALUES(tmp_setting_value);'
		);
	}
	
	public static function deleteSettingTmp($settingName, $likeStatement=false)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		if(!$likeStatement)
		{
			$sql = 'DELETE FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting_tmp 
					WHERE tmp_setting_name = '.BlueHatNetworkDatabase::quote($settingName).';';
		}
		else
		{
			$sql = 'DELETE FROM '.BlueHatNetworkDatabase::getTablePrefix().'_setting_tmp 
					WHERE tmp_setting_name LIKE '.BlueHatNetworkDatabase::quote($settingName).';';
		}
		
		return BlueHatNetworkDatabase::query($sql);
	}
}