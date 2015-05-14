<?php
require_once( dirname( __FILE__ ) . '/doubles.php' );
require_once( dirname( __FILE__ ) . '/../promote-mdn.php' );


class AdminNoticesTest extends PHPUnit_Framework_TestCase
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
			'allowcomments' => ''
        );
        $this->pm = new PromoteMDN( $options );
    }

    public function test_none_hidden()
    {
        $this->expectOutputRegex( '/<div class="updated">.*Thanks for installing/' );
        $this->pm->admin_notices();
    }

    public function test_hide_new()
    {
        $this->pm->options['hide_notices']['new'] = true;
        $this->expectOutputRegex( '/<div class="updated">.*(?!Thanks for installing)/' );
        $this->pm->admin_notices();
    }
}
