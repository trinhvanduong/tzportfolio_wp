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
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TPApp_Functions' ) ) {


	/**
	 * Class TPApp_Functions
	 */
	class TPApp_Functions {


		/**
		 * TPApp_Functions constructor.
		 */
		function __construct() {
		}


		/**
		 * What type of request is this?
		 *
		 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
		 *
		 * @return bool
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}


		/**
		 * Autoload TPApp classes handler
		 *
		 * @since 2.0
		 *
		 * @param $class
		 */
		function tp__autoloader( $class ) {
			if ( strpos( $class, 'tp' ) === 0 ) {

				$array = explode( '\\', strtolower( $class ) );
				if ( strpos( $class, 'tp_ext' ) === 0 ) {
					$full_path = str_replace( 'tp-portfolio', '', rtrim( tp_path, '/' ) ) . str_replace( '_', '-', $array[1] ) . '/includes/';
					unset( $array[0], $array[1] );
					$path = implode( DIRECTORY_SEPARATOR, $array );
					$path = str_replace( '_', '-', $path );
					$full_path .= $path . '.php';
				} else if ( strpos( $class, 'tp\\' ) === 0 ) {
					$class = implode( '\\', $array );
					$slash = DIRECTORY_SEPARATOR;
					$path = str_replace(
						array( 'tp\\', '_', '\\' ),
						array( $slash, '-', $slash ),
						$class );
					$full_path =  tp_path . 'includes' . $path . '.php';
				}

				if( isset( $full_path ) && file_exists( $full_path ) ) {
					include_once $full_path;
				}
			}
		}


		/**
		 * Get ajax routed URL
		 *
		 * @param string $route
		 * @param string $method
		 *
		 * @return string
		 */
		public function get_ajax_route( $route, $method ) {

			$route = str_replace( array( '\\', '/' ), '!', $route );
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
			$nonce = wp_create_nonce( $ip . get_current_user_id() . $route . $method );

			if ( is_admin() ) {
				$url = add_query_arg( array(
					'action'        => 'tp_router',
					'tp_action'     => 'route',
					'tp_resource'   => $route,
					'tp_method'     => $method,
					'tp_verify'     => $nonce
				), get_admin_url( null, 'admin-ajax.php' ) );
			} else if ( get_option( 'permalink_structure' ) ) {
				$url = get_home_url( null, 'tp-api/route/' . $route . '/' . $method . '/' . $nonce );
			} else {
				$url = add_query_arg( array(
					'tp_page'       => 'api',
					'tp_action'     => 'route',
					'tp_resource'   => $route,
					'tp_method'     => $method,
					'tp_verify'     => $nonce
				), get_home_url() );
			}
			return $url;
		}


		/**
		 * Help Tip displaying
		 *
		 * Function for render/displaying TZ Portfolio help tip
		 *
		 * @since  2.0.0
		 *
		 * @param string $tip Help tip text
		 * @param bool $allow_html Allow sanitized HTML if true or escape
		 * @param bool $echo Return HTML or echo
		 * @return string
		 */
		function tooltip( $tip, $allow_html = false, $echo = true ) {
			if ( $allow_html ) {

				$tip = htmlspecialchars( wp_kses( html_entity_decode( $tip ), array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'small'  => array(),
					'span'   => array(),
					'ul'     => array(),
					'li'     => array(),
					'ol'     => array(),
					'p'      => array(),
				) ) );

			} else {
				$tip = esc_attr( $tip );
			}

			ob_start(); ?>

			<span class="tp_tooltip dashicons dashicons-editor-help" title="<?php echo $tip ?>"></span>

			<?php if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}

		}


		/**
		 * @return mixed|void
		 */
		function excluded_taxonomies() {
			$taxes = array(
				'nav_menu',
				'link_category',
				'post_format',
			);

			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_excluded_taxonomies
			 * @description Exclude taxonomies for TPApp
			 * @input_vars
			 * [{"var":"$taxes","type":"array","desc":"Taxonomies keys"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'tp_excluded_taxonomies', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'tp_excluded_taxonomies', 'my_excluded_taxonomies', 10, 1 );
			 * function my_excluded_taxonomies( $taxes ) {
			 *     // your code here
			 *     return $taxes;
			 * }
			 * ?>
			 */
			return apply_filters( 'tp_excluded_taxonomies', $taxes );
		}


		/**
		 * Output templates
		 *
		 * @access public
		 * @param string $template_name
		 * @param string $basename (default: '')
		 * @param array $t_args (default: array())
		 * @param bool $echo
		 *
		 * @return string|void
		 */
		function get_template( $template_name, $basename = '', $t_args = array(), $echo = false ) {
			if ( ! empty( $t_args ) && is_array( $t_args ) ) {
				extract( $t_args );
			}

			$path = '';
			if( $basename ) {
				$array = explode( '/', trim( $basename, '/' ) );
				$path  = $array[0];
			}

			$located = $this->locate_template( $template_name, $path );
			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return;
			}


			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_get_template
			 * @description Change template location
			 * @input_vars
			 * [{"var":"$located","type":"string","desc":"template Located"},
			 * {"var":"$template_name","type":"string","desc":"Template Name"},
			 * {"var":"$path","type":"string","desc":"Template Path at server"},
			 * {"var":"$t_args","type":"array","desc":"Template Arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_get_template', 'function_name', 10, 4 );
			 * @example
			 * <?php
			 * add_filter( 'tp_get_template', 'my_get_template', 10, 4 );
			 * function my_get_template( $located, $template_name, $path, $t_args ) {
			 *     // your code here
			 *     return $located;
			 * }
			 * ?>
			 */
			$located = apply_filters( 'tp_get_template', $located, $template_name, $path, $t_args );

			ob_start();

			/**
			 * TPApp hook
			 *
			 * @type action
			 * @title tp_before_template_part
			 * @description Make some action before include template file
			 * @input_vars
			 * [{"var":"$template_name","type":"string","desc":"Template Name"},
			 * {"var":"$path","type":"string","desc":"Template Path at server"},
			 * {"var":"$located","type":"string","desc":"template Located"},
			 * {"var":"$t_args","type":"array","desc":"Template Arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'tp_before_template_part', 'function_name', 10, 4 );
			 * @example
			 * <?php
			 * add_action( 'tp_before_template_part', 'my_before_template_part', 10, 4 );
			 * function my_before_template_part( $template_name, $path, $located, $t_args ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'tp_before_template_part', $template_name, $path, $located, $t_args );
			include( $located );

			/**
			 * TPApp hook
			 *
			 * @type action
			 * @title tp_after_template_part
			 * @description Make some action after include template file
			 * @input_vars
			 * [{"var":"$template_name","type":"string","desc":"Template Name"},
			 * {"var":"$path","type":"string","desc":"Template Path at server"},
			 * {"var":"$located","type":"string","desc":"template Located"},
			 * {"var":"$t_args","type":"array","desc":"Template Arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'tp_after_template_part', 'function_name', 10, 4 );
			 * @example
			 * <?php
			 * add_action( 'tp_after_template_part', 'my_after_template_part', 10, 4 );
			 * function my_after_template_part( $template_name, $path, $located, $t_args ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'tp_after_template_part', $template_name, $path, $located, $t_args );
			$html = ob_get_clean();

			if ( ! $echo ) {
				return $html;
			} else {
				echo $html;
				return;
			}
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @access public
		 * @param string $template_name
		 * @param string $path (default: '')
		 * @return string
		 */
		function locate_template( $template_name, $path = '' ) {
			// check if there is template at theme folder
			$template = locate_template( array(
				trailingslashit( 'tz-portfolio/' . $path ) . $template_name
			) );

			if( !$template ) {
				if( $path ) {
					$template = trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . $path );
				} else {
					$template = trailingslashit( tp_path );
				}
				$template .= 'templates/' . $template_name;
			}


			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_locate_template
			 * @description Change template locate
			 * @input_vars
			 * [{"var":"$template","type":"string","desc":"Template locate"},
			 * {"var":"$template_name","type":"string","desc":"Template Name"},
			 * {"var":"$path","type":"string","desc":"Template Path at server"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_filter( 'tp_locate_template', 'function_name', 10, 3 );
			 * @example
			 * <?php
			 * add_filter( 'tp_locate_template', 'my_locate_template', 10, 3 );
			 * function my_locate_template( $template, $template_name, $path ) {
			 *     // your code here
			 *     return $template;
			 * }
			 * ?>
			 */
			return apply_filters( 'tp_locate_template', $template, $template_name, $path );
		}


		/**
		 * @return mixed|void
		 */
		function cpt_list() {
			/**
			 * TPApp hook
			 *
			 * @type filter
			 * @title tp_cpt_list
			 * @description Extend TPApp Custom Post Types
			 * @input_vars
			 * [{"var":"$list","type":"array","desc":"Custom Post Types list"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'tp_cpt_list', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'tp_cpt_list', 'my_cpt_list', 10, 1 );
			 * function my_admin_pending_queue( $list ) {
			 *     // your code here
			 *     return $list;
			 * }
			 * ?>
			 */
			$cpt = apply_filters( 'tp_cpt_list', array( 'tp_post', 'tp_category' ) );
			return $cpt;
		}

	}
}