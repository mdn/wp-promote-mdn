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
 * All the CMB related code.
 */
class Pm_CMB {
	/**
	 * Initialize CMB2.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'cmb2_init', array( $this, 'cmb_demo_metaboxes' ) );
	}
	/**
	 * NOTE:     Your metabox on Demo CPT
	 *
	 * @since 2.0.0
	 * 
	 * @return void
	 */
	public function cmb_demo_metaboxes() {
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_demo_';
		$cmb_demo = new_cmb2_box( array(
			'id' => $prefix . 'metabox',
			'title' => __( 'Demo Metabox', PM_TEXTDOMAIN ),
			'object_types' => array( 'demo', ), // Post type
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true, // Show field names on the left
					) );
		$field1 = $cmb_demo->add_field( array(
			'name' => __( 'Text', PM_TEXTDOMAIN ),
			'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
			'id' => $prefix . PM_TEXTDOMAIN . '_text',
			'type' => 'text'
				) );
		$field2 = $cmb_demo->add_field( array(
			'name' => __( 'Text 2', PM_TEXTDOMAIN ),
			'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
			'id' => $prefix . PM_TEXTDOMAIN . '_text2',
			'type' => 'text'
				) );
		$field3 = $cmb_demo->add_field( array(
			'name' => __( 'Text Small', PM_TEXTDOMAIN ),
			'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
			'id' => $prefix . PM_TEXTDOMAIN . '_textsmall',
			'type' => 'text_small'
				) );
		$field4 = $cmb_demo->add_field( array(
			'name' => __( 'Text Small 2', PM_TEXTDOMAIN ),
			'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
			'id' => $prefix . PM_TEXTDOMAIN . '_textsmall2',
			'type' => 'text_small'
				) );
	}
}
new Pm_CMB();
