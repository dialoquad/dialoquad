<?php

/**
 * Handles exception and errors
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Exception
 */
class Ai1ec_Exception_Handler {

	/**
	 * @var string The option for the messgae in the db
	 */
	const DB_DEACTIVATE_MESSAGE = 'ai1ec_deactivate_message';

	/**
	 * @var string The GET parameter to reactivate the plugin
	 */
	const DB_REACTIVATE_PLUGIN  = 'ai1ec_reactivate_plugin';

	/**
	 * @var callable|null Previously set exception handler if any
	 */
	protected $_prev_ex_handler;

	/**
	 * @var callable|null Previously set error handler if any
	 */
	protected $_prev_er_handler;

	/**
	 * @var string The name of the Exception class to handle
	 */
	protected $_exception_class;

	/**
	 * @var string The name of the ErrorException class to handle
	 */
	protected $_error_exception_class;

	/**
	 * @var string The message to display in the admin notice
	 */
	protected $_message;

	/**
	 * @var array Mapped list of errors that are non-fatal, to be ignored
	 *            in production.
	 */
	protected $_nonfatal_errors = null;

	/**
	 * Store exception handler that was previously set
	 *
	 * @param callable|null $_prev_ex_handler
	 *
	 * @return void Method does not return
	 */
	public function set_prev_ex_handler( $prev_ex_handler ) {
		$this->_prev_ex_handler = $prev_ex_handler;
	}

	/**
	 * Store error handler that was previously set
	 *
	 * @param callable|null $_prev_er_handler
	 *
	 * @return void Method does not return
	 */
	public function set_prev_er_handler( $prev_er_handler ) {
		$this->_prev_er_handler = $prev_er_handler;
	}

	/**
	 * Constructor accepts names of classes to be handled
	 *
	 * @param string $exception_class Name of exceptions base class to handle
	 * @param string $error_class     Name of errors base class to handle
	 *
	 * @return void Constructor newer returns
	 */
	public function __construct( $exception_class, $error_class ) {
		$this->_exception_class       = $exception_class;
		$this->_error_exception_class = $error_class;
		$this->_nonfatal_errors       = array(
			E_USER_WARNING => true,
			E_WARNING      => true,
			E_USER_NOTICE  => true,
			E_NOTICE       => true,
			E_STRICT       => true,
		);
		if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
			// wrapper `constant( 'XXX' )` is used to avoid compile notices
			// on earlier PHP versions.
			$this->_nonfatal_errors[constant( 'E_DEPRECATED' )]      = true;
			$this->_nonfatal_errors[constant( 'E_USER_DEPRECATED') ] = true;
		}
	}

	/**
	 * Global exceptions handling method
	 *
	 * @param Exception $exception Previously thrown exception to handle
	 *
	 * @return void Exception handler is not expected to return
	 */
	public function handle_exception( Exception $exception ) {
		if ( defined( 'AI1EC_DEBUG' ) && true === AI1EC_DEBUG ) {
			echo '<pre>';
			var_dump( $exception );
			echo '</pre>';
			die();
		}
		// if it's something we handle, handle it
		$backtrace = '<br><br>' . nl2br( $exception );
		if ( $exception instanceof $this->_exception_class ) {
			// check if it has a methof for deatiled html
			$message = method_exists( $exception, 'get_html_message' ) ?
					$exception->get_html_message() :
					$exception->getMessage();
			$this->soft_deactivate_plugin( $message . $backtrace );
		}
		// if it's a PHP error in our plugin files, deactivate and redirect
		else if ( $exception instanceof $this->_error_exception_class ) {
			$this->soft_deactivate_plugin(
				$exception->getMessage() . $backtrace
			);
		}
		// if another handler was set, let it handle the exception
		if ( is_callable( $this->_prev_ex_handler ) ) {
			call_user_func( $this->_prev_ex_handler, $exception );
		}
	}

	/**
	 * Throws an Ai1ec_Error_Exception if the error comes from our plugin
	 *
	 * @param int    $errno      Error level as integer
	 * @param string $errstr     Error message raised
	 * @param string $errfile    File in which error was raised
	 * @param string $errline    Line in which error was raised
	 * @param array  $errcontext Error context symbols table copy
	 *
	 * @throws Ai1ec_Error_Exception If error originates from within Ai1EC
	 *
	 * @return boolean|void Nothing when error is ours, false when no
	 *                      other handler exists
	 */
	public function handle_error(
		$errno,
		$errstr,
		$errfile,
		$errline,
		$errcontext
	) {
		// if the error is not in our plugin, let PHP handle things.
		if ( false === strpos( $errfile, AI1EC_PLUGIN_NAME ) ) {
			if ( is_callable( $this->_prev_er_handler ) ) {
				return call_user_func_array(
					$this->_prev_er_handler,
					func_get_args()
				);
			}
			return false;
		}
		// do not disable plugin in production if the error is rather low
		if (
			isset( $this->_nonfatal_errors[$errno] ) && (
				! defined( 'AI1EC_DEBUG' ) || false === AI1EC_DEBUG
			)
		) {
			$message = sprintf(
				'All-in-One Event Calendar: %s @ %s:%d #%d',
				$errstr,
				$errfile,
				$errline,
				$errno
			);
			return error_log( $message, 0 );
		}
		throw new Ai1ec_Error_Exception(
			$errstr,
			$errno,
			0,
			$errfile,
			$errline
		);
	}

	/**
	 * Perform what's needed to deactivate the plugin softly
	 *
	 * @param string $message Error message to be displayed to admin
	 *
	 * @return void Method does not return
	 */
	protected function soft_deactivate_plugin( $message ) {
		add_option( self::DB_DEACTIVATE_MESSAGE, $message );
		$this->redirect();
	}

	/**
	 * Perform what's needed to reactivate the plugin
	 *
	 * @return boolean Success
	 */
	public function reactivate_plugin() {
		return delete_option( self::DB_DEACTIVATE_MESSAGE );
	}

	/**
	 * Get message to be displayed to admin if any
	 *
	 * @return string|boolean Error message or false if plugin is not disabled
	 */
	public function get_disabled_message() {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
				self::DB_DEACTIVATE_MESSAGE
			)
		);
		if ( is_object( $row ) ) {
			return $row->option_value;
		} else { // option does not exist, so we must cache its non-existence
			return false;
		}
	}

	/**
	 * Add an admin notice
	 *
	 * @param string $message Message to be displayed to admin
	 *
	 * @return void Method does not return
	 */
	public function show_notices( $message ) {
		// save the message to use it later
		$this->_message = $message;
		add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
	}

	/**
	 * Render HTML snipped to be displayd as a notice to admin
	 *
	 * @hook admin_notices When plugin is soft-disabled
	 *
	 * @return void Method does not return
	 */
	public function render_admin_notice() {
		$redirect_url = add_query_arg(
			self::DB_REACTIVATE_PLUGIN,
			'true',
			get_admin_url( $_SERVER['REQUEST_URI'] )
		);
		$label = __(
			'All In One Event Calendar has been disabled due to an error:',
			AI1EC_PLUGIN_NAME
		);
		$message = '<div class="message error">'.
						'<h3>' . $label . '</h3>' .
						'<p>' . $this->_message . '</p>';
		$message .= sprintf(
			__(
				'<p>If you corrected the error and wish to try reactivating the plugin, <a href="%s">click here</a>.</p>',
				AI1EC_PLUGIN_NAME
			),
			$redirect_url
		);
		$message .= '</div>';
		echo $message;
	}

	/**
	 * Redirect the user either to the front page or the dashbord page
	 *
	 * @return void Method does not return
	 */
	protected function redirect() {
		if ( is_admin() ) {
			Ai1ec_Http_Response_Helper::redirect( get_admin_url() );
		} else {
			Ai1ec_Http_Response_Helper::redirect( get_site_url() );
		}
	}

}
