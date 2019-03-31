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
namespace tp\admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\admin\Admin_Functions' ) ) {


	/**
	 * Class Admin_Functions
	 * @package tp\admin\core
	 */
	class Admin_Functions {


		/**
		 * Admin_Functions constructor.
		 */
		function __construct() {

		}

		/**
		 * Boolean check if we're viewing TP backend
		 *
		 * @return bool
		 */
		function is_tp_screen() {
			global $current_screen;
			$screen_id = $current_screen->id;

			if ( strstr( $screen_id, 'tzportfolio') ||
				strstr( $screen_id, 'tp_') ||
				$screen_id == 'nav-menus' ) {
				return true;
			}

			if ( $this->is_plugin_post_type() ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if current page load TP post type
		 *
		 * @return bool
		 */
		function is_plugin_post_type() {
			$cpt = TPApp()->cpt_list();

			if ( isset( $_REQUEST['post_type'] ) ) {
				$post_type = $_REQUEST['post_type'];
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			} elseif ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
				$post_type = get_post_type();
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check wp-admin nonce
		 *
		 * @param bool $action
		 */
		function check_ajax_nonce( $action = false ) {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
			$action = empty( $action ) ? 'tp-admin-nonce' : $action;

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_send_json_error( esc_js( __( 'Wrong Nonce', 'tz-portfolio' ) ) );
			}
		}
	}
}