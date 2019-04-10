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
namespace tp\admin\models;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'tp\admin\models\Addon_Model_List' ) ) {
	if( ! class_exists( 'WP_List_Table' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	class Addon_Model_List extends \WP_List_Table {
		/**
		 * @var string
		 */
		var $no_items_message = '';


		/**
		 * @var array
		 */
		var $sortable_columns = array();


		/**
		 * @var string
		 */
		var $default_sorting_field = '';


		/**
		 * @var array
		 */
		var $actions = array();


		/**
		 * @var array
		 */
		var $bulk_actions = array();


		/**
		 * @var array
		 */
		var $columns = array();


		/**
		 * MP_Roles_List_Table constructor.
		 *
		 * @param array $args
		 */
		function __construct( $args = array() ){
			$args = wp_parse_args( $args, array(
				'singular'  => __( 'item', 'tz-portfolio' ),
				'plural'    => __( 'items', 'tz-portfolio' ),
				'ajax'      => false
			) );

			$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', 'tz-portfolio' );

			parent::__construct( $args );
		}


		/**
		 *
		 */
		function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
		}


		/**
		 * @param object $item
		 * @param string $column_name
		 *
		 * @return string
		 */
		function column_default( $item, $column_name ) {
			if( isset( $item[ $column_name ] ) ) {
				return $item[ $column_name ];
			} else {
				return '';
			}
		}


		/**
		 *
		 */
		function no_items() {
			echo $this->no_items_message;
		}


		/**
		 * @param array $args
		 *
		 * @return $this
		 */
		function set_sortable_columns( $args = array() ) {
			$return_args = array();
			foreach( $args as $k=>$val ) {
				if( is_numeric( $k ) ) {
					$return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
				} else if( is_string( $k ) ) {
					$return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
				} else {
					continue;
				}
			}
			$this->sortable_columns = $return_args;
			return $this;
		}


		/**
		 * @return array
		 */
		function get_sortable_columns() {
			return $this->sortable_columns;
		}


		/**
		 * @param array $args
		 *
		 * @return $this
		 */
		function set_columns( $args = array()  ) {
			if( count( $this->bulk_actions ) ) {
				$args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
			}
			$this->columns = $args;
			return $this;
		}


		/**
		 * @return array
		 */
		function get_columns() {
			return $this->columns;
		}


		/**
		 * @param array $args
		 *
		 * @return $this
		 */
		function set_actions( $args = array() ) {
			$this->actions = $args;
			return $this;
		}


		/**
		 * @return array
		 */
		function get_actions() {
			return $this->actions;
		}


		/**
		 * @param array $args
		 *
		 * @return $this
		 */
		function set_bulk_actions( $args = array() ) {
			$this->bulk_actions = $args;
			return $this;
		}


		/**
		 * @return array
		 */
		function get_bulk_actions() {
			return $this->bulk_actions;
		}


		/**
		 * @param object $item
		 *
		 * @return string
		 */
		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="item[]" value="%s" />', $item['key'] );
		}


		/**
		 * @param $item
		 *
		 * @return string
		 */
		function column_title( $item ) {
			$actions = array();
			$addonApp=  \TPApp()->call('addon');
			if ($addonApp->is_plugin_active($item['key'])) {
				$actions['deactivate'] = '<a href="admin.php?page=tzportfolio-addon&action=deactivate&id=' . $item['key'] . '&_wpnonce=' . wp_create_nonce( 'tp_addon_deactivate' . $item['key'] . get_current_user_id() ) . '">' . __( 'Deactivate', 'tz-portfolio' ). '</a>';
			} else {
				$actions['activate'] = '<a href="admin.php?page=tzportfolio-addon&action=activate&id=' . $item['key'] . '&_wpnonce=' . wp_create_nonce( 'tp_addon_activate' . $item['key'] . get_current_user_id() ) . '">' . __( 'Activate', 'tz-portfolio' ). '</a>';
			}

			$actions['settings'] = '<a href="admin.php?page=tzportfolio-addon&action=settings&id=' . $item['key'] . '">' . __( 'Settings', 'tz-portfolio' ). '</a>';
			$actions['delete'] = '<a href="admin.php?page=tzportfolio-addon&action=delete&id=' . $item['key'] . '&_wpnonce=' . wp_create_nonce( 'tp_addon_delete' . $item['key'] . get_current_user_id() ) . '" onclick="return confirm( \'' . __( 'Are you sure you want to delete this addon?', 'tz-portfolio' ) . '\' );">' . __( 'Delete', 'tz-portfolio' ). '</a>';


			return sprintf('%1$s %2$s', '<strong><a class="row-title" href="admin.php?page=tzportfolio-addon&action=settings&id=' . $item['key'] . '">' . $item['Name'] . '</a></strong>', $this->row_actions( $actions ) );
		}

		/**
		 * @param $item
		 *
		 * @return string
		 */
		function column_type( $item ) {
			return ! empty( $item['Type'] ) ? $item['Type'] : '-';
		}

		/**
		 * @param $item
		 *
		 * @return string
		 */
		function column_element( $item ) {
			return ! empty( $item['Element'] ) ? $item['Element'] : '-';
		}

		/**
		 * @param $item
		 *
		 * @return string
		 */
		function column_version( $item ) {
			return ! empty( $item['Version'] ) ? $item['Version'] : '-';
		}

		/**
		 * @param $item
		 *
		 * @return string
		 */
		function column_author( $item ) {
			return ! empty( $item['Author'] ) ? $item['Author'] : '-';
		}


		/**
		 * @param array $attr
		 */
		function tp_set_pagination_args( $attr = array() ) {
			$this->set_pagination_args( $attr );
		}
	}
}