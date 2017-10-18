<?php
/**
 * Promote_MDN
 * 
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Your Name or Company Name
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */
/**
 * This class contain the Templating stuff for the frontend
 */
class Pm_Template {
	/**
	 * Initialize the class
	 */
	public function initialize() {
		if ( !apply_filters( 'plugin_name_pm_template_initialize', true ) ) {
			return;
		}
		
	}
	
}
$pm_enqueue = new Pm_Template();
$pm_enqueue->initialize();
do_action( 'plugin_name_pm_template_instance', $pm_enqueue );
