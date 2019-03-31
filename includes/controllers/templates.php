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


if ( ! class_exists( 'Templates' ) ) {


	/**
	 * Class Templates
	 * @package tp\controllers
	 */
	class Templates {

		function __construct() {

		}


		/**
		 * Get template path
		 *
		 *
		 * @param $slug
		 * @return string
		 */
		function get_template( $slug ) {
			$file_list = tp_path . "templates/{$slug}.php";
			$theme_file = get_stylesheet_directory() . "/tz-portfolio/templates/{$slug}.php";

			if ( file_exists( $theme_file ) ) {
				$file_list = $theme_file;
			}

			return $file_list;
		}
	}
}