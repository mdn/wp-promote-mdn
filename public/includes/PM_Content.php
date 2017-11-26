<?php

/**
 * Promote_MDN
 * 
 * @package   Promote_MDN
 * @author    Luke Crouch and Daniele Scasciafratte <mte90net@gmail.com>
 * @copyright 2017 Mozilla
 * @license   GPL 2.0+
 * @link      https://github.com/mdn/wp-promote-mdn
 */

/**
 * This class contain the Content stuff for the frontend
 */
class Pm_Content {

	/**
	 * Initialize the class
	 */
	public function initialize() {
		if ( !apply_filters( 'promote_mdn_template_initialize', true ) ) {
			return;
		}
		$this->options = promote_mdn_settings();
		add_filter( 'the_content', array( &$this, 'process_text' ), 10 );
		if ( isset( $this->options[ 'allowcomments' ] ) && $this->options[ 'allowcomments' ] ) {
			add_filter( 'comment_text', array( &$this, 'process_text' ), 10 );
		}
	}

	function process_text( $text ) {
		$options = $this->options;
		$tracking_querystring = '?utm_source=wordpress%%20blog&amp;utm_medium=content%%20link&amp;utm_campaign=promote%%20mdn';
		$links = 0;
		if ( is_feed() && (!isset( $options[ 'allowfeed' ] ) || $options[ 'allowfeed' ] === 0 ) ) {
			return $text;
		}
		if ( is_page() && isset( $options[ 'ignoreallpages' ] ) && $options[ 'ignoreallpages' ] === 'on' ) {
			return $text;
		}
		if ( is_single() && isset( $options[ 'ignoreallposts' ] ) && $options[ 'ignoreallposts' ] === 'on' ) {
			return $text;
		}
		if ( isset( $options[ 'ignorepost' ] ) ) {
			$arrignorepost = $this->explode_lower_trim( ',', ( $options[ 'ignorepost' ] ) );
			foreach ( $arrignorepost as $arrignore ) {
				if ( is_page( $arrignore ) || is_single( $arrignore ) ) {
					return $text;
				}
			}
		}
		if ( isset( $options[ 'ignoreposttype' ] ) && is_array( $options[ 'ignoreposttype' ] ) ) {
			foreach ( $options[ 'ignoreposttype' ] as $post_type => $value ) {
				if ( get_post_type( get_the_ID() ) === $post_type ) {
					return $text;
				}
			}
		}
		$maxlinks = ( isset( $options[ 'maxlinks' ] ) && $options[ 'maxlinks' ] > 0 ) ? $options[ 'maxlinks' ] : 0;
		$maxsingle = ( isset( $options[ 'maxsingle' ] ) && $options[ 'maxsingle' ] > 0 ) ? $options[ 'maxsingle' ] : 0 - 1;
		$maxsingleurl = ( isset( $options[ 'maxsingleurl' ] ) && $options[ 'maxsingleurl' ] > 0 ) ? $options[ 'maxsingleurl' ] : 0;
		if ( $maxlinks == 0 ) {
			return $text;
		}
		$urls = array();
		if ( isset( $options[ 'ignore' ] ) ) {
			$arrignore = $this->explode_lower_trim( ',', ( $options[ 'ignore' ] ) );
		}
		if ( isset( $options[ 'exclude_elems' ] ) ) {
			$exclude_elems = $this->explode_lower_trim( ',', ( $options[ 'exclude_elems' ] ) );
			if ( $exclude_elems ) {
				// add salt to elements
				foreach ( $exclude_elems as $el ) {
					$re = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
					$text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_insertspecialchars($matches[2]) . $matches[3];' ), $text );
				}
			}
		}
		$reg = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU';
		$text = " $text ";
		if ( !empty( $options[ 'url' ] ) ) {
			$url_value = get_transient( 'promote_mdn_url_value' );
			if ( false === $url_value ) {
				$url_value = $this->reload_value( $options[ 'url' ] );
			}
			//customkey is popolated on the fly with the data of the url
			if ( !isset( $options[ 'customkey' ] ) ) {
				$options[ 'customkey' ] = $url_value;
			} else {
				$options[ 'customkey' ] .= "\n" . $url_value;
			}
		}
		// custom keywords
		if ( !empty( $options[ 'customkey' ] ) ) {
			$kw_array = array();
			foreach ( explode( "\n", $options[ 'customkey' ] ) as $line ) {
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
			foreach ( $kw_array as $name => $url ) {
				if ( in_array( strtolower( $name ), $arrignore ) ) {
					continue;
				}
				if ( (!$maxlinks || ( $links < $maxlinks ) ) && (!in_array( strtolower( $name ), $arrignore ) ) && (!$maxsingleurl || !isset( $urls[ $url ] ) || $urls[ $url ] < $maxsingleurl )
				) {
					if ( !isset( $options[ 'customkey_preventduplicatelink' ] ) ) {
						$options[ 'customkey_preventduplicatelink' ] = FALSE;
					}
					if ( $options[ 'customkey_preventduplicatelink' ] == TRUE || stripos( $text, $name ) !== false ) {
						$name = preg_quote( $name, '/' );
						if ( $options[ 'customkey_preventduplicatelink' ] == TRUE ) {
							$name = str_replace( ',', '|', $name );
						}
						$target = ' ';
						if ( isset( $options[ 'blanko' ] ) ) {
							$target = ' target="_blank"';
						}
						$href = $url;
						if ( $options[ 'add_src_param' ] == TRUE ) {
							$href .= $tracking_querystring;
						}
						if ( strpos( 'GoogleAnalyticsObject', $href ) === false ) {
							$link = "<a$target title=\"%s\" href=\"$href\" class=\"promote-mdn\">%s</a>";
							$regexp = str_replace( '$name', $name, $reg );
							$replace = 'return sprintf(\'' . $link . '\', $matches[1], $matches[1]);';
							$newtext = preg_replace_callback( $regexp, create_function( '$matches', $replace ), $text, $maxsingle );
							if ( $newtext != $text ) {
								$links++;
								$text = $newtext;
								if ( !isset( $urls[ $url ] ) ) {
									$urls[ $url ] = 1;
								} else {
									$urls[ $url ] ++;
								}
							}
						}
					}
				}
			}
		}
		if ( isset( $options[ 'exclude_elems' ] ) && $exclude_elems ) {
			// remove salt from elements
			foreach ( $exclude_elems as $el ) {
				$re = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
				$text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_removespecialchars($matches[2]) . $matches[3];' ), $text );
			}
		}
		return trim( $text );
	}

	function explode_lower_trim( $separator, $text ) {
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

	function reload_value( $url ) {
		$body = wp_remote_retrieve_body(
				wp_remote_get(
						$url, array(
			'headers' =>
			array( 'cache-control' => 'no-cache, must-revalidate' )
						)
				)
		);
		$url_value = strip_tags( $body );
		if ( !isset( $this->options[ 'recurrence' ] ) ) {
			$this->options[ 'recurrence' ] = 'daily';
		}
		set_transient( 'promote_mdn_url_value', $url_value, $this->options[ 'recurrence' ] );
		return $url_value;
	}

}

$pm_content = new Pm_Content();
$pm_content->initialize();
do_action( 'promote_mdn_content_instance', $pm_content );
