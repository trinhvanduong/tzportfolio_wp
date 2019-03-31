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

use tp\MVC;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\admin\controllers\Admin_Menu' ) ) {


	/**
	 * Class Admin_Menu
	 * @package tp\admin\controllers
	 */
	class Admin_Menu extends MVC {


		/**
		 * @var string
		 */
		var $pagehook;
		var $slug = 'tzportfolio';


		/**
		 * Admin_Menu constructor.
		 */
		function __construct() {
			add_action( 'admin_menu', array( &$this, 'primary_admin_menu' ), 0 );
			add_action( 'admin_menu', array( &$this, 'secondary_menu_items' ), 1000 );
			add_action( 'admin_menu', array( &$this, 'extension_menu' ), 9999 );
            parent::__construct();
//			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1000 );
		}


		/**
		 * Change the admin footer text on TP admin pages
		 *
		 * @param $footer_text
		 *
		 * @return string
		 */
		public function admin_footer_text( $footer_text ) {
			$current_screen = get_current_screen();

			// Add the dashboard pages
			$tp_pages[] = 'toplevel_page_tzportfolio';
			$tp_pages[] = 'tz-portfolio_page_tp_options';
			$tp_pages[] = 'tz-portfolio_page_tzportfolio-extensions';

			if ( isset( $current_screen->id ) && in_array( $current_screen->id, $tp_pages ) ) {
				// Change the footer text
				if ( ! get_option( 'tp_admin_footer_text_rated' ) ) {

					ob_start(); ?>
						<a href="https://wordpress.org/support/plugin/tz-portfolio/reviews/?filter=5" target="_blank" class="tp-admin-rating-link" data-rated="<?php esc_attr_e( 'Thanks :)', 'tz-portfolio' ) ?>">
							&#9733;&#9733;&#9733;&#9733;&#9733;
						</a>
					<?php $link = ob_get_clean();

					ob_start();

					printf( __( 'If you like TZ Portfolio please consider leaving a %s review. It will help us to grow the plugin and make it more popular. Thank you.', 'tz-portfolio' ), $link ) ?>

					<script type="text/javascript">
						jQuery( 'a.tp-admin-rating-link' ).click(function() {
							jQuery.ajax({
								url: wp.ajax.settings.url,
								type: 'post',
								data: {
									action: 'tp_rated',
									nonce: tp_admin_scripts.nonce
								},
								success: function(){

								}
							});
							jQuery(this).parent().text( jQuery( this ).data( 'rated' ) );
						});
					</script>

					<?php $footer_text = ob_get_clean();
				}
			}

			return $footer_text;
		}


		/**
		 * When user clicks the review link in backend
		 */
		function tzportfolio_rated() {
			TPApp()->admin()->check_ajax_nonce();

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Please login as administrator', 'tz-portfolio' ) );
			}

			update_option( 'tp_admin_footer_text_rated', 1 );
			wp_send_json_success();
		}


		/**
		 * Setup admin menu
		 */
		function primary_admin_menu() {
			$this->pagehook = add_menu_page( __( 'TZ Portfolio', 'tz-portfolio' ), __( 'TZ Portfolio', 'tz-portfolio' ), 'read', $this->slug, array( &$this, 'admin_page' ), 'dashicons-schedule', '25');

			add_action( 'load-' . $this->pagehook, array( &$this, 'on_load_page' ) );

			add_submenu_page( $this->slug, __( 'Dashboard', 'tz-portfolio' ), __( 'Dashboard', 'tz-portfolio' ), 'read', $this->slug, array( &$this, 'admin_page' ) );
		}


		/**
		 * Secondary admin menu (after settings)
		 */
		function secondary_menu_items() {
			add_submenu_page( $this->slug, __( 'Posts', 'tz-portfolio' ), __( 'Posts', 'tz-portfolio' ), 'edit_tp_posts', 'edit.php?post_type=tp_post', '' );
			add_submenu_page( $this->slug, __( 'Categories', 'tz-portfolio' ), __( 'Categories', 'tz-portfolio' ), 'edit_tp_posts', 'edit-tags.php?taxonomy=tp_category&post_type=tp_post', '' );
			add_submenu_page( $this->slug, __( 'Tags', 'tz-portfolio' ), __( 'Tags', 'tz-portfolio' ), 'edit_tp_posts', 'edit-tags.php?taxonomy=tp_tag&post_type=tp_post', '' );
			add_submenu_page(
				$this->slug,
				esc_html__( 'ACL Manager',  'tz-portfolio' ), /*page title*/
				esc_html__( 'ACL',  'tz-portfolio' ), /*menu title*/
				'manage_options', /*roles and capabiliyt needed*/
				$this->slug.'-acl',
				array( &$this, 'acl_page' )
			);

			/**
			 * TPApp hook
			 *
			 * @type action
			 * @title tp_extend_admin_menu
			 * @description Extend TPApp menu
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'tp_extend_admin_menu', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'tp_extend_admin_menu', 'my_extend_admin_menu', 10 );
			 * function my_extend_admin_menu() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'tp_extend_admin_menu' );
		}

		/**
		 * ACL menu callback
		 */
		function acl_page () {
			$acl  =   new ACL();
		}


		/**
		 * Role page menu callback
		 */
		function tp_roles_pages() {
			if ( empty( $_GET['tab'] ) ) {
				include_once tp_path . 'includes/admin/controllers/list-tables/roles-list-table.php';
			} elseif ( $_GET['tab'] == 'add' || $_GET['tab'] == 'edit' ) {
				include_once tp_path . 'includes/admin/templates/role/role-edit.php';
			} else {
				tp_js_redirect( add_query_arg( array( 'page' => 'tp_roles' ), get_admin_url( 'admin.php' ) ) );
			}
		}


		/**
		 * Extension menu
		 */
		function extension_menu() {
			add_submenu_page( $this->slug, __( 'Addons', 'tz-portfolio' ), '<span style="color: #00B9EB">' .__( 'Addons', 'tz-portfolio' ) . '</span>', 'manage_options', $this->slug . '-extensions', array( &$this, 'admin_page' ) );
		}


		/**
		 * Load metabox stuff
		 */
		function on_load_page() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			/** custom metaboxes for dashboard defined here **/
			add_meta_box( 'tp-metaboxes-contentbox-1', __( 'Overview','tz-portfolio' ), array( &$this, 'portfolio_overview' ), $this->pagehook, 'core', 'core' );

			add_meta_box( 'tp-metaboxes-mainbox-1', __( 'Latest from our blog', 'tz-portfolio' ), array( &$this, 'tp_news' ), $this->pagehook, 'normal', 'core' );

			add_meta_box( 'tp-metaboxes-sidebox-1', __( 'Version Information', 'tz-portfolio' ), array( &$this, 'version_information' ), $this->pagehook, 'side', 'core' );

			add_meta_box( 'tp-metaboxes-sidebox-2', __( 'Author Information', 'tz-portfolio' ), array( &$this, 'author_information' ), $this->pagehook, 'side', 'core' );
		}


		/**
		 *
		 */
		function tp_news() {
			$this->display('dashboard','feed');
		}


		/**
		 *
		 */
		function portfolio_overview() {
			$this->display('dashboard','overview');
		}


		/**
		 *
		 */
		function version_information() {
			$this->display('dashboard','version');
		}


		/**
		 *
		 */
		function author_information() {
			$this->display('dashboard','author');
		}


		/**
		 * Which admin page to show?
		 */
		function admin_page() {

			$page = $_REQUEST['page'];
			if ( $page == 'tzportfolio' && ! isset( $_REQUEST['tp-addon'] ) ) { ?>

				<div id="tp-metaboxes-general" class="wrap">

					<h1>TZ Portfolio <sup><?php echo tp_version; ?></sup></h1>

					<?php wp_nonce_field( 'tp-metaboxes-general' ); ?>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

					<input type="hidden" name="action" value="save_tp_metaboxes_general" />

					<div id="dashboard-widgets-wrap">

						<div id="dashboard-widgets" class="metabox-holder tp-metabox-holder">

							<div id="postbox-container-1" class="postbox-container"><?php do_meta_boxes( $this->pagehook, 'core', null );  ?></div>
							<div id="postbox-container-2" class="postbox-container"><?php do_meta_boxes( $this->pagehook, 'normal', null ); ?></div>
							<div id="postbox-container-3" class="postbox-container"><?php do_meta_boxes( $this->pagehook, 'side', null ); ?></div>

						</div>

					</div>

				</div>
				<div class="tp-admin-clear"></div>

				<script type="text/javascript">
					//<![CDATA[
					jQuery(document).ready( function($) {
						// postboxes setup
						postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
					});
					//]]>
				</script>

			<?php } elseif ( $page == 'tzportfolio-extensions' ) {

				include_once TPApp()->admin()->templates_path . 'extensions.php';

			}

		}

	}
}