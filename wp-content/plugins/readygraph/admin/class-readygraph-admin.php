<?php
/**
 * Plugin Name.
 *
 * @package   ReadyGraph_Admin
 * @author    dan@readygraph.com
 * @license   GPL-2.0+
 * @link      http://www.readygraph.com
 * @copyright 2014 ReadyGraph (Under App Uprising, Inc)
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-readygraph.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package ReadyGraph_Admin
 * @author  dan@readygraph.com
 */
class ReadyGraph_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 * @TODO:
		 *
		 * - Rename "Plugin_Name" to the name of your initial plugin class
		 *
		 */
		$plugin = ReadyGraph::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		add_filter( 'plugin_action_links' , array( $this, 'add_action_links' ), 10, 2 );
    
		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
    
    add_action( 'admin_notices', array( $this, 'add_plugin_warning' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), ReadyGraph::VERSION );
			//wp_enqueue_style( $this->plugin_slug .'-admin-styles', 'http://localhost/~jasukkas/wordpress/wp-content/plugins/adsoptimal/admin/assets/css/admin.css?ver=1.0.0', array(), ReadyGraph::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), ReadyGraph::VERSION );
			//wp_enqueue_script( $this->plugin_slug . '-admin-script', 'http://localhost/~jasukkas/wordpress/wp-content/plugins/adsoptimal/admin/assets/js/admin.js?ver=1.0.0', array( 'jquery' ), ReadyGraph::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'ReadyGraph', $this->plugin_slug ),
			__( 'ReadyGraph', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links, $file ) {
    $plugin_basename = 'readygraph/readygraph.php';
    if ( $file == $plugin_basename ) {
      return array_merge(
        array(
          'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
        ),
        $links
      );
    }
    else {
      return $links;
    }
	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

  
  public function add_plugin_warning() {
    if (get_option('readygraph_access_token', '') != '') return;

    global $hook_suffix, $current_user;
    if ( $hook_suffix == 'plugins.php' ) {              
      echo '<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">  
        <style type="text/css">  
          .readygraph_activate {
            min-width:825px;
            padding:7px;
            margin:15px 0;
            background:#1b75bb;
            -moz-border-radius:3px;
            border-radius:3px;
            -webkit-border-radius:3px;
            position:relative;
            overflow:hidden;
          }
          .readygraph_activate .aa_button {
            cursor: pointer;
            -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
            -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
            box-shadow:inset 0px 1px 0px 0px #ffffff;
            background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f9f9f9), color-stop(1, #e9e9e9) );
            background:-moz-linear-gradient( center top, #f9f9f9 5%, #e9e9e9 100% );
            filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9f9f9", endColorstr="#e9e9e9");
            background-color:#f9f9f9;
            -webkit-border-top-left-radius:3px;
            -moz-border-radius-topleft:3px;
            border-top-left-radius:3px;
            -webkit-border-top-right-radius:3px;
            -moz-border-radius-topright:3px;
            border-top-right-radius:3px;
            -webkit-border-bottom-right-radius:3px;
            -moz-border-radius-bottomright:3px;
            border-bottom-right-radius:3px;
            -webkit-border-bottom-left-radius:3px;
            -moz-border-radius-bottomleft:3px;
            border-bottom-left-radius:3px;
            text-indent:0;
            border:1px solid #dcdcdc;
            display:inline-block;
            color:#333333;
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
            font-size:15px;
            font-weight:normal;
            font-style:normal;
            height:40px;
            line-height:40px;
            width:275px;
            text-decoration:none;
            text-align:center;
            text-shadow:1px 1px 0px #ffffff;
          }
          .readygraph_activate .aa_button:hover {
            background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #e9e9e9), color-stop(1, #f9f9f9) );
            background:-moz-linear-gradient( center top, #e9e9e9 5%, #f9f9f9 100% );
            filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#e9e9e9", endColorstr="#f9f9f9");
            background-color:#e9e9e9;
          }
          .readygraph_activate .aa_button:active {
            position:relative;
            top:1px;
          }
          /* This button was generated using CSSButtonGenerator.com */
          .readygraph_activate .aa_description {
            position:absolute;
            top:19px;
            left:285px;
            margin-left:25px;
            color:#ffffff;
            font-size:15px;
            z-index:1000
          }
          .readygraph_activate .aa_description strong {
            color:#FFF;
            font-weight:normal
          }
        </style>                       
        <form name="readygraph_activate" action="'.admin_url( 'options-general.php?page=' . $this->plugin_slug ).'" method="POST"> 
          <input type="hidden" name="return" value="1"/>
          <input type="hidden" name="jetpack" value="'.(string) class_exists( 'Jetpack' ).'"/>
          <input type="hidden" name="user" value="'.esc_attr( $current_user->user_login ).'"/>
          <div class="readygraph_activate">
            <div class="aa_button" onclick="document.readygraph_activate.submit();">  
              '.__('Connect Your ReadyGraph Account').'
            </div>  
            <div class="aa_description">'.__('<strong>Almost done</strong> - connect your account to start earning money').'</div>  
          </div>  
        </form>  
      </div>';      
    }
  }
}
