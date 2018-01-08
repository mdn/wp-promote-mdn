<?php

use tad\FunctionMocker\FunctionMocker;

class PMContentTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var string
	 */
	protected $root_dir;

	public function setUp() {
		parent::setUp();
		// your set up methods here
		$this->root_dir = dirname( dirname( dirname( __FILE__ ) ) );
		include_once($this->root_dir . '/_support/functions.php');
		FunctionMocker::replace( 'get_transient', 'pmdn_get_transient' );
		FunctionMocker::replace( 'promote_mdn_settings', 'pmdn_settings' );
		$this->pm = pmdn_content();
		$this->js_href = 'https://developer.mozilla.org/docs/JavaScript' . pmdn_settings()[ 'tracking_querystring' ];
		$this->js_linked = '<a target="_blank" title="JavaScript" href="' . $this->js_href . '" class="promote-mdn">JavaScript</a>';
		$this->css_href = 'https://developer.mozilla.org/docs/CSS' . pmdn_settings()[ 'tracking_querystring' ];
		$this->css_linked = '<a target="_blank" title="CSS" href="' . $this->css_href . '" class="promote-mdn">CSS</a>';
		$this->text = '<p>I like JavaScript.</p>';
		$this->linked_text = '<p>I like ' . $this->js_linked . '.</p>';
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @test
	 * it should auto link
	 */
	public function test_auto_link() {
		$this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
	}

	public function test_link_src_url_param() {
		$linked_text = $this->pm->process_text( $this->text );
		$this->assertEquals( $this->linked_text, $linked_text );
		$this->assertContains( $this->pm->options[ 'tracking_querystring' ], $linked_text );
		$this->pm->options[ 'add_src_param' ] = false;
		$linked_text = $this->pm->process_text( $this->text );
		$this->assertEquals( $linked_text, $linked_text );
	}

	public function test_fix105() {
		$this->pm->options[ 'customkey' ] = array( 'url' => 'https://developer.mozilla.org/docs/Learn/Common_questions/What_is_a_URL' );
		$original = '<div class="shariff" data-backend-url="https://a-blog-url/wp-content/plugins/shariff-sharing/backend/index.php" data-temp="/tmp" data-ttl="60" data-service="gft" data-image="" data-url="https://a-blog-url/" data-services=\'["googleplus","facebook","twitter","whatsapp","info"]\'></div>';
		$linked_text = $this->pm->process_text( $original );
		$this->assertEquals( $original, $linked_text );
		$this->pm->options[ 'customkey' ] = '';
	}

	public function test_feed() {
		FunctionMocker::replace( 'is_feed', '__return_true' );
		$this->pm->options[ 'allowfeed' ] = 0;
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
		$this->pm->options[ 'allowfeed' ] = 1;
		FunctionMocker::replace( 'is_feed', '__return_false' );
		$this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
	}

	public function test_new_window() {
		$this->pm->options[ 'blanko' ] = '';
		$linked_text_same_window = '<p>I like <a title="JavaScript" href="' . $this->js_href . '" class="promote-mdn">JavaScript</a>.</p>';
		$this->assertEquals( $linked_text_same_window, $this->pm->process_text( $this->text ) );
		$this->pm->options[ 'blanko' ] = ' target="_blank"';
		$linked_text_new_window = '<p>I like ' . $this->js_linked . '.</p>';
		$this->assertEquals( $linked_text_new_window, $this->pm->process_text( $this->text ) );
	}

	public function test_new_window_doesnt_affect_existing_links() {
		$this->pm->options[ 'blanko' ] = ' target="_blank"';
		$text = '<p>I already made a link to <a href="http://www.w3.org/Protocols/">w3</a>. Don\'t change it to open in a new window.</p>';
		$this->assertEquals( $text, $this->pm->process_text( $text ) );
	}

	public function test_exclude_heading() {
		$this->pm->options[ 'exclude_elems' ] = array( 'h' );
		$text = '<h2>The Code</h2><h3>JavaScript</h3>';
		$this->assertEquals( $text, $this->pm->process_text( $text ) );
		$this->pm->options[ 'exclude_elems' ] = '';
		$this->assertEquals(
				'<h2>The Code</h2><h3>' . $this->js_linked . '</h3>', $this->pm->process_text( $text )
		);
	}

	public function test_exclude_other_elements() {
		$text = '<h2>The Code</h2><pre>JavaScript</pre>';
		$this->pm->options[ 'exclude_elems' ] = '';
		$this->assertEquals(
				'<h2>The Code</h2><pre>' . $this->js_linked . '</pre>', $this->pm->process_text( $text )
		);
		$this->pm->options[ 'exclude_elems' ] = array( 'h', 'pre', 'code' );
		$this->assertEquals( $text, $this->pm->process_text( $text ) );
	}

	public function test_max_links() {
		$this->pm->options[ 'maxlinks' ] = 1;
		$text = '<p>JavaScript and CSS</p>';
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and CSS</p>', $this->pm->process_text( $text )
		);
		$this->pm->options[ 'maxlinks' ] = 2;
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and ' . $this->css_linked . '</p>', $this->pm->process_text( $text )
		);
	}

	public function test_max_links_0_disables_links() {
		$this->pm->options[ 'maxlinks' ] = 0;
		$text = '<p>JavaScript and CSS</p>';
		$this->assertEquals(
				$text, $this->pm->process_text( $text )
		);
	}

	public function test_max_single_term() {
		$this->pm->options[ 'maxsingle' ] = 1;
		$text = '<p>JavaScript and JavaScript</p>';
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and JavaScript</p>', $this->pm->process_text( $text )
		);
		$this->pm->options[ 'maxsingle' ] = 2;
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and ' . $this->js_linked . '</p>', $this->pm->process_text( $text )
		);
	}

	public function test_max_single_url() {
		$this->pm->options[ 'maxsingleurl' ] = 1;
		$text = '<p>JavaScript and JS</p>';
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and JS</p>', $this->pm->process_text( $text )
		);
		$this->pm->options[ 'maxsingleurl' ] = 2;
		$this->assertEquals(
				'<p>' . $this->js_linked . ' and <a target="_blank" title="JS" href="' . $this->js_href . '" class="promote-mdn">JS</a></p>', $this->pm->process_text( $text )
		);
	}

	public function test_custom_key() {
		$this->pm->options[ 'customkey' ] = '';
		$text = '<p>JavaScript and groovecoder</p>';
		$this->pm->options[ 'customkey' ] = array( 'groovecoder' => 'http://groovecoder.com' );
		$this->assertEquals(
				'<p>JavaScript and <a target="_blank" title="groovecoder" href="http://groovecoder.com' . $this->pm->options[ 'tracking_querystring' ] . '" class="promote-mdn">groovecoder</a></p>', $this->pm->process_text( $text )
		);
	}

	public function test_ignore_term() {
		$this->pm->options[ 'ignore' ] = array( '' );
		$this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
		$this->pm->options[ 'ignore' ] = array( 'javascript' );
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
	}

	public function test_ignore_post() {
		FunctionMocker::replace( 'is_page', function () {
			return 'about';
		} );
		$this->pm->options[ 'ignorepost' ] = array( 'about' );
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
	}

	public function test_ignore_all_pages() {
		global $page;
		$page = 'is-page';
		$this->pm->options[ 'ignoreallpages' ] = 'on';
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
	}

	public function test_ignore_all_posts() {
		FunctionMocker::replace( 'is_single', '__return_true' );
		$this->pm->options[ 'ignoreallposts' ] = 'on';
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
		FunctionMocker::replace( 'is_single', '__return_false' );
		FunctionMocker::replace( 'is_page', '__return_false' );
		$this->pm->options[ 'ignoreallposts' ] = 'off';
		$this->assertEquals( $this->linked_text, $this->pm->process_text( $this->text ) );
		FunctionMocker::replace( 'is_single', function () {
			return 'other-post';
		} );
		$this->pm->options[ 'ignorepost' ] = array( 'other-post' );
		$this->assertEquals( $this->text, $this->pm->process_text( $this->text ) );
	}

	public function test_leave_escaped_quotes_alone() {
		$text = 'strip("<img onerror=\'alert(\\"could run arbitrary JS here\\")\' src=bogus>")';
		$this->assertEquals( $text, $this->pm->process_text( $text ) );
	}

}
