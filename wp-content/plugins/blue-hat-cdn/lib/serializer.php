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

class BlueHatNetworkSerializer
{
	function encode($data)
	{
		return json_encode($data);
	}
	
	function decode($str)
	{
		return json_decode($str);
	}
}