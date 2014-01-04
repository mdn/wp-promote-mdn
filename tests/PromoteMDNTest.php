<?php
require_once( dirname( __FILE__ ) . '/doubles.php' );
require_once( dirname( __FILE__ ) . '/../promote-mdn.php' );


class PromoteMDNTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $feed, $page;
        $feed = false;
        $page = 'great-page';
        $options = array(
            'add_src_param' => 'on',
            'exclude_elems' => '',
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

        $this->expected_tracking_querystring = sprintf( $this->pm->tracking_querystring );
        $this->js_href     = 'https://developer.mozilla.org/docs/JavaScript' . $this->expected_tracking_querystring;
        $this->js_linked   = '<a target="_blank" title="JavaScript" href="' . $this->js_href . '">JavaScript</a>';
        $this->css_href    = 'https://developer.mozilla.org/docs/CSS' . $this->expected_tracking_querystring;
        $this->css_linked  = '<a target="_blank" title="CSS" href="' . $this->css_href . '">CSS</a>';
        $this->text        = '<p>I like JavaScript.</p>';
        $this->linked_text = '<p>I like ' . $this->js_linked . '.</p>';
    }
    public function test_options_injection()
    {
        $this->assertEquals(
            'https://developer.mozilla.org/test',
            $this->pm->options['customkey_url']
        );
    }

    public function test_auto_link()
    {
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
    }

    public function test_link_src_url_param()
    {
        $linked_text = $this->pm->process_text( $this->text );
        $this->assertEquals( $this->linked_text, $linked_text );
        $this->assertContains( $this->expected_tracking_querystring, $linked_text );
        $this->pm->options['add_src_param'] = false;
        $linked_text = $this->pm->process_text( $this->text );
        $this->assertEquals( $linked_text, $linked_text );
    }

    public function test_feed()
    {
        global $feed;
        $feed = true;
        $this->pm->options['allowfeed'] = 0;
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
        $this->pm->options['allowfeed'] = 1;
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) ); 
    }

    public function test_new_window()
    {
        $this->pm->options['blanko'] = '';
        $linked_text_same_window     = '<p>I like <a  title="JavaScript" href="' . $this->js_href . '">JavaScript</a>.</p>';
        $this->assertEquals( $linked_text_same_window, $this->pm->process_text( $this->text ) );

        $this->pm->options['blanko'] = 'on';
        $linked_text_new_window = '<p>I like ' . $this->js_linked .'.</p>';
        $this->assertEquals( $linked_text_new_window, $this->pm->process_text( $this->text ) );
    }

    public function test_new_window_doesnt_affect_existing_links()
    {
        $this->pm->options['blanko'] = 'on';
        $text = '<p>I already made a link to <a href="http://www.w3.org/Protocols/">w3</a>. Don\'t change it to open in a new window.</p>';
        $linked_text_new_window = '<p>I like ' . $this->js_linked .'.</p>';
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
    }

    public function test_exclude_heading()
    {
        $this->pm->options['exclude_elems'] = 'h,';
        $text = '<h2>The Code</h2><h3>JavaScript</h3>';
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
        $this->pm->options['exclude_elems'] = '';
        $this->assertEquals(
            '<h2>The Code</h2><h3>' . $this->js_linked . '</h3>',
            $this->pm->process_text( $text )
        );
    }

    public function test_exclude_other_elements()
    {
        $text = '<h2>The Code</h2><pre>JavaScript</pre>';
        $this->pm->options['exclude_elems'] = '';
        $this->assertEquals(
            '<h2>The Code</h2><pre>' . $this->js_linked . '</pre>',
            $this->pm->process_text( $text )
        );
        $this->pm->options['exclude_elems'] = 'h, pre, code';
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
    }

    public function test_max_links()
    {
        $this->pm->options['maxlinks'] = 1;
        $text = '<p>JavaScript and CSS</p>';
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and CSS</p>',
            $this->pm->process_text( $text )
        );
        $this->pm->options['maxlinks'] = 2;
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and ' . $this->css_linked . '</p>',
            $this->pm->process_text( $text )
        );
    }

    public function test_max_single_term()
    {
        $this->pm->options['maxsingle'] = 1;
        $text = '<p>JavaScript and JavaScript</p>';
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and JavaScript</p>',
            $this->pm->process_text( $text )
        );
        $this->pm->options['maxsingle'] = 2;
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and ' . $this->js_linked . '</p>',
            $this->pm->process_text( $text )
        );
    }

    public function test_max_single_url()
    {
        $this->pm->options['maxsingleurl'] = 1;
        $text = '<p>JavaScript and JS</p>';
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and JS</p>',
            $this->pm->process_text( $text )
        );
        $this->pm->options['maxsingleurl'] = 2;
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and <a target="_blank" title="JS" href="' . $this->js_href . '">JS</a></p>',
            $this->pm->process_text( $text )
        );
    }

    public function test_custom_key()
    {
        $this->pm->options['customkey'] = '';
        $text = '<p>JavaScript and groovecoder</p>';
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and groovecoder</p>',
            $this->pm->process_text( $text )
        );
        $this->pm->options['customkey'] = 'groovecoder, http://groovecoder.com';
        $this->assertEquals(
            '<p>' . $this->js_linked . ' and <a target="_blank" title="groovecoder" href="http://groovecoder.com' . $this->expected_tracking_querystring . '">groovecoder</a></p>',
            $this->pm->process_text( $text )
        );
    }

    public function test_ignore_term()
    {
        $this->pm->options['ignore'] = '';
        $this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
        $this->pm->options['ignore'] = 'JavaScript, ';
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
    }

    public function test_ignore_post()
    {
        global $page;
        $page = 'about';
        $this->pm->options['ignorepost'] = 'about, ';
        $this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
    }

    public function test_leave_escaped_quotes_alone()
    {
        $text = 'strip("<img onerror=\'alert(\\"could run arbitrary JS here\\")\' src=bogus>")';
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
    }
}
