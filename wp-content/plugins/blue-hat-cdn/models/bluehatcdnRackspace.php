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

class BlueHatCDNModelBlueHatCDNRackspace extends BlueHatCDNModelBlueHatCDN 
{
	private $_isUK = false;
	private $_authObj = null;
	private $_connObj = null;
	private $_filesSynced = array();
	
	final public function __construct($html='')
	{
		parent::__construct($html);
	}
	
	public function setUK()
	{
		$this->_isUK = true;
	}
	
	final public function validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('CF_Authentication')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'php-cloudfiles'.DIRECTORY_SEPARATOR.'cloudfiles.php';
		
		$cURLCaCertFilePath = BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'share'.DIRECTORY_SEPARATOR.'cacert.pem';
		
		try {
			$this->_authObj = ($this->_isUK) ? new CF_Authentication($cdnUsername, $cdnAPIKey, null, UK_AUTHURL) : new CF_Authentication($cdnUsername, $cdnAPIKey);
			$this->_authObj->ssl_use_cabundle($cURLCaCertFilePath);
			$this->_authObj->authenticate();
			
			$this->_connObj = new CF_Connection($this->_authObj);
			$this->_connObj->ssl_use_cabundle($cURLCaCertFilePath);  # bypass cURL's old CA bundle
			
			$bucketName = preg_replace('@[^a-zA-Z0-9]@', '_', preg_replace('@^https?://@i', '', BlueHatNetworkFactory::getSiteRoot(), 1)).'_bht';
			
			$bucket = $this->_connObj->create_container($bucketName);

			$bucketPublicUri = preg_replace('@^https?:@i', '', rtrim($bucket->make_public((86400*365)), '/'), 1);
			$bucketPublicSecureUri = preg_replace('@\.r[0-9]+\.@', '.ssl.', $bucketPublicUri, 1);

			BlueHatNetworkSetting::set('bucket_name', $bucketName);
			BlueHatNetworkSetting::set('bucket_public_uri', $bucketPublicUri);
			BlueHatNetworkSetting::set('bucket_public_secure_uri', $bucketPublicSecureUri);
			
			return array('bucket_public_uri' => $bucketPublicUri, 'bucket_public_secure_uri' => $bucketPublicSecureUri);
		} catch(Exception $e) {
			return false;
		}
	}
	
	final public function syncFiles($allFiles)
	{
		$this->_filesSynced = array();
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		if(!class_exists('BHNOptimizer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'optimize.php';
		
		if(!empty($allFiles))
		{
			$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
			$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
			$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
			
			$numOfFilesToSync = count($allFiles);
			
			$this->_touchLockFile(5);
			
			if(!$this->validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey))
			{
				$this->_setStatusMessage(BlueHatNetworkLanguage::_('BHN_COULD_NOT_VALIDATE_API_CREDENTIALS_ERROR'));
				
				$this->_slowDown();
				
				return false;
			}
			
			$this->_touchLockFile(20);
			
			$this->ensureCrossDomainXmlFileExistsOnCDN();
			
			$this->_touchLockFile();
			
			try {
				$bucketObj = $this->_connObj->get_container(BlueHatNetworkSetting::get('bucket_name'));
			} catch(Exception $e) {
				return false;
			}
			
			if($bucketObj)
			{
				$currentUnixTimeStamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
				$cacheExpireTime = 86400*365;
				$expiresTime = $currentUnixTimeStamp+$cacheExpireTime;
				$expiresDateTimeFormat = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
				
				foreach($allFiles as $key => $syncFile)
				{
					$this->_touchLockFile();
					
					$fullLocalPath = (is_file($syncFile)) ? $syncFile : BlueHatNetworkFactory::getSiteFrontEndBasePath().$syncFile;
					$originalFileFullPath = (is_numeric($key)) ? $syncFile : $key;
					$originalFileStats = $this->getFileOriginalChecksum($originalFileFullPath);
					$doSyncForThisFile = ($numOfFilesToSync > 1) ? false : true;
					
					if(!empty($originalFileStats))
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
						$remotePath = ltrim($originalFileFullPath, '/');
						
						try {
							$remoteFileObj = $bucketObj->get_object($remotePath);
						} catch(Exception $e) {
							$remoteFileObj = null;
						}
						
						try {
							if(empty($remoteFileObj)) $remoteFileObj = $bucketObj->create_object($remotePath);
						} catch(Exception $e) {
							$remoteFileObj = null;
						}
						
						if(!empty($remoteFileObj))
						{
							$optimizedFilePath = BlueHatNetworkFactory::getTmpPath().DIRECTORY_SEPARATOR.basename($originalFileFullPath);
							
							if($fullLocalPath != $optimizedFilePath)
							{
								if(is_file($optimizedFilePath)) unlink($optimizedFilePath);
								
								if(!copy($fullLocalPath, $optimizedFilePath)) continue;
							}
							
							$this->_touchLockFile();
							
							BHNOptimizer::optimizeFile($fullLocalPath, $optimizedFilePath);
							
							$this->_touchLockFile();
							
							clearstatcache(true, $fullLocalPath);
							clearstatcache(true, $optimizedFilePath);
							
							if($fullLocalPath != $optimizedFilePath && (filesize($optimizedFilePath) < 1 || filesize($optimizedFilePath) >= filesize($fullLocalPath))) copy($fullLocalPath, $optimizedFilePath);
							
							$shouldGzip = $this->gzipFile($optimizedFilePath);
							
							$this->_touchLockFile();
							
							clearstatcache(true, $optimizedFilePath);
							
							$finalFileSize = filesize($optimizedFilePath);
							$finalFileChecksum = md5_file($optimizedFilePath);
							
							$this->_touchLockFile();
							
							// Now upload the file to CDN
							$fileUploadWasSuccess = false;
							
							try {
								$fileCorrectMimeType = $this->_getFileMimeType($optimizedFilePath);
								
								if(!empty($fileCorrectMimeType)) $remoteFileObj->content_type = $fileCorrectMimeType;
								
								$remoteFileObj->load_from_filename($optimizedFilePath);
								
								if($shouldGzip) $remoteFileObj->headers['Content-Encoding'] = 'gzip';
								
								if(preg_match('@\.(?:css|js|swf|svg|ttf|otf|woff|eot|map|xml)$@i', $syncFile)) $remoteFileObj->headers['Access-Control-Allow-Origin'] = '*';
								
								//$remoteFileObj->headers['Cache-Control'] = 'max-age='.$cacheExpireTime;
								//$remoteFileObj->headers['Expires'] = $expiresDateTimeFormat;
								
								$remoteFileObj->sync_metadata();
								
								$fileUploadWasSuccess = true;
							} catch(Exception $e) {
								$fileUploadWasSuccess = false;
							}
							
							if($fileUploadWasSuccess)
							{
								try {
									$remoteFileObj->purge_from_cdn();
								} catch(Exception $e) {
									
								}
								
								$sql = 'UPDATE '.BlueHatNetworkDatabase::getTablePrefix().'_file SET 
											file_final_md5 = '.BlueHatNetworkDatabase::quote($finalFileChecksum).',
											file_final_filesize = '.(int)$finalFileSize.', 
											file_mdate = '.$currentUnixTimeStamp.' 
										WHERE file_full_path = '.BlueHatNetworkDatabase::quote($originalFileFullPath).';';
								BlueHatNetworkDatabase::query($sql);

								$this->_syncFileCount++;
								
								if($this->_syncFileCount > 1)
								{
									if(!$this->_supressStatusMessageUpdates) $this->_setStatusMessage(BlueHatNetworkLanguage::sprintf('BHN_SYNCING_FILES', $this->_syncFileCount, $originalFileFullPath));
									
									$this->_setLastFileProcessed($originalFileFullPath);
								}
								else
								{
									if(!$this->_supressStatusMessageUpdates) $this->_setStatusMessage(BlueHatNetworkLanguage::sprintf('BHN_SYNCING_FILE', $this->_syncFileCount, $originalFileFullPath));
								}
								
								$this->_filesSynced[] = $originalFileFullPath;
							}
							
							$this->_touchLockFile();
							
							if($fullLocalPath != $optimizedFilePath && is_file($optimizedFilePath)) unlink($optimizedFilePath); // delete tmp optimized file when done with it
						}
					}
					
					if($numOfFilesToSync > 1 && !$this->_doesLockFileExist()) BlueHatNetworkFactory::exitNow();
				}
			}
		}
		
		return $this->_filesSynced;
	}
	
	final public function deleteCDNFile($filePathToDelete)
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		$bucketName = BlueHatNetworkSetting::get('bucket_name');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey) || empty($bucketName) || empty($filePathToDelete)) return false;
		
		if(empty($this->_connObj)) if(!$this->validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)) return false;
		
		try {
			$bucketObj = $this->_connObj->get_container($bucketName);
			$bucketObj->delete_object(ltrim($filePathToDelete, '/'));
			
			return true;
		} catch(Exception $e) {
			return false;
		}
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
		
		$remoteFileName = 'crossdomain.xml';
		
		try {
			$bucketObj = $this->_connObj->get_container(BlueHatNetworkSetting::get('bucket_name'));
		} catch(Exception $e) {
			return false;
		}
		
		$remoteObjEtag = null;
		
		try {
			$remoteObj = $bucketObj->get_object($remoteFileName);
			
			if(!empty($remoteObj)) $remoteObjEtag = $remoteObj->getETag();
		} catch(Exception $e) {
			$remoteObjEtag = null;
		}
		
		if(empty($remoteObjEtag))
		{
			$remoteFileContents = <<<EOF
<?xml version="1.0"?>
<cross-domain-policy>
	<allow-access-from domain="*"/>
</cross-domain-policy>
EOF;
			$localFilePath = BlueHatNetworkFactory::getTmpPath().DIRECTORY_SEPARATOR.$remoteFileName;
			
			if(file_put_contents($localFilePath, $remoteFileContents))
			{
				$shouldGzip = $this->gzipFile($localFilePath);
				
				try {
					$remoteFileObj = $bucketObj->create_object($remoteFileName);
					
					$fileCorrectMimeType = $this->_getFileMimeType($remoteFileName);
					
					if(!empty($fileCorrectMimeType)) $remoteFileObj->content_type = $fileCorrectMimeType;
					
					$remoteFileObj->load_from_filename($localFilePath);
					
					if($shouldGzip) $remoteFileObj->headers['Content-Encoding'] = 'gzip';
					
					$remoteFileObj->headers['Access-Control-Allow-Origin'] = '*';
					
					$remoteFileObj->sync_metadata();
				} catch(Exception $e) {
					
				}
				
				$this->_disableErrorReporting();
				
				@unlink($localFilePath);
				
				$this->_restoreErrorReporting();
				
				return true;
			}
		}
		
		return false;
	}
}