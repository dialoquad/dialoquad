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

/**
 * Custom Exceptions for the CloudFiles API
 *
 * Requres PHP 5.x (for Exceptions and OO syntax)
 *
 * See COPYING for license information.
 *
 * @author Eric "EJ" Johnson <ej@racklabs.com>
 * @copyright Copyright (c) 2008, Rackspace US, Inc.
 * @package php-cloudfiles-exceptions
 */

/**
 * Custom Exceptions for the CloudFiles API
 * @package php-cloudfiles-exceptions
 */
class SyntaxException extends Exception { }
class AuthenticationException extends Exception { }
class InvalidResponseException extends Exception { }
class NonEmptyContainerException extends Exception { }
class NoSuchObjectException extends Exception { }
class NoSuchContainerException extends Exception { }
class NoSuchAccountException extends Exception { }
class MisMatchedChecksumException extends Exception { }
class IOException extends Exception { }
class CDNNotEnabledException extends Exception { }
class BadContentTypeException extends Exception { }
class InvalidUTF8Exception extends Exception { }
class ConnectionNotOpenException extends Exception { }

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
