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

defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );

define( 'tp_url', plugin_dir_url( __FILE__ ) );
define( 'tp_path', plugin_dir_path( __FILE__ ) );
define( 'tp_plugin', plugin_basename( __FILE__ ) );
define( 'tp_version', $plugin_data['Version'] );
define( 'tp_plugin_name', $plugin_data['Name'] );

require_once 'includes/init.php';