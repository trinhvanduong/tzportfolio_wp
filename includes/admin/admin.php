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
            add_action( 'restrict_manage_posts', array( &$this, 'filter_portfolio_by_taxonomies' ) , 10, 2);
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
         * Filter taxonomies on post view
         * @param $post_type
         * @param $which
         */
        function filter_portfolio_by_taxonomies( $post_type, $which ) {

            // Apply this only on a specific post type
            if ( 'tp_post' !== $post_type )
                return;

            // A list of taxonomy slugs to filter by
            $taxonomies = array( 'tp_category', 'tp_tag' );

            foreach ( $taxonomies as $taxonomy_slug ) {

                // Retrieve taxonomy data
                $taxonomy_obj = get_taxonomy( $taxonomy_slug );
                $taxonomy_name = $taxonomy_obj->labels->name;

                // Retrieve taxonomy terms
                $terms = get_terms( $taxonomy_slug );

                // Display filter HTML
                echo "<select name='{$taxonomy_slug}' id='{$taxonomy_slug}' class='postform'>";
                echo '<option value="">' . sprintf( esc_html__( 'Show All %s', 'tz-portfolio' ), $taxonomy_name ) . '</option>';
                foreach ( $terms as $term ) {
                    printf(
                        '<option value="%1$s" %2$s>%3$s (%4$s)</option>',
                        $term->slug,
                        ( ( isset( $_GET[$taxonomy_slug] ) && ( $_GET[$taxonomy_slug] == $term->slug ) ) ? ' selected="selected"' : '' ),
                        $term->name,
                        $term->count
                    );
                }
                echo '</select>';
            }

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