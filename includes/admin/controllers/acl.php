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
use tp\MVC;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\admin\controllers\ACL' ) ) {


	/**
	 * Class ACL
	 * @package tp\admin\controllers
	 */
	class ACL extends MVC {
		var $list_table = null;
		var $data_edit  = null;
		/**
		 * ACL constructor.
		 */
		function __construct() {
			parent::__construct();
			if ( empty( $_GET['action'] ) ) {
				$this->getListTable();
				$this->display();
			} elseif ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' ) {
				$this->edit();
				$this->display('', 'edit');
			} else {
				if ( isset( $_GET['action'] ) ) {
					switch ( $_GET['action'] ) {
						/* delete action */
						case 'delete': {
							$this->delete();
							break;
						}
						case 'reset': {
							$this->reset();
							break;
						}
					}
				} else {
					tp_js_redirect( get_admin_url(). 'admin.php?page=tzportfolio-acl' );
				}

			}
		}
		public function getListTable () {
			global $wp_roles;
			if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
				$redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
			} else {
				$redirect = get_admin_url(). 'admin.php?page=tzportfolio-acl';
			}

			//remove extra query arg
			if ( ! empty( $_GET['_wp_http_referer'] ) )
				tp_js_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

			$order_by = 'name';
			$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';
			$this->list_table  =   $this->getModel('ListTable', array(
				'singular'  => __( 'Role', 'tz-portfolio' ),
				'plural'    => __( 'Roles', 'tz-portfolio' ),
				'ajax'      => false
			));

			$per_page   = 20;
			$paged      = $this->list_table->get_pagenum();

			$this->list_table->set_bulk_actions( array(
				'delete' => __( 'Delete', 'tz-portfolio' )
			) );

			$this->list_table->set_columns( array(
				'title'         => __( 'Role Title', 'tz-portfolio' ),
				'roleid'        => __( 'Role ID', 'tz-portfolio' ),
				'users'         => __( 'No.of Members', 'tz-portfolio' )
			) );

			$this->list_table->set_sortable_columns( array(
				'title' => 'title'
			) );

			$users_count = count_users();

			$roles = array();
			$role_keys = get_option( 'tp_roles' );

			if ( $role_keys ) {
				foreach ( $role_keys as $role_key ) {
					$role_meta = get_option( "tp_role_{$role_key}_meta" );
					if ( $role_meta ) {

						$roles['mp_' . $role_key] = array(
							'key'   => $role_key,
							'users' => ! empty( $users_count['avail_roles']['mp_' . $role_key] ) ? $users_count['avail_roles']['mp_' . $role_key] : 0
						);
						$roles['mp_' . $role_key] = array_merge( $roles['mp_' . $role_key], $role_meta );
					}
				}
			}

			foreach ( $wp_roles->roles as $roleID => $role_data ) {
				if ( in_array( $roleID, array_keys( $roles ) ) )
					continue;

				$roles[$roleID] = array(
					'key'   => $roleID,
					'users' => ! empty( $users_count['avail_roles'][$roleID] ) ? $users_count['avail_roles'][$roleID] : 0,
					'name' => $role_data['name']
				);

				$role_meta = get_option( "tp_role_{$roleID}_meta" );
				if ( $role_meta )
					$roles[$roleID] = array_merge( $roles[$roleID], $role_meta );
			}

			switch( strtolower( $order ) ) {
				case 'asc':
					uasort( $roles, function( $a, $b ) {
						return strnatcmp( $a['name'], $b['name'] );
					} );
					break;
				case 'desc':
					uasort( $roles, function( $a, $b ) {
						return strnatcmp( $a['name'], $b['name'] ) * -1;
					} );
					break;
			}

			$this->list_table->prepare_items();
			$this->list_table->items = array_slice( $roles, ( $paged - 1 ) * $per_page, $per_page );
			$this->list_table->mp_set_pagination_args( array( 'total_items' => count( $roles ), 'per_page' => $per_page ) );
		}

		public function edit() {
			wp_enqueue_script( 'postbox' );
			wp_enqueue_media();

			/**
			 * MP hook
			 *
			 * @type action
			 * @title tp_roles_add_meta_boxes
			 * @description Add meta boxes on add/edit TP Role
			 * @input_vars
			 * [{"var":"$meta","type":"string","desc":"Meta Box Key"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'tp_roles_add_meta_boxes', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'tp_roles_add_meta_boxes', 'my_roles_add_meta_boxes', 10, 1 );
			 * function my_roles_add_meta_boxes( $meta ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			$roleApp    =   \TPApp()->call('roles');
			$this->data_edit['form']        =   $this->getModel('Edit');
			$this->data_edit['form']->roles =   $roleApp;
			$this->data_edit['form']->add_metabox_role();
			do_action( 'tp_roles_add_meta_boxes', 'tp_role_meta' );

			$data = array();
			$option = array();
			global $wp_roles;

			if ( ! empty( $_GET['id'] ) ) {
				$data = get_option( "tp_role_{$_GET['id']}_meta" );
				if ( empty( $data['_tp_is_custom'] ) )
					$data['name'] = $wp_roles->roles[ $_GET['id'] ]['name'];
			}


			if ( ! empty( $_POST['role'] ) ) {

				$data = $_POST['role'];

				// Song data
				$data['edit_tp_post'] = $data['read_tp_post'] = $data['edit_tp_posts'];
				$data['delete_tp_post'] = $data['delete_tp_posts'];

				$id = '';
				$redirect = '';
				$error = '';
				if ( empty( $data['name'] ) ) {

					$error .= __( 'Title is empty!', 'tz-portfolio' ) . '<br />';

				} else {

					if ( 'edit' == $_GET['action'] && ! empty( $_GET['id'] ) ) {
						$id = $_GET['id'];
						$redirect = add_query_arg( array( 'page' => 'tzportfolio-acl', 'action'=>'edit', 'id'=>$id, 'msg'=>'u' ), admin_url( 'admin.php' ) );
					}

				}

				$all_roles = array_keys( get_editable_roles() );
				if ( 'add' == $_GET['action'] ) {
					if ( in_array( 'tp_' . $id, $all_roles ) || in_array( $id, $all_roles ) )
						$error .= __( 'Role already exists!', 'tz-portfolio' ) . '<br />';
				}

				if ( '' == $error ) {

					if ( 'add' == $_GET['action'] ) {
						$roles = get_option( 'tp_roles' );
						$roles[] = $id;

						update_option( 'tp_roles', $roles );
					}

					$role_meta = $data;
					if (isset($role_meta['name'])) unset($role_meta['name']);
					unset( $role_meta['id'] );
					update_option( "tp_role_{$id}_meta", $role_meta );
					$roleApp->set_roles($id);

					tp_js_redirect( $redirect );
				}
			}

			global $current_screen;
			$this->data_edit['screen_id']   =   $current_screen->id;
			$this->data_edit['data']        =   $data;
			$this->data_edit['option']      =   $option;

		}
		public function reset() {
			if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
				$redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
			} else {
				$redirect = get_admin_url(). 'admin.php?page=tzportfolio-acl';
			}
			$role_keys = array();
			if ( isset( $_REQUEST['id'] ) ) {
				check_admin_referer( 'tp_role_reset' .  $_REQUEST['id'] . get_current_user_id() );
				$role_keys = (array)$_REQUEST['id'];
			} elseif( isset( $_REQUEST['item'] ) )  {
				check_admin_referer( 'bulk-' . sanitize_key( __( 'Roles', 'tz-portfolio' ) ) );
				$role_keys = $_REQUEST['item'];
			}
			if ( ! count( $role_keys ) )
				tp_js_redirect( $redirect );

			$roleApp    =   \TPApp()->call('roles');
			foreach ( $role_keys as $k=>$role_key ) {
				$roleApp->reset_default_role($role_key);
			}

			tp_js_redirect( add_query_arg( 'msg', 'reset', $redirect ) );
		}
	}

}