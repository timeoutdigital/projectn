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
class eachPoiHasAtleastOneVendorCategoryTest extends PHPUnit_Framework_TestCase
{
  public function testVerify()
  {
    $mockTaskGood = new eachPoiHasAtleastOneVendorCategoryTask( true );
    $verifier     = new eachPoiHasAtleastOneVendorCategory( $mockTaskGood );
    $this->assertTrue( $verifier->run( $mockTaskGood ) );
    $this->assertEquals( "All POI entries have atleast one vendor-category tag.", $verifier->getMessage() );

    $mockTaskBad = new eachPoiHasAtleastOneVendorCategoryTask( false );
    $verifier    = new eachPoiHasAtleastOneVendorCategory( $mockTaskBad );
    $this->assertFalse( $verifier->run( $mockTaskBad ) );
    $this->assertEquals( 
      '1 POI entries have no vendor-category tags, showing first 10:' . PHP_EOL . 'Entry two' . PHP_EOL,
      $verifier->getMessage() 
    );
  }
}

class eachPoiHasAtleastOneVendorCategoryTask
{
  private $good;

  public function __construct( $good = true )
  {
    $this->good = $good;
  }

  public function getOption( $option )
  {
    return $this->fileNames[ $option ];
  }

  public function getPoiXml()
  {
    if( $this->good )
    {
      return simplexml_load_string( '
      <root>

        <entry vpid="1">
          <name>Entry one</name>
          <version>
            <content>
              <vendor-category><![CDATA[foo]]></vendor-category>
              <vendor-category><![CDATA[bar & grill]]></vendor-category>
            </content>
          </version>
        </entry>

        <entry vpid="2">
          <name>Entry two</name>
          <version>
            <content>
              <vendor-category>foo</vendor-category>
            </content>
          </version>
        </entry>

        <entry vpid="3">
          <name>Entry three</name>
          <version>
            <content>
              <vendor-category>foo</vendor-category>
            </content>
          </version>
        </entry>

      </root>
      ' );
    }
    else
    {
      return simplexml_load_string( '
      <root>

        <entry vpid="1">
          <name>Entry one</name>
          <version>
            <content>
              <vendor-category>foo</vendor-category>
              <vendor-category>bar</vendor-category>
            </content>
          </version>
        </entry>

        <entry vpid="2">
          <name>Entry two</name>
          <version>
            <content>
            </content>
          </version>
        </entry>

        <entry vpid="3">
          <name>Entry three</name>
          <version>
            <content>
              <vendor-category>foo</vendor-category>
            </content>
          </version>
        </entry>

      </root>
      ' );
    }
  }
}
