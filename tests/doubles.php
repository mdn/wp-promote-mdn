<?php
$feed = false;
$page = '';

// "mocks" for the wordpress stuff
class WP_Widget {}
function __( $str ) { return $str; }
function _e( $str ) { return $str; }
function add_filter( $hook_point, $hook_callback, $mode ) { return true; }
function add_action( $hook_point, $hook_callback ) { return true; }
function get_bloginfo( $name ) { return 'http://hacks.mozilla.org'; }
function load_plugin_textdomain( $text_domain, $false, $where ) { return true; }
function register_activation_hook( $file, $callback ) { return true; }
function is_feed() {
    global $feed;
    if ( $feed ) return true;
    return false;
}
function is_page( $arr_ignores ) {
    global $page;
    return in_array( $page, $arr_ignores );
}
function is_single( $arr_ignores ) {
    global $page;
    return in_array( $page, $arr_ignores );
}
function trailingslashit( $string ) {
    return rtrim( $string, '/' ) . '/';
}
function get_transient( $key ) {
    return "JavaScript, JS, JS Documentation, JS Reference, https://developer.mozilla.org/docs/JavaScript
DOM, https://developer.mozilla.org/docs/DOM
WebGL, https://developer.mozilla.org/docs/WebGL
WebSockets, WebSocket https://developer.mozilla.org/docs/WebSockets
JSON, https://developer.mozilla.org/docs/JSON
HTML, https://developer.mozilla.org/docs/HTML
HTML5, https://developer.mozilla.org/learn/html5
CSS, https://developer.mozilla.org/docs/CSS
CSS3, https://developer.mozilla.org/docs/CSS/CSS3
MDN, Mozilla Developer Network, devmo, https://developer.mozilla.org/
Kuma, https://developer.mozilla.org/docs/Project:Getting_started_with_Kuma
KumaScript, https://developer.mozilla.org/docs/Project:Introduction_to_KumaScript
Boot to Gecko, B2G, https://developer.mozilla.org/docs/Mozilla/Boot_to_Gecko
Mozilla, https://www.mozilla.org/
Persona, BrowserID, https://developer.mozilla.org/docs/Persona
IndexedDB, https://developer.mozilla.org/docs/IndexedDB
Vibration API, https://developer.mozilla.org/docs/DOM/window.navigator.vibrate
Geolocation, https://developer.mozilla.org/docs/Using_geolocation
SVG, https://developer.mozilla.org/docs/SVG";
}
