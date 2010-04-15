<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../../bootstrap.php';

/**
 * Test class for the eventPlaceIdsShouldExistInPoiXml
 *
 *
 * @package test
 * @subpackage task.lib.unit.test
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class eventsShouldHaveAtleastOneVendorCategoryTest extends PHPUnit_Framework_TestCase
{
  public function testRun()
  {
    $goodTask = new eventsShouldHaveAtleastOneVendorCategoryTestTask( true );
    $test     = new eventsShouldHaveAtleastOneVendorCategory(  );
    $this->assertTrue( $test->run( $goodTask ) );

    $badTask = new eventsShouldHaveAtleastOneVendorCategoryTestTask( false );
    $test    = new eventsShouldHaveAtleastOneVendorCategory( );
    $this->assertFalse( $test->run( $badTask ) );
  }
}

class eventsShouldHaveAtleastOneVendorCategoryTestTask
{
  private $good;
  private $fileNames = array(
    'event-xml' => 'event.xml',
    'poi-xml'   => 'poi.xml',
  );

  public function __construct( $good = true )
  {
    $this->good = $good;
  }

  public function getOption( $option )
  {
    return $this->fileNames[ $option ];
  }

  public function getPoiXml() { }

  public function getEventXml()
  {
    if( $this->good )
    {
      return simplexml_load_string( '
      <root>
        <event>
          <name>foo</name>
          <version>
            <vendor-category>foo</vendor-category>
          </version>
        </event>
        <event>
          <name>bar</name>
          <version>
            <vendor-category>bar</vendor-category>
          </version>
        </event>
        <event>
          <name>baz kee</name>
          <version>
            <vendor-category>baz</vendor-category>
            <vendor-category>kee</vendor-category>
          </version>
        </event>
      </root>
      ' );
    }
    else
    {
      return simplexml_load_string( '
      <root>
        <event>
          <name>oof</name>
          <version>
            <vendor-category>oof</vendor-category>
          </version>
        </event>
        <event>
          <name>ofo</name>
          <version>
          </version>
        </event>
        <event>
          <name>rab</name>
          <version>
            <vendor-category>rab</vendor-category>
          </version>
        </event>
        <event>
          <name>fio</name>
          <version>
          </version>
        </event>
      </root>
      ' );
    }
  }
}
