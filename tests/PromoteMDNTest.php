<?php
require_once(dirname(__FILE__) . '/doubles.php');
require_once(dirname(__FILE__) . '/../promote-mdn.php');


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
        $this->pm->options['blanko'] = '';
        $linked_text_same_window = '<p>I like <a  title="JavaScript" href="' . $this->js_href . '">JavaScript</a>.</p>';
        $this->assertEquals( $linked_text_same_window, $this->pm->process_text ( $this->text ) );

        $this->pm->options['blanko'] = 'on';
        $linked_text_new_window = '<p>I like ' . $this->js_linked .'.</p>';
        $this->assertEquals( $linked_text_new_window, $this->pm->process_text ( $this->text ) );
    }

    public function testNewWindowDoesntAffectExistingLinks()
    {
        $this->pm->options['blanko'] = 'on';
        $text = '<p>I already made a link to <a href="http://www.w3.org/Protocols/">w3</a>. Don\'t change it to open in a new window.</p>';
        $linked_text_new_window = '<p>I like ' . $this->js_linked .'.</p>';
        $this->assertEquals( $text, $this->pm->process_text ( $text ) );
    }

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

    public function testLeaveEscapedQuotesAlone()
    {
        $text = 'strip("<img onerror=\'alert(\\"could run arbitrary JS here\\")\' src=bogus>")';
        $this->assertEquals( $text, $this->pm->process_text( $text ) );
    }
}
