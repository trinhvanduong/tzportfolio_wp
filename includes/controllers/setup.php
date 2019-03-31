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

if ( ! class_exists( 'tp\controllers\Setup' ) ) {


	/**
	 * Class Setup
	 * @package tp\controllers
	 */
	class Setup {


		/**
		 * Setup constructor.
		 */
		function __construct() {
			//run activation
			register_activation_hook( tp_plugin, array( &$this, 'activation' ) );
		}


		/**
		 * Plugin Activation
		 *
		 * @since 2.0
		 */
		function activation() {
			if ( is_multisite() ) {
				//get all blogs
				$blogs = get_sites();
				if ( ! empty( $blogs ) ) {
					foreach( $blogs as $blog ) {
						switch_to_blog( $blog->blog_id );
						//make activation script for each sites blog
						$this->single_site_activation();
						restore_current_blog();
					}
				}
			} else {
				$this->single_site_activation();
			}
		}


		/**
		 * Single site plugin activation handler
		 */
		function single_site_activation() {
			//first install
			$version = get_option( 'tp_version' );
			if ( ! $version ) {
				update_option( 'tp_last_version_upgrade', tp_version );

				add_option( 'tp_first_activation_date', time() );

				//show avatars on first install
				if ( ! get_option( 'show_avatars' ) ) {
					update_option( 'show_avatars', 1 );
				}
			}

			if ( $version != tp_version ) {
				update_option( 'tp_version', tp_version );
			}
			//run setup
			\TPApp()->common()->create_post_types();
			$this->run_setup();
		}


		/**
		 * Run setup
		 */
		function run_setup() {
			$this->install_basics();
			$this->set_default_settings();
			$this->set_default_role_meta();
		}


		/**
		 * Basics
		 */
		function install_basics() {

		}


		/**
		 * Set default TPApp settings
		 */
		function set_default_settings() {
			$options = get_option( 'tp_options' );
			$options = empty( $options ) ? array() : $options;

			foreach ( TPApp()->config()->settings_defaults as $key => $value ) {
				//set new options to default
				if ( ! isset( $options[ $key ] ) )
					$options[ $key ] = $value;
			}

			update_option( 'tp_options', $options );
		}


		/**
		 * Set TPApp roles meta to Default WP roles
		 */
		function set_default_role_meta() {

			foreach ( TPApp()->config()->default_roles_metadata as $role => $meta ) {
				add_option( "tp_role_{$role}_meta", $meta );
				TPApp()->roles()->set_roles($role);
			}
		}
	}
}