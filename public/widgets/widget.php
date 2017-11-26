<?php

// Create custom widget class extending WPH_Widget
class Pm_MDN extends WPH_Widget {

	/**
	 * Initialize the widget
	 * 
	 * @return void
	 */
	function __construct() {
		// Configure widget array
		$args = array(
			'label' => __( 'Promote MDN', PM_TEXTDOMAIN ),
			'description' => __( 'Sidebar image and links to MDN.', PM_TEXTDOMAIN ),
		);
		$args[ 'fields' ] = array(
			array(
				'name' => __( 'Title', PM_TEXTDOMAIN ),
				'desc' => __( 'Enter the widget title.', PM_TEXTDOMAIN ),
				'id' => 'title',
				'type' => 'text',
				'class' => 'widefat',
				'std' => __( 'Discover MDN', PM_TEXTDOMAIN ),
				'validate' => 'alpha_dash',
				'filter' => 'strip_tags|esc_attr'
			),
			array(
				'name' => __( 'Pick a banner' ),
				'id' => 'banner',
				'type' => 'select',
				'fields' => array(
					array(
						'name' => __( 'Square: Open Docs', PM_TEXTDOMAIN ),
						'value' => 'https://mdn.mozillademos.org/files/7093/MDN_promoBanner_120x120px.png'
					),
					array(
						'name' => __( 'Vertical: Better Docs', PM_TEXTDOMAIN ),
						'value' => 'https://mdn.mozillademos.org/files/7095/MDN_promoBanner_120x240px_v1.png'
					),
					array(
						'name' => __( 'Vertical: Docs by developers', PM_TEXTDOMAIN ),
						'value' => 'https://mdn.mozillademos.org/files/7097/MDN_promoBanner_120x240px_v2.png'
					),
					array(
						'name' => __( 'Vertical: Open Docs', PM_TEXTDOMAIN ),
						'value' => 'https://mdn.mozillademos.org/files/7099/MDN_promoBanner_120x240px_v3.png'
					)
				),
				'filter' => 'strip_tags|esc_attr',
			),
		);

		$this->create_widget( $args );
	}

	/**
	 * Output function
	 * 
	 * @param array $args     The argument shared to the output from WordPress.
	 * @param array $instance The settings saved.
	 * 
	 * @return void
	 */
	function widget( $args, $instance ) {
		$out = $args[ 'before_widget' ];
		// And here do whatever you want
		$out .= $args[ 'before_title' ];
		$out .= $instance[ 'title' ];
		$out .= $args[ 'after_title' ];
		$out .= '<a href="https://developer.mozilla.org" target="_blank"><img src="' . esc_html( $instance[ 'banner' ] ) . '" /></a><br />
	    <a href="https://developer.mozilla.org/promote" target="_blank">' . __( 'Help Promote MDN!', PM_TEXTDOMAIN ) . '</a><br />
<a href="http://wordpress.org/plugins/promote-mdn/" target="_blank">' . __( 'Get the WordPress plugin', PM_TEXTDOMAIN ) . '</a>';
		$out .= $args[ 'after_widget' ];
		echo $out;
	}

}

// Register widget
if ( !function_exists( 'pm_mdn' ) ) {

	/**
	 * Initialize the widget
	 * 
	 * @return void
	 */
	function pm_mdn() {
		register_widget( 'Pm_MDN' );
	}

	add_action( 'widgets_init', 'pm_mdn', 1 );
}
