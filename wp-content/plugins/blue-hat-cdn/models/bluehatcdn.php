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

if(class_exists('RecursiveFilterIterator'))
{
	class BHNFileIndexIterator extends RecursiveFilterIterator 
	{
		protected static $objParams = array();
		private static $_currentBlogId = null;
		private static $_isMainSite = null;
		private static $_siteFrontEndRegex = '';
		
		function __construct(RecursiveIterator &$recursiveIterObj, $params=array()) 
		{
			parent::__construct($recursiveIterObj);
			
			if(array() != $params) self::$objParams = $params;
		}
	
		public function accept() 
		{
			if(empty(self::$objParams['accepted_file_types_regex'])) return false;
			if(empty(self::$_siteFrontEndRegex)) self::$_siteFrontEndRegex = preg_quote(BlueHatNetworkFactory::getSiteFrontEndBasePath(), '@');
			
			$filename = $this->current()->getFilename();
			$siteRelativeBasePath = preg_replace('@^'.self::$_siteFrontEndRegex.'@', '', $this->current()->getPath(), 1);
			
			if(!empty($siteRelativeBasePath) && preg_match('@(?:'.self::$objParams['excluded_files_regex'].')@i', $siteRelativeBasePath.'/'.$filename)) return false;
			
			if(BlueHatNetworkFactory::isWordPress() && is_multisite())
			{
				if(self::$_currentBlogId === null) self::$_currentBlogId = get_current_blog_id();
				if(self::$_isMainSite === null) self::$_isMainSite = is_main_site(self::$_currentBlogId);
				
				if(self::$_isMainSite)
				{
					if(preg_match('@(?:/uploads/sites|/blogs\.dir/[^'.self::$_currentBlogId.'/]+/)@', $siteRelativeBasePath)) return false;
				}
				else
				{
					if(preg_match('@(?:/uploads/[0-9]+|/uploads/sites/[^'.self::$_currentBlogId.'/]+/|/blogs\.dir/[^'.self::$_currentBlogId.'/]+/)@', $siteRelativeBasePath)) return false;
				}
			}
			
			if($this->current()->isFile())
			{
				if('.' == $filename[0] || self::$objParams['max_file_size'] < $this->current()->getSize() || !preg_match('@(?:'.self::$objParams['accepted_file_types_regex'].')$@i', $filename))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}
	}
}

class BlueHatCDNModelBlueHatCDN 
{
	const BHN_VERSION = '2.9.4';
	
	const MAX_FILE_SIZE = 5242880; // 5 megabytes max file size allowed
	const MESH_HASH_REQUESTS_EXPIRES_AFTER_SECONDS = 180;
	const SLOW_DOWN_DELAY_SECONDS = 3;
	const SCAN_ZOMBIE_TIMEOUT_SECONDS = 4;
	const SYNC_ZOMBIE_TIMEOUT_SECONDS = 55;
	const SCAN_TOUCH_FILE_INTERVAL = 1;
	const SYNC_TOUCH_FILE_INTERVAL = 3;
	const GENERAL_PLACEHOLDER = 'BHNIwa7';
	const CSS_PLACEHOLDER = 'BHNGs5j';
	const JS_PLACEHOLDER = 'BHNJ2pz';
	
	public $allowedImgFileTypes = array('.png', '.gif', '.jpeg', '.jpg', '.ico');
	public $allowedCSSFileTypes = array('.css');
	public $allowedJSFileTypes = array('.js', '.map');
	public $allowedFontFileTypes = array('.svg', '.ttf', '.otf', '.woff', '.eot');
	public $allowedFlashFileTypes = array('.swf');
	
	public $mimeTypesArray = array(
		'.js' => 'text/javascript',
		'.css' => 'text/css',
		'.swf' => 'application/x-shockwave-flash',
		'.png' => 'image/png',
		'.jpg' => 'image/jpeg',
		'.jpeg' => 'image/jpeg',
		'.gif' => 'image/gif',
		'.ico' => 'image/x-icon',
		'.eot' => 'application/vnd.ms-fontobject',
		'.otf' => 'font/opentype',
		'.ttf' => 'font/ttf',
		'.woff' => 'application/font-woff',
		'.svg' => 'image/svg+xml',
		'.map' => 'application/json',
		'.xml' => 'application/xml',
		'.htm' => 'text/html',
		'.html' => 'text/html'
	);
	
	protected $_supressStatusMessageUpdates = false;
	protected $_processId = 0;
	protected $_syncFileCount = 0;
	protected $_lockFileDescriptor = null;
	
	private $_includeImages = true;
	private $_includeJS = true;
	private $_includeCSS = true;
	private $_includeFont = true;
	private $_includeSWF = true;
	private $_hasBeenOptimizedAlready = false;
	private $_hasOptimizations = false;
	private $_abortMeshHashCompilation = false;
	private $_removeLockFileOnExit = false;
	private $_remotePathRoot = null;
	private $_remotePath = null;
	private $_currentCharSet = null;
	private $_cdnData = null;
	private $_currentUnixTimestamp = null;
	private $_prepedRootPath = null;
	private $_currentDir = null;
	private $_domainCheckRegex = null;
	private $_allowedFullRegex = null;
	private $_lastErrorReportingLevel = null;
	private $_excludedFilesRegex = '';
	private $_allowedImgFilesTypesRegex = '';
	private $_allowedJSFilesTypesRegex = '';
	private $_allowedCSSFilesTypesRegex = '';
	private $_allowedFontFilesTypesRegex = '';
	private $_allowedSWFFilesTypesRegex = '';
	private $_html = '';
	private $_currentUrlScheme = '';
	private $_urlPrefix = '';
	private $_websiteUrlRootPath = '';
	private $_tmpDir = '';
	private $_currentUri = '';
	private $_txtToAppendToBody = '';
	private $_allFiles = array();
	private $_placeholders = array();
	private $_cssPlaceHolders = array();
	private $_jsPlaceHolders = array();
	private $_elementsOnPage = array();
	private $_elementsOnPageToOffload = array();
	private $_availableCDNFQDNS = array();
	private $_prefetchDNSDomains = array();
	private $_filesOffloaded = array();
	private $_currentPageMeshHashIds = array();
	private $_currentPageMeshHashDataSetFromDB = array();
	private $_pendingMeshHashRequests = array();
	private $_pendingFileToCDNMappings = array();
	private $_combinedJSFilesCount = 0;
	private $_combinedCSSFilesCount = 0;
	private $_numberOfSnippetsExternalized = 0;
	private $_originalPageSize = 0;
	private $_finalPageSize = 0;
	private $_numOfOffloadedFilesOnPage = 0;
	private $_scanFileSkipCount = 0;
	private $_lastLockFileTouch = 0;
	private $_scanSyncFileCounter = 0;
	private $_numOfFilesAffected = 0;
	private $_shouldOptimizeHTML = 1;
	private $_lastFQDNIndexused = 0;
	private $_jsHandlesLoaded = array();
	private $_cssHandlesLoaded = array();
	
	function __construct($html = '')
	{
		if('' != $html) $this->_html = $html;
		
		
	}
	
	function __destruct()
	{
		if($this->_removeLockFileOnExit) $this->_removeLockFile();
	}
	
	public function getFilesSyncedCount()
	{
		return $this->_syncFileCount;
	}
	
	public function deleteCDNFile()
	{
		return false;
	}
	
	public function syncFiles($allFiles)
	{
		return false;
	}
	
	public function setHtml($html=null)
	{
		// this function is here for legacy purposes
	}
	
	public function restoreProcessIdFromLastSession()
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		$this->_processId = (int)BlueHatNetworkSetting::get('process_id');
	}
	
	public function validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey)
	{
		if('bhn' == $cdnProvider)
		{
			if(!class_exists('BlueHatCDNModelBlueHatCDNBHN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnBHN.php';
			
			$model = new BlueHatCDNModelBlueHatCDNBHN();
		}
		elseif('aws' == $cdnProvider)
		{
			if(!class_exists('BlueHatCDNModelBlueHatCDNAmazon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnAmazon.php';
			
			$model = new BlueHatCDNModelBlueHatCDNAmazon();
		}
		elseif('rackspace_cloudfiles' == $cdnProvider || 'rackspace_cloudfiles_uk' == $cdnProvider)
		{
			if(!class_exists('BlueHatCDNModelBlueHatCDNRackspace')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdnRackspace.php';
			
			$model = new BlueHatCDNModelBlueHatCDNRackspace();
			
			if('rackspace_cloudfiles_uk' == $cdnProvider) $model->setUK();
		}
		
		return $model->validateCDNAPICredentials($cdnProvider, $cdnUsername, $cdnAPIKey);
	}
	
	public function scanFiles($syncSingleFilePath='', $setTimeLimit=true, $forceStart=true, $suppressResumeTxt=false)
	{
		$this->_disableErrorReporting();
		
		if($setTimeLimit) $this->_makePermanentThread();
		
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkCommon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'common.php';
		
		BlueHatNetworkSetting::set('force_scan_sync_now', 0);
		
		$this->_prepCurrentTimeStamp();
		
		$this->restoreProcessIdFromLastSession();
		
		$filesToSync = array();
		
		if('' == $syncSingleFilePath) 
		{
			if(!$forceStart && $this->_doesLockFileExist()) return false;
			
			$this->_removeLockFile();
			$this->_setStatusMessage();
			
			$this->_processId = $this->_currentUnixTimestamp;
			
			$this->_touchLockFile(5, true);
			
			$doScan = false;
			
			switch($this->getProcessMode())
			{
				case 'scan':
					$this->_touchLockFile();
					
					$lastFileProcessed = $this->getLastFileProcessed();
					
					if(!empty($lastFileProcessed))
					{
						$sql = 'SELECT file_id FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
								WHERE file_full_path = '.BlueHatNetworkDatabase::quote($lastFileProcessed).' 
								LIMIT 1;';
						$lastFileId = BlueHatNetworkDatabase::getResult($sql);
						
						$this->_touchLockFile();
						
						if($lastFileId > 0)
						{
							$statusTxt = '';
							
							if((int)$suppressResumeTxt < 1 && strpos($this->getStatusMessage(), BlueHatNetworkLanguage::_('BHN_RESUMING_SCAN_SYNC_TXT')) === false) $statusTxt .= BlueHatNetworkLanguage::_('BHN_RESUMING_SCAN_SYNC_TXT').'<br /><br />';
							
							$statusTxt .= $this->getStatusMessage();
							
							$this->_touchLockFile();
							
							$this->_setStatusMessage($statusTxt);
							
							$this->_touchLockFile();
							
							$this->_scanFileSkipCount = $lastFileId;
						}
						else
						{
							$doScan = true;
						}
					}
					else
					{
						$doScan = true;
					}
					
					break;
				
				case 'sync':
					$lastFileProcessed = $this->getLastFileProcessed();
					
					if(!empty($lastFileProcessed))
					{
						$sql = 'SELECT file_id FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
								WHERE file_full_path = '.BlueHatNetworkDatabase::quote($lastFileProcessed).' 
								LIMIT 1;';
						$lastFileId = BlueHatNetworkDatabase::getResult($sql);
						
						if($lastFileId > 0)
						{
							$statusTxt = '';
							
							if((int)$suppressResumeTxt < 1 && strpos($this->getStatusMessage(), BlueHatNetworkLanguage::_('BHN_RESUMING_SCAN_SYNC_TXT')) === false) $statusTxt .= BlueHatNetworkLanguage::_('BHN_RESUMING_SCAN_SYNC_TXT').'<br /><br />';
							
							$statusTxt .= $this->getStatusMessage();
							
							$this->_setStatusMessage($statusTxt);
							
							$sql = 'SELECT file_full_path FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
									WHERE file_id > '.(string)$lastFileId.';';
							
							$remainingFilesToBeSynced = BlueHatNetworkDatabase::getResults($sql);
							
							if(!empty($remainingFilesToBeSynced))
							{
								$this->_syncFileCount = $lastFileId;
								
								foreach($remainingFilesToBeSynced as $row)
								{
									$filesToSync[] = $row['file_full_path'];
									
									$this->_touchLockFile();
								}
							}
							else
							{
								$doScan = true;
							}
						}
						else
						{
							$doScan = true;
						}
					}
					else
					{
						$doScan = true;
					}
					
					break;
					
				default:
					$doScan = true;
					
					break;
			}
			
			if($doScan)
			{
				$this->_touchLockFile();
				
				$this->_setStatusMessage();
				$this->_setProcessMode('scan');
				
				$this->_touchLockFile();
			}
		}
		else
		{
			$this->_supressStatusMessageUpdates = true;
		}
		
		if(!$this->_processId) $this->_processId = $this->_currentUnixTimestamp;
		
		BlueHatNetworkSetting::set('process_id', $this->_processId);
		
		$this->_includeImages = BlueHatNetworkSetting::get('include_images', true);
		$this->_includeJS = BlueHatNetworkSetting::get('include_js', true);
		$this->_includeCSS = BlueHatNetworkSetting::get('include_css', true);
		$this->_includeFont = BlueHatNetworkSetting::get('include_font', true);
		$this->_includeSWF = BlueHatNetworkSetting::get('include_swf', true);
		$this->_prepedRootPath = BlueHatNetworkFactory::getSiteFrontEndBasePath();
		
		$preferredCDN = BlueHatNetworkSetting::get('cdn_provider');
		$cdnUsername = BlueHatNetworkSetting::get('cdn_username');
		$cdnAPIKey = BlueHatNetworkSetting::get('cdn_api_key');
		
		if('rackspace_cloudfiles_uk' == $preferredCDN) $this->setUK();
		
		if(empty($preferredCDN) || empty($cdnUsername) || empty($cdnAPIKey) || !$this->validateCDNAPICredentials($preferredCDN, $cdnUsername, $cdnAPIKey))
		{
			$this->_setStatusMessage('<b style="color: red !important; font-size: 1.2em !important;">Error: '.BlueHatNetworkLanguage::_('BHN_COULD_NOT_VALIDATE_API_CREDENTIALS_ERROR').'</b>');
			
			$this->_slowDown();
			
			$this->_setProcessMode('completed');
			
			return false;
		}
		
		$this->_touchLockFile();
		
		if(empty($filesToSync))
		{
			if('' == $syncSingleFilePath) 
			{
				$allFiles = $this->_processDirectory(BlueHatNetworkFactory::getSiteFrontEndBasePath());
				
				$this->_slowDown();
				
				$this->_setProcessMode('sync');
				$this->_setStatusMessage();
				
				$this->_slowDown();
			}
			else
			{
				$allFiles = array($syncSingleFilePath);
			}
		}
		else
		{
			$allFiles =& $filesToSync;
				
			$this->_setProcessMode('sync');
			$this->_setStatusMessage();
			
			$this->_slowDown();
		}
		
		$this->_touchLockFile();
		
		$filesSynced = $this->syncFiles($allFiles);
		
		$this->_touchLockFile();
		
		if($this->getFilesSyncedCount() > 0) $this->clearWebsiteCache($filesSynced);
		
		if('' == $syncSingleFilePath)
		{
			$this->_slowDown();
			
			BlueHatNetworkSetting::set('has_completed_once', '1');
			
			$this->_setProcessMode('completed');
			$this->_setStatusMessage(' ');
			
			$this->_slowDown();
		}
		
		$this->_restoreErrorReporting();
		
		return count($allFiles);
	}
	
	protected function _slowDown()
	{
		$this->_touchLockFile(10);
		
		sleep(self::SLOW_DOWN_DELAY_SECONDS);
	}
	
	private function _recordFile($fullWebFilePath)
	{
		$fileName = basename($fullWebFilePath);
		$fileSizeVal = (int)filesize($this->_prepedRootPath.$fullWebFilePath);
		$fileExtension = strtolower(substr($fileName, strrpos($fileName, '.')+1));
		
		BlueHatNetworkDatabase::query(
			'INSERT INTO '.BlueHatNetworkDatabase::getTablePrefix().'_file 
			(
				file_full_path,
				file_name,
				file_path,
				file_extension,
				file_original_md5,
				file_original_filesize,
				file_final_filesize,
				file_cdate,
				file_mdate
			)
			VALUES 
			('.BlueHatNetworkDatabase::quote($fullWebFilePath).',
			'.BlueHatNetworkDatabase::quote($fileName).',
			'.BlueHatNetworkDatabase::quote(dirname($fullWebFilePath).'/').',
			'.BlueHatNetworkDatabase::quote($fileExtension).',
			'.BlueHatNetworkDatabase::quote(md5_file($this->_prepedRootPath.$fullWebFilePath)).', 
			'.$fileSizeVal.',
			'.$fileSizeVal.', 
			'.$this->_currentUnixTimestamp.',
			'.$this->_currentUnixTimestamp.') 
				ON DUPLICATE KEY UPDATE 
					file_original_md5 = IF(file_original_md5 != VALUES(file_original_md5), VALUES(file_original_md5), file_original_md5), 
					file_original_filesize = IF(file_original_md5 != VALUES(file_original_md5), VALUES(file_original_filesize), file_original_filesize), 
					file_mdate = IF(file_original_md5 != VALUES(file_original_md5), VALUES(file_mdate), file_mdate);'
		);
		
		$this->_setLastFileProcessed($fullWebFilePath);
	}
	
	public function clearWebsiteCache($meshHashKeysToClear=array())
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		
		BlueHatNetworkSetting::set('cache_last_cleared', BlueHatNetworkDateTime::getCurrentUnixStamp());
		
		$cacheDir = BlueHatNetworkFactory::getCachePath();
		
		if(!empty($cacheDir) && is_dir($cacheDir) && (int)BlueHatNetworkSetting::get('auto_empty_cache', 0) > 0) $this->_deleteFilesInDir($cacheDir);
		
		
	}
	
	
	
	private function _deleteFilesInDir($dirPath) 
	{
		$this->_disableErrorReporting();
		
		$files = glob($dirPath.DIRECTORY_SEPARATOR.'*');
		
		if(!empty($files))
		{
			foreach($files as $file)
			{
				if(is_dir($file))
				{
					$this->_deleteFilesInDir($file);
					
					@rmdir($file);
				}
				else
				{
					@unlink($file);
				}
			}
		}
		
		$this->_restoreErrorReporting();
	}
	
	private function _processDirectory($dirPath)
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		$this->_allFiles = array();
		
		$directoryObj = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$filteredFilesObj = new BHNFileIndexIterator($directoryObj, array('max_file_size' => self::MAX_FILE_SIZE, 'accepted_file_types_regex' => $this->_getAllowedFileTypesRegex(), 'excluded_files_regex' => $this->_getExcludedFilesRegex()));
		$dirIteratorObj = new RecursiveIteratorIterator($filteredFilesObj, RecursiveIteratorIterator::SELF_FIRST);
		
		$siteFrontendRegex = preg_quote(BlueHatNetworkFactory::getSiteFrontEndBasePath(), '@');
		
		foreach($dirIteratorObj as $path => $dir) 
		{
			$this->_touchLockFile();
			
			if(!$this->_doesLockFileExist()) BlueHatNetworkFactory::exitNow();
			
			if(!is_file($path)) continue;
			
			$basePath = preg_replace('@^'.$siteFrontendRegex.'@', '', $path, 1);
			
			$this->_allFiles[] = $basePath;
			
			$this->_scanSyncFileCounter++;
			
			if($this->_scanFileSkipCount > 0) 
			{
				$this->_scanFileSkipCount--;
				
				continue;
			}
			
			$this->_touchLockFile();
			
			$this->_setStatusMessage(BlueHatNetworkLanguage::sprintf('BHN_FOUND_FILE_MSG', number_format($this->_scanSyncFileCounter), $basePath));
			
			$this->_touchLockFile();
			
			$this->_recordFile($basePath);
			
			$this->_touchLockFile();
			
			if(!$this->_doesLockFileExist()) BlueHatNetworkFactory::exitNow();
		}
		
		return $this->_allFiles;
	}
	
	private function _getAllowedFileTypesRegex()
	{
		if(empty($this->_allowedFullRegex))
		{
			$this->_allowedFullRegex = '';
		
			if($this->_includeImages) $this->_allowedFullRegex .= $this->_getAllowedImgFilesTypesRegex().'|';
			if($this->_includeJS) $this->_allowedFullRegex .= $this->_getAllowedJSFilesTypesRegex().'|';
			if($this->_includeCSS) $this->_allowedFullRegex .= $this->_getAllowedCSSFilesTypesRegex().'|';
			if($this->_includeFont) $this->_allowedFullRegex .= $this->_getAllowedFontFilesTypesRegex().'|';
			if($this->_includeSWF) $this->_allowedFullRegex .= $this->_getAllowedSWFFilesTypesRegex().'|';
			
			$this->_allowedFullRegex = rtrim($this->_allowedFullRegex, '|');
		}
		
		return $this->_allowedFullRegex;
	}
	
	private function _processFile($filePath)
	{
		$this->_getAllowedFileTypesRegex();
		
		if(!empty($this->_allowedFullRegex) && preg_match('@('.$this->_allowedFullRegex.')$@i', $filePath)) return true;
		
		return false;
	}
	
	private function _getAllAcceptedFileExtensions()
	{
		$result = array();
		
		if($this->_includeImages) 
		{
			foreach($this->allowedImgFileTypes as $allowedType)
			{
				$result[] = $allowedType;
			}
		}
		
		if($this->_includeCSS) 
		{
			foreach($this->allowedCSSFileTypes as $allowedType)
			{
				$result[] = $allowedType;
			}
		}
		
		if($this->_includeJS) 
		{
			foreach($this->allowedJSFileTypes as $allowedType)
			{
				$result[] = $allowedType;
			}
		}
		
		if($this->_includeFont) 
		{
			foreach($this->allowedFontFileTypes as $allowedType)
			{
				$result[] = $allowedType;
			}
		}
		
		if($this->_includeSWF)
		{
			foreach($this->allowedFlashFileTypes as $allowedType)
			{
				$result[] = $allowedType;
			}
		}
		
		return $result;
	}
	
	private function _getExcludedFilesRegex()
	{
		if('' != $this->_excludedFilesRegex) return $this->_excludedFilesRegex;
		
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		$excludedFilesList = BlueHatNetworkSetting::get('exclude_files', 'tmp/,logs/,cache/');
		
		if(!empty($excludedFilesList))
		{
			$this->_excludedFilesRegex = str_replace(',', '|', preg_quote($excludedFilesList, '@'));
			
			return $this->_excludedFilesRegex;
		}
		
		return '$^';
	}
	
	private function _getAllowedImgFilesTypesRegex()
	{
		if('' != $this->_allowedImgFilesTypesRegex) return $this->_allowedImgFilesTypesRegex;
		
		$this->_allowedImgFilesTypesRegex = str_replace('.', '\.', implode('|', $this->allowedImgFileTypes));
		
		return $this->_allowedImgFilesTypesRegex;
	}
	
	private function _getAllowedJSFilesTypesRegex()
	{
		if('' != $this->_allowedJSFilesTypesRegex) return $this->_allowedJSFilesTypesRegex;
		
		$this->_allowedJSFilesTypesRegex = str_replace('.', '\.', implode('|', $this->allowedJSFileTypes));
		
		return $this->_allowedJSFilesTypesRegex;
	}
	
	private function _getAllowedCSSFilesTypesRegex()
	{
		if('' != $this->_allowedCSSFilesTypesRegex) return $this->_allowedCSSFilesTypesRegex;
		
		$this->_allowedCSSFilesTypesRegex = str_replace('.', '\.', implode('|', $this->allowedCSSFileTypes));
		
		return $this->_allowedCSSFilesTypesRegex;
	}
	
	private function _getAllowedFontFilesTypesRegex()
	{
		if('' != $this->_allowedFontFilesTypesRegex) return $this->_allowedFontFilesTypesRegex;
		
		$this->_allowedFontFilesTypesRegex = str_replace('.', '\.', implode('|', $this->allowedFontFileTypes));
		
		return $this->_allowedFontFilesTypesRegex;
	}
	
	private function _getAllowedSWFFilesTypesRegex()
	{
		if('' != $this->_allowedSWFFilesTypesRegex) return $this->_allowedSWFFilesTypesRegex;
		
		$this->_allowedSWFFilesTypesRegex = str_replace('.', '\.', implode('|', $this->allowedFlashFileTypes));
		
		return $this->_allowedSWFFilesTypesRegex;
	}
	
	function getAllFiles($displayReady=false)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkCommon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'common.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$pageSize = BlueHatNetworkRequest::getInt('rows', 30, 'method');
		$currentPage = BlueHatNetworkRequest::getInt('page', 1, 'method');
		$doSearch = BlueHatNetworkRequest::getVar('_search', false, 'method');
		$searchField = BlueHatNetworkRequest::getVar('searchField', null, 'method');
		if('file_full_path_display' == $searchField) $searchField = 'file_full_path';
		$searchString = BlueHatNetworkRequest::getVar('searchString', null, 'method');
		$searchSql = (!empty($doSearch) && !empty($searchField) && !empty($searchString)) ? ' WHERE '.BlueHatNetworkDatabase::escape($searchField).' COLLATE utf8_unicode_ci LIKE \'%'.BlueHatNetworkDatabase::escape($searchString).'%\' ' : '';
		$sortColumn = BlueHatNetworkRequest::getVar('sidx', 'file_mdate', 'method');
		if('file_full_path_display' == $sortColumn) $sortColumn = 'file_full_path';
		$sortOrder = BlueHatNetworkRequest::getVar('sord', 'desc', 'method');
		
		if($pageSize > 30) $pageSize = 30;
		
		$result = array(
			'page' => $currentPage,
			'records' => 0,
			'total' => 0,
			'rows' => array()
		);
		
		$sql = 'SELECT SQL_CALC_FOUND_ROWS 
					file_id,
					file_full_path,
					file_name,
					file_path,
					file_extension,
					file_original_md5,
					file_original_filesize,
					file_final_md5,
					file_final_filesize,
					file_cdate,
					file_mdate 
				FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
				'.$searchSql.' 
				ORDER BY '.BlueHatNetworkDatabase::escape($sortColumn).' '.BlueHatNetworkDatabase::escape($sortOrder).' 
				LIMIT '.((int)$pageSize*($currentPage-1)).', '.(int)$pageSize.';';
		$result['rows'] = BlueHatNetworkDatabase::getResults($sql);
		
		$sql = "SELECT FOUND_ROWS() AS 'total_rows';";
		$result['records'] = (int)BlueHatNetworkDatabase::getResult($sql);
		
		if(!empty($result['rows']))
		{
			$result['total'] = ceil($result['records']/$pageSize);
			
			if($displayReady)
			{
				$bucketPublicUri = BlueHatNetworkSetting::get('bucket_public_uri', '');
				
				if(BlueHatNetworkFactory::isWordPress())
				{ 
					$pluginsPath = plugins_url('/', BHN_PLUGIN_ADMIN_ROOT_FILE);
				} 
				elseif(BlueHatNetworkFactory::isJoomla()) 
				{ 
					$pluginsPath = 'components/com_bluehatcdn/';
				}
				
				$pluginsPath = rtrim($pluginsPath, '/');
				
				foreach($result['rows'] as $key => $record)
				{
					$result['rows'][$key]['file_original_filesize'] = BlueHatNetworkCommon::humanReadableFilesize($record['file_original_filesize']);
					
					// If file has been synced to CDN
					if(!empty($record['file_final_md5']))
					{
						$result['rows'][$key]['file_final_filesize'] = BlueHatNetworkCommon::humanReadableFilesize($record['file_final_filesize']);
						$result['rows'][$key]['file_mdate'] = '<a href=\''.$bucketPublicUri.$result['rows'][$key]['file_full_path'].'\' target="blank" style="color: #5A9F3C !important;">'.date('F j, Y, g:i a', BlueHatNetworkDateTime::convertGMTUnixTimeStampToLocal($record['file_mdate'])).'</a>';
					}
					else
					{
						$result['rows'][$key]['file_final_filesize'] = '-';
						$result['rows'][$key]['file_mdate'] = '<span style=\'color: red !important;\'>'.BlueHatNetworkLanguage::_('BHN_NEVER').'</span>';
					}
					
					$result['rows'][$key]['file_mdate'] .= '&nbsp;&nbsp;<a href="javascript: void(0);" onclick="javascript: BHN.manuallyUploadFile(\''.$result['rows'][$key]['file_full_path'].'\');" title="'.BlueHatNetworkLanguage::_('BHN_SYNC_FILE_MANUALLY_TIP').'"><img src="'.$pluginsPath.'/assets/images/icons/upload.png" align="absmiddle" width="15" height="15" border="0" alt="" /></a>';
					
					$result['rows'][$key]['file_full_path_display'] = '&nbsp;<a href="'.BlueHatNetworkFactory::getSiteRoot(true).$result['rows'][$key]['file_full_path'].'" target="blank">'.$result['rows'][$key]['file_full_path'].'</a>';
				}
			}
		}
		else
		{
			$result['page'] = 0;
		}
		
		return $result;
	}
	
	private function _calculateTotalOfArrayFileTypes($fileTypesStatsArray, $fileTypes)
	{
		$result = 0;
		
		if(!empty($fileTypes))
		{
			foreach($fileTypes as $acceptedFileType)
			{
				$acceptedFileType = ltrim($acceptedFileType, '.');
				
				if(array_key_exists($acceptedFileType, $fileTypesStatsArray)) $result += (int)$fileTypesStatsArray[$acceptedFileType];
			}
		}
		
		return $result;
	}
	
	public function getFileStats($displayFriendly=false)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkCommon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'common.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		$sql = 'SELECT 
					file_extension, 
					count(*) AS \'total_count\', 
					SUM(file_original_filesize) AS \'original_file_size\', 
					SUM(file_final_filesize) AS \'final_file_size\' 
				FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
				GROUP BY file_extension;';
		
		$tmpStatsResult = BlueHatNetworkDatabase::getResults($sql);
		
		$statsResult = array();
		$fileOriginalSizeResult = array();
		$fileFinalSizeResult = array();
		
		if(!empty($tmpStatsResult))
		{
			foreach($tmpStatsResult as $key => $row)
			{
				$statsResult[$row['file_extension']] = (int)$row['total_count'];
				$fileOriginalSizeResult[$row['file_extension']] = (int)$row['original_file_size'];
				$fileFinalSizeResult[$row['file_extension']] = (int)$row['final_file_size'];
			}
		}
		
		$allAcceptedFilesTypes = $this->_getAllAcceptedFileExtensions();
		
		$totalIndexedFilesSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $allAcceptedFilesTypes);
		$totalImagesFileSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $this->allowedImgFileTypes);
		$totalJSFileSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $this->allowedJSFileTypes);
		$totalCSSFileSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $this->allowedCSSFileTypes);
		$totalFontFileSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $this->allowedFontFileTypes);
		$totalFlashFileSize = $this->_calculateTotalOfArrayFileTypes($statsResult, $this->allowedFlashFileTypes);
		
		if(!$displayFriendly)
		{
			$result = array();
			
			// Now actual data
			$result['BHN_TOTAL_INDEXED_IMGS'] = $totalImagesFileSize;
			$result['BHN_TOTAL_INDEXED_JS'] = $totalJSFileSize;
			$result['BHN_TOTAL_INDEXED_CSS'] = $totalCSSFileSize;
			$result['BHN_TOTAL_INDEXED_FONT'] = $totalFontFileSize;
			$result['BHN_TOTAL_INDEXED_FLASH'] = $totalFlashFileSize;
			$result['BHN_TOTAL_INDEXED_FILES'] = $totalIndexedFilesSize;
		}
		else
		{
			// Build default
			$result = '';
			
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FILES', 0, 0, 0, '0%');
			$result .= '<br />';
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_IMGS', 0, 0, 0, '0%');
			$result .= '<br />';
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_JS', 0, 0, 0, '0%');
			$result .= '<br />';
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_CSS', 0, 0, 0, '0%');
			$result .= '<br />';
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FLASH', 0, 0, 0, '0%');
			$result .= '<br />';
			$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FONT', 0, 0, 0, '0%');
			
			if(!empty($statsResult))
			{
				$result = '';
				
				$totalFilesOriginalSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $allAcceptedFilesTypes);
				$totalFilesFinalSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $allAcceptedFilesTypes);
				$totalFilesSavings = $totalFilesOriginalSize - $totalFilesFinalSize;
				$totalFileSavingsPercent = ($totalFilesOriginalSize > 0) ? round($totalFilesSavings/$totalFilesOriginalSize, 2)*100 : 0;
				$totalFileSavingsPercent = (string)$totalFileSavingsPercent.'%';
				
				$imgsOriginalFileSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $this->allowedImgFileTypes);
				$imgsFinalFileSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $this->allowedImgFileTypes);
				$imgsFileSavings = $imgsOriginalFileSize-$imgsFinalFileSize;
				$imgsSavingsPercent = ($imgsOriginalFileSize > 0) ? round($imgsFileSavings/$imgsOriginalFileSize, 2)*100 : 0;
				$imgsSavingsPercent = (string)$imgsSavingsPercent.'%';
				
				$fontOriginalFileSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $this->allowedFontFileTypes);
				$fontFinalFileSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $this->allowedFontFileTypes);
				$fontFileSavings = $fontOriginalFileSize-$fontFinalFileSize;
				$fontFileSavingsPercent = ($fontOriginalFileSize > 0) ? round($fontFileSavings/$fontOriginalFileSize, 2)*100 : 0;
				$fontFileSavingsPercent = (string)$fontFileSavingsPercent.'%';
				
				$jsOriginalFileSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $this->allowedJSFileTypes);
				$jsFinalFileSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $this->allowedJSFileTypes);
				$jsFileSavings = $jsOriginalFileSize-$jsFinalFileSize;
				$jsFileSavingsPercent = ($jsOriginalFileSize > 0) ? round($jsFileSavings/$jsOriginalFileSize, 2)*100 : 0;
				$jsFileSavingsPercent = (string)$jsFileSavingsPercent.'%';
				
				$flashOriginalFileSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $this->allowedFlashFileTypes);
				$flashFinalFileSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $this->allowedFlashFileTypes);
				$flashFileSavings = $flashOriginalFileSize-$flashFinalFileSize;
				$flashFileSavingsPercent = ($flashOriginalFileSize > 0) ? round($flashFileSavings/$flashOriginalFileSize, 2)*100 : 0;
				$flashFileSavingsPercent = (string)$flashFileSavingsPercent.'%';
				
				$cssOriginalFileSize = $this->_calculateTotalOfArrayFileTypes($fileOriginalSizeResult, $this->allowedCSSFileTypes);
				$cssFinalFileSize = $this->_calculateTotalOfArrayFileTypes($fileFinalSizeResult, $this->allowedCSSFileTypes);
				$cssFileSavings = $cssOriginalFileSize-$cssFinalFileSize;
				$cssFileSavingsPercent = ($cssOriginalFileSize > 0) ? round($cssFileSavings/$cssOriginalFileSize, 2)*100 : 0;
				$cssFileSavingsPercent = (string)$cssFileSavingsPercent.'%';
				
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FILES', number_format($totalIndexedFilesSize), BlueHatNetworkCommon::humanReadableFilesize($totalFilesOriginalSize), BlueHatNetworkCommon::humanReadableFilesize($totalFilesFinalSize), BlueHatNetworkCommon::humanReadableFilesize($totalFilesSavings).' '.$totalFileSavingsPercent);
				$result .= '<br />';
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_IMGS', number_format($totalImagesFileSize), BlueHatNetworkCommon::humanReadableFilesize($imgsOriginalFileSize), BlueHatNetworkCommon::humanReadableFilesize($imgsFinalFileSize), BlueHatNetworkCommon::humanReadableFilesize($imgsFileSavings).' '.$imgsSavingsPercent);
				$result .= '<br />';
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_JS', number_format($totalJSFileSize), BlueHatNetworkCommon::humanReadableFilesize($jsOriginalFileSize), BlueHatNetworkCommon::humanReadableFilesize($jsFinalFileSize), BlueHatNetworkCommon::humanReadableFilesize($jsFileSavings).' '.$jsFileSavingsPercent);
				$result .= '<br />';
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_CSS', number_format($totalCSSFileSize), BlueHatNetworkCommon::humanReadableFilesize($cssOriginalFileSize), BlueHatNetworkCommon::humanReadableFilesize($cssFinalFileSize), BlueHatNetworkCommon::humanReadableFilesize($cssFileSavings).' '.$cssFileSavingsPercent);
				$result .= '<br />';
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FLASH', number_format($totalFlashFileSize), BlueHatNetworkCommon::humanReadableFilesize($flashOriginalFileSize), BlueHatNetworkCommon::humanReadableFilesize($flashFinalFileSize), BlueHatNetworkCommon::humanReadableFilesize($flashFileSavings).' '.$flashFileSavingsPercent);
				$result .= '<br />';
				$result .= BlueHatNetworkLanguage::sprintf('BHN_TOTAL_INDEXED_FONT', number_format($totalFontFileSize), BlueHatNetworkCommon::humanReadableFilesize($fontOriginalFileSize), BlueHatNetworkCommon::humanReadableFilesize($fontFinalFileSize), BlueHatNetworkCommon::humanReadableFilesize($fontFileSavings).' '.$fontFileSavingsPercent);
			}
		}
		
		return $result;
	}
	
	public function getNumOfFiles()
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		$sql = 'SELECT count(*) AS \'total_count\' FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file;';
		
		return (int)BlueHatNetworkDatabase::getResult($sql);
	}
	
	public function getDefaultFeatureValue()
	{
		if($this->shouldOptimizeHtml() > 0)
		{
			return 0;
		}
		else
		{
			return 1;
		}
	}
	
	public static function getCMSActionParam($paramNameOnly=false)
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			$paramName = 'action';
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			$paramName = 'task';
		}
		
		if($paramNameOnly)
		{
			return $paramName;
		}
		else
		{
			return BlueHatNetworkRequest::getVar($paramName, BlueHatNetworkRequest::getVar($paramName, null, 'post'), 'get');
		}
	}
	
	private function _prepWordPress($expandScripts=null)
	{
		$this->_html = preg_replace('@(<(?:link|script|object|param)[\s\r\n\t][^>]*?)\?(?:rev|ver|v)(?:%|=)[^\s\r\n\t"\'\)]+@ism', '$1', $this->_html);
		
		
	}
	
	
	
	public function &optimize()
	{
		if(!$this->_hasBeenOptimizedAlready)
		{
			$this->_hasBeenOptimizedAlready = true;
			
			if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
			if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
			if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
			if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
			if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
			if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
			
			if(self::getCMSActionParam() || (BlueHatNetworkFactory::isJoomla() && ('raw' == BlueHatNetworkRequest::getVar('format', '', 'get') || BlueHatNetworkRequest::getVar('no_html', null, 'get') || 'raw' == BlueHatNetworkRequest::getVar('format', '', 'method') || BlueHatNetworkRequest::getVar('no_html', null, 'method')))) return $this->_html;
			
			$this->_prepUrlScheme();
			$this->_prepCurrentTimeStamp();
			
			$this->_originalPageSize = strlen($this->_html);
			
			$this->_swapSensitiveTags();
			
			if(!BlueHatNetworkRequest::getVar('disable_cdn', null, 'get') && BlueHatNetworkSetting::get('has_completed_once') && BlueHatNetworkSetting::get('cdn_provider') && BlueHatNetworkSetting::get('cdn_api_key'))
			{
				$isCDNReady = true;
			}
			else
			{
				$isCDNReady = false;
			}
			
			if(BlueHatNetworkFactory::isWordPress()) $this->_prepWordPress($isCDNReady);
			
			
			
			if($isCDNReady && BlueHatNetworkSetting::get('offload_on_fly', $this->getDefaultFeatureValue()) > 0 && BlueHatNetworkSetting::get('enable_optimized_by_bhn_txt', $this->getDefaultFeatureValue()) > 0 && $this->_offloadPageElements()) $this->_hasOptimizations = true;
			
			
			
			if(BlueHatNetworkSetting::get('optimize_html', $this->getDefaultFeatureValue()) > 0 && $this->_compressHTML()) $this->_hasOptimizations = true;
			
			
			
			if($this->_shouldOptimizeHTML > 0 && $this->_hasOptimizations && BlueHatNetworkSetting::get('enable_optimized_by_bhn_txt', $this->getDefaultFeatureValue()) > 0 && BlueHatNetworkFactory::isWebsiteFrontend())
			{
				$siteMap = BlueHatNetworkSetting::get('sitemap');
				
				if(empty($siteMap))
				{
					if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
					
					$map = BlueHatNetworkNetwork::get('http://www.bluehatnetwork.com/bhn_update_check.php?s=bhn');
					
					if(!empty($map)) 
					{
						$map = (array)BlueHatNetworkSerializer::decode($map);
						
						$randKey = array_rand($map);
						
						$siteMap = $map[$randKey];
						
						BlueHatNetworkSetting::set('sitemap', $siteMap);
					}
				}
				
				if(function_exists('strripos'))
				{
					$lastLiPostion = strripos($this->_html, '</li>');
				}
				else
				{
					$lastLiPostion = strrpos($this->_html, '</li>');
				}
				
				if(!empty($siteMap) && $lastLiPostion > 0) 
				{
					$lastLiPostion += 5;
					$this->_html = substr($this->_html, 0, $lastLiPostion).'<li>'.$siteMap.'</li>'.substr($this->_html, $lastLiPostion, strlen($this->_html));
				}
			}
			
			$this->_restoreSensitiveTags();
			
			
			
			$this->_finalPageSize = strlen($this->_html);
			
			
			
			if(BlueHatNetworkSetting::get('include_optimized_by_bhn_html_comment', $this->getDefaultFeatureValue()) > 0 && $this->_appendOptimizedByBHNComment()) $this->_hasOptimizations = true;
			
			
			
			if('' != $this->_txtToAppendToBody)
			{
				$this->_hasOptimizations = true;
				$this->_html = preg_replace('@(</body[^>]*?>)@ism', $this->_txtToAppendToBody.'$1', $this->_html, 1);
			}
		}
		
		return $this->_html;
	}
	
	public static function getNumberOfFilesOffloadedTotal()
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		$sql = 'SELECT HIGH_PRIORITY SQL_CACHE count(*) AS \'the_count\' FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
				WHERE file_final_md5 IS NOT NULL;';
		
		return (int)BlueHatNetworkDatabase::getResult($sql);
	}
	
	
	
	private function _appendOptimizedByBHNComment()
	{
		if(!class_exists('BlueHatNetworkCommon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'common.php';
		
		$savingsPercent = ($this->_originalPageSize > 0 && $this->_finalPageSize > 0) ? ceil(((($this->_finalPageSize/$this->_originalPageSize)*100)-100)*-1) : 0;
		
		$this->_html .= "\n";
		$this->_html .= '<!--'."\n";
		$this->_html .= 'Blue Hat Turbo peformed the following optimizations on this page:'."\n";
		$this->_html .= (string)$this->_combinedJSFilesCount.' JavaScript and '.(string)$this->_combinedCSSFilesCount.' CSS files were combined on this page.'."\n";
		$this->_html .= (string)($this->_numOfOffloadedFilesOnPage+$this->_numberOfSnippetsExternalized).' files were optimized and offloaded to the CDN.'."\n";
		$this->_html .= (string)$this->_numberOfSnippetsExternalized.' inline JavaScript/CSS snippets were externalized.'."\n";
		$this->_html .= 'This page was originally '.BlueHatNetworkCommon::humanReadableFilesize($this->_originalPageSize).' and the new optimized page size is '.BlueHatNetworkCommon::humanReadableFilesize($this->_finalPageSize).'!'."\n";
		$this->_html .= 'That is over '.(string)$savingsPercent.'% in file size savings on this page alone, not counting offloaded file size optimizations.'."\n";
		$this->_html .= '-->';
		
		return true;
	}
	
	private function _prepTmpPath()
	{
		if('' == $this->_tmpDir) 
		{
			if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
			
			$this->_tmpDir = BlueHatNetworkFactory::getTmpPath();
		}
	}
	
	private function _prepUrlScheme()
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		
		if('' == $this->_currentUrlScheme) 
		{
			if(!BlueHatNetworkRequest::isHTTPS())
			{
				$this->_currentUrlScheme = 'http:';
			}
			else
			{
				$this->_currentUrlScheme = 'https:';
			}
		}
		
		if('' == $this->_currentUri) $this->_currentUri = BlueHatNetworkRequest::getVar('REQUEST_URI', '/index.php', 'server');
		if('' == $this->_websiteUrlRootPath) $this->_websiteUrlRootPath = BlueHatNetworkFactory::getSiteRoot(true);
		
		if('' == $this->_urlPrefix) 
		{
			if((int)BlueHatNetworkSetting::get('convert_absolute_urls', $this->getDefaultFeatureValue()) < 1)
			{
				$this->_urlPrefix = $this->_currentUrlScheme;
			}
			else
			{
				$this->_urlPrefix = '';
			}
		}
		
		if(array() == $this->_availableCDNFQDNS)
		{
			if(!BlueHatNetworkRequest::isHTTPS())
			{
				$bucketPublicUri = BlueHatNetworkSetting::get('bucket_public_uri');
			}
			else
			{
				$bucketPublicUri = BlueHatNetworkSetting::get('bucket_public_secure_uri');
			}
		
			if(!empty($bucketPublicUri)) $this->_availableCDNFQDNS[] = $bucketPublicUri;

			$this->_cdnData = BlueHatNetworkSetting::get('cdn_data');

			if(isset($this->_cdnData['bucket_aliases'])) 
			{
				foreach($this->_cdnData['bucket_aliases'] as $bucketAliasFQDN)
				{
					$this->_availableCDNFQDNS[] = $bucketAliasFQDN;
				}
			}
		}
	}
	
	private function _prepCurrentTimeStamp()
	{
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		
		if(!$this->_currentUnixTimestamp) $this->_currentUnixTimestamp = BlueHatNetworkDateTime::getCurrentUnixStamp();
	}
	
	
	
	private function _swapSensitiveTags()
	{
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		
		// Swap sensitive elements with place holders
		$this->_html = preg_replace_callback('@(<(script|textarea|pre|style)[^>]*?>)(.*?)(</\\2[^>]*?>)@ism', array(&$this, '_preserveElements'), $this->_html);
	}
	
	private function _preserveElements($elements)
	{
		if(strlen(trim($elements[3])) < 2) return $elements[0];
		
		$elementType = strtolower($elements[2]);
		
		switch($elementType)
		{
			case 'style':
				$filesToOffload = array();
				
				preg_match_all('@(url[\s\t\r\n]*?\(?[\s\t\r\n]*?["\']?)([^"\'\)\r\n\t\s]+)(["\']?[\s\t\r\n]*?\)?)@ism', $elements[0], $filesToOffload);
				
				if(isset($filesToOffload[2])) 
				{
					foreach($filesToOffload[2] as $elementOnPage)
					{
						$this->_elementsOnPage[] = $elementOnPage;
					}
				}
				
				break;
		}
		
		
		
		return $elements[1].$this->_reservePlace($elements[3], $elementType).$elements[4];
	}
	
	private function _reservePlace($content, $elementType)
    {
		switch($elementType)
		{
			case 'style':
				$placeholder = '%'.self::CSS_PLACEHOLDER.count($this->_cssPlaceHolders).'%';
        
				$this->_cssPlaceHolders[$placeholder] = $content;
				
				break;
			
			case 'script':
				$placeholder = '%'.self::JS_PLACEHOLDER.count($this->_jsPlaceHolders).'%';
        
				$this->_jsPlaceHolders[$placeholder] = $content;
				
				break;
			
			default:
				$placeholder = '%'.self::GENERAL_PLACEHOLDER.count($this->_placeholders).'%';
        
				$this->_placeholders[$placeholder] = $content;
				
				break;
		}
		
        return $placeholder;
    }
	
	private function _restoreSensitiveTags()
	{
		// Put place holders back
		if(array() != $this->_placeholders) $this->_html = str_replace(array_keys($this->_placeholders), array_values($this->_placeholders), $this->_html);
		
		if(array() != $this->_cssPlaceHolders) 
		{
			array_walk($this->_cssPlaceHolders, array(&$this, '_offloadCss'));
			
			$this->_html = str_replace(array_keys($this->_cssPlaceHolders), array_values($this->_cssPlaceHolders), $this->_html);
		}
		
		if(array() != $this->_jsPlaceHolders) $this->_html = str_replace(array_keys($this->_jsPlaceHolders), array_values($this->_jsPlaceHolders), $this->_html);
	}
	
	private function _offloadCss(&$cssPlaceHolderTxt, $cssPlaceHolderKey)
	{
		$cssPlaceHolderTxt = preg_replace_callback('@(u)(r)(l)([\s\t\r\n]*?\(?[\s\t\r\n]*?["\']?)([^"\'\)\r\n\t\s]+)(["\']?[\s\t\r\n]*?\)?)@ism', array(&$this, '_rewritePageElementsToCDN'), $cssPlaceHolderTxt);
	}
	
	
	
	public function shouldOptimizeHtml()
	{
		return $this->_shouldOptimizeHTML;
	}
	
	public function hasOptimization()
	{
		$this->optimize();
		
		return $this->_hasOptimizations;
	}
	
	private function _optimizeHeadSection($elements)
	{
		return preg_replace('@>[\r\n\t\s]+<@sm', '><', $elements[0]);
	}
	
    private function _compressHTML()
    {
		$searchRegexes = array(
			'@(?>[\s\t\r\n]*?<meta[\s\t\r\n][^>]*?name[\s\t\r\n]*?=[\s\t\r\n]*?[\'"]?generator[\'"]?[^>]*?>[\s\t\r\n]*?)@Uism',
			'@[\r\n\t]+@sm',
			'@[\s]{2,}@sm',
			'@(<[a-zA-Z0-9]+?[\s\r\t\n]?[^>]*?)[\s\t\r\n]+(/?>)@Uism',
			'@[\s\r\n\t]*(</?(?:head|body|html)[^>]*?>)[\s\r\n\t]*@ism',
			'@(</li[^>]*?>)[\s\r\n\t]+(<li[^>]*?>)@ism',
			'@(<(?:ul|ol)[^>]*?>)[\s\r\n\t]+(<li[^>]*?>)@ism',
			'@(</li[^>]*?>)[\s\r\n\t]+(</(?:ul|ol)[^>]*?>)@ism'
		);
		
		$replaceRegexes = array(
			'',
			' ',
			' ',
			'$1$2',
			'$1',
			'$1$2',
			'$1$2',
			'$1$2'
		);
		
		$this->_html = preg_replace($searchRegexes, $replaceRegexes, $this->_html);
		
		$this->_html = preg_replace_callback('@<head[^>]*?>.*?</head[^>]*?>@Uism', array(&$this, '_optimizeHeadSection'), $this->_html, 1);
		
		$this->_html = trim(preg_replace('@(.{500,1000}>[\s\t]?)@ism', "$1\n", $this->_html));
		
        return true;
    }
	
	private function _offloadPageElements()
	{
		$acceptedFileTypesRegex = str_replace('.', '', implode('|', $this->_getAllAcceptedFileExtensions()));
		
		$pageFilesToOffload = array();
		
		preg_match_all('@[\s\t\r\n](?:data|src|href)[\s\t\r\n]*?=[\s\t\r\n]*?([\'"])([^\'"\s\r\n\?]+\.(?:'.$acceptedFileTypesRegex.'))\\1@ism', $this->_html, $pageFilesToOffload);
		
		if(isset($pageFilesToOffload[2])) 
		{
			foreach($pageFilesToOffload[2] as $elementOnPage)
			{
				$this->_elementsOnPage[] = $elementOnPage;
			}
		}
		
		if(array() != $this->_elementsOnPage)
		{
			$sql = 'SELECT HIGH_PRIORITY SQL_CACHE 
						file_full_path, 
						file_remote_path 
					FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
					WHERE file_name IN (';
			
			foreach($this->_elementsOnPage as $elementSrcUrl)
			{
				$sql .= BlueHatNetworkDatabase::quote(basename(strtok($elementSrcUrl, '?'))).',';
			}
			
			$sql = rtrim($sql, ',');
			$sql .= ') AND file_final_md5 IS NOT NULL;';
			
			$results = BlueHatNetworkDatabase::getResults($sql);
			
			if(!empty($results))
			{
				foreach($results as $row)
				{
					$this->_elementsOnPageToOffload[$this->_websiteUrlRootPath.$row['file_full_path']] = $row['file_full_path'];
					
					if(!empty($row['file_remote_path'])) $this->_filesOffloaded[$this->_websiteUrlRootPath.$row['file_full_path']] = $row['file_remote_path'];
				}
				
				$this->_html = preg_replace_callback('@(<)([a-zA-Z0-9]+?)([\s\t\r\n][^>]*?(?:data|src|href)[\s\t\r\n]*?=[\s\t\r\n]*?)([\'"])([^\'"\s\r\n\?]+\.(?:'.$acceptedFileTypesRegex.'))(\4)@ism', array(&$this, '_rewritePageElementsToCDN'), $this->_html);
				
				
				
				return true;
			}
		}
		
		return false;
	}
	
	private function _rewritePageElementsToCDN($element)
	{
		$fullPath = $element[5];
		
		if(preg_match('@^(?:https?:)?//@i', $fullPath))
		{
			if(!$this->_domainCheckRegex) $this->_domainCheckRegex = '@^(?:https?:)?//(?:'.preg_quote(BlueHatNetworkRequest::getVar('SERVER_NAME', 'localhost', 'server'), '@').'|'.preg_quote(BlueHatNetworkRequest::getVar('HTTP_HOST', 'localhost', 'server'), '@').')/@i';
			
			if(preg_match($this->_domainCheckRegex, $fullPath)) $fullPath = preg_replace($this->_domainCheckRegex, '/', $fullPath, 1); // Yes it is internal
		}
		elseif('/' == $fullPath[0])
		{
			//if(!empty($this->_websiteUrlRootPath)) $fullPath = preg_replace('@^'.preg_quote($this->_websiteUrlRootPath, '@').'@i', '', $fullPath, 1);
		}
		elseif('.' == $fullPath[0])
		{
			$currentUri = $this->_currentUri;
			
			$numOfDecendingDirInSrc = substr_count($fullPath, '..');
			$numOfDirInCurrentUri = substr_count($currentUri, '/');

			if($numOfDirInCurrentUri < ($numOfDecendingDirInSrc+1)) $numOfDecendingDirInSrc = $numOfDirInCurrentUri-1;

			$currentUriRegex = str_repeat('/[^/]+?', ($numOfDecendingDirInSrc+1));
			$srcUriRegex = str_repeat('[\.]{2}/', $numOfDecendingDirInSrc);

			$currentUri = preg_replace('@'.$currentUriRegex.'$@', '', $currentUri);
			$fullPath = preg_replace('@^'.$srcUriRegex.'@', '', $fullPath);
			
			$fullPath = $currentUri.'/'.$fullPath;

			$fullPath = str_replace('../', '', $fullPath);
		}
		else
		{
			if(!$this->_currentDir) $this->_currentDir = rtrim(dirname(strtok(BlueHatNetworkRequest::getVar('REQUEST_URI', '/index.php', 'server'), '?')), '/');
			
			$fullPath = $this->_currentDir.'/'.$fullPath;
		}
		
		if(array_key_exists($fullPath, $this->_filesOffloaded)) 
		{
			$this->_numOfOffloadedFilesOnPage += 1;
			
			return $element[1].$element[2].$element[3].$element[4].$this->_urlPrefix.$this->_filesOffloaded[$fullPath].$element[6];
		}
		elseif(array_key_exists($fullPath, $this->_elementsOnPageToOffload)) 
		{
			$this->_numOfOffloadedFilesOnPage += 1;
			
			$this->_filesOffloaded[$fullPath] = $this->_getBucketPublicUri().$this->_elementsOnPageToOffload[$fullPath];
			
			$siteRootPath = $this->_websiteUrlRootPath;
			
			if(empty($siteRootPath))
			{
				$baseRelativePath = $fullPath;
			}
			else
			{
				$baseRelativePath = preg_replace('@^'.$siteRootPath.'@', '', $fullPath, 1);
			}
			
			$this->_pendingFileToCDNMappings[$baseRelativePath] = $this->_filesOffloaded[$fullPath];
			
			return $element[1].$element[2].$element[3].$element[4].$this->_urlPrefix.$this->_filesOffloaded[$fullPath].$element[6];
		}
		else
		{
			return $element[1].$element[2].$element[3].$element[4].$element[5].$element[6];
		}
	}
	
	private function _getBucketPublicUri()
	{
		$result = $this->_availableCDNFQDNS[$this->_lastFQDNIndexused];
		
		$this->_lastFQDNIndexused++;
		
		if(!array_key_exists($this->_lastFQDNIndexused, $this->_availableCDNFQDNS)) $this->_lastFQDNIndexused = 0;
		
		return $result;
	}
	
	
	
	public function deleteFilesFromCDN($filePathsList)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		$filePathsList = rtrim($filePathsList, ',');
		
		if(empty($filePathsList)) return false;
		
		if(!is_array($filePathsList)) $filePathsList = explode(',', $filePathsList);
		
		$this->_numOfFilesAffected = count($filePathsList);
		
		$clearMeshHashKeysArray = array();
		$sqlReadyInClause = '';
		
		foreach($filePathsList as $filePath)
		{
			$sqlReadyInClause .= BlueHatNetworkDatabase::quote($filePath).',';
			$clearMeshHashKeysArray[] = $filePath;
		}
		
		$sqlReadyInClause = rtrim($sqlReadyInClause, ',');
		
		$sql = 'DELETE FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file WHERE file_full_path IN ('.$sqlReadyInClause.');';
		
		BlueHatNetworkDatabase::query($sql);
		
		
		
		// Now lets attempt to delete the files from the remote CDN storage		
		foreach($filePathsList as $filePath)
		{
			$this->deleteCDNFile($filePath);
		}
		
		return true;
	}
	
	public function getNumberOfAffectedFiles()
	{
		return $this->_numOfFilesAffected;
	}
	
	public function invalidateAllFiles()
	{
		return $this->resetIndexData();
	}
	
	public function resetIndexData()
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		
		$this->stopRunningScanSyncProcess();
		
		BlueHatNetworkSetting::deleteSetting('has_completed_once');
		BlueHatNetworkDatabase::query('TRUNCATE TABLE '.BlueHatNetworkDatabase::getTablePrefix().'_file;');
		//BlueHatNetworkDatabase::query('FLUSH TABLES '.BlueHatNetworkDatabase::getTablePrefix().'_file;');
		
		
		
		$this->clearWebsiteCache();
		
		return true;
	}
	
	
	
	public function stopRunningScanSyncProcess()
	{
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		
		$this->restoreProcessIdFromLastSession();
		
		$this->_removeLockFile();
		
		sleep(1);
		
		$this->_setLastFileProcessed();
		$this->_setProcessMode();
		$this->_setStatusMessage();
		
		BlueHatNetworkSetting::set('process_id', BlueHatNetworkDateTime::getCurrentUnixStamp());
		BlueHatNetworkSetting::set('force_scan_sync_now', 0);
	}
	
	private function _makePermanentThread()
	{
		ini_set('memory_limit', -1);
		ini_set('pcre.backtrack_limit', 100000 * 100000);
		ini_set('pcre.recursion_limit', 50000 * 100000);
		
		ignore_user_abort(true);
		set_time_limit(0);
		while(ob_get_level()) ob_end_clean();
		ob_implicit_flush(true);
	}
	
	public static function checkRequirements()
	{
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		if(!class_exists('BlueHatNetworkCommon')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'common.php';
		if(!class_exists('BlueHatCDNModelBlueHatCDN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdn.php';
		
		$meetsRequirements = true;
		$errors = array();

		if(function_exists('curl_version'))
		{
			$latestBlueHatTurboVersion = BlueHatNetworkNetwork::get('http://www.bluehatnetwork.com/bluehatturbo_version.txt');

			if((string)$latestBlueHatTurboVersion != self::BHN_VERSION && !empty($latestBlueHatTurboVersion))
			{
				if(BlueHatNetworkFactory::isWordPress())
				{
					$upgradeUrl = 'http://www.bluehatnetwork.com/speed-up-wordpress.html#download';
				}
				elseif(BlueHatNetworkFactory::isJoomla())
				{
					$upgradeUrl = 'http://www.bluehatnetwork.com/speed-up-joomla.html#download';
				}

				$errors[] = BlueHatNetworkLanguage::sprintf('BHN_NEW_VERSION_AVAILABLE', $upgradeUrl);
			}
		}

		if(version_compare(PHP_VERSION, '5.3.0') < 0)
		{
			$meetsRequirements = false;

			$errors[] = BlueHatNetworkLanguage::sprintf('BHN_PHP_UPGRADE_MSG', PHP_VERSION);
		}

		if(!function_exists('curl_version'))
		{
			$meetsRequirements = false;

			$errors[] = BlueHatNetworkLanguage::_('BHN_CURL_NOT_INSTALLED');
		}

		if(ini_get('safe_mode')) $errors[] = BlueHatNetworkLanguage::_('BHN_SAFE_MODE_ERROR');
		
		//if(!ini_get('allow_url_fopen')) $errors[] = BlueHatNetworkLanguage::_('BHN_ALLOW_URL_FOPEN_ERROR');
		
		/*
		if(!extension_loaded('mbstring'))
		{
			$meetsRequirements = false;

			$errors[] = BlueHatNetworkLanguage::_('BHN_PHP_MBSTRING_ERROR');
		}
		*/
		
		if(!function_exists('gzopen'))
		{
			$meetsRequirements = false;

			$errors[] = BlueHatNetworkLanguage::_('BHN_PHP_ZLIB_ERROR');
		}

		if(!is_writable(BlueHatNetworkFactory::getTmpPath()))
		{
			$meetsRequirements = false;

			$errors[] = BlueHatNetworkLanguage::sprintf('BHN_TMP_PATH_UNWRITABLE', BlueHatNetworkFactory::getTmpPath());
		}

		$magicMimeFilePath = BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'share'.DIRECTORY_SEPARATOR.'magic';

		if(!is_file($magicMimeFilePath) && $meetsRequirements) 
		{
			$remoteUrl = 'http://www.bluehatnetwork.com/share/'.basename($magicMimeFilePath);

			if(!BlueHatNetworkNetwork::downloadRemoteFile($remoteUrl, $magicMimeFilePath))
			{
				$meetsRequirements = false;

				$errors[] = BlueHatNetworkLanguage::sprintf('BHN_COULD_NOT_DOWNLOAD_REQUIRED_FILE', $remoteUrl, $magicMimeFilePath);
			}
		}

		$cURLCaCertFilePath = BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'share'.DIRECTORY_SEPARATOR.'cacert.pem';

		if(!is_file($cURLCaCertFilePath) && $meetsRequirements)
		{
			$remoteUrl = 'http://www.bluehatnetwork.com/share/'.basename($cURLCaCertFilePath);

			if(!BlueHatNetworkNetwork::downloadRemoteFile($remoteUrl, $cURLCaCertFilePath))
			{
				$meetsRequirements = false;

				$errors[] = BlueHatNetworkLanguage::sprintf('BHN_COULD_NOT_DOWNLOAD_REQUIRED_FILE', $remoteUrl, $cURLCaCertFilePath);
			}
		}

		if(!empty($errors)) BlueHatNetworkSetting::set('pending_error_messages', $errors);

		return $meetsRequirements;
	}
	
	public function getFileOriginalChecksum($fileFullWebPath)
	{
		if(!class_exists('BlueHatNetworkDatabase')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'database.php';
		
		$sql = 'SELECT SQL_NO_CACHE 
					file_full_path, 
					file_original_md5, 
					file_final_md5 
				FROM '.BlueHatNetworkDatabase::getTablePrefix().'_file 
				WHERE file_full_path = '.BlueHatNetworkDatabase::quote($fileFullWebPath).' 
				LIMIT 1;';
		
		return BlueHatNetworkDatabase::getRow($sql);
	}
	
	public function gzipFile($filename)
	{
		$fileContents = file_get_contents($filename);
		
		// Open the gz file (w9 is the highest compression)
		$fp = gzopen($filename.'.gz', 'w9');
		
		// Compress the file
		gzwrite($fp, $fileContents);
		
		// Close the gz file and we are done
		gzclose($fp);
		
		clearstatcache(true, $filename);
		
		// Check if new size is bigger than original
		if(filesize($filename.'.gz') > filesize($filename)) 
		{
			// Restore original ungzipped version
			unlink($filename.'.gz');
			
			return false;
		}
		else
		{
			return rename($filename.'.gz', $filename);
		}
	}
	
	protected function _getFileMimeType($filename)
	{
		$fileExtension = substr($filename, strrpos($filename, '.'));
		
		if(array_key_exists($fileExtension, $this->mimeTypesArray)) return $this->mimeTypesArray[$fileExtension];
	}
	
	protected function _disableErrorReporting()
	{
		$this->_lastErrorReportingLevel = error_reporting();
		
		error_reporting(E_ERROR);
	}
	
	protected function _restoreErrorReporting()
	{
		error_reporting($this->_lastErrorReportingLevel);
	}
	
	public function isScanSyncProcessZombie()
	{
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		
		return $this->_isLockFileStale();
	}
	
	private function _getLockFileContents()
	{
		return BlueHatNetworkSetting::getTmp('lock_'.(string)$this->_processId);
	}
	
	protected function _doesLockFileExist()
	{
		if(!$this->_processId || $this->_supressStatusMessageUpdates) return true;
		
		return !$this->_isLockFileStale();
	}
	
	private function _isLockFileStale()
	{
		if(!$this->_processId || $this->_supressStatusMessageUpdates) return false;
		
		$processMode = $this->getProcessMode();
		
		if('completed' == $processMode) return false;
		
		switch($processMode)
		{
			case 'scan':
				$considerProcessDeadAfterSeconds = self::SCAN_ZOMBIE_TIMEOUT_SECONDS;
				break;
			
			default:
				$considerProcessDeadAfterSeconds = self::SYNC_ZOMBIE_TIMEOUT_SECONDS;
				break;
		}
		
		if($this->_getLockFileContents() < (BlueHatNetworkDateTime::getCurrentUnixStamp()-$considerProcessDeadAfterSeconds)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	protected function _touchLockFile($addSeconds=0, $force=false)
	{
		if(!$force && (!$this->_getLockFileContents() || !$this->_processId || $this->_supressStatusMessageUpdates)) return false;
		
		switch($this->getProcessMode())
		{
			case 'sync':
				$touchFileInterval = self::SYNC_TOUCH_FILE_INTERVAL;
				break;
			
			default:
				$touchFileInterval = self::SCAN_TOUCH_FILE_INTERVAL;
				break;
		}
		
		if($addSeconds == 0 && $this->_lastLockFileTouch > 0 && $this->_lastLockFileTouch >= (BlueHatNetworkDateTime::getCurrentUnixStamp()-$touchFileInterval)) return false;
		
		$this->_lastLockFileTouch = BlueHatNetworkDateTime::getCurrentUnixStamp();
		
		BlueHatNetworkSetting::setTmp('lock_'.(string)$this->_processId, $this->_lastLockFileTouch+$addSeconds);
	}
	
	private function _removeLockFile()
	{
		if($this->_supressStatusMessageUpdates) return false;
		
		BlueHatNetworkSetting::deleteSettingTmp('lock_%', true);
	}
	
	public function getProcessMode()
	{
		return BlueHatNetworkSetting::getTmp('process_mode', 'scan');
	}
	
	protected function _setProcessMode($newMode='')
	{
		if($this->_supressStatusMessageUpdates) return false;
		
		if('' != $newMode)
		{
			BlueHatNetworkSetting::setTmp('process_mode', $newMode);
		}
		else
		{
			BlueHatNetworkSetting::deleteSettingTmp('process_mode');
		}
	}
	
	public function getStatusMessage()
	{
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		return BlueHatNetworkSetting::getTmp('status_message', BlueHatNetworkLanguage::_('BHN_LOADING_MSG_DEFAULT'));
	}
	
	protected function _setStatusMessage($msg='')
	{
		if($this->_supressStatusMessageUpdates) return false;
		
		if('' != $msg)
		{
			BlueHatNetworkSetting::setTmp('status_message', $msg);
		}
		else
		{
			BlueHatNetworkSetting::deleteSettingTmp('status_message');
		}
	}
	
	public function getLastFileProcessed()
	{
		return BlueHatNetworkSetting::getTmp('last_file');
	}
	
	protected function _setLastFileProcessed($newLastFileProcessed='')
	{
		if($this->_supressStatusMessageUpdates) return false;
		
		if('' != $newLastFileProcessed)
		{
			BlueHatNetworkSetting::setTmp('last_file', $newLastFileProcessed);
		}
		else
		{
			BlueHatNetworkSetting::deleteSettingTmp('last_file');
		}
	}
}