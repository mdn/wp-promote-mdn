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

	function return_text( $text ) {
		$options = $this->options;
		if ( is_feed() && (!isset( $options[ 'allowfeed' ] ) || $options[ 'allowfeed' ] === 0 ) ) {
			return $text;
		}
		if ( is_page() && isset( $options[ 'ignoreallpages' ] ) && $options[ 'ignoreallpages' ] === 'on' ) {
			return $text;
		}
		if ( is_single() && isset( $options[ 'ignoreallposts' ] ) && $options[ 'ignoreallposts' ] === 'on' ) {
			return $text;
		}
		if ( $options[ 'maxlinks' ] == 0 ) {
			return $text;
		}
		if ( isset( $options[ 'ignorepost' ] ) && is_array( $options[ 'ignorepost' ] ) ) {
			foreach ( $options[ 'ignorepost' ] as $arrignore ) {
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
		return false;
	}

	function process_text( $text ) {
		$options = $this->options;
		$check = $this->return_text( $text );
		if ( gettype( $check ) === 'string' ) {
			return $check;
		}
		$tracking_querystring = '?utm_source=wordpress%%20blog&amp;utm_medium=content%%20link&amp;utm_campaign=promote%%20mdn';
		$links = 0;

		$urls = array();
		if ( isset( $options[ 'exclude_elems' ] ) && is_array( $options[ 'exclude_elems' ] ) ) {
			// add salt to elements
			foreach ( $options[ 'exclude_elems' ] as $el ) {
				$re = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
				$text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_insertspecialchars($matches[2]) . $matches[3];' ), $text );
			}
		}
		$reg = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU';
		$text = " $text ";
		// custom keywords
		if ( !empty( $options[ 'customkey' ] ) ) {
			foreach ( $options[ 'customkey' ] as $name => $url ) {
				if ( in_array( strtolower( $name ), $options[ 'ignore' ] ) ) {
					continue;
				}
				if ( strpos( 'GoogleAnalyticsObject', $url ) ) {
					continue;
				}
				if ( $options[ 'add_src_param' ] == true ) {
					$url .= $tracking_querystring;
				}
				if ( !isset( $urls[ $url ] ) ) {
					$urls[ $url ] = 0;
				}
				if ( $links < $options[ 'maxlinks' ] && (!$options[ 'maxsingleurl' ] || $urls[ $url ] < $options[ 'maxsingleurl' ] )				) {
					if ( stripos( $text, $name ) !== false ) {
						$name = preg_quote( $name, '/' );
						$link = "<a" . $options[ 'blanko' ] . " title=\"%s\" href=\"$url\" class=\"promote-mdn\">%s</a>";
						$regexp = str_replace( '$name', $name, $reg );
						$replace = 'return sprintf(\'' . $link . '\', $matches[1], $matches[1]);';
						$newtext = preg_replace_callback( $regexp, create_function( '$matches', $replace ), $text, $options[ 'maxsingle' ] );
						if ( $newtext != $text ) {
							$links++;
							$text = $newtext;
							$urls[ $url ] ++;
						}
					}
				}
			}
		}
		if ( isset( $options[ 'exclude_elems' ] ) && is_array( $options[ 'exclude_elems' ] ) ) {
			// remove salt from elements
			foreach ( $options[ 'exclude_elems' ] as $el ) {
				$re = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
				$text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_removespecialchars($matches[2]) . $matches[3];' ), $text );
			}
		}
		return trim( $text );
	}

}

$pm_content = new Pm_Content();
$pm_content->initialize();
do_action( 'promote_mdn_content_instance', $pm_content );
