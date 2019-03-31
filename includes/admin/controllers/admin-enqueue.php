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
namespace tp\admin\controllers;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'tp\admin\controllers\Admin_Enqueue' ) ) {


	/**
	 * Class Admin_Enqueue
	 * @package tp\admin\controllers
	 */
	class Admin_Enqueue {


		/**
		 * @var string
		 */
		var $js_url;


		/**
		 * @var string
		 */
		var $css_url;


		/**
		 * @var string
		 */
		var $front_js_baseurl;


		/**
		 * @var string
		 */
		var $front_css_baseurl;


		/**
		 * @var string
		 */
		var $suffix;



		/**
		 * @var bool
		 */
		var $post_page;


		/**
		 * Admin_Enqueue constructor.
		 */
		function __construct() {
			$this->js_url = tp_url . 'includes/admin/assets/js/';
			$this->css_url = tp_url . 'includes/admin/assets/css/';

			$this->front_js_baseurl = tp_url . 'assets/js/';
			$this->front_css_baseurl = tp_url . 'assets/css/';

			$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'TP_SCRIPT_DEBUG' ) ) ? '' : '.min';

			add_action( 'admin_head', array( &$this, 'admin_head' ), 9 );

			add_action( 'admin_enqueue_scripts',  array( &$this, 'admin_enqueue_scripts' ) );

			add_filter( 'enter_title_here', array( &$this, 'enter_title_here' ) );
		}


		/**
		 * Enter title placeholder
		 *
		 * @param $title
		 *
		 * @return string
		 */
		function enter_title_here( $title ) {
			$screen = get_current_screen();
			if ( 'tp_post' == $screen->post_type ) {
				$title = __( 'e.g. Posts Manager', 'tz-portfolio' );
			} elseif ( 'tp_form' == $screen->post_type ) {
				$title = __( 'e.g. Categories Manager', 'tz-portfolio' );
			}
			return $title;
		}


		/**
		 * Runs on admin head
		 */
		function admin_head() {
			if ( TPApp()->admin()->is_plugin_post_type() ) { ?>

			<?php }
		}


		/**
		 * Load admin style
		 */
		function load_style() {
			wp_register_style( 'tp-admin', $this->css_url . 'tp-admin.css', array(), tp_version );
			wp_enqueue_style( 'tp-admin' );
		}

		/**
		 * Load admin js
		 */
		function load_javascript() {
			wp_register_script( 'tp-admin', $this->js_url . 'tp-admin.js', array( 'jquery' ), tp_version, true );
			wp_enqueue_script( 'tp-admin' );
		}


		/**
		 * Load controllers WP styles/scripts
		 */
		function load_core_wp() {
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_script( 'jquery-ui-tooltip' );
		}

		/**
		 * Load Gutenberg scripts
		 */
		function load_gutenberg_js() {
			//disable Gutenberg scripts to avoid the conflicts
			$disable_script = apply_filters( 'tp_disable_blocks_script', false );
			if ( $disable_script ) {
				return;
			}

			$restricted_blocks = TPApp()->options()->get( 'restricted_blocks' );
			if ( empty( $restricted_blocks ) ) {
				return;
			}

			wp_register_script( 'tp_block_js', $this->js_url . 'tp-admin-blocks.js', array( 'wp-i18n', 'wp-blocks', 'wp-components' ), tp_version, true );
			wp_set_script_translations( 'tp_block_js', 'tz-portfolio' );

			$restrict_options = array();
			$roles = TPApp()->roles()->get_roles( false, array( 'administrator' ) );
			if ( ! empty( $roles ) ) {
				foreach ( $roles as $role_key => $title ) {
					$restrict_options[] = array(
						'label' => $title,
						'value' => $role_key
					);
				}
			}
			wp_localize_script( 'tp_block_js', 'tp_restrict_roles', $restrict_options );

			wp_enqueue_script( 'tp_block_js' );
		}

		/**
		 * Load FontAwesome 5
		 */
		function load_FontAwesome() {
			wp_register_style( 'tp-admin-fontawesome', $this->front_css_baseurl . 'all.min.css', array(), tp_version );
			wp_enqueue_style( 'tp-admin-fontawesome' );
        }


		/**
		 * Load localize scripts
		 */
		function load_localize_scripts() {

			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_admin_enqueue_localize_data
			 * @description Extend localize data at wp-admin side
			 * @input_vars
			 * [{"var":"$localize_data","type":"array","desc":"Localize Data"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_admin_enqueue_localize_data', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_admin_enqueue_localize_data', 'my_admin_enqueue_localize_data', 10, 1 );
			 * function my_admin_enqueue_localize_data( $localize_data ) {
			 *     // your code here
			 *     return $localize_data;
			 * }
			 * ?>
			 */
			$localize_data = apply_filters( 'tp_admin_enqueue_localize_data', array(
					'nonce' => wp_create_nonce( "tp-admin-nonce" )
				)
			);

			wp_localize_script( 'tp-admin', 'tp_admin_scripts', $localize_data );
		}


		/**
		 * Enqueue scripts and styles
		 */
		function admin_enqueue_scripts() {
			if ( TPApp()->admin()->is_tp_screen() ) {
				$this->load_style();
				$this->load_javascript();
				$this->load_core_wp();

			} else {
				$this->load_style();
				$this->load_javascript();
			}

			$this->load_localize_scripts();
			$this->load_FontAwesome();

			global $wp_version;
			if ( version_compare( $wp_version, '5.0', '>=' ) && ! empty( $this->post_page ) ) {
//				$this->load_gutenberg_js();
			}

		}

	}
}