<?php

function promote_mdn_settings() {
	$settings = array();
	$keywords = array();
	$settings_saved = get_option( PM_TEXTDOMAIN . '-settings' );
	$keywords_saved = get_option( PM_TEXTDOMAIN . '-keywords' );
	if ( is_array( $settings_saved ) ) {
		$settings = $settings_saved;
	}
	if ( is_array( $keywords_saved ) ) {
		$keywords = $keywords_saved;
	}

	return apply_filters( 'promote_mdn_settings', array_merge( $settings, $keywords ) );
}

function wp_insertspecialchars( $str ) {
	$strarr = wp_str2arr( $str );
	$str = implode( '<!---->', $strarr );
	return $str;
}

function wp_removespecialchars( $str ) {
	$strarr = explode( '<!---->', $str );
	$str = implode( '', $strarr );
	$str = stripslashes( $str );
	return $str;
}

function wp_str2arr( $str ) {
	$chararray = array();
	for ( $i = 0; $i < strlen( $str ); $i++ ) {
		array_push( $chararray, $str{$i} );
	}
	return $chararray;
}
