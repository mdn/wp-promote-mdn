<?php

/**
 * Plugin_name
 * 
 * @package   Plugin_name
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */

/**
 * This class contain all the snippet or extra that improve the experience on the backend
 */
class Pm_Extras_Admin {

	/**
	 * Initialize the snippet
	 */
	function initialize() {
		new Yoast_I18n_WordPressOrg_v3(
				array(
			'textdomain' => PM_TEXTDOMAIN,
			'plugin_name' => PM_NAME,
			'hook' => 'admin_notices',
				)
		);
		whip_wp_check_versions( array(
			'php' => '>=5.6',
		) );
		add_action( 'cmb2_save_options-page_fields', array( $this, 'missing_fields' ), 4, 9999 );
	}

	public function missing_fields( $object_id, $cmb_id, $updated, $object ) {
		$options = get_option( PM_TEXTDOMAIN . '-settings' );
		if ( !$options ) {
			$notice = new WP_Admin_Notice( __( 'Promote MDN is not configured yet!', PM_TEXTDOMAIN ), 'error' );
			$notice->output();
		}
	}

}

$pm_extras_admin = new Pm_Extras_Admin();
$pm_extras_admin->initialize();
do_action( 'plugin_name_pm_extras_admin_instance', $pm_extras_admin );
