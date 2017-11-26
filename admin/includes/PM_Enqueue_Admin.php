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
 * This class contain the Enqueue stuff for the backend
 */
class Pm_Enqueue_Admin {

	/**
	 * Slug of the plugin screen.
	 *
	 * @var string
	 */
	protected $admin_view_page = null;

	/**
	 * Initialize the class
	 */
	public function initialize() {
		if ( !apply_filters( 'plugin_name_pm_enqueue_admin_initialize', true ) ) {
			return;
		}
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . PM_TEXTDOMAIN . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if ( !isset( $this->admin_view_page ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $this->admin_view_page === $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
			wp_enqueue_style( PM_TEXTDOMAIN . '-settings-styles', plugins_url( 'admin/assets/css/settings.css', PM_PLUGIN_ABSOLUTE ), array( 'dashicons' ), PM_VERSION );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if ( !isset( $this->admin_view_page ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->admin_view_page === $screen->id ) {
			wp_enqueue_script( PM_TEXTDOMAIN . '-settings-script', plugins_url( 'admin/assets/js/settings.js', PM_PLUGIN_ABSOLUTE ), array( 'jquery', 'jquery-ui-tabs' ), PM_VERSION );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 2.0.0
	 * 
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a settings page for this plugin to the main menu
		 * 
		 */
		$this->admin_view_page = add_menu_page( PM_NAME, PM_NAME, 'manage_options', PM_TEXTDOMAIN, array( $this, 'display_plugin_admin_page' ), 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( PM_PLUGIN_ROOT.'admin/assets/img/logo.svg' ) ), 90 );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 2.0.0
	 * 
	 * @return void
	 */
	public function display_plugin_admin_page() {
		include_once( PM_PLUGIN_ROOT . 'admin/views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 2.0.0
	 * 
	 * @param array $links Array of links.
	 * 
	 * @return array
	 */
	public function add_action_links( $links ) {
		return array_merge(
				array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=' . PM_TEXTDOMAIN ) . '">' . __( 'Settings' ) . '</a>',
				), $links
		);
	}

}

$pm_enqueue_admin = new Pm_Enqueue_Admin();
$pm_enqueue_admin->initialize();
do_action( 'plugin_name_pm_enqueue_admin_instance', $pm_enqueue_admin );
