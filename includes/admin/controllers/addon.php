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

if ( ! class_exists( 'tp\admin\controllers\Addon' ) ) {


	/**
	 * Class ACL
	 * @package tp\admin\controllers
	 */
	class Addon extends MVC {
		var $list_table = null;
		var $data_edit  = null;
		/**
		 * ACL constructor.
		 */
		function __construct() {
			parent::__construct();

		}


		public function instance () {
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
						case 'activate': {
							$this->activate();
							break;
						}
						case 'reset': {
							$this->reset();
							break;
						}
					}
				} else {
					tp_js_redirect( get_admin_url(). 'admin.php?page=tzportfolio-addon' );
				}

			}
		}

		/**
		 * Get List Table
		 */
		public function getListTable () {
			global $wp_roles;
			if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
				$redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
			} else {
				$redirect = get_admin_url(). 'admin.php?page=tzportfolio-addon';
			}

			//remove extra query arg
			if ( ! empty( $_GET['_wp_http_referer'] ) )
				tp_js_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

			$order_by = 'name';
			$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';
			$this->list_table  =   $this->getModel('List', array(
				'singular'  => __( 'Add-on', 'tz-portfolio' ),
				'plural'    => __( 'Add-ons', 'tz-portfolio' ),
				'ajax'      => false
			));

			$per_page   = 20;
			$paged      = $this->list_table->get_pagenum();

			$this->list_table->set_bulk_actions( array(
				'delete' => __( 'Delete', 'tz-portfolio' )
			) );

			$this->list_table->set_columns( array(
				'title'         => __( 'Add-on Name', 'tz-portfolio' ),
				'type'          => __( 'Type', 'tz-portfolio' ),
				'element'       => __( 'Element', 'tz-portfolio' ),
				'version'       => __( 'Version', 'tz-portfolio' )
			) );

			$this->list_table->set_sortable_columns( array(
				'title' => 'title'
			) );

			$addons =   $this->get_plugins('/tz-portfolio/addons');
			$items  =   array();
			if (count($addons)) {
				foreach ($addons as $key => $item) {
					$item['key']=   $key;
					$items[]    =   $item;
				}
			}

			switch( strtolower( $order ) ) {
				case 'asc':
					uasort( $items, function( $a, $b ) {
						return strnatcmp( $a['Name'], $b['Name'] );
					} );
					break;
				case 'desc':
					uasort( $items, function( $a, $b ) {
						return strnatcmp( $a['Name'], $b['Name'] ) * -1;
					} );
					break;
			}

			$this->list_table->prepare_items();
			$this->list_table->items = array_slice( $items, ( $paged - 1 ) * $per_page, $per_page );
			$this->list_table->mp_set_pagination_args( array( 'total_items' => count( $items ), 'per_page' => $per_page ) );
		}

		public function activate() {
			if ( ! empty( $_GET['id'] ) ) {
				$this->activate_plugin($_GET['id']);
			}
			if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
				$redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
			} else {
				$redirect = get_admin_url(). 'admin.php?page=tzportfolio-addon';
			}
			tp_js_redirect( add_query_arg( 'msg', 'activate', $redirect ) );
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

		/**
		 * Check the plugins directory and retrieve all plugin files with plugin data.
		 *
		 * WordPress only supports plugin files in the base plugins directory
		 * (wp-content/plugins) and in one directory above the plugins directory
		 * (wp-content/plugins/my-plugin). The file it looks for has the plugin data
		 * and must be found in those two locations. It is recommended to keep your
		 * plugin files in their own directories.
		 *
		 * The file with the plugin data is the file that will be included and therefore
		 * needs to have the main execution for the plugin. This does not mean
		 * everything must be contained in the file and it is recommended that the file
		 * be split for maintainability. Keep everything in one file for extreme
		 * optimization purposes.
		 *
		 * @since 1.5.0
		 *
		 * @param string $plugin_folder Optional. Relative path to single plugin folder.
		 * @return array Key is the plugin file path and the value is an array of the plugin data.
		 */
		function get_plugins( $plugin_folder = '' ) {

			$cache_plugins = wp_cache_get( 'tz-portfolio', 'addons' );
			if ( ! $cache_plugins ) {
				$cache_plugins = array();
			}

			if ( isset( $cache_plugins[ $plugin_folder ] ) ) {
				return $cache_plugins[ $plugin_folder ];
			}

			$wp_plugins  = array();
			$plugin_root = WP_PLUGIN_DIR;
			if ( ! empty( $plugin_folder ) ) {
				$plugin_root .= $plugin_folder;
			}

			// Files in wp-content/plugins directory
			$plugins_dir  = @ opendir( $plugin_root );
			$plugin_files = array();
			if ( $plugins_dir ) {

				while ( ( $file = readdir( $plugins_dir ) ) !== false ) {

					if ( substr( $file, 0, 1 ) == '.' ) {
						continue;
					}
					if ( is_dir( $plugin_root . '/' . $file ) ) {
						$plugins_subdir = @ opendir( $plugin_root . '/' . $file );
						if ( $plugins_subdir ) {
							while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
								if ( substr( $subfile, 0, 1 ) == '.' ) {
									continue;
								}
								if ( substr( $subfile, -4 ) == '.php' ) {
									$plugin_files[] = "$file/$subfile";
								}
							}
							closedir( $plugins_subdir );
						}
					} else {
						if ( substr( $file, -4 ) == '.php' ) {
							$plugin_files[] = $file;
						}
					}
				}
				closedir( $plugins_dir );
			}

			if ( empty( $plugin_files ) ) {
				return $wp_plugins;
			}

			foreach ( $plugin_files as $plugin_file ) {
				if ( ! is_readable( "$plugin_root/$plugin_file" ) ) {
					continue;
				}

				$plugin_data = $this->get_plugin_data( "$plugin_root/$plugin_file", false, false ); //Do not apply markup/translate as it'll be cached.

				if ( empty( $plugin_data['Name'] ) ) {
					continue;
				}

				$wp_plugins[ plugin_basename( $plugin_file ) ] = $plugin_data;
			}

			uasort( $wp_plugins, '_sort_uname_callback' );

			$cache_plugins[ $plugin_folder ] = $wp_plugins;
			wp_cache_set( 'tz-portfolio', $cache_plugins, 'addons' );

			return $wp_plugins;
		}

		/**
		 * Parses the plugin contents to retrieve plugin's metadata.
		 *
		 * The metadata of the plugin's data searches for the following in the plugin's
		 * header. All plugin data must be on its own line. For plugin description, it
		 * must not have any newlines or only parts of the description will be displayed
		 * and the same goes for the plugin data. The below is formatted for printing.
		 *
		 *     /*
		 *     Plugin Name: Name of Plugin
		 *     Plugin URI: Link to plugin information
		 *     Description: Plugin Description
		 *     Author: Plugin author's name
		 *     Author URI: Link to the author's web site
		 *     Version: Must be set in the plugin for WordPress 2.3+
		 *     Text Domain: Optional. Unique identifier, should be same as the one used in
		 *          load_plugin_textdomain()
		 *     Domain Path: Optional. Only useful if the translations are located in a
		 *          folder above the plugin's base path. For example, if .mo files are
		 *          located in the locale folder then Domain Path will be "/locale/" and
		 *          must have the first slash. Defaults to the base folder the plugin is
		 *          located in.
		 *     Network: Optional. Specify "Network: true" to require that a plugin is activated
		 *          across all sites in an installation. This will prevent a plugin from being
		 *          activated on a single site when Multisite is enabled.
		 *      * / # Remove the space to close comment
		 *
		 * Some users have issues with opening large files and manipulating the contents
		 * for want is usually the first 1kiB or 2kiB. This function stops pulling in
		 * the plugin contents when it has all of the required plugin data.
		 *
		 * The first 8kiB of the file will be pulled in and if the plugin data is not
		 * within that first 8kiB, then the plugin author should correct their plugin
		 * and move the plugin data headers to the top.
		 *
		 * The plugin file is assumed to have permissions to allow for scripts to read
		 * the file. This is not checked however and the file is only opened for
		 * reading.
		 *
		 * @since 1.5.0
		 *
		 * @param string $plugin_file Absolute path to the main plugin file.
		 * @param bool   $markup      Optional. If the returned data should have HTML markup applied.
		 *                            Default true.
		 * @param bool   $translate   Optional. If the returned data should be translated. Default true.
		 * @return array {
		 *     Plugin data. Values will be empty if not supplied by the plugin.
		 *
		 *     @type string $Name        Name of the plugin. Should be unique.
		 *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
		 *     @type string $Description Plugin description.
		 *     @type string $Author      Author's name.
		 *     @type string $AuthorURI   Author's website address (if set).
		 *     @type string $Version     Plugin version.
		 *     @type string $TextDomain  Plugin textdomain.
		 *     @type string $DomainPath  Plugins relative directory path to .mo files.
		 *     @type bool   $Network     Whether the plugin can only be activated network-wide.
		 * }
		 */
		function get_plugin_data( $plugin_file, $markup = true, $translate = true ) {

			$default_headers = array(
				'Name'        => 'Plugin Name',
				'PluginURI'   => 'Plugin URI',
				'Version'     => 'Version',
				'Description' => 'Description',
				'Author'      => 'Author',
				'AuthorURI'   => 'Author URI',
				'TextDomain'  => 'Text Domain',
				'DomainPath'  => 'Domain Path',
				'Network'     => 'Network',
				'Type'        => 'Type',
				'Element'     => 'Element',
				// Site Wide Only is deprecated in favor of Network.
				'_sitewide'   => 'Site Wide Only',
			);
			$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );
			// Site Wide Only is the old header for Network
			if ( ! $plugin_data['Network'] && $plugin_data['_sitewide'] ) {
				/* translators: 1: Site Wide Only: true, 2: Network: true */
				_deprecated_argument( __FUNCTION__, '3.0.0', sprintf( __( 'The %1$s plugin header is deprecated. Use %2$s instead.' ), '<code>Site Wide Only: true</code>', '<code>Network: true</code>' ) );
				$plugin_data['Network'] = $plugin_data['_sitewide'];
			}
			$plugin_data['Network'] = ( 'true' == strtolower( $plugin_data['Network'] ) );
			unset( $plugin_data['_sitewide'] );

			// If no text domain is defined fall back to the plugin slug.
			if ( ! $plugin_data['TextDomain'] ) {
				$plugin_slug = dirname( plugin_basename( $plugin_file ) );
				if ( '.' !== $plugin_slug && false === strpos( $plugin_slug, '/' ) ) {
					$plugin_data['TextDomain'] = $plugin_slug;
				}
			}

			if ( $markup || $translate ) {
				$plugin_data = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
			} else {
				$plugin_data['Title']      = $plugin_data['Name'];
				$plugin_data['AuthorName'] = $plugin_data['Author'];
			}

			return $plugin_data;
		}

		/**
		 * Determines whether a plugin is active.
		 *
		 * Only plugins installed in the plugins/ folder can be active.
		 *
		 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
		 * return false for those plugins.
		 *
		 * For more information on this and similar theme functions, check out
		 * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
		 * Conditional Tags} article in the Theme Developer Handbook.
		 *
		 * @since 2.5.0
		 *
		 * @param string $plugin Path to the plugin file relative to the plugins directory.
		 * @return bool True, if in the active plugins list. False, not in the list.
		 */
		function is_plugin_active( $plugin ) {
			return in_array( $plugin, (array) get_option( 'tp_active_plugins', array() ) );
		}

		function activate_plugin ($plugin) {
			/**
			 * Fires before a plugin is activated.
			 *
			 * If a plugin is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $plugin       Path to the plugin file relative to the plugins directory.
			 */
			do_action( 'tp_activate_plugin', $plugin );

			/**
			 * Fires as a specific plugin is being activated.
			 *
			 * This hook is the "activation" hook used internally by register_activation_hook().
			 * The dynamic portion of the hook name, `$plugin`, refers to the plugin basename.
			 *
			 * If a plugin is silently activated (such as during an update), this hook does not fire.
			 *
			 * @since 2.0.0
			 */
			do_action( "tp_activate_{$plugin}" );
			$current   = get_option( 'tp_active_plugins', array() );
			$current[] = $plugin;
			sort( $current );
			update_option( 'tp_active_plugins', $current );

			/**
			 * Fires after a plugin has been activated.
			 *
			 * If a plugin is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $plugin       Path to the plugin file relative to the plugins directory.
			 */
			do_action( 'tp_activated_plugin', $plugin );
			return true;
		}
	}
}