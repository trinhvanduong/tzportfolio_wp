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
namespace tp;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\Config' ) ) {


	/**
	 * Class Config
	 *
	 * Class with global variables for TPApp
	 *
	 * @package tp
	 */
	class Config {


		/**
		 * @var mixed|void
		 */
		var $default_roles_metadata;

		/**
		 * @var mixed|void
		 */
		var $settings_defaults;


		/**
		 * @var array
		 */
		var $permalinks;


		/**
		 * Config constructor.
		 */
		function __construct() {
			//settings defaults
			$this->settings_defaults = array(
				'jquery'        => 1,
				'isotope'       => 1
			);
			$this->default_roles_metadata = array(
				'subscriber' => array(
					'edit_tp_post' => 0,
					'edit_tp_posts' => 0,
					'edit_others_tp_posts' => 0,
					'read_tp_post' => 1,
					'delete_tp_post' => 0,
					'publish_tp_posts' => 0,
					'read_private_tp_posts' => 0,
					'delete_tp_posts' => 0,
					'delete_private_tp_posts' => 0,
					'delete_published_tp_posts' => 0,
					'delete_others_tp_posts' => 0,
					'edit_private_tp_posts' => 0,
					'edit_published_tp_posts' => 0,
				),
				'author' => array(
					'edit_tp_post' => 1,
					'edit_tp_posts' => 1,
					'edit_others_tp_posts' => 0,
					'read_tp_post' => 1,
					'delete_tp_post' => 1,
					'publish_tp_posts' => 1,
					'read_private_tp_posts' => 0,
					'delete_tp_posts' => 1,
					'delete_private_tp_posts' => 0,
					'delete_published_tp_posts' => 1,
					'delete_others_tp_posts' => 0,
					'edit_private_tp_posts' => 0,
					'edit_published_tp_posts' => 1,
				),
				'contributor' => array(
					'edit_tp_post' => 1,
					'edit_tp_posts' => 1,
					'edit_others_tp_posts' => 0,
					'read_tp_post' => 1,
					'delete_tp_post' => 0,
					'publish_tp_posts' => 0,
					'read_private_tp_posts' => 0,
					'delete_tp_posts' => 0,
					'delete_private_tp_posts' => 0,
					'delete_published_tp_posts' => 0,
					'delete_others_tp_posts' => 0,
					'edit_private_tp_posts' => 0,
					'edit_published_tp_posts' => 0,
				),
				'editor' => array(
					'edit_tp_post' => 1,
					'edit_tp_posts' => 1,
					'edit_others_tp_posts' => 1,
					'read_tp_post' => 1,
					'delete_tp_post' => 1,
					'publish_tp_posts' => 1,
					'read_private_tp_posts' => 1,
					'delete_tp_posts' => 1,
					'delete_private_tp_posts' => 1,
					'delete_published_tp_posts' => 1,
					'delete_others_tp_posts' => 1,
					'edit_private_tp_posts' => 1,
					'edit_published_tp_posts' => 1,
				),
				'administrator' => array(
					'edit_tp_post' => 1,
					'edit_tp_posts' => 1,
					'edit_others_tp_posts' => 1,
					'read_tp_post' => 1,
					'delete_tp_post' => 1,
					'publish_tp_posts' => 1,
					'read_private_tp_posts' => 1,
					'delete_tp_posts' => 1,
					'delete_private_tp_posts' => 1,
					'delete_published_tp_posts' => 1,
					'delete_others_tp_posts' => 1,
					'edit_private_tp_posts' => 1,
					'edit_published_tp_posts' => 1,
				),
			);
		}
		//end class
	}
}