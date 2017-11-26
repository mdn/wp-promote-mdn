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
 * This class contain the Enqueue stuff for the frontend
 */
class Pm_Enqueue {
	/**
	 * Initialize the class
	 */
	public function initialize() {
		if ( !apply_filters( 'promote_mdn_enqueue_initialize', true ) ) {
			return;
		}
	}
			
}
$pm_enqueue = new Pm_Enqueue();
$pm_enqueue->initialize();
do_action( 'promote_mdn_enqueue_instance', $pm_enqueue );
