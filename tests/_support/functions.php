<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

add_filter( 'promote_mdn_settings', 'pmdn_settings', 9999, 1 );

function pmdn_settings( $settings ) {
	return array(
		'ignoreallpages' => '',
		'ignoreallposts' => '',
		'ignoreposttype' => '',
		'add_src_param' => 'off',
		'exclude_elems' => '',
		'ignore' => 'about,',
		'ignorepost' => 'contact,',
		'maxlinks' => 3,
		'maxsingle' => 1,
		'customkey' => '',
		'url' => 'https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt',
		'customkey_url_expire' => 86400,
		'blanko' => 'on',
		'allowfeed' => '',
		'maxsingleurl' => '1',
		'allowcomments' => '',
		'tracking_querystring' => '?utm_source=wordpress%20blog&amp;utm_medium=content%20link&amp;utm_campaign=promote%20mdn'
	);
}

function pmdn_content() {
	$pm = new Pm_Content();
	$pm->initialize();
	$pm->options = pmdn_settings( array() );
	return $pm;
}
