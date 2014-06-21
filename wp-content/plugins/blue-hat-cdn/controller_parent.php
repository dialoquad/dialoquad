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

if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';

class BlueHatTurboController
{
	public static function display(&$parentControllerObj)
	{
		$view =& $parentControllerObj->getView('BlueHatCDN');
		$view->display();
	}
	
	public static function save_settings()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel('', BlueHatNetworkRequest::getVar('cdn_provider'));
		
		$isError = false;
		$errorMsg = '';
		
		if(!BlueHatNetworkRequest::getVar('cdn_provider')) 
		{
			$isError = true;
			$errorMsg .= BlueHatNetworkLanguage::_('BHN_SELECT_CDN_ERROR').'<br />';
		}
		else
		{
			// Check if CDN provider changed
			if(BlueHatNetworkRequest::getVar('cdn_provider') != BlueHatNetworkSetting::get('cdn_provider') || BlueHatNetworkRequest::getVar('cdn_username') != BlueHatNetworkSetting::get('cdn_username') || BlueHatNetworkRequest::getVar('cdn_api_key') != BlueHatNetworkSetting::get('cdn_api_key')) 
			{
				BlueHatNetworkSetting::deleteSetting('cdn_data');
				
				$model->invalidateAllFiles();
			}
			
			BlueHatNetworkSetting::set('cdn_provider', BlueHatNetworkRequest::getVar('cdn_provider'));
		}
		
		if(!BlueHatNetworkRequest::getVar('cdn_username') || !BlueHatNetworkRequest::getVar('cdn_api_key')) 
		{
			$isError = true;
			$errorMsg .= BlueHatNetworkLanguage::_('BHN_PROVIDE_VALID_API_CREDENTIALS').'<br />';
			
			BlueHatNetworkSetting::deleteSetting(array('cdn_api_key', 'cdn_data', 'bucket_public_secure_uri', 'bucket_public_uri'));
		}
		
		if(BlueHatNetworkRequest::getVar('cdn_username')) BlueHatNetworkSetting::set('cdn_username', BlueHatNetworkRequest::getVar('cdn_username', null, 'method'));
		
		if(!$isError)
		{
			// Validate API Key
			$validateAPICredentials = $model->validateCDNAPICredentials(BlueHatNetworkRequest::getVar('cdn_provider'), BlueHatNetworkRequest::getVar('cdn_username'), BlueHatNetworkRequest::getVar('cdn_api_key'));
			
			if(!$validateAPICredentials)
			{
				$isError = true;
				$errorMsg .= BlueHatNetworkLanguage::_('BHN_COULD_NOT_VALIDATE_API_CREDENTIALS_ERROR').'<br />';
				
				BlueHatNetworkSetting::deleteSetting(array('bucket_public_secure_uri', 'bucket_public_uri', 'cdn_api_key', 'cdn_data'));
			}
			else
			{
				// Save CDN result data
				BlueHatNetworkSetting::set('bucket_public_secure_uri', $validateAPICredentials['bucket_public_secure_uri']);
				BlueHatNetworkSetting::set('bucket_public_uri', $validateAPICredentials['bucket_public_uri']);
				BlueHatNetworkSetting::set('cdn_api_key', BlueHatNetworkRequest::getVar('cdn_api_key'));
				BlueHatNetworkSetting::set('cdn_data', $validateAPICredentials);
			}
		}
		
		$excludeFileList = preg_replace('@[\'"]+@', '', BlueHatNetworkRequest::getVar('exclude_file_list', '', 'method'));
		if(empty($excludeFileList)) $excludeFileList = 'tmp/,logs/,cache/';
		
		BlueHatNetworkSetting::set('enable_optimized_by_bhn_txt', BlueHatNetworkRequest::getInt('enable_optimized_by_bhn_txt', 0, 'method'));
		BlueHatNetworkSetting::set('offload_on_fly', BlueHatNetworkRequest::getInt('offload_on_fly', 0, 'method'));
		BlueHatNetworkSetting::set('include_js', BlueHatNetworkRequest::getInt('include_js', 0, 'method'));
		BlueHatNetworkSetting::set('include_css', BlueHatNetworkRequest::getInt('include_css', 0, 'method'));
		BlueHatNetworkSetting::set('include_images', BlueHatNetworkRequest::getInt('include_images', 0, 'method'));
		BlueHatNetworkSetting::set('include_swf', BlueHatNetworkRequest::getInt('include_swf', 0, 'method'));
		BlueHatNetworkSetting::set('include_font', BlueHatNetworkRequest::getInt('include_font', 0, 'method'));
		BlueHatNetworkSetting::set('include_optimized_by_bhn_html_comment', BlueHatNetworkRequest::getInt('include_optimized_by_bhn_html_comment', 0, 'method'));
		BlueHatNetworkSetting::set('optimize_html', BlueHatNetworkRequest::getInt('optimize_html', 0, 'method'));
		BlueHatNetworkSetting::set('combine_files', BlueHatNetworkRequest::getInt('combine_files', 0, 'method'));
		BlueHatNetworkSetting::set('externalize_snippets', BlueHatNetworkRequest::getInt('externalize_snippets', 0, 'method'));
		BlueHatNetworkSetting::set('prefetch_dns', BlueHatNetworkRequest::getInt('prefetch_dns', 0, 'method'));
		BlueHatNetworkSetting::set('remove_html_comments', BlueHatNetworkRequest::getInt('remove_html_comments', 0, 'method'));
		BlueHatNetworkSetting::set('remove_inline_type_attr', BlueHatNetworkRequest::getInt('remove_inline_type_attr', 0, 'method'));
		BlueHatNetworkSetting::set('remove_quotes', BlueHatNetworkRequest::getInt('remove_quotes', 0, 'method'));
		BlueHatNetworkSetting::set('convert_absolute_urls', BlueHatNetworkRequest::getInt('convert_absolute_urls', 0, 'method'));
		BlueHatNetworkSetting::set('auto_sync_on_article_change', BlueHatNetworkRequest::getInt('auto_sync_on_article_change', 0, 'method'));
		BlueHatNetworkSetting::set('auto_empty_cache', BlueHatNetworkRequest::getInt('auto_empty_cache', 0, 'method'));
		BlueHatNetworkSetting::set('shorten_doctype', BlueHatNetworkRequest::getInt('shorten_doctype', 0, 'method'));
		BlueHatNetworkSetting::set('shorten_meta_http_equiv_content_type', BlueHatNetworkRequest::getInt('shorten_meta_http_equiv_content_type', 0, 'method'));
		BlueHatNetworkSetting::set('optimize_database', BlueHatNetworkRequest::getVar('optimize_database', null, 'method'));
		BlueHatNetworkSetting::set('auto_scan_sync_interval', BlueHatNetworkRequest::getInt('auto_scan_sync_interval', 0, 'method'));
		BlueHatNetworkSetting::set('exclude_files', $excludeFileList);
		
