<?php

$feed = false;
$page = '';

require_once(dirname(__FILE__) . '/../promote-mdn.php');

// "mocks" for the wordpress stuff
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

class PromoteMDNTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $feed, $page;
        $feed = false;
        $page = 'great-page';
        $options = array(
            'excludeheading' => 'on',
            'ignore' => 'about,',
            'ignorepost' => 'contact,',
            'maxlinks' => 3,
            'maxsingle' => 1,
            'customkey' => '',
            'customkey_url' => 'https://developer.mozilla.org/test',
            'customkey_url_expire' => 86400,
            'blanko' => 'on',
            'allowfeed' => '',
            'maxsingleurl' => '1',
        );
        $this->pm = new PromoteMDN( $options );

        $this->js_href = 'https://developer.mozilla.org/docs/JavaScript';
        $this->js_linked = '<a target="_blank" title="JavaScript" href="' . $this->js_href . '">JavaScript</a>';
        $this->css_href = 'https://developer.mozilla.org/docs/CSS';
        $this->css_linked = '<a target="_blank" title="CSS" href="' . $this->css_href . '">CSS</a>';
        $this->text = '<p>I like JavaScript.</p>';
        $this->linked_text = '<p>I like ' . $this->js_linked . '.</p>';
    }
    public function testOptionsInjection()
    {
        $this->assertEquals('https://developer.mozilla.org/test',
            $this->pm->options['customkey_url']);
    }

    public function testAutoLink()
    {
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
    }

    public function testFeed()
    {
        global $feed;
        $feed = true;
        $this->pm->options['allowfeed'] = 0;
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
        $this->pm->options['allowfeed'] = 1;
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) ); 
    }

    public function testNewWindow()
    {
        $linked_text_new_window = '<p>I like <a target="_blank" title="JavaScript" href="' . $this->js_href . '">JavaScript</a>.</p>';
        $this->assertEquals( $linked_text_new_window, $this->pm->process_text ( $this->text ) );
    }

    // TODO: Test for http://git.io/T6VIFg

    public function testExcludeHeading()
    {
        $this->pm->options['excludeheading'] = 'on';
        $text = "<h2>The Code</h2><h3>JavaScript</h3>";
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
        $this->pm->options['excludeheading'] = 'off';
        $this->assertEquals( '<h2>The Code</h2><h3>' . $this->js_linked . '</h3>',
                             $this->pm->process_text( $text ) );
    }

    public function testMaxLinks()
    {
        $this->pm->options['maxlinks'] = 1;
        $text = "<p>JavaScript and CSS</p>";
        $this->assertEquals( '<p>' . $this->js_linked . ' and CSS</p>',
                             $this->pm->process_text( $text) );
        $this->pm->options['maxlinks'] = 2;
        $this->assertEquals( '<p>' . $this->js_linked . ' and ' . $this->css_linked . '</p>',
                             $this->pm->process_text( $text) );
    }

    public function testMaxSingle()
    {
        $this->pm->options['maxsingle'] = 1;
        $text = "<p>JavaScript and JavaScript</p>";
        $this->assertEquals( '<p>' . $this->js_linked . ' and JavaScript</p>',
                             $this->pm->process_text( $text) );
        $this->pm->options['maxsingle'] = 2;
        $this->assertEquals( '<p>' . $this->js_linked . ' and ' . $this->js_linked . '</p>',
                             $this->pm->process_text( $text) );
    }

    public function testMaxSingleUrl()
    {
        $this->pm->options['maxsingleurl'] = 1;
        $text = "<p>JavaScript and JS</p>";
        $this->assertEquals( '<p>' . $this->js_linked . ' and JS</p>',
                             $this->pm->process_text( $text) );
        $this->pm->options['maxsingleurl'] = 2;
        $this->assertEquals( '<p>' . $this->js_linked . ' and <a target="_blank" title="JS" href="' . $this->js_href . '">JS</a></p>',
                             $this->pm->process_text( $text) );
    }

    public function testCustomKey()
    {
        $this->pm->options['customkey'] = '';
        $text = "<p>JavaScript and groovecoder</p>";
        $this->assertEquals( '<p>' . $this->js_linked . ' and groovecoder</p>',
                             $this->pm->process_text( $text) );
        $this->pm->options['customkey'] = 'groovecoder, http://groovecoder.com';
        $this->assertEquals( '<p>' . $this->js_linked . ' and <a target="_blank" title="groovecoder" href="http://groovecoder.com">groovecoder</a></p>',
                             $this->pm->process_text( $text) );
    }

    public function testIgnore()
    {
        $this->pm->options['ignore'] = '';
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
        $this->pm->options['ignore'] = 'JavaScript, ';
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
    }

    public function testIgnorePost()
    {
        global $page;
        $page = 'about';
        $this->pm->options['ignorepost'] = "about, ";
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
    }

}
