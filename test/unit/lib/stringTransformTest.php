<?php
setlocale(LC_ALL, array('en_US.UTF-8', ));
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../lib/stringTransform.class.php';


/**
 * Test class for stringn transform
 *
 * @package test
 * @subpackage lib.unit
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class stringTransformTest extends PHPUnit_Framework_TestCase {
  /**
   * @var stringTransform
   */
  protected $object;

  /**
   * @var string test string
   */
  protected $input = 'chicken.fox email@address.com, email@address.c, email2@address.co.uk, some
                      text, 0983 3483 993 anotheremail@address.com, blurb@test,
                      @super.com
                      http://www.google.com/
                      www.google.ch/
                      google.com/home.html
                      http://www.google.com/home.html
                      www.google.com/home.html
                      google.com/home.htm
                      https://www.google.com/home.html
                      https://google.com
                      http://google.com/test/sub/folder/
                      http://google.com/?x=this
                      http://google.com?x=this
                      http://google.com?this
                      http://google.com#this
                      http://google.com/#this';


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
  }

  /**
   * @todo Implement testExtractEmailAddressesFromText().
   */
  public function testExtractEmailAddressesFromText() {
    
    $expectedOutput = array( 'email@address.com',
                             'email2@address.co.uk',
                             'anotheremail@address.com' );

    $emailAddressesArray = stringTransform::extractEmailAddressesFromText( $this->input );
    
    $this->assertEquals( $expectedOutput, $emailAddressesArray );
  }

  /**
   * @todo Implement testExtractUrlsFromText().
   */
  public function testExtractUrlsFromText() {

    $expectedOutput = array( 'chicken.fox',
                             'http://www.google.com/',
                             'www.google.ch/',
                             'google.com/home.html',
                             'http://www.google.com/home.html',
                             'www.google.com/home.html',
                             'google.com/home.htm',
                             'https://www.google.com/home.html',
                             'https://google.com',
                             'http://google.com/test/sub/folder/',
                             'http://google.com/?x=this',
                             'http://google.com?x=this',
                             'http://google.com?this',
                             'http://google.com#this',
                             'http://google.com/#this' );

    $urlArray = stringTransform::extractUrlsFromText( $this->input );
    
    $this->assertEquals( $expectedOutput, $urlArray );
  }

  /**
   * @todo Implement testExtractPhoneNumbersFromText().
   */
  public function testExtractPhoneNumbersFromText()
  {
  }

  
  /**
   * Test that a valid E.123 number is returned
   */
  public function testFormatPhoneNumber()
  {

      $transform = stringTransform::formatPhoneNumber('630 420-6010' , '+1');
      $this->assertEquals('+1 630 420 6010', $transform, 'Testing American number type 1');

      $transform = stringTransform::formatPhoneNumber('212 633-2229, ext 2' , '+1');
      $this->assertEquals('+1 212 633 2229', $transform, 'Testing American number type 2');
      
      $transform = stringTransform::formatPhoneNumber('718 499-YOGA' , '+1');
      $this->assertEquals('+1 718 499 9642', $transform, 'Testing American number type 3');
      
      $transform = stringTransform::formatPhoneNumber('212 777- 6800' , '+1');
      $this->assertEquals('+1 212 777 6800', $transform, 'Testing American number type 4');
      
      $transform = stringTransform::formatPhoneNumber('212 582-6050,ext207' , '+1');
      $this->assertEquals('+1 212 582 6050', $transform, 'Testing American number type 5');

      $transform = stringTransform::formatPhoneNumber('212 3608163' , '+1');
      $this->assertEquals('+1 212 360 8163', $transform, 'Testing American number type 6');

      $transform = stringTransform::formatPhoneNumber('1-800-MY-CIGAR' , '+1');
      $this->assertEquals('+1 1 800 692 4427', $transform, 'Testing American number type 7');

      $transform = stringTransform::formatPhoneNumber('0207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 1');

      $transform = stringTransform::formatPhoneNumber('207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 2');

      $transform = stringTransform::formatPhoneNumber('+44 207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 3');
     
  }

  /**
   * @todo Implement testFormatUrl().
   */
  public function testFormatUrl()
  {
      $this->assertEquals('http://www.google.com', stringTransform::formatUrl('http://www.google.com'), 'Test that http is in string');

  }

  /**
   * testFormatPriceRange
   */
  public function testFormatPriceRange()
  {
    setlocale(LC_MONETARY, 'en_GB.UTF-8');

    $this->assertEquals( '', stringTransform::formatPriceRange( '0', '0.00' ) );
    $this->assertEquals( '£ 1.00', stringTransform::formatPriceRange( '1', '0.00' ) );
    $this->assertEquals( 'between £ 1.00 and £ 5.00', stringTransform::formatPriceRange( '1', '5' ) );
    $this->assertEquals( 'between £ 1.50 and £ 3.00', stringTransform::formatPriceRange( '1.50', '3.00' ) );
    $this->assertEquals( '£ 1.50 - £ 3.00', stringTransform::formatPriceRange( '1.50', '3.00', 'short' ) );
    setlocale(LC_ALL, array('en_US.UTF-8', ));
    $this->assertEquals( '$1.50 - $3.00', stringTransform::formatPriceRange( '1.50', '3.00', 'short' ) );
  }

  /**
   * Test concatNonBlankStrings
   */
  public function testConcatNonBlankStrings()
  {
    $values = array( 'one', '', 'two', ' ', 'three' );
    $this->assertEquals( 'one, two, three', stringTransform::concatNonBlankStrings( ', ', $values ) );
  }
  
  /**
   * Test xml fixing
   */
  public function testCleanXML( )
  {
   $string = '
one

two

three
';
   $expected = 'onetwothree';
   
   $this->assertEquals( $expected, stringTransform::stripEmptyLines( $string ) );
  }
}
?>
