<?php
class PNTestAdmin extends \Codeception\TestCase\WPTestCase {
  /**
   * @var string
   */
  protected $root_dir;
  public function setUp() {
    parent::setUp();
    // your set up methods here
    $this->root_dir = dirname( dirname( dirname( __FILE__ ) ) );
  }
  public static function setUpBeforeClass() {
    require_once(  dirname(__FILE__)  . '/../../../admin/Promote_MDN_Admin.php' );
    add_action( 'plugins_loaded', array( 'Promote_MDN_Admin', 'get_instance' ) );
  }
  public function tearDown() {
    parent::tearDown();
  }
  /**
   * @test
   * it should be instantiatable
   */
  public function it_should_be_instantiatable() {
    $this->assertInstanceOf( 'Promote_MDN_Admin', Promote_MDN_Admin::get_instance() );
  }
}
