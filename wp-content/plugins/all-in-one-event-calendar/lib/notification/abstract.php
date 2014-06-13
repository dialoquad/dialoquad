<?php

/**
 * Abstract class for notifications.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Notification
 */
abstract class Ai1ec_Notification {

	/**
	 * @var string The message to send.
	 */
	protected $_message;

	/**
	 * @var array A list of recipients.
	 */
	protected $recipients = array();

	/**
	 * Set local variables.
	 *
	 * @param array  $recipients List of recipients.
	 * @param string $message    Message text.
	 *
	 * @return void
	 */
	public function __construct( $message, array $recipients ) {
		$this->_message    = $message;
		$this->_recipients = $recipients;
	}

	/**
	 * This function performs the actual sending of the message.
	 *
	 * Must be implemented in child classes.
	 *
	 * @return bool Success.
	 */
	abstract public function send();

}