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
	$settings = apply_filters( 'promote_mdn_settings', array_merge( $settings, $keywords ) );
	
	$settings[ 'maxlinks' ] = ( isset( $settings[ 'maxlinks' ] ) && $settings[ 'maxlinks' ] > 0 ) ? $settings[ 'maxlinks' ] : 0;
	$settings[ 'maxsingle' ] = ( isset( $settings[ 'maxsingle' ] ) && $settings[ 'maxsingle' ] > 0 ) ? $settings[ 'maxsingle' ] : 0 - 1;
	$settings[ 'maxsingleurl' ] = ( isset( $settings[ 'maxsingleurl' ] ) && $settings[ 'maxsingleurl' ] > 0 ) ? $settings[ 'maxsingleurl' ] : 0;
	$settings[ 'ignorepost' ] = (isset( $settings[ 'ignorepost' ] )) ? promote_mdn_explode_lower_trim( ',', ( $settings[ 'ignorepost' ] ) ) : array();
	$settings[ 'ignore' ] = (isset( $settings[ 'ignore' ] )) ? promote_mdn_explode_lower_trim( ',', ( $settings[ 'ignore' ] ) ) : array();
	$settings[ 'exclude_elems' ] = (isset( $settings[ 'exclude_elems' ] )) ? promote_mdn_explode_lower_trim( ',', ( $settings[ 'exclude_elems' ] ) ) : array();

	if ( isset( $settings[ 'blanko' ] ) ) {
		$settings[ 'blanko' ] = ' target="_blank"';
	} else {
		$settings[ 'blanko' ] = '';
	}
	if ( !empty( $settings[ 'url' ] ) ) {
		$url_value = get_transient( 'promote_mdn_url_value' );
		if ( false === $url_value ) {
			$url_value = promote_mdn_reload_value( $settings[ 'url' ] );
		}
		//customkey is popolated on the fly with the data of the url
		if ( !isset( $settings[ 'customkey' ] ) ) {
			$settings[ 'customkey' ] = $url_value;
		} else {
			$settings[ 'customkey' ] .= "\n" . $url_value;
		}
	}
	if ( isset( $settings[ 'customkey' ] ) ) {
		$settings[ 'customkey' ] = explode( "\n", $settings[ 'customkey' ] );
		$kw_array = array();
		foreach ( $settings[ 'customkey' ] as $line ) {
			$chunks = array_map( 'trim', explode( ',', $line ) );
			$total_chuncks = count( $chunks );
			if ( $total_chuncks > 2 ) {
				$i = 0;
				$url = $chunks[ $total_chuncks - 1 ];
				while ( $i < $total_chuncks - 1 ) {
					if ( !empty( $chunks[ $i ] ) ) {
						$kw_array[ $chunks[ $i ] ] = $url;
					}
					$i++;
				}
			} else {
				$pieces_array = explode( ',', $line, 2 );
				if ( count( $pieces_array ) > 1 ) {
					list( $keyword, $url ) = array_map( 'trim', $pieces_array );
				}
				if ( !empty( $keyword ) ) {
					$kw_array[ $keyword ] = $url;
				}
			}
		}
		$settings[ 'customkey' ] = $kw_array;
	}

	return $settings;
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

function promote_mdn_explode_lower_trim( $separator, $text ) {
	$arr = explode( $separator, $text );
	$ret = array();
	foreach ( $arr as $e ) {
		if ( !empty( $e ) ) {
			$ret[] = strtolower( trim( $e ) );
		}
	}
	// return empty array for single empty string element
	// for simpler if-checks
	if ( count( $ret ) == 1 && $ret[ 0 ] == '' ) {
		$ret = array();
	}
	return $ret;
}

function promote_mdn_reload_value( $url ) {
	$body = wp_remote_retrieve_body(
			wp_remote_get(
					$url, array(
		'headers' =>
		array( 'cache-control' => 'no-cache, must-revalidate' )
					)
			)
	);
	$url_value = strip_tags( $body );
    $options = get_option( PM_TEXTDOMAIN . '-settings' );
	if ( !isset( $options[ 'recurrence' ] ) ) {
		$options[ 'recurrence' ] = 'daily';
	}
	set_transient( 'promote_mdn_url_value', $url_value, $options[ 'recurrence' ] );
	return $url_value;
}
