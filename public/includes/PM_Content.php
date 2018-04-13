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
	
	public $link = '';

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
		$check = $this->return_text( $text );
		if ( gettype( $check ) === 'string' ) {
			return $check;
		}
		$tracking_querystring = '?utm_source=wordpress%%20blog&amp;utm_medium=content%%20link&amp;utm_campaign=promote%%20mdn';
		$links = 0;

		$urls = array();

		$exclude_elems = $this->exclude_elems();
		$reg = '/(?!(?:[^<\[]+[>\-\=\?\]]|[^>\]]+(<\/a>' . $exclude_elems . ')))\b($name)\b/imsU';

		$text = " $text ";
		// custom keywords
		if ( !empty( $this->options[ 'customkey' ] ) ) {
			foreach ( $this->options[ 'customkey' ] as $name => $url ) {
				if ( in_array( strtolower( $name ), $this->options[ 'ignore' ] ) || strpos( 'GoogleAnalyticsObject', $url ) ) {
					continue;
				}
				if ( isset( $this->options[ 'add_src_param' ] ) && $this->options[ 'add_src_param' ] == true ) {
					$url .= $tracking_querystring;
				}
				if ( !isset( $urls[ $url ] ) ) {
					$urls[ $url ] = 0;
				}
				if ( $links < $this->options[ 'maxlinks' ] && (!$this->options[ 'maxsingleurl' ] || $urls[ $url ] < $this->options[ 'maxsingleurl' ] ) ) {
					if ( stripos( $text, $name ) !== false ) {
						$name = preg_quote( $name, '/' );
						$this->link = "<a" . $this->options[ 'blanko' ] . " title=\"%s\" href=\"$url\" class=\"promote-mdn\">%s</a>";
						$regexp = str_replace( '$name', $name, $reg );
						$newtext = preg_replace_callback( $regexp, array( $this, 'replace_link' ), $text, $this->options[ 'maxsingle' ] );
						if ( $newtext != $text ) {
							$links++;
							$text = $newtext;
							$urls[ $url ] ++;
						}
					}
				}
			}
		}

		return trim( $text );
	}

	function exclude_elems( $regex = '', $elems = array() ) {
		$regex = '';
		$new_elems = array();
		if ( empty( $elems ) ) {
			$elems = $this->options[ 'exclude_elems' ];
		}
		if ( isset( $elems ) && is_array( $elems ) ) {
			foreach ( $elems as $el ) {
				if ( $el === 'h' ) {
					for ( $i = 1; $i <= 6; $i++ ) {
						$new_elems[] = 'h' . $i;
					}
				} elseif ( !empty( $el ) ) {
					$regex .= '|<\/' . $el . '>';
				}
			}
		}
		if ( !empty( $new_elems ) ) {
			$regex .= $this->exclude_elems( $regex, $new_elems );
		}
		return $regex;
	}

	public function replace_link( $matches ) {
		return sprintf( $this->link, $matches[0], $matches[0]);
	}

}

$pm_content = new Pm_Content();
$pm_content->initialize();
do_action( 'promote_mdn_content_instance', $pm_content );
