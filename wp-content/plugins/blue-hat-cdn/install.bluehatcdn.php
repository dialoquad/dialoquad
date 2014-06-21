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

if(!class_exists('BlueHatTurboDatabaseInstaller'))
{
	class BlueHatTurboDatabaseInstaller
	{
		private static $_dbObj = null;
		private static $_tablePrefix = null;
		
		protected static function getTablePrefix($oldPrefixes=false)
		{
			if(class_exists('WP'))
			{
				if(empty(self::$_dbObj)) 
				{
					global $wpdb;

					self::$_dbObj =& $wpdb;
				}
				
				if(!$oldPrefixes)
				{
					self::$_tablePrefix = self::$_dbObj->prefix.'bhn_v2';
				}
				else
				{
					self::$_tablePrefix = self::$_dbObj->prefix.'bhn';
				}
			}
			elseif(class_exists('JConfig'))
			{
				if(!$oldPrefixes)
				{
					self::$_tablePrefix = '#__bhn_v2';
				}
				else
				{
					self::$_tablePrefix = '#__bhn';
				}
			}
			
			return self::$_tablePrefix;
		}
		
		public static function setupDB()
		{
			$sqlQueriesToRun = array();
			
			$sqlQueriesToRun[] = 'DROP TABLE IF EXISTS '.self::getTablePrefix(true).'_file;';
			$sqlQueriesToRun[] = 'DROP TABLE IF EXISTS '.self::getTablePrefix(true).'_setting;';
			
			$sqlQueriesToRun[] = 'CREATE TABLE IF NOT EXISTS '.self::getTablePrefix().'_file (
				file_id int(11) unsigned NOT NULL AUTO_INCREMENT,
				file_full_path varchar(255) COLLATE utf8_bin NOT NULL,
				file_name varchar(255) COLLATE utf8_bin NOT NULL,
				file_path varchar(255) COLLATE utf8_bin NOT NULL,
				file_extension varchar(4) COLLATE utf8_bin NOT NULL,
				file_remote_path varchar(255) COLLATE utf8_bin DEFAULT NULL,
				file_original_md5 char(32) COLLATE utf8_bin NOT NULL,
				file_original_filesize int(10) unsigned NOT NULL,
				file_final_md5 char(32) COLLATE utf8_bin DEFAULT NULL,
				file_final_filesize int(10) unsigned DEFAULT NULL,
				file_cdate int(10) unsigned NOT NULL,
				file_mdate int(10) unsigned NOT NULL,
				PRIMARY KEY (file_id),
				UNIQUE KEY file_full_path_UNIQUE (file_full_path),
				KEY file_final_md5_idx (file_final_md5),
				KEY file_name_md5 (file_name,file_final_md5),
				KEY file_path_md5 (file_full_path,file_final_md5)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
			
			$sqlQueriesToRun[] = 'CREATE TABLE IF NOT EXISTS '.self::getTablePrefix().'_mesh_hash_data (
				mesh_hash_key bigint(20) NOT NULL,
				mesh_hash_key_raw mediumtext COLLATE utf8_bin NOT NULL,
				mesh_hash_destination_url varchar(255) COLLATE utf8_bin NOT NULL,
				mesh_hash_cdate int(10) unsigned NOT NULL,
				mesh_hash_mdate int(10) unsigned NOT NULL,
				PRIMARY KEY (mesh_hash_key),
				KEY mesh_hash_key_raw_idx (mesh_hash_key_raw(255))
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
			
			$sqlQueriesToRun[] = 'CREATE TABLE IF NOT EXISTS '.self::getTablePrefix().'_mesh_hash_requests (
				mesh_hash_key bigint(20) NOT NULL,
				mesh_hash_compile_type varchar(25) COLLATE utf8_unicode_ci NOT NULL,
				mesh_hash_file_extension varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
				mesh_hash_bucket_url varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				mesh_hash_relative_url varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				mesh_hash_data mediumtext COLLATE utf8_unicode_ci NOT NULL,
				mesh_hash_cdate int(10) unsigned NOT NULL,
				mesh_hash_currently_processing tinyint(1) unsigned NOT NULL DEFAULT \'0\',
				PRIMARY KEY (mesh_hash_key),
				KEY currently_processing_idx (mesh_hash_currently_processing)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			
			$sqlQueriesToRun[] = 'CREATE TABLE IF NOT EXISTS '.self::getTablePrefix().'_setting (
				setting_id int(10) unsigned NOT NULL AUTO_INCREMENT,
				setting_name varchar(45) COLLATE utf8_bin NOT NULL,
				setting_value text COLLATE utf8_bin,
				setting_cdate int(10) unsigned NOT NULL,
				setting_mdate int(10) unsigned NOT NULL,
				PRIMARY KEY (setting_id),
				UNIQUE KEY setting_name_UNIQUE (setting_name),
				KEY setting_name_cdate_idx (setting_name,setting_cdate)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
			
			$sqlQueriesToRun[] = 'CREATE TABLE IF NOT EXISTS '.self::getTablePrefix().'_setting_tmp (
				tmp_setting_name varchar(25) NOT NULL,
				tmp_setting_value varchar(255) NOT NULL,
				PRIMARY KEY (tmp_setting_name) 
			  ) ENGINE=MEMORY DEFAULT CHARSET=utf8;';
			
			//$sqlQueriesToRun[] = 'TRUNCATE TABLE '.self::getTablePrefix().'_file;';
			//$sqlQueriesToRun[] = 'TRUNCATE TABLE '.self::getTablePrefix().'_mesh_hash_data;';
			$sqlQueriesToRun[] = 'TRUNCATE TABLE '.self::getTablePrefix().'_mesh_hash_requests;';
			$sqlQueriesToRun[] = 'TRUNCATE TABLE '.self::getTablePrefix().'_setting_tmp;';
			
			if(class_exists('WP'))
			{
				global $wpdb;
				
				foreach($sqlQueriesToRun as $sqlQuery)
				{
					$wpdb->query($sqlQuery);
				}
			}
			elseif(class_exists('JConfig'))
			{
				jimport('joomla.version');

				$jversion = new JVersion();
				
				if(version_compare($jversion->getShortVersion(), '1.6', 'lt')) 
				{
					$sqlQueriesToRun[] = 'UPDATE #__plugins SET published = 0 WHERE element = \'bluehatcdn\';';
				}
				else
				{
					$sqlQueriesToRun[] = 'UPDATE #__extensions SET enabled = 0 WHERE element = \'bluehatcdn\' AND type = \'plugin\';';
				}
				
				$db =& JFactory::getDBO();
				
				foreach($sqlQueriesToRun as $sqlQuery)
				{
					$db->setQuery($sqlQuery);
					$db->query();
				}
			}
			
			return true;
		}
	}
}

function com_install()
{
    return BlueHatTurboDatabaseInstaller::setupDB();
}