<?php
/**
 * Uninstall
 */
// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

if ( ! defined( 'tp_path' ) )
	define( 'tp_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'tp_url' ) )
	define( 'tp_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'tp_plugin' ) )
	define( 'tp_plugin', plugin_basename( __FILE__ ) );

//for delete Email options only for Core email notifications
remove_all_filters( 'tp_email_notifications' );
//for delete only Core Theme Link pages
remove_all_filters( 'tp_core_pages' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';

$delete_options = TPApp()->options()->get( 'uninstall_on_delete' );
if ( ! empty( $delete_options ) ) {

	//remove core pages
	foreach ( TPApp()->config()->core_pages as $page_key => $page_value ) {
		$page_id = TPApp()->options()->get( TPApp()->options()->get_core_page_id( $page_key ) );
		if ( ! empty( $page_id ) )
			wp_delete_post( $page_id, true );
	}

	//remove core settings
	$settings_defaults = TPApp()->config()->settings_defaults;
	foreach ( $settings_defaults as $k => $v ) {
		TPApp()->options()->remove( $k );
	}

	//delete TPApp Custom Post Types posts
	$tp_posts = get_posts( array(
		'post_type'     => array(
			'tp_form',
			'tp_directory',
			'tp_role'
		),
		'numberposts'   => -1
	) );

	foreach ( $tp_posts as $tp_post )
		wp_delete_post( $tp_post->ID, 1 );

	delete_option( 'tp_options' );
	delete_option( 'tp_version' );
	delete_option( 'tp_is_installed' );
	delete_option( 'tp_core_forms' );
	delete_option( 'tp_core_directories' );
	delete_option( 'tp_last_version_upgrade' );
	delete_option( 'tp_first_setup_roles' );
	delete_option( 'tp_hashed_passwords_fix' );
	delete_option( 'tp_cached_users_queue' );
	delete_option( 'tp_options-transients' );
	delete_option( 'tp_cached_role_admin' );
	delete_option( 'tp_cached_role_member' );
	delete_option( 'tp_cache_fonticons' );
	delete_option( 'widget_tp_search_widget' );
	delete_option( '__ultimatemember_sitekey' );

	foreach ( wp_load_alloptions() as $k => $v ) {
		if ( substr( $k, 0, 18 ) == 'tp_cache_userdata_' )
			delete_option( $k );
	}


	global $wpdb;


	$wpdb->query(
		"DELETE 
        FROM {$wpdb->usermeta} 
        WHERE meta_key LIKE '_tp%' OR 
              meta_key LIKE 'tp%' OR 
              meta_key LIKE 'reviews%' OR 
              meta_key = 'submitted' OR 
              meta_key = 'account_status' OR 
              meta_key = 'password_rst_attempts' OR 
              meta_key = 'profile_photo' OR 
              meta_key = '_enable_new_follow' OR 
              meta_key = '_enable_new_friend' OR 
              meta_key = '_mylists' OR 
              meta_key = '_enable_new_pm' OR 
              meta_key = '_hidden_conversations' OR 
              meta_key = '_pm_blocked' OR 
              meta_key = '_notifications_prefs' OR 
              meta_key = '_profile_progress' OR 
              meta_key = '_completed' OR 
              meta_key = '_cannot_add_review' OR 
              meta_key = 'synced_profile_photo' OR 
              meta_key = 'full_name'"
	);
}