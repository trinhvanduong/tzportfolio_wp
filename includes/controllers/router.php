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

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\controllers\Router' ) ) {


	/**
	 * Class Router
	 * @package tp\controllers
	 */
	class Router {


		/**
		 * Run backend process
		 */
		function backend_requests() {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
			$user_id = get_current_user_id();

			if ( empty( $_REQUEST['tp_action'] ) )
				exit( __( 'Wrong action', 'tz-portfolio' ) );

			if ( empty( $_REQUEST['tp_resource'] ) )
				exit( __( 'Wrong resource', 'tz-portfolio' ) );

			if ( $_REQUEST['tp_action'] == 'route' )
				$verify = wp_verify_nonce( $_REQUEST['tp_verify'], $ip . $user_id . $_REQUEST['tp_resource'] . $_REQUEST['tp_method'] );
			else
				$verify = wp_verify_nonce( $_REQUEST['tp_verify'], $ip . $user_id . $_REQUEST['tp_action'] . $_REQUEST['tp_resource'] );

			if ( empty( $verify ) )
				exit( __( 'Wrong nonce', 'tz-portfolio' ) );

			$this->request_process( array(
				'route'     => $_REQUEST['tp_resource'],
				'method'    => $_REQUEST['tp_method']
			) );
		}


		/**
		 * Request process
		 *
		 * @param $params array
		 * @return bool
		 */
		function request_process( $params ) {
			if ( empty( $params['route'] ) || empty( $params['method'] ) )
				return false;

			$route = str_replace( array( '!', '/' ), '\\', $params['route'] );

			if ( ! class_exists( $route ) )
				return false;

			if ( method_exists( $route, 'instance' ) )
				$object = $route::instance();
			else
				$object = new $route();

			if ( ! method_exists( $object, $params['method'] ) )
				return false;


			call_user_func( array( &$object, $params['method'] ) );
			return true;
		}


		/**
		 * Run frontend process
		 */
		function frontend_requests() {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
			$user_id = get_current_user_id();
			if ( ! get_query_var( 'tp_action' ) )
				exit( __( 'Wrong action', 'tz-portfolio' ) );

			if ( ! get_query_var( 'tp_resource' ) )
				exit( __( 'Wrong resource', 'tz-portfolio' ) );

			$verify = false;
			if ( get_query_var( 'tp_action' ) == 'route' )
				$verify = wp_verify_nonce( get_query_var( 'tp_verify' ), $ip . $user_id . get_query_var( 'tp_resource' ) . get_query_var( 'tp_method' ) );

			if ( $verify ) {
				if ( get_query_var( 'tp_action' ) == 'route' ) {
					$this->request_process( array(
						'route' => get_query_var( 'tp_resource' ),
						'method' => get_query_var( 'tp_method' )
					) );
				}
			} else {
				exit( __( 'Wrong nonce', 'tz-portfolio' ) );
			}
		}

	}
}