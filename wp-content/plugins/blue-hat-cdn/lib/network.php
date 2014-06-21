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

class BlueHatNetworkNetwork
{
	private static $_ch = null;
	private static $_lastConnectedHost = '';
	
	private static function _do($url, $params=null, $keepConnectionOpen=true, $saveToFileHandler=null, $multiPartFormData=false, $binaryResult=false, $returnHandleOnly=false)
	{
		// Check this urls host to see if it changed from last
		$thisUrlHost = parse_url($url, PHP_URL_HOST);
		
		if(self::$_ch && self::$_lastConnectedHost != $thisUrlHost) self::closeConnection();
		
		//open connection
		if(!self::$_ch) 
		{
			self::$_lastConnectedHost = $thisUrlHost;
			
			self::$_ch = curl_init();
			
			curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, true);	
			curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt(self::$_ch, CURLOPT_CONNECTTIMEOUT, 20);
			if(!ini_get('open_basedir') && !ini_get('safe_mode')) curl_setopt(self::$_ch, CURLOPT_FOLLOWLOCATION, true);
			
			$cURLCaCertFilePath = BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'share'.DIRECTORY_SEPARATOR.'cacert.pem';
			
			if(is_file($cURLCaCertFilePath)) curl_setopt(self::$_ch, CURLOPT_CAINFO, $cURLCaCertFilePath);
		}
		
		curl_setopt(self::$_ch, CURLOPT_URL, $url);
		
		if($keepConnectionOpen)
		{
			$httpHeaders = array('Accept-Encoding: gzip, deflate', 'Expect:');
			
			if(!$returnHandleOnly)
			{
				$httpHeaders[] = 'Connection: Keep-Alive';
				$httpHeaders[] = 'Keep-Alive: 300';
			}
		}
		else
		{
			$httpHeaders = array('Accept-Encoding: gzip, deflate', 'Expect:');
		}
		
		if($multiPartFormData) $httpHeaders[] = 'Content-Type: multipart/form-data';
		
		curl_setopt(self::$_ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt(self::$_ch, CURLOPT_HTTPHEADER, $httpHeaders);
		
		if($saveToFileHandler) curl_setopt(self::$_ch, CURLOPT_FILE, $saveToFileHandler);
		
		if(!$binaryResult) 
		{
			curl_setopt(self::$_ch, CURLOPT_BINARYTRANSFER, false);
		}
		else
		{
			curl_setopt(self::$_ch, CURLOPT_BINARYTRANSFER, true);
		}
		
		if(!$params)
		{
			curl_setopt(self::$_ch, CURLOPT_HTTPGET, true);
			curl_setopt(self::$_ch, CURLOPT_POST, false);
		}
		else
		{
			// This is a post
			curl_setopt(self::$_ch, CURLOPT_HTTPGET, false);
			curl_setopt(self::$_ch, CURLOPT_POST, true);
			curl_setopt(self::$_ch, CURLOPT_POSTFIELDS, $params);
		}
		
		if(!$returnHandleOnly)
		{
			//execute post
			$result = curl_exec(self::$_ch);
		}
		else
		{
			return curl_copy_handle(self::$_ch);
		}
		
		//close connection
		if(!$keepConnectionOpen) self::closeConnection();
		
		return $result;
	}
	
	public static function get($url, $keepConnectionOpen=false, $binaryResult=false)
	{
		return self::_do($url, null, $keepConnectionOpen, null, false, $binaryResult);
	}
	
	public static function post($url, $params, $keepConnectionOpen=false, $multiPartFormData=false, $returnHandleOnly=false, $binaryResult=false)
	{
		return self::_do($url, $params, $keepConnectionOpen, null, $multiPartFormData, $binaryResult, $returnHandleOnly);
	}
	
	public static function closeConnection()
	{
		if(self::$_ch) 
		{
			curl_close(self::$_ch);
			
			self::$_ch = null;
		}
	}
	
	public static function didErrorOccurWithLastRequest()
	{
		if(!self::$_ch || curl_errno(self::$_ch) || (int)curl_getinfo(self::$_ch, CURLINFO_HTTP_CODE) >= 400) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function downloadRemoteFile($remotePath, $localPath)
	{
		$didErrorOccur = true;
		
		$saveToFileHandler = fopen($localPath, 'w+');
		
		if($saveToFileHandler)
		{
			self::_do($remotePath, null, true, $saveToFileHandler);
		
			$didErrorOccur = self::didErrorOccurWithLastRequest();

			self::closeConnection();
			
			fclose($saveToFileHandler);
		}
		
		if(!$didErrorOccur)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

register_shutdown_function(array('BlueHatNetworkNetwork', 'closeConnection'));