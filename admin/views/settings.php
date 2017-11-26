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
		'name' => __( 'Load keywords from URL', PM_TEXTDOMAIN ),
		'id' => 'url',
		'default' => 'https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt',
		'type' => 'text_url',
	) );
	$cmb->add_field( array(
		'name' => __( 'Reload keywords after (seconds)', PM_TEXTDOMAIN ),
		'id' => 'recurrence',
		'type' => 'radio_inline',
		'default' => 'daily',
		'options' => array(
			'hourly' => __( 'Hourly', PM_TEXTDOMAIN ),
			'daily' => __( 'Daily', PM_TEXTDOMAIN ),
			'twicedaily' => __( 'Twice daily', PM_TEXTDOMAIN ),
			'weekly' => __( 'Weekly', PM_TEXTDOMAIN ),
			'monthly' => __( 'Monthly', PM_TEXTDOMAIN ),
		),
	) );
	$cmb->add_field( array(
		'name' => __( 'Add links to RSS feeds', PM_TEXTDOMAIN ),
		'id' => 'allowfeed',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Add links to comments', PM_TEXTDOMAIN ),
		'id' => 'allowcomments',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Include src url params (Helps MDN measure effectiveness)', PM_TEXTDOMAIN ),
		'id' => 'add_src_param',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Open links in new window', PM_TEXTDOMAIN ),
		'id' => 'blanko',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Exceptions', PM_TEXTDOMAIN ),
		'id' => 'second_title',
		'type' => 'title',
	) );
	$cmb->add_field( array(
		'name' => __( 'Do not add links to any <em>pages</em>.', PM_TEXTDOMAIN ),
		'id' => 'ignoreallpages',
		'type' => 'checkbox',
	) );
	$cmb->add_field( array(
		'name' => __( 'Do not add links to any <em>posts</em>.', PM_TEXTDOMAIN ),
		'id' => 'ignoreallposts',
		'type' => 'checkbox',
	) );
	// Get list of post types
	$post_types = get_post_types( '', 'names' );
	unset( $post_types[ 'revision' ] );
	unset( $post_types[ 'attachment' ] );
	unset( $post_types[ 'post' ] );
	unset( $post_types[ 'page' ] );
	unset( $post_types[ 'nav_menu_item' ] );
	unset( $post_types[ 'custom_css' ] );
	unset( $post_types[ 'customize_changeset' ] );
	unset( $post_types[ 'iwp_log' ] );
	foreach ( $post_types as $key => $post_type ) {
		$post_types[ $key ] = sprintf( __( 'Do not add links to any <em>%s</em>.', PM_TEXTDOMAIN ), $post_type );
	}
	$cmb->add_field( array(
		'name' => __( 'Post types', PM_TEXTDOMAIN ),
		'id' => 'ignoreposttype',
		'type' => 'multicheck',
		'options' => $post_types
	) );
	$cmb->add_field( array(
		'name' => __( 'Do not add links to the following posts or pages (comma-separated id, slug, name)', PM_TEXTDOMAIN ),
		'id' => 'ignorepost',
		'default' => 'contact,',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Do not add links inside the following HTML elements (comma-separated, partial-matching)', PM_TEXTDOMAIN ),
		'id' => 'exclude_elems',
		'default' => 'blockquote, code, h, pre, q',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Do not add links on the following phrases (comma-separated)', PM_TEXTDOMAIN ),
		'id' => 'ignore',
		'default' => 'about,',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name' => __( 'Limits', PM_TEXTDOMAIN ),
		'id' => 'third_title',
		'type' => 'title',
	) );
	$cmb->add_field( array(
		'name' => __( 'Max links to generate per post', PM_TEXTDOMAIN ),
		'description' => __( '(0 to disable all links)', PM_TEXTDOMAIN ),
		'id' => 'maxlinks',
		'default' => '14',
		'type' => 'text_small',
	) );
	$cmb->add_field( array(
		'name' => __( 'Max links to generate for a single keyword/phrase', PM_TEXTDOMAIN ),
		'id' => 'maxsingle',
		'default' => '1',
		'type' => 'text_small',
	) );
	$cmb->add_field( array(
		'name' => __( 'Max links to generate for a single URL', PM_TEXTDOMAIN ),
		'id' => 'maxsingleurl',
		'default' => '1',
		'type' => 'text_small',
	) );
	cmb2_metabox_form( PM_TEXTDOMAIN . '_options', PM_TEXTDOMAIN . '-settings' );
	?>
</div>
