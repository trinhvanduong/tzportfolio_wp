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


if ( ! class_exists( 'tp\controllers\Roles' ) ) {


	/**
	 * Class Roles_Capabilities
	 * @package tp\controllers
	 */
	class Roles {

		/**
		 * Roles_Capabilities constructor.
		 */
		function __construct() {
			// include hook files
//			add_action( 'wp_roles_init', array( &$this, 'mp_roles_init' ), 0 );
		}

		/**
		 * Remove user role
		 *
		 * @param $user_id
		 * @param $role
		 */
		function remove_role( $user_id, $role ) {
			// Validate user id
			$user = get_userdata( $user_id );

			// User exists
			if ( ! empty( $user ) ) {
				// Remove role
				$user->remove_role( $role );
			}
		}

		/**
		 * Set roles for groups
		 * make user only with $roles roles
		 *
		 * @param int $user_id
		 * @param string|array $roles
		 */
		function set_roles( $roleID ) {
			global $wp_roles;
			$role_meta = get_option( "tp_role_{$roleID}_meta" );
			if ( ! empty( $role_meta ) ) {
				unset($role_meta['name']);
				foreach ($role_meta as $key => $meta) {
					if ($meta) {
						$wp_roles->add_cap($roleID,$key);
					} else {
						$wp_roles->remove_cap($roleID,$key);
					}
				}
			}
		}

		/**
		 * Reset role by id to default.
		 * @param $roleid
		 */
		function reset_default_role($roleID) {
			global $wp_roles;
			$role_data = get_option( "tp_role_{$roleID}_meta" );
			if (!empty($role_data)) {
				$meta_data  =   $this->get_default($roleID);
				update_option( "tp_role_{$roleID}_meta", $meta_data );
				foreach ($meta_data as $key => $meta) {
					if ($meta) {
						$wp_roles->add_cap($roleID,$key);
					} else {
						$wp_roles->remove_cap($roleID,$key);
					}
				}
			}
		}

		/**
		 * Get default role from config
		 * @param string $role
		 *
		 * @return array $default role
		 */
		function get_default ($role = 'subscriber') {
			$configApp  =   \TPApp()->call('config');
			$roles      =   $configApp->default_roles_metadata;
			if (isset($roles[$role])) {
				return $roles[$role];
			} else {
				return $roles['subscriber'];
			}
		}
	}
}