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

class BlueHatCDNModelBlueHatCDNAmazon extends BlueHatCDNModelBlueHatCDN
{
	private $_connObj = null;
	private $_filesSynced = array();
	private $_lastSuccessfulValidateResult = null;
	
	final public function __construct($html='')
	{
		parent::__construct($html);
	}
	
	final public function validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)
	{
		if(!empty($this->_lastSuccessfulValidateResult)) return $this->_lastSuccessfulValidateResult;
		
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('S3')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'cloudfront'.DIRECTORY_SEPARATOR.'S3.php';
		
		$bucketName = preg_replace('@[^a-zA-Z0-9]@', '_', preg_replace('@^https?://@i', '', BlueHatNetworkFactory::getSiteRoot(), 1)).'_bht';
		
		$this->_connObj = new S3($cdnUsername, $cdnAPIKey, true);
		
		S3::$useExceptions = true;
		
		$cURLCaCertFilePath = BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'share'.DIRECTORY_SEPARATOR.'cacert.pem';
		
		S3::setSSLAuth(null, null, $cURLCaCertFilePath);
		
		try {
			// First make sure bucket exists
			$acctBuckets = S3::listBuckets();
			
			if(!in_array($bucketName, $acctBuckets))
			{
				// Bucket doesnt exist, create it
				S3::putBucket($bucketName, S3::ACL_PUBLIC_READ);
				
				BlueHatNetworkSetting::set('bucket_name', $bucketName);
			}
			
			// Now make sure cloudfront distribution exists
			$cfDistributions = S3::listDistributions();
			$createDistribution = true;
			
			if(!empty($cfDistributions)) 
			{
				foreach($cfDistributions as $cfDistribution)
				{
					if($cfDistribution['origin'] == $bucketName.'.s3.amazonaws.com') $createDistribution = false;
				}
			}
			
			if($createDistribution) S3::createDistribution($bucketName, true, array(), null, 'index.html');
			
			$cfDistributions = S3::listDistributions();
			$cfDistributionDomain = null;
			$cfDistributionId = null;
			
			if(!empty($cfDistributions)) 
			{
				foreach($cfDistributions as $cfDistribution)
				{
					if($cfDistribution['origin'] == $bucketName.'.s3.amazonaws.com') 
					{
						$cfDistributionDomain = $cfDistribution['domain'];
						$cfDistributionId = $cfDistribution['id'];
					}
				}
			}
			
			if(!empty($cfDistributionDomain))
			{
				BlueHatNetworkSetting::set('bucket_public_uri', '//'.$cfDistributionDomain);
				BlueHatNetworkSetting::set('bucket_public_secure_uri', '//'.$cfDistributionDomain);
				BlueHatNetworkSetting::set('cloudfront_distribution_id', $cfDistributionId);
				
				$this->_lastSuccessfulValidateResult = array('bucket_public_uri' => '//'.$cfDistributionDomain, 'bucket_public_secure_uri' => '//'.$cfDistributionDomain);
				
				return $this->_lastSuccessfulValidateResult;
			}
			else
			{
				return false;
			}
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
		if(!class_exists('S3')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'cloudfront'.DIRECTORY_SEPARATOR.'S3.php';
		
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
			
			$this->ensureRootIndexFileExists();
			
			$this->_touchLockFile(20);
			
			$this->ensureCrossDomainXmlFileExistsOnCDN();
			
			$this->_touchLockFile();
			
			$bucketName = BlueHatNetworkSetting::get('bucket_name');
			
			if(!empty($bucketName))
			{
				$currentUnixTimeStamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
				$cacheExpireTime = 86400*365;
				$expiresTime = $currentUnixTimeStamp+$cacheExpireTime;
				$expiresDateTimeFormat = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
				$invalidateFilesArray = array();
				
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
						
						if($optimizedFilePath != $fullLocalPath && (filesize($optimizedFilePath) < 1 || filesize($optimizedFilePath) >= filesize($fullLocalPath))) copy($fullLocalPath, $optimizedFilePath);
						
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
							
							$metaHeaders = array();
							$requestHeaders = array(
								'Cache-Control' => 'max-age='.$cacheExpireTime,
								'Expires' => $expiresDateTimeFormat
							);
							
							if(!empty($fileCorrectMimeType)) $requestHeaders['Content-Type'] = $fileCorrectMimeType;
							if($shouldGzip) $requestHeaders['Content-Encoding'] = 'gzip';
							if(preg_match('@\.(?:css|js|swf|svg|ttf|otf|woff|eot|map|xml)$@i', $syncFile)) $requestHeaders['Access-Control-Allow-Origin'] = '*';
							
							S3::putObjectFile($optimizedFilePath, $bucketName, $remotePath, S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders);
							
							$fileUploadWasSuccess = true;
						} catch(Exception $e) {
							$fileUploadWasSuccess = false;
						}

						if($fileUploadWasSuccess)
						{
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
							$invalidateFilesArray[] = $originalFileFullPath;
						}
						
						$this->_touchLockFile();
						
						if($fullLocalPath != $optimizedFilePath && is_file($optimizedFilePath)) unlink($optimizedFilePath); // delete tmp optimized file when done with it
					}
					
					if($numOfFilesToSync > 1 && !$this->_doesLockFileExist()) BlueHatNetworkFactory::exitNow();
				}
				
				if(!empty($invalidateFilesArray))
				{
					$chunked = array_chunk($invalidateFilesArray, 1000);

					foreach($chunked as $chunkedInvalidateArr)
					{
						try {
							S3::invalidateDistribution(BlueHatNetworkSetting::get('cloudfront_distribution_id'), $chunkedInvalidateArr);
						} catch(Exception $e) {
							
						}
					}
				}
			}
		}
		
		return $this->_filesSynced;
	}
	
	final public function deleteCDNFile($filePathToDelete)
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		$bucketName = BlueHatNetworkSetting::get('bucket_name');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey) || empty($bucketName) || empty($filePathToDelete)) return false;
		
		if(empty($this->_connObj)) if(!$this->validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)) return false;
		
		try {
			S3::deleteObject($bucketName, ltrim($filePathToDelete, '/'));
			
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
		$bucketName = BlueHatNetworkSetting::get('bucket_name');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey) || empty($bucketName)) return false;
		
		$remoteFileName = 'crossdomain.xml';
		$remoteFileObject = null;
		
		try {
			$remoteFileObject = S3::getObject($bucketName, $remoteFileName);
		} catch(Exception $e) {
			$remoteFileObject = null;
		}
		
		if(empty($remoteFileObject))
		{
			try {
					$remoteFileContents = <<<EOF
<?xml version="1.0"?>
<cross-domain-policy>
	<allow-access-from domain="*"/>
</cross-domain-policy>
EOF;
					$localFilePath = BlueHatNetworkFactory::getTmpPath().DIRECTORY_SEPARATOR.$remoteFileName;

					if(file_put_contents($localFilePath, $remoteFileContents))
					{
						$currentUnixTimeStamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
						$cacheExpireTime = 86400*365;
						$expiresTime = $currentUnixTimeStamp+$cacheExpireTime;
						$expiresDateTimeFormat = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
						$metaHeaders = array();
						$requestHeaders = array(
							'Cache-Control' => 'max-age='.$cacheExpireTime,
							'Expires' => $expiresDateTimeFormat
						);

						$requestHeaders['Access-Control-Allow-Origin'] = '*';

						if($this->gzipFile($localFilePath)) $requestHeaders['Content-Encoding'] = 'gzip';

						$fileCorrectMimeType = $this->_getFileMimeType($remoteFileName);

						if(!empty($fileCorrectMimeType)) $requestHeaders['Content-Type'] = $fileCorrectMimeType;

						S3::putObjectFile($localFilePath, $bucketName, $remoteFileName, S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders);
						
						$this->_disableErrorReporting();
						
						@unlink($localFilePath);
						
						$this->_restoreErrorReporting();
						
						return true;
					}
			} catch(Exception $e) {

			}
		}
		
		return false;
	}
	
	public function ensureRootIndexFileExists()
	{
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$cdnProvider = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		$bucketName = BlueHatNetworkSetting::get('bucket_name');
		
		if(empty($cdnProvider) || empty($cdnUsername) || empty($cdnAPIKey) || empty($bucketName)) return false;
		
		$remoteFileName = 'index.html';
		$remoteFileObject = null;
		
		try {
			$remoteFileObject = S3::getObject($bucketName, $remoteFileName);
		} catch(Exception $e) {
			$remoteFileObject = null;
		}
		
		if(empty($remoteFileObject))
		{
			try {
					$remoteFileContents = <<<EOF
<!DOCTYPE html>
<html>
<body><!-- This file exists for security purposes and was left empty on purpose --></body>
</html>
EOF;
					$localFilePath = BlueHatNetworkFactory::getTmpPath().DIRECTORY_SEPARATOR.$remoteFileName;

					if(file_put_contents($localFilePath, $remoteFileContents))
					{
						$currentUnixTimeStamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
						$cacheExpireTime = 86400*365;
						$expiresTime = $currentUnixTimeStamp+$cacheExpireTime;
						$expiresDateTimeFormat = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
						$metaHeaders = array();
						$requestHeaders = array(
							'Cache-Control' => 'max-age='.$cacheExpireTime,
							'Expires' => $expiresDateTimeFormat
						);

						$requestHeaders['Access-Control-Allow-Origin'] = '*';

						if($this->gzipFile($localFilePath)) $requestHeaders['Content-Encoding'] = 'gzip';

						$fileCorrectMimeType = $this->_getFileMimeType($remoteFileName);

						if(!empty($fileCorrectMimeType)) $requestHeaders['Content-Type'] = $fileCorrectMimeType;

						S3::putObjectFile($localFilePath, $bucketName, $remoteFileName, S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders);
						
						$this->_disableErrorReporting();
						
						@unlink($localFilePath);
						
						$this->_restoreErrorReporting();
						
						return true;
					}
			} catch(Exception $e) {

			}
		}
		
		return false;
	}
}