<?php
/*
Plugin Name: TZ Portfolio
Plugin URI: https://www.tzportfolio.com/
Description: All you need for a Portfolio here. TZ Portfolio+ is an open source advanced portfolio plugin for WordPress
Version: 1.0.0
Author: TemPlaza, Sonny
Author URI: https://www.tzportfolio.com/
Text Domain: tz-portfolio
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'TPApp' ) ) {

	/**
	 * Main TPApp Class
	 *
	 * @class TPApp
	 * @version 1.0
	 *
	 */
	require_once 'functions.php';
	final class TPApp extends TPApp_Functions {


		/**
		 * @var TPApp the single instance of the class
		 */
		protected static $instance = null;


		/**
		 * @var array all plugin's classes
		 */
		public $classes = array();


		/**
		 * @var bool Old variable
		 *
		 * @todo deprecate this variable
		 */
		public $is_filtering;


		/**
		 * WP Native permalinks turned on?
		 *
		 * @var
		 */
		public $is_permalinks;


		/**
		 * Main TPApp Instance
		 *
		 * Ensures only one instance of TPApp is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see TPApp()
		 * @return TPApp - Main instance
		 */
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->_tp_construct();
			}

			return self::$instance;
		}


		/**
		 * Call-back plugin classes
		 *
		 * @since 1.0
		 * @see TPApp()
		 *
		 * @param $name
		 * @return mixed
		 */
		public function call( $name ) {
			if ( empty( $this->classes[ $name ] ) ) {

				/**
				 * TPApp hook
				 *
				 * @type filter
				 * @title tp_call_object_{$class_name}
				 * @description Extend call classes of Extensions for use TPApp()->class_name()->method|function
				 * @input_vars
				 * [{"var":"$class","type":"object","desc":"Class Instance"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_filter( 'tp_call_object_{$class_name}', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_filter( 'tp_call_object_{$class_name}', 'my_extension_class', 10, 1 );
				 * function my_extension_class( $class ) {
				 *     // your code here
				 *     return $class;
				 * }
				 * ?>
				 */
				$this->classes[ $name ] = apply_filters( 'tp_call_object_' . $name, false );
			}

			return $this->classes[ $name ];

		}


		/**
		 * Function for add classes to $this->classes
		 * for run using TPApp()
		 *
		 * @since 1.0
		 *
		 * @param string $class_name
		 * @param array $config
		 * @param bool $instance
		 */
		public function set_class( $class_name, $config = array() , $instance = false ) {
			if ( empty( $this->classes[ $class_name ] ) ) {
				$this->classes[ $class_name ] = $instance ? $class_name::instance($config) : new $class_name($config);
			}
		}

		/**
		 * TPApp constructor.
		 *
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * TPApp pseudo-constructor.
		 *
		 * @since 2.0.18
		 */
		function _tp_construct() {
			//register autoloader for include TPApp classes
			spl_autoload_register( array( $this, 'tp__autoloader' ) );

			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

				if ( get_option( 'permalink_structure' ) ) {
					$this->is_permalinks = true;
				}

				$this->is_filtering = 0;

				// textdomain loading
				$this->localize();

				// include TPApp classes
				$this->includes();

				// include hook files
				add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

				// init widgets
				add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

				//include short non class functions
				require_once 'tp-short-functions.php';
			}
		}


		/**
		 * Loading TPApp textdomain
		 *
		 * 'tp-portfolio' by default
		 */
		function localize() {
			$language_locale = ( get_locale() != '' ) ? get_locale() : 'en_US';

			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_language_locale
			 * @description Change TPApp language locale
			 * @input_vars
			 * [{"var":"$locale","type":"string","desc":"TPApp language locale"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_language_locale', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_language_locale', 'my_language_locale', 10, 1 );
			 * function my_language_locale( $locale ) {
			 *     // your code here
			 *     return $locale;
			 * }
			 * ?>
			 */
			$language_locale = apply_filters( 'tp_language_locale', $language_locale );


			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_language_textdomain
			 * @description Change TPApp textdomain
			 * @input_vars
			 * [{"var":"$domain","type":"string","desc":"TPApp Textdomain"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_language_textdomain', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_language_textdomain', 'my_textdomain', 10, 1 );
			 * function my_textdomain( $domain ) {
			 *     // your code here
			 *     return $domain;
			 * }
			 * ?>
			 */
			$language_domain = apply_filters( 'tp_language_textdomain', 'tp-portfolio' );

			$language_file = WP_LANG_DIR . '/plugins/' . $language_domain . '-' . $language_locale . '.mo';

			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_language_file
			 * @description Change TPApp language file path
			 * @input_vars
			 * [{"var":"$language_file","type":"string","desc":"TPApp language file path"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_language_file', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_language_file', 'my_language_file', 10, 1 );
			 * function my_language_file( $language_file ) {
			 *     // your code here
			 *     return $language_file;
			 * }
			 * ?>
			 */
			$language_file = apply_filters( 'tp_language_file', $language_file );

			load_textdomain( $language_domain, $language_file );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		public function includes() {

			$this->config();
			$this->setup();
			$this->options();
			$this->common();
			$this->roles();
			$this->mvc();
			if ( $this->is_request( 'ajax' ) ) {
				$this->admin();
			} elseif ( $this->is_request( 'admin' ) ) {
				$this->admin();
				$this->addon();
				$this->admin_menu();
				$this->admin_enqueue();
			} elseif ( $this->is_request( 'frontend' ) ) {
				$this->enqueue();
			}
			
		}


		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Common()
		 */
		function common() {
			if ( empty( $this->classes['common'] ) ) {
				$this->classes['common'] = new tp\controllers\Common();
			}

			return $this->classes['common'];
		}


		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Options()
		 */
		function options() {
			if ( empty( $this->classes['options'] ) ) {
				$this->classes['options'] = new tp\controllers\Options();
			}
			return $this->classes['options'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\admin\Admin()
		 */
		function admin() {
			if ( empty( $this->classes['admin'] ) ) {
				$this->classes['admin'] = new tp\admin\Admin();
			}
			return $this->classes['admin'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\admin\Admin()
		 */
		function addon() {
			if ( empty( $this->classes['addon'] ) ) {
				$this->classes['addon'] = new tp\admin\controllers\Addon();
			}
			return $this->classes['addon'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\Config
		 */
		function config() {
			if ( empty( $this->classes['config'] ) ) {
				$this->classes['config'] = new tp\Config();
			}

			return $this->classes['config'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\admin\controllers\Admin_Menu()
		 */
		function admin_menu() {
			if ( empty( $this->classes['admin_menu'] ) ) {
				$this->classes['admin_menu'] = new tp\admin\controllers\Admin_Menu();
			}
			return $this->classes['admin_menu'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\admin\controllers\Admin_Enqueue()
		 */
		function admin_enqueue() {
			if ( empty( $this->classes['admin_enqueue'] ) ) {
				$this->classes['admin_enqueue'] = new tp\admin\controllers\Admin_Enqueue();
			}
			return $this->classes['admin_enqueue'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Roles()
		 */
		function roles() {
			if ( empty( $this->classes['roles'] ) ) {
				$this->classes['roles'] = new tp\controllers\Roles();
			}
			return $this->classes['roles'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\MVC()
		 */
		function mvc() {
			if ( empty( $this->classes['mvc'] ) ) {
				$this->classes['mvc'] = new tp\MVC();
			}
			return $this->classes['mvc'];
		}

		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Enqueue
		 */
		function enqueue() {
			if ( empty( $this->classes['enqueue'] ) ) {
				$this->classes['enqueue'] = new tp\controllers\Enqueue();
			}

			return $this->classes['enqueue'];
		}


		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Templates
		 */
		function templates() {
			if ( empty( $this->classes['templates'] ) ) {
				$this->classes['templates'] = new tp\controllers\Templates();
			}

			return $this->classes['templates'];
		}


		/**
		 * @since 2.0
		 *
		 * @return tp\lib\mobiledetect\Um_Mobile_Detect
		 */
		function mobile() {
			if ( empty( $this->classes['mobile'] ) ) {
				$this->classes['mobile'] = new tp\lib\mobiledetect\TP_Mobile_Detect();
			}

			return $this->classes['mobile'];
		}


		/**
		 * Include files with hooked filters/actions
		 *
		 * @since 2.0
		 */
		function init() {

		}


		/**
		 * Init TPApp widgets
		 *
		 * @since 2.0
		 */
		function widgets_init() {

		}

		/**
		 * @since 2.0
		 *
		 * @return tp\controllers\Setup
		 */
		function setup() {
			if ( empty( $this->classes['setup'] ) ) {
				$this->classes['setup'] = new tp\controllers\Setup();
			}

			return $this->classes['setup'];
		}
	}
}


/**
 * Function for calling TPApp methods and variables
 *
 * @since 2.0
 *
 * @return TPApp
 */
function TPApp() {
	return TPApp::instance();
}


// Global for backwards compatibility.
$GLOBALS['tzportfolio'] = TPApp();