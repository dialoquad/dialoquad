<?php
/*
Plugin Name: Blue Hat CDN
Plugin URI: http://www.BlueHatNetwork.com
Description: Plugin for turbo charging website.
Author: Blue Hat Network
Author URI: http://www.BlueHatNetwork.com
Text Domain: blue-hat-cdn
Version: 2.9.4
*/

if(!defined('_JEXEC')) define('_JEXEC', true);
if(!defined('BHN_PLUGIN_ADMIN_ROOT_FILE')) define('BHN_PLUGIN_ADMIN_ROOT_FILE', __FILE__);
if(!defined('BHN_PLUGIN_ADMIN_ROOT')) define('BHN_PLUGIN_ADMIN_ROOT', dirname(BHN_PLUGIN_ADMIN_ROOT_FILE));

require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'bluehatcdn.php';