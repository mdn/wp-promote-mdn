<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pmdn_settings() {
	return array(
		'ignoreallpages' => '',
		'ignoreallposts' => '',
		'ignorepost' => array( 'contact' ),
		'ignoreposttype' => '',
		'add_src_param' => 'off',
		'exclude_elems' => array( 'blockquote', 'code', 'h', 'pre', 'q' ),
		'ignore' => array( 'about' ),
		'ignorepost' => 'contact,',
		'maxlinks' => 3,
		'maxsingle' => 1,
		'customkey' => array( 'JavaScript' => 'https://developer.mozilla.org/docs/JavaScript',
			'JS' => 'https://developer.mozilla.org/docs/JavaScript',
			'DOM' => 'https://developer.mozilla.org/docs/DOM',
			'WebGL' => 'https://developer.mozilla.org/docs/WebGL',
			'WebSockets' => 'https://developer.mozilla.org/docs/WebSockets',
			'JSON' => 'https://developer.mozilla.org/docs/JSON',
			'HTML' => 'https://developer.mozilla.org/docs/HTML',
			'HTML5' => 'https://developer.mozilla.org/learn/html5',
			'CSS' => 'https://developer.mozilla.org/docs/CSS',
			'CSS3' => 'https://developer.mozilla.org/docs/CSS/CSS3',
			'MDN' => 'https://developer.mozilla.org/',
			'Mozilla Developer Network' => 'https://developer.mozilla.org/',
			'Kuma' => 'https://developer.mozilla.org/docs/Project:Getting_started_with_Kuma',
			'KumaScript' => 'https://developer.mozilla.org/docs/Project:Introduction_to_KumaScript',
			'Mozilla' => 'https://www.mozilla.org/',
			'IndexedDB' => 'https://developer.mozilla.org/docs/IndexedDB',
			'Vibration API' => 'https://developer.mozilla.org/docs/DOM/window.navigator.vibrate',
			'Geolocation' => 'https://developer.mozilla.org/docs/Using_geolocation',
			'SVG' => 'https://developer.mozilla.org/docs/SVG' ),
		'url' => 'https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt',
		'recurrence' => 'daily',
		'blanko' => ' target="_blank"',
		'allowfeed' => '',
		'maxsingleurl' => '1',
		'allowcomments' => '',
		'tracking_querystring' => '?utm_source=wordpress%20blog&amp;utm_medium=content%20link&amp;utm_campaign=promote%20mdn'
	);
}

function pmdn_content() {
	$pm = new Pm_Content();
	$pm->initialize();
	return $pm;
}
