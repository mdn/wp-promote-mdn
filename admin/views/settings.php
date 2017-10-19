<div id="tabs-1" class="wrap">
	<?php
	$cmb = new_cmb2_box( array(
		'id' => PM_TEXTDOMAIN . '_options',
		'hookup' => false,
		'show_on' => array( 'key' => 'options-page', 'value' => array( PM_TEXTDOMAIN ), ),
		'show_names' => true,
			) );
	$cmb->add_field( array(
		'name' => __( 'Proprierties', PM_TEXTDOMAIN ),
		'id' => 'first_title',
		'type' => 'title',
	) );
	$cmb->add_field( array(
		'name' => __( 'Text', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => 'text',
		'type' => 'text',
		'default' => 'Default Text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Text Medium', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_textmedium',
		'type' => 'text_medium',
			// 'repeatable' => true,
	) );
	$cmb->add_field( array(
		'name' => __( 'Website URL', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_url',
		'type' => 'text_url',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Text Area', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_textarea',
		'type' => 'textarea',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Text Area Small', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_textareasmall',
		'type' => 'textarea_small',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Title Weeeee', PM_TEXTDOMAIN ),
		'desc' => __( 'This is a title description', PM_TEXTDOMAIN ),
		'id' => '_title',
		'type' => 'title',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Select', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_select',
		'type' => 'select',
		'show_option_none' => true,
		'options' => array(
			'standard' => __( 'Option One', PM_TEXTDOMAIN ),
			'custom' => __( 'Option Two', PM_TEXTDOMAIN ),
			'none' => __( 'Option Three', PM_TEXTDOMAIN ),
		),
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Radio inline', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_radio_inline',
		'type' => 'radio_inline',
		'show_option_none' => 'No Selection',
		'options' => array(
			'standard' => __( 'Option One', PM_TEXTDOMAIN ),
			'custom' => __( 'Option Two', PM_TEXTDOMAIN ),
			'none' => __( 'Option Three', PM_TEXTDOMAIN ),
		),
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Radio', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_radio',
		'type' => 'radio',
		'options' => array(
			'option1' => __( 'Option One', PM_TEXTDOMAIN ),
			'option2' => __( 'Option Two', PM_TEXTDOMAIN ),
			'option3' => __( 'Option Three', PM_TEXTDOMAIN ),
		),
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Taxonomy Radio', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_text_taxonomy_radio',
		'type' => 'taxonomy_radio',
		'taxonomy' => 'category', // Taxonomy Slug
			// 'inline'  => true, // Toggles display to inline
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Taxonomy Select', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_taxonomy_select',
		'type' => 'taxonomy_select',
		'taxonomy' => 'category', // Taxonomy Slug
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Taxonomy Multi Checkbox', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_multitaxonomy',
		'type' => 'taxonomy_multicheck',
		'taxonomy' => 'category', // Taxonomy Slug
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Checkbox', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_checkbox',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Multi Checkbox', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_multicheckbox',
		'type' => 'multicheck',
		'options' => array(
			'check1' => __( 'Check One', PM_TEXTDOMAIN ),
			'check2' => __( 'Check Two', PM_TEXTDOMAIN ),
			'check3' => __( 'Check Three', PM_TEXTDOMAIN ),
		),
	) );
	$cmb->add_field( array(
		'name' => __( 'Test wysiwyg', PM_TEXTDOMAIN ),
		'desc' => __( 'field description (optional)', PM_TEXTDOMAIN ),
		'id' => '_wysiwyg',
		'type' => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
	$cmb->add_field( array(
		'name' => __( 'Test Image', PM_TEXTDOMAIN ),
		'desc' => __( 'Upload an image or enter a URL.', PM_TEXTDOMAIN ),
		'id' => '_image',
		'type' => 'file',
	) );

	cmb2_metabox_form( PM_TEXTDOMAIN . '_options', PM_TEXTDOMAIN . '-settings' );
	?>
	<!-- @TODO: Provide other markup for your options page here. -->
</div>
