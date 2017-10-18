<?php
/**
 * Plugin_name
 * 
 * @package   Plugin_name
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Your Name or Company Name
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
			}
				}
$pm_extras_admin = new Pm_Extras_Admin();
$pm_extras_admin->initialize();
do_action( 'plugin_name_pm_extras_admin_instance', $pm_extras_admin );