		if($isError)
		{
			echo BlueHatNetworkSerializer::encode(array('msg' => '<span class="bhn-error-msg">'.BlueHatNetworkLanguage::sprintf('BHN_AN_ERROR_OCCURRED2', $errorMsg).'</span>'));
		}
		else
		{
			echo BlueHatNetworkSerializer::encode(array('msg' => BlueHatNetworkLanguage::_('BHN_SETTINGS_SAVED_SUCCESSFULLY')));
			
			$model->clearWebsiteCache(); // Clear website cache
		}
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function scan_sync_files()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		echo BlueHatNetworkSerializer::encode(
			array(
				'total_num_of_files' => $model->scanFiles(BlueHatNetworkRequest::getVar('file_to_sync', null, 'method'), true, true, BlueHatNetworkRequest::getInt('suppress_resume_txt', 0, 'method'))
			)
		);
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function get_scan_sync_status()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		$model->restoreProcessIdFromLastSession();
		
		echo BlueHatNetworkSerializer::encode(
			array(
				'status_msg' => $model->getStatusMessage(), 
				'process_mode' => $model->getProcessMode(), 
				'file_stats' => $model->getFileStats(true),
				'is_zombie' => (int)$model->isScanSyncProcessZombie()
			)
		);
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function get_files()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		echo BlueHatNetworkSerializer::encode($model->getAllFiles(true));
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function clear_index_data()
	{
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		$model->resetIndexData();
		
		if(BlueHatNetworkFactory::isWordPress())
		{
			$redirectUri = 'options-general.php?page=blue-hat-turbo-mainpage';
		}
		elseif(BlueHatNetworkFactory::isJoomla())
		{
			$redirectUri = 'index.php?option=com_bluehatcdn';
		}
		
		header('Location: '.$redirectUri);
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function delete_selected_files_from_cdn()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		if($model->deleteFilesFromCDN(BlueHatNetworkRequest::getVar('file_paths_list', null, 'method')))
		{
			if($model->getNumberOfAffectedFiles() > 1)
			{
				$msg = BlueHatNetworkLanguage::_('BHN_FILES_DELETED_FROM_CDN_SUCCESSFULLY');
			}
			else
			{
				$msg = BlueHatNetworkLanguage::_('BHN_FILE_DELETED_FROM_CDN_SUCCESSFULLY');
			}
			
			echo BlueHatNetworkSerializer::encode(array('success' => 1, 'msg' => $msg));
		}
		else
		{
			// An error ocurred
			if($model->getNumberOfAffectedFiles() > 1)
			{
				$msg = '<span style="color: red;">'.BlueHatNetworkLanguage::_('BHN_FILES_DELETED_FROM_CDN_ERROR').'</span>';
			}
			else
			{
				$msg = '<span style="color: red;">'.BlueHatNetworkLanguage::_('BHN_FILE_DELETED_FROM_CDN_ERROR').'</span>';
			}
			
			echo BlueHatNetworkSerializer::encode(array('success' => 0, 'msg' => $msg));
		}
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function stop_scan_sync_process()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		if(!class_exists('BlueHatNetworkSerializer')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'serializer.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		echo BlueHatNetworkSerializer::encode(array('success' => $model->stopRunningScanSyncProcess()));
		
		BlueHatNetworkFactory::exitNow();
	}
	
	public static function triggerMeshHashCompilerEvent()
	{
		if(!class_exists('BlueHatNetworkRequest')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'request.php';
		if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
		
		$model =& BlueHatNetworkFactory::getSyncModel();
		
		if(method_exists($model, 'meshHashCompile')) $model->meshHashCompile();
		
		BlueHatNetworkFactory::exitNow();
	}
}