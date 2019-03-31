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
namespace tp;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'tp\MVC' ) ) {


	/**
	 * Class Config
	 *
	 * Class with global variables for TPApp
	 *
	 * @package tp
	 */
	class MVC {

		var $name = null;

		/**
		 * Controllers constructor.
		 */
		public function __construct() {
			spl_autoload_register( array( $this, 'tp__autoloader' ) );
			$array = explode( '\\', get_class($this) );
			$this->name = end($array);
		}

		/**
		 * Autoload TPApp classes handler
		 *
		 * @since 2.0
		 *
		 * @param $class
		 */
		function tp__autoloader( $class ) {
			\TPApp()->tp__autoloader($class);
		}

		public function display($view = '', $layout = '') {
			$adminApp   =   \TPApp()->call('admin');
			$view       =   $view ? $view : strtolower($this->name);
			$layout     =   $layout ? 'default_'.$layout.'.php' : 'default.php' ;
			include_once $adminApp->templates_path . $view. '/' .$layout;
		}

		public function getModel($model, $config = array()) {
			$class_name =   '';
			if (\TPApp()->is_request( 'admin' )) {
				$class_name     =   'tp\admin\models\\'.$this->name.'_Model_'.$model;
			} elseif (\TPApp()->is_request( 'frontend' )) {
				$class_name     =   'tp\models\\'.$this->name.'_Model_'.$model;
			}
			if ($class_name) {
				\TPApp()->set_class($class_name, $config);
				return \TPApp()->call($class_name);
			} else {
				return false;
			}
		}
	}
}