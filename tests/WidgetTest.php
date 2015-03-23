<?php
require_once( dirname( __FILE__ ) . '/doubles.php' );
require_once( dirname( __FILE__ ) . '/../promote-mdn.php' );


class WidgetTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->w = new PromoteMDN_Widget();
        $this->i = array( 'text' => 'HTML', 'color' => 'red', 'choosen' => 'old' );
    }

    public function test_update_returns()
    {
        $this->assertEquals( $this->i, $this->w->update( $this->i, '' ) );
    }

    public function test_form_defaults()
    {
        $this->expectOutputRegex( '/value="grey" selected/' );
        $this->expectOutputRegex( '/value="Web" selected/' );
        $this->w->form( array() );
    }

    public function test_form_selected()
    {
        $this->expectOutputRegex( '/value="red" selected/' );
        $this->expectOutputRegex( '/value="HTML" selected/' );
        $this->w->form( $this->i );
    }

    public function test_widget_defaults()
    {
        $this->expectOutputRegex( '/promobutton_mdn4.png/' );
        $this->w->widget( array(), array() );
    }

    public function test_widget_selected()
    {
        $this->expectOutputRegex( '/promobutton_mdn10.png/' );
        $this->w->widget( array(), $this->i );
    }
}
