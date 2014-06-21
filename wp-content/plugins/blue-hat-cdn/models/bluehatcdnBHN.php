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

if(!class_exists('BlueHatCDNModelBlueHatCDN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdn.php';

class BlueHatCDNModelBlueHatCDNBHN extends BlueHatCDNModelBlueHatCDN 
{
	const API_URL = 'https://api.bluehatnetwork.com/api/index.php?option=com_bluehatnetwork&view=api';
	const UPLOAD_RATE = 524288; // 512 KB per second
	const UPLOAD_MAX_FILES_PER_REQUEST = 2; // max files per upload request
	
	private $_filesSynced = array();
	
	final public function __construct($html='')
	{
		parent::__construct($html);
	}
	
	final public function validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)
	{
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$response = BlueHatNetworkNetwork::post(self::API_URL, array(
			'task' => 'bucket_info', 
			'api_username' => $cdnUsername, 
			'bucket_api_key' => $cdnAPIKey)
		);
		
		if(!empty($response))
		{
			$response = (array)BlueHatNetworkSerializer::decode($response);
			
			if(isset($response['bucket_path'])) if(!empty($response['bucket_path'])) return $response;
		}
		
		return false;
	}
	
	
	final public function syncFiles($allFiles)
	{
		$this->_filesSynced = array();
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		
		if(!empty($allFiles))
		{
			$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
			$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
			$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
			
			$this->_touchLockFile(5);
			
			// First lets validate CDN credentials make sure they are valid
			if(!$this->validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey))
			{
				$this->_setStatusMessage(BlueHatNetworkLanguage::_('BHN_COULD_NOT_VALIDATE_API_CREDENTIALS_ERROR'));
				
				$this->_slowDown();
				
				return false;
			}
			
			$this->_touchLockFile(20);
			
			$this->ensureCrossDomainXmlFileExistsOnCDN();
			
			$this->_touchLockFile();
			
			$currentUnixTimeStamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
			$pendingPostParams = array();
			$currentPostIndex = 0;
			$numOfFilesTotal = count($allFiles);
			$numOfFilesProcessed = 0;
			$pendingFileSize = 0;
			
			foreach($allFiles as $key => $syncFile)
			{
				$doSyncForThisFile = false;
				
				$this->_touchLockFile();
				
				if(is_file($syncFile))
				{
					$fullLocalPath = $syncFile;
				}
				else
				{
					$fullLocalPath = BlueHatNetworkFactory::getSiteFrontEndBasePath().$syncFile;
				}
				
				if(is_numeric($key))
				{
					$originalFileFullPath = $syncFile;
				}
				else
				{
					$originalFileFullPath = $key;
				}
				
				$this->_touchLockFile();
				
				$originalFileStats = $this->getFileOriginalChecksum($originalFileFullPath);
				
				$this->_touchLockFile();
				
				if(!$this->_supressStatusMessageUpdates && !empty($originalFileStats))
				{
					// File is indexed in DB, check checksum
					if(empty($originalFileStats['file_final_md5']) || $originalFileStats['file_original_md5'] != md5_file($fullLocalPath)) $doSyncForThisFile = true;
				}
				else
				{
					// File doesnt even exist in DB
					$doSyncForThisFile = true;
				}
				
				if($doSyncForThisFile) 
				{
					$this->_touchLockFile();
					
					$pendingFileSize += (int)filesize($fullLocalPath);
					
					$fileContents = file_get_contents($fullLocalPath);
					
					$this->_touchLockFile();
					
					$currentPostIndex++;
					
					$pendingPostParams['file_path_'.$currentPostIndex] = $originalFileFullPath;
					
					if(empty($fileContents) || '@' != $fileContents[0])
					{
						$pendingPostParams['file_content_'.$currentPostIndex] = $fileContents;
					}
					else
					{
						$pendingPostParams['file_name_'.$currentPostIndex] = '@'.$fullLocalPath;
					}
					
					$this->_touchLockFile();
				}
				
				$numOfFilesProcessed++;
				
				if(array() != $pendingPostParams && ($currentPostIndex >= self::UPLOAD_MAX_FILES_PER_REQUEST || $pendingFileSize >= self::UPLOAD_RATE || $numOfFilesProcessed >= $numOfFilesTotal))
				{
					$postResult = BlueHatNetworkNetwork::post(self::API_URL, array_merge(array(
						'task' => 'put_files',
						'api_username' => $cdnUsername,
						'bucket_api_key' => $cdnAPIKey
					), $pendingPostParams), true, true);
					
					if(!empty($postResult))
					{
						$this->_touchLockFile();

						$postResult = (array)BlueHatNetworkSerializer::decode($postResult);

						if(!empty($postResult) && (int)$postResult['success'] > 0)
						{
							// Post was a success
							if(!is_array($postResult['child_results'])) $postResult['child_results'] = (array)$postResult['child_results'];
							
							foreach($postResult['child_results'] as $uploadResult)
							{
								if(!is_array($uploadResult)) $uploadResult = (array)$uploadResult;
								
								if((int)$uploadResult['success'] > 0)
								{
									$this->_touchLockFile();

									$sql = 'UPDATE '.BlueHatNetworkDatabase::getTablePrefix().'_file SET 
												file_final_md5 = '.BlueHatNetworkDatabase::quote($uploadResult['file_final_checksum']).',
												file_final_filesize = '.(int)$uploadResult['file_final_filesize'].', 
												file_mdate = '.$currentUnixTimeStamp.' 
											WHERE file_full_path = '.BlueHatNetworkDatabase::quote($uploadResult['file_final_destination_path']).';';
									BlueHatNetworkDatabase::query($sql);

									$this->_touchLockFile();

									$this->_syncFileCount++;

									if($this->_syncFileCount > 1 && !$this->_supressStatusMessageUpdates)
									{
										$this->_setStatusMessage(BlueHatNetworkLanguage::sprintf('BHN_SYNCING_FILES', $this->_syncFileCount, $uploadResult['file_final_destination_path']));

										$this->_setLastFileProcessed($uploadResult['file_final_destination_path']);
									}
									elseif(!$this->_supressStatusMessageUpdates) 
									{
										$this->_setStatusMessage(BlueHatNetworkLanguage::sprintf('BHN_SYNCING_FILE', $this->_syncFileCount, $uploadResult['file_final_destination_path']));

										$this->_setLastFileProcessed($uploadResult['file_final_destination_path']);
									}

									$this->_touchLockFile();
									
									$this->_filesSynced[] = $uploadResult['file_final_destination_path'];
								}
							}
						}
					}
					
					$currentPostIndex = 0;
					$pendingFileSize = 0;
					$pendingPostParams = array();
				}
				
				if(!$this->_supressStatusMessageUpdates && !$this->_doesLockFileExist()) BlueHatNetworkFactory::exitNow();
			}
			
			BlueHatNetworkNetwork::closeConnection();
		}
		
		return $this->_filesSynced;
	}
	
	final public function deleteCDNFile($filePathToDelete)
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey) || empty($filePathToDelete)) return false;
		
		$postResult = BlueHatNetworkNetwork::post(self::API_URL, array(
			'task' => 'delete_file_from_bucket',
			'api_username' => $cdnUsername,
			'bucket_api_key' => $cdnAPIKey,
			'file_path' => $filePathToDelete
		), true);
		
		if(!empty($postResult))
		{
			$postResult = (array)BlueHatNetworkSerializer::decode($postResult);
			
			if((int)@$postResult['success'] == 1) return true;
		}
		
		return false;
	}
	
	public function ensureCrossDomainXmlFileExistsOnCDN()
	{
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey)) return false;
		
		$remoteFilePath = '/crossdomain.xml';
		
		$fileCheckResult = BlueHatNetworkNetwork::post(self::API_URL, array(
			'task' => 'get_file_info',
			'api_username' => $cdnUsername,
			'bucket_api_key' => $cdnAPIKey,
			'file_path' => $remoteFilePath
		), true);
		
		if(!empty($fileCheckResult))
		{
			$fileCheckResult = (array)BlueHatNetworkSerializer::decode($fileCheckResult);
			
			if(!empty($fileCheckResult))
			{
				if((int)$fileCheckResult['success'] < 1)
				{
					$remoteFileContents = <<<EOF
<?xml version="1.0"?>
<cross-domain-policy>
	<allow-access-from domain="*"/>
</cross-domain-policy>
EOF;
					$localFilePath = BlueHatNetworkFactory::getTmpPath().DIRECTORY_SEPARATOR.basename($remoteFilePath);
					
					if(file_put_contents($localFilePath, $remoteFileContents))
					{
						$postResult = BlueHatNetworkNetwork::post(self::API_URL, array(
							'task' => 'put_file',
							'api_username' => $cdnUsername,
							'bucket_api_key' => $cdnAPIKey,
							'file_path' => $remoteFilePath,
							'file_name' => '@'.$localFilePath
						), true);
						
						$this->_disableErrorReporting();
						
						@unlink($localFilePath);
						
						$this->_restoreErrorReporting();
						
						return true;
					}
				}
			}
		}
		
		return false;
	}
}