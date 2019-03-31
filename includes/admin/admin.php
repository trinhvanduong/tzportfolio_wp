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

if ( ! class_exists( 'tp\admin\Admin' ) ) {


	/**
	 * Class Admin
	 * @package tp\admin
	 */
	class Admin extends Admin_Functions {

		/**
		 * Admin constructor.
		 */
		function __construct() {
			parent::__construct();

			$this->templates_path = tp_path . 'includes/admin/templates/';

			add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ), 999 );
			add_action( 'tp_roles_add_meta_boxes', array( &$this, 'add_metabox_role' ) );
		}

		/**
		 * Adds class to our admin pages
		 *
		 * @param $classes
		 *
		 * @return string
		 */
		function admin_body_class( $classes ) {
			if ( $this->is_tp_screen() ) {
				return "$classes tp-admin";
			}
			return $classes;
		}

		/**
		 * Add role metabox
		 */
		function add_metabox_role() {
			$roles_metaboxes    =   array();

			/**
			 * TP hook
			 *
			 * @type filter
			 * @title tp_admin_role_metaboxes
			 * @description Extend metaboxes at Add/Edit User Role
			 * @input_vars
			 * [{"var":"$roles_metaboxes","type":"array","desc":"Metaboxes at Add/Edit TP Role"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_admin_role_metaboxes', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'tp_admin_role_metaboxes', 'my_admin_role_metaboxes', 10, 1 );
			 * function my_admin_role_metaboxes( $roles_metaboxes ) {
			 *     // your code here
			 *     $roles_metaboxes[] = array(
			 *         'id'        => 'tp-admin-form-your-custom',
			 *         'title'     => __( 'My Roles Metabox', 'music-press-pro' ),
			 *         'callback'  => 'my-metabox-callback',
			 *         'screen'    => 'tp_role_meta',
			 *         'context'   => 'side',
			 *         'priority'  => 'default'
			 *     );
			 *
			 *     return $roles_metaboxes;
			 * }
			 * ?>
			 */
			$roles_metaboxes = apply_filters( 'tp_admin_role_metaboxes', $roles_metaboxes );

			foreach ( $roles_metaboxes as $metabox ) {
				add_meta_box(
					$metabox['id'],
					$metabox['title'],
					$metabox['callback'],
					$metabox['screen'],
					$metabox['context'],
					$metabox['priority']
				);
			}
		}
	}
}