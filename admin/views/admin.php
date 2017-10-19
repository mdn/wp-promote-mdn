<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */
?>
<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<p><?php _e( 'MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies.', 'promote-mdn' ) ?></p>
    <div id="tabs" class="settings-tab">
		<ul>
			<li><a href="#tabs-1"><?php _e( 'Settings' ); ?></a></li>
			<li><a href="#tabs-2"><?php _e( 'Keywords', PM_TEXTDOMAIN ); ?></a></li>
		</ul>
		<?php
		require_once( plugin_dir_path( __FILE__ ) . 'settings.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'settings-2.php' );
		?>
		<?php
				?>
    </div>
    <div class="right-column-settings-page metabox-holder">
		<div class="postbox">
			<h3 class="hndle"><span><?php _e( 'About us', PM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<a href="https://github.com/mdn/wp-promote-mdn/"><img src="<?php echo plugins_url( 'assets/img/banner.png',dirname( __FILE__) ) ?>" alt=""></a>
			</div>
		</div>
    </div>
</div>
