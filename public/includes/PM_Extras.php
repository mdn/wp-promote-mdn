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
 * This class contain all the snippet or extra that improve the experience on the frontend
 */
class Pm_Extras {
	/**
	 * Initialize the snippet
	 */
	function initialize() {
	}
	}
$pm_extras = new Pm_Extras();
$pm_extras->initialize();
do_action( 'plugin_name_pm_extras_instance', $pm_extras );
