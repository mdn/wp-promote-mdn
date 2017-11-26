<?php
/**
 * Promote_MDN
 *
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */
/**
 * This class should ideally be used to work with the administrative side of the WordPress site.
 */
class Promote_MDN_Admin {
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since 2.0.0
	 * 
	 * @return void
	 */
	public static function initialize() {
		if ( !apply_filters( 'plugin_name_pm_admin_initialize', true ) ) {
			return;
		}
		require_once( PM_PLUGIN_ROOT . 'admin/includes/lib/cmb2/init.php' );
		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		  if( ! is_super_admin() ) {
		  return;
		  }
		 */
		require_once( PM_PLUGIN_ROOT . 'admin/includes/PM_Enqueue_Admin.php' );
		/*
		 * All the extras functions
		 */
		require_once( PM_PLUGIN_ROOT . 'admin/includes/PM_Extras_Admin.php' );
	}
	/**
	 * Return an instance of this class.
	 *
	 * @since 2.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		  if( ! is_super_admin() ) {
		  return;
		  }
		 */
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			try {
				self::$instance = new self;
				self::initialize();
			} catch ( Exception $err ) {
				do_action( 'plugin_name_admin_failed', $err );
				if ( WP_DEBUG ) {
					throw $err->getMessage();
				}
			}
		}
		return self::$instance;
	}
}
add_action( 'plugins_loaded', array( 'Promote_MDN_Admin', 'get_instance' ) );
