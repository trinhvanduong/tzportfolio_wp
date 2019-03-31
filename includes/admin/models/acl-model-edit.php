<?php
namespace tp\admin\models;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'tp\admin\models\ACL_Model_Edit' ) ) {
	class ACL_Model_Edit extends \tp\admin\Admin_Forms {
		var $roles      =   null;
		/**
		 * Add role metabox
		 */
		function add_metabox_role() {
			add_filter( 'tp_admin_role_metaboxes', array($this, 'core_role_metaboxes'));
		}

		function core_role_metaboxes($roles_metaboxes) {

			$callback = array( &$this, 'load_metabox_role' );
			$roles_metaboxes = array_merge( $roles_metaboxes,  array(
				array(
					'id'        => 'tp-admin-form-post',
					'title'     => __( 'Post', 'tz-portfolio' ),
					'callback'  => $callback,
					'screen'    => 'tp_role_meta',
					'context'   => 'normal',
					'priority'  => 'default'
				)
			));

			return $roles_metaboxes;
		}

		/**
		 * Load a role metabox
		 *
		 * @param $object
		 * @param $box
		 */
		function load_metabox_role( $object, $box ) {
			global $post;
			$box['id'] = str_replace( 'tp-admin-form-', '', $box['id'] );

			preg_match('#\{.*?\}#s', $box['id'], $matches);

			if ( isset($matches[0]) ){
				$path = $matches[0];
				$box['id'] = preg_replace('~(\\{[^}]+\\})~','', $box['id'] );
			} else {
				$adminApp   =   \TPApp()->call('admin');
				$path = $adminApp->templates_path;
			}

			$path = str_replace('{','', $path );
			$path = str_replace('}','', $path );
			include_once $path . 'acl/default_'. $box['id'] . '.php';
			//wp_nonce_field( basename( __FILE__ ), 'mp_admin_save_metabox_role_nonce' );
		}
	}
}
