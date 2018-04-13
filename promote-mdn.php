<?php
/**
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 *
 * Plugin Name:       Promote MDN
 * Description:       Automatically links your WordPress blog with MDN.
 * Version:           2.0.4
 * Author:            Daniele Scasciafratte and Luke Crouch
 * Author URI:        https://github.com/mdn/wp-promote-mdn
 * Text Domain:       promote-mdn
 * License:           GPL 2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate-Powered: v2.0.5
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}
define( 'PM_VERSION', '2.0.4' );
define( 'PM_TEXTDOMAIN', 'promote-mdn' );
define( 'PM_NAME', 'Promote MDN' );
define( 'PM_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'PM_PLUGIN_ABSOLUTE',  __FILE__  );

require_once( PM_PLUGIN_ROOT . 'composer/autoload.php' );
require_once( PM_PLUGIN_ROOT . 'includes/functions.php' );
require_once( PM_PLUGIN_ROOT . 'public/Promote_MDN.php' );
require_once( PM_PLUGIN_ROOT . 'includes/PM_ActDeact.php' );
require_once( PM_PLUGIN_ROOT . 'includes/PM_Uninstall.php' );
/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() ) {
	if (
			(function_exists( 'wp_doing_ajax' ) && !wp_doing_ajax() ||
			(!defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
	) {
		require_once( PM_PLUGIN_ROOT . 'admin/Promote_MDN_Admin.php' );
	}
}
