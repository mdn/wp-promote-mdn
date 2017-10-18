<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Your Name or Company Name
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */
?>
<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <div id="tabs" class="settings-tab">
		<ul>
			<li><a href="#tabs-1"><?php _e( 'Settings' ); ?></a></li>
			<li><a href="#tabs-2"><?php _e( 'Settings 2', PM_TEXTDOMAIN ); ?></a></li>
			<?php
						?>
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
			<h3 class="hndle"><span><?php _e( 'Plugin Name.', PM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<a href="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered"><img src="https://github.com/Mte90/WordPress-Plugin-Boilerplate-Powered/raw/master/promote-mdn/assets/icon-256x256.png" alt=""></a>
			</div>
		</div>
    </div>
</div>
