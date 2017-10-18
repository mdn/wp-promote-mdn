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
 * This class contain the Uninstall code
 */
class Pm_Uninstall {
	/**
	 * Initialize the snippet
	 * 
	 * @return void
	 */
	function __construct() {
		add_action( 'after_uninstall', array( $this, 'uninstall_hook' ) );
	}
	/**
	 * Loop for uninstall
	 * 
	 * @global type $wpdb
	 * 
	 * @return void
	 */
	function uninstall_hook() {
		if ( is_multisite() ) {
			$blogs = get_sites();
			if ( !empty( $blogs ) ) {
				foreach ( $blogs as $blog ) {
					switch_to_blog( $blog->blog_id );
					$this->uninstall();
					restore_current_blog();
				}
				return;
			}
		}
		$this->uninstall();
	}
	/**
	 * What happen on uninstall?
	 * 
	 * @global WP_Roles $wp_roles
	 * 
	 * @return void
	 */
	function uninstall() {
		global $wp_roles;
		$plugin_roles = Promote_MDN::get_plugin_roles();
		/*
		  @TODO
		  // Delete all transient and options
		  delete_transient( 'TRANSIENT_NAME' );
		  delete_option( 'OPTION_NAME' );
		  remove_role( 'advanced' );
		  // Remove custom file directory
		  $upload_dir = wp_upload_dir();
		  $directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "CUSTOM_DIRECTORY_NAME" . DIRECTORY_SEPARATOR;
		  if (is_dir($directory)) {
		  foreach(glob($directory.'*.*') as $v){
		  unlink($v);
		  }
		  rmdir($directory);
		  // Delete post meta data
		  $posts = get_posts(array('posts_per_page' => -1));
		  foreach ($posts as $post) {
		  $post_meta = get_post_meta($post->ID);
		  delete_post_meta($post->ID, 'your-post-meta');
		  }
		  // Delete user meta data
		  $users = get_users();
		  foreach ($users as $user) {
		  delete_user_meta($user->ID, 'your-user-meta');
		  }
		  // Remove and optimize tables
		  $GLOBALS['wpdb']->query("DROP TABLE `".$GLOBALS['wpdb']->prefix."TABLE_NAME`");
		  $GLOBALS['wpdb']->query("OPTIMIZE TABLE `" .$GLOBALS['wpdb']->prefix."options`");
		 */
		if ( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles;
		}
		foreach ( $wp_roles->role_names as $role => $label ) {
			// If the role is a standard role, map the default caps, otherwise, map as a subscriber
			$caps = ( array_key_exists( $role, $plugin_roles ) ) ? $plugin_roles[ $role ] : $plugin_roles[ 'subscriber' ];
			// Loop and assign
			foreach ( $caps as $cap => $grant ) {
				// Check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
					$wp_roles->remove_cap( $cap );
				}
			}
		}
	}
}
new Pm_Uninstall();
