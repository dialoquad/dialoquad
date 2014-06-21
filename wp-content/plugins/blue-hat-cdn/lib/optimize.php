<?php
class BHNOptimizer
{
	const API_URL = 'http://api.bluehatnetwork.com/api/index.php?option=com_bluehatnetwork&view=api';
	
	public static function optimizeFile($filePath, $destPath='')
	{
		if(!class_exists('BlueHatNetworkNetwork')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'network.php';
		
		if('' == $destPath) $destPath =& $filePath;
		
		return file_put_contents($destPath, BlueHatNetworkNetwork::post(self::API_URL, array(
			'task' => 'optimize_file',
			'file' => '@'.$filePath
		), true, false, false, true));
	}
	
	public static function optimizeImg($filePath, $destPath='')
	{
		return self::optimizeFile($filePath, $destPath);
	}
	
	public static function optimizeJS($filePath, $destPath='')
	{
		return self::optimizeFile($filePath, $destPath);
	}
	
	public static function optimizeCSS($filePath, $destPath='')
	{
		return self::optimizeFile($filePath, $destPath);
	}
}