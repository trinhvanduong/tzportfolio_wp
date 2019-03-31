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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\controllers\Common' ) ) {


	/**
	 * Class Common
	 * @package tp\controllers
	 */
	class Common {
		/**
		 * Common constructor.
		 */
		function __construct() {
			add_action( 'init',  array( &$this, 'create_post_types' ), 1 );
		}


		/**
		 * Create taxonomies for use for TZ Portfolio
		 */
		function create_post_types() {
			register_post_type( 'tp_post', array(
				'labels' => array(
					'name' => __( 'Posts', 'tz-portfolio' ),
					'singular_name' => __( 'All Posts', 'tz-portfolio' ),
					'add_new' => __( 'Add New', 'tz-portfolio' ),
					'add_new_item' => __('Add New Post', 'tz-portfolio' ),
					'edit_item' => __('Edit Post', 'tz-portfolio'),
					'not_found' => __('You did not create any post yet', 'tz-portfolio'),
					'not_found_in_trash' => __('Nothing found in Trash', 'tz-portfolio'),
					'search_items' => __('Search Posts', 'tz-portfolio')
				),
				'capability_type'     => 'tp_post',
				'capabilities' => array(
					'publish_posts' => 'publish_tp_posts',
					'edit_posts' => 'edit_tp_posts',
					'edit_others_posts' => 'edit_others_tp_posts',
					'delete_posts' => 'delete_tp_posts',
					'delete_others_posts' => 'delete_others_tp_posts',
					'read_private_posts' => 'read_private_tp_posts',
					'edit_post' => 'edit_tp_post',
					'delete_post' => 'delete_tp_post',
					'read_post' => 'read_tp_post',
					'delete_private_posts' => 'delete_private_tp_posts',
					'delete_published_posts' => 'delete_published_tp_posts',
					'edit_private_posts' => 'edit_private_tp_posts',
					'edit_published_posts' => 'edit_published_tp_posts'
				),
				'show_ui' => true,
				'show_in_menu' => false,
				'public' => true,
				'taxonomies' => array('tp_category'),
				'rewrite'           => array( 'slug' => 'tp_post' )
			) );

			$labels = array(
				'name'              => _x( 'Categories', 'categories taxonomy', 'tz-portfolio' ),
				'singular_name'     => _x( 'Category', 'category taxonomy', 'tz-portfolio' ),
				'search_items'      => __( 'Search Categories', 'tz-portfolio' ),
				'all_items'         => __( 'All Categories', 'tz-portfolio' ),
				'parent_item'       => __( 'Parent Category', 'tz-portfolio' ),
				'parent_item_colon' => __( 'Parent Category:', 'tz-portfolio' ),
				'edit_item'         => __( 'Edit Category', 'tz-portfolio' ),
				'update_item'       => __( 'Update Category', 'tz-portfolio' ),
				'add_new_item'      => __( 'Add New Category', 'tz-portfolio' ),
				'new_item_name'     => __( 'New Category Name', 'tz-portfolio' ),
				'menu_name'         => __( 'Category', 'tz-portfolio' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'tp_category' )
			);

			register_taxonomy( 'tp_category',  'tp_post' , $args );

            $labels = array(
                'name'                       => _x( 'Tags', 'tags taxonomy', 'tz-portfolio' ),
                'singular_name'              => _x( 'Tag', 'tag taxonomy', 'tz-portfolio' ),
                'search_items'               => __( 'Search Tags', 'tz-portfolio' ),
                'popular_items'              => __( 'Popular Tags', 'tz-portfolio' ),
                'all_items'                  => __( 'All Tags', 'tz-portfolio' ),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __( 'Edit Tags', 'tz-portfolio' ),
                'update_item'                => __( 'Update Tags', 'tz-portfolio' ),
                'add_new_item'               => __( 'Add New Tags', 'tz-portfolio' ),
                'new_item_name'              => __( 'New Tags Name', 'tz-portfolio' ),
                'separate_items_with_commas' => __( 'Separate Tags with commas', 'tz-portfolio' ),
                'add_or_remove_items'        => __( 'Add or remove Tags', 'tz-portfolio' ),
                'choose_from_most_used'      => __( 'Choose from the most used Tags', 'tz-portfolio' ),
                'not_found'                  => __( 'No Tags found.', 'tz-portfolio' ),
                'menu_name'                  => __( 'Tags', 'tz-portfolio' ),
            );

            $args = array(
                'hierarchical'          => false,
                'labels'                => $labels,
                'show_ui'               => true,
                'show_admin_column'     => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'tag' ),
            );

            register_taxonomy( 'tp_tag', 'tp_post', $args );
		}
	}

}