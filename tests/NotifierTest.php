<?php
require_once( dirname( __FILE__ ) . '/doubles.php' );
require_once( dirname( __FILE__ ) . '/../promote-mdn.php' );


class NotifierTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->notifier = new PromoteMDN_Notifier();
    }

    public function test_render_meta_box_content()
    {
        $post = '';
        $this->expectOutputRegex( '/name="notify_mozilla"/' );
        $this->notifier->render_meta_box_content( $post );
    }
}
