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
namespace tp\controllers;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'tp\controllers\Enqueue' ) ) {


	/**
	 * Class Enqueue
	 * @package tp\controllers
	 */
	class Enqueue {


		/**
		 * @var string
		 */
		var $suffix = '';


		/**
		 * @var string
		 */
		var $js_baseurl = '';


		/**
		 * @var string
		 */
		var $css_baseurl = '';


		/**
		 * Enqueue constructor.
		 */
		function __construct() {
			$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'TP_SCRIPT_DEBUG' ) ) ? '' : '.min';

			$this->js_baseurl = tp_url . 'assets/js/';
			$this->css_baseurl = tp_url . 'assets/css/';

			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_core_enqueue_priority
			 * @description Change Enqueue scripts priority
			 * @input_vars
			 * [{"var":"$priority","type":"int","desc":"Priority"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_core_enqueue_priority', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_core_enqueue_priority', 'my_core_enqueue_priority', 10, 1 );
			 * function my_core_enqueue_priority( $priority ) {
			 *     // your code here
			 *     return $priority;
			 * }
			 * ?>
			 */
			$priority = apply_filters( 'tp_core_enqueue_priority', 100 );
			add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), $priority );
		}


		/**
		 *
		 */
		function register_scripts() {
			wp_register_script('tp_isotope', $this->js_baseurl . 'jquery.isotope' . $this->suffix . '.js', array( 'jquery' ), tp_version, true );
		}


		/**
		 *
		 */
		function register_styles() {
			wp_register_style( 'tp_isotope', $this->css_baseurl . 'isotope.min.css', array(), tp_version );
		}


		/**
		 * Enqueue scripts and styles
		 */
		function wp_enqueue_scripts() {
			$this->register_scripts();
			$this->register_styles();

			$this->load_original();
		}


		/**
		 * This will load original files (not minified)
		 */
		function load_original() {
			$this->load_isotope();
		}


		/**
		 * Include Isotope Library
		 */
		function load_isotope() {
			wp_enqueue_script( 'tp_isotope' );
			wp_enqueue_style( 'tp_isotope' );
		}

	}
}