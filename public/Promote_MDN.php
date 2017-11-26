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
 * This class should ideally be used to work with the public-facing side of the WordPress site.
 */
class Promote_MDN {
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 2.0.0
	 * 
	 * @return void
	 */
	public static function initialize() {
		require_once( PM_PLUGIN_ROOT . 'public/includes/PM_Extras.php' );
		require_once( PM_PLUGIN_ROOT . 'public/includes/PM_Enqueue.php' );
		require_once( PM_PLUGIN_ROOT . 'public/includes/PM_Content.php' );
		require_once( PM_PLUGIN_ROOT . 'public/widgets/widget.php' );
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since 2.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			try {
				self::$instance = new self;
				self::initialize();
			} catch ( Exception $err ) {
				do_action( 'plugin_name_failed', $err );
				if ( WP_DEBUG ) {
					throw $err->getMessage();
				}
			}
		}
		return self::$instance;
	}
}
/*
 * @TODO:
 *
 * - 9999 is used for load the plugin as last for resolve some
 *   problems when the plugin use API of other plugins, remove
 *   if you don' want this
 */
add_action( 'plugins_loaded', array( 'Promote_MDN', 'get_instance' ), 9999 );
