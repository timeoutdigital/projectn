<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../bootstrap.php';

require_once dirname(__FILE__).'/../../../lib/stringTransform.class.php';


/**
 * Test class for stringn transform.
 *
 * @package test
 * @subpackage lib.unit.lib
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

  public function testByteToHumanReadable()
  {
      // valid test
      $this->assertEquals( '1 kb', stringTransform::byteToHumanReadable( 1024 ), ' Should be 1KB');
      $this->assertEquals( '1 mb', stringTransform::byteToHumanReadable( 1024 * 1024 ) );
      $this->assertEquals( '1 gb', stringTransform::byteToHumanReadable( 1024 * 1024 * 1024 ) );
      $this->assertEquals( '1 tb', stringTransform::byteToHumanReadable( 1024 * 1024 * 1024 * 1024 ) );
      $this->assertEquals( '1 pb', stringTransform::byteToHumanReadable( 1024 * 1024 * 1024 * 1024 * 1024 ) );

      // invalid

      $this->assertEquals( NAN , stringTransform::byteToHumanReadable( 'invalid' ) );
      $this->assertEquals( NAN , stringTransform::byteToHumanReadable( -1245 ) );
  }

  /**
   * Load a comprehensive list of timeinfo strings (delimeted by ^^) and try to
   * apply the extractTimeRangesFromText() & extractTimesFromText() functions, and see what happens.
   * also uses stringTransform::formatAsTime()
   * eg. 'Tera a domingo das 10.00 s 18.00.' 'Ter-Sex 10-18h' '21.30' 'At 1 de Abril.'
   * Rejects: multiple date ranges, multiple times (except where we have 2 times and they are part of a single range).
   */
  public function testExtractStartingTimesFromText()
  {
      $import = explode( "^^", file_get_contents( TO_TEST_DATA_PATH . '/timeinfo_examples.txt' ) );
      $data = array(); // Array to store results for this test.

      //$import = array( 'Todos os sbados; Formao A: 14.30h-16h; ' );
      foreach( $import as $item )
      {
          $ranges = stringTransform::extractTimeRangesFromText( trim( $item ) );
          $times = stringTransform::extractTimesFromText( trim( $item ) );

          if( ( count( $ranges ) == 1 && count( $times ) == 2 ) || count( $times ) == 1 )
              $data[] = stringTransform::toDBTime( trim( $times[0], " :-h." ) );
      }
      //print_r( $data );
      //print( "-- Found start time found for: " . count( $data ) . " of " . count( $import ) . " strings checked. --" . PHP_EOL );
  }

  /**
   * Check if an email address is valid, returns boolean.
   * Email addresses are a bit off a weird one, valid characters include _-/=!%
   */
  public function testIsValidEmail()
  {
      // Valid
      $this->assertTrue( stringTransform::isValidEmail( 'peterjohnson@timeout.com' ) );
      $this->assertTrue( stringTransform::isValidEmail( 'peter.johnson=cool@example.museum' ) );
      $this->assertTrue( stringTransform::isValidEmail( 'a@a.a.ab' ) );

      // Escaped Characters
      $this->assertFalse( stringTransform::isValidEmail( 'Abc@def89@example.com' ) ); // Two @ symbols
      $this->assertTrue( stringTransform::isValidEmail( 'Abc\@def89@example.com' ) ); // Apparently this IS a valid email address because the @ is quoted.

      // Invalid
      $this->assertFalse( stringTransform::isValidEmail( '@example.com' ) ); // Missing alias
      $this->assertFalse( stringTransform::isValidEmail( 'a@example.' ) ); // Missing Domain 2LD
      $this->assertFalse( stringTransform::isValidEmail( 'a@com' ) ); // Missing Domain
      $this->assertFalse( stringTransform::isValidEmail( 'a@.com' ) ); // Missing Domain
      $this->assertFalse( stringTransform::isValidEmail( 'www.google.com' ) ); // A domain name
      $this->assertFalse( stringTransform::isValidEmail( 'peter.com@a' ) );
  }
  
  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
  }

  /**
   * Test multibyte trim
   */
  public function testMbTrim()
  {
    // Using this weird portugese space --> <--
    //$trimmed = stringTransform::mb_trim( " \n.,.,.,\nMajor de Sarrià\n., \n  ", "., " );
    // mb trim failed to trim Portugese weird whitespace. We will create a function to handle this
    // directly in portugese mapper

    $trimmed = stringTransform::mb_trim( "\nMajor de Sarrià\n " );
    $this->assertEquals( $trimmed, "Major de Sarrià" );

    $trimmed = stringTransform::mb_trim( "\n.Major de Sarrià\n ", "." );
    $this->assertEquals( $trimmed, "Major de Sarrià" );

    $trimmed = stringTransform::mb_trim( "\n,Major de Sarrià\n ", "," );
    $this->assertEquals( $trimmed, "Major de Sarrià" );

    $trimmed = stringTransform::mb_trim( "\n.,Major de Sarrià\n ,.\n\t\r
        ", ".," );
    $this->assertEquals( $trimmed, "Major de Sarrià" );

    $trimmed = stringTransform::mb_trim( "\n.,Major de \nSarrià\n ,.\n", ".," );
    $this->assertEquals( $trimmed, "Major de \nSarrià" );

    $trimmed = stringTransform::mb_trim( "\0.,Major de \nSarrià\n |.\n", ".,|" );
    $this->assertEquals( $trimmed, "Major de \nSarrià" );

    $trimmed = stringTransform::mb_trim( "\t.,Major de \nSarrià\n ,.\n", ".," );
    $this->assertEquals( $trimmed, "Major de \nSarrià" );

    $trimmed = stringTransform::mb_trim( "\0.,Major de \nSarrià\n ,.\n", ".," );
    $this->assertEquals( $trimmed, "Major de \nSarrià" );

$london_weird_characters = <<<EOF
<I>Swans	</I>																						Wisely moving from the middle of July to the middle of autumn, this indoor, forward-thinking avant-rock weekend brings together all sorts of fiercely experimental noisemakers, from psychedelic-folk to death metal, with a hotly anticipated headline set from Michael Gira's New York noise inspiration Swans. Don't expect many stony-faced rock nerds, though. The organisers serve tea and cake throughout and they're promising other fun and games this year.
EOF;

    $trimmed = stringTransform::mb_trim( $london_weird_characters );
    $this->assertEquals( $trimmed, $london_weird_characters );

$multiline = <<<EOF
testing





    efgjen
EOF;

    $trimmed = stringTransform::mb_trim( $multiline );
    $this->assertEquals( $trimmed, $multiline );
    
  }

  /**
   * Test to extract and email address from a field
   */
  public function testExtractEmailAddressesFromText() {

    $expectedOutput = array( 'email@address.com',
                             'email2@address.co.uk',
                             'anotheremail@address.com' );

    $emailAddressesArray = stringTransform::extractEmailAddressesFromText( $this->input );

    $this->assertEquals( $expectedOutput, $emailAddressesArray );
  }

  /**
   * Test to extract urls from text
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
      $transform = stringTransform::formatPhoneNumber('93 424 65 77' , '+34');
      $this->assertEquals('+34 9 3424 6577', $transform, 'Testing Barcelona');

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

      $transform = stringTransform::formatPhoneNumber('212-864-7326' , '+1');
      $this->assertEquals('+1 212 864 7326', $transform, 'Testing American number type 8');


      $transform = stringTransform::formatPhoneNumber('0207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 1');

      $transform = stringTransform::formatPhoneNumber('207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 2');

      $transform = stringTransform::formatPhoneNumber('+44 207 3577173', '+44');
      $this->assertEquals('+44 207 357 7173', $transform, 'UK number type 3');

      $transform = stringTransform::formatPhoneNumber('04 334 4159', '+971');
      $this->assertEquals('+971 4 334 4159', $transform, 'UAE number type 1');



      $transform = stringTransform::formatPhoneNumber(' ', '+971');
      $this->assertEquals(null, $transform, 'No number type 1');

      $transform = stringTransform::formatPhoneNumber('', '+971');
      $this->assertEquals(null, $transform, 'No number type 2');


  }

  /**
   * Test that a url has a http at the start
   */
  public function testFormatUrl()
  {
      $this->assertEquals( 'http://www.google.com', stringTransform::formatUrl('http://www.google.com'), 'Test that http is in string' );
      $this->assertEquals( 'http://www.google.com', stringTransform::formatUrl( 'www.google.com' ) );
      $this->assertEquals( 'http://foo.com/', stringTransform::formatUrl( 'foo.com/' ) );

      $this->assertNull( stringTransform::formatUrl( null ) );
      $this->assertNull( stringTransform::formatUrl( 99 ) );
      $this->assertNull( stringTransform::formatUrl( new stdClass ) );
      $this->assertNull( stringTransform::formatUrl( new SimpleXMLElement( '<xml />' ) ) );
      $this->assertNull( stringTransform::formatUrl( '' ) );
      
      $this->assertNull( stringTransform::formatUrl( 'http://' ) );
      $this->assertNull( stringTransform::formatUrl( 'google' ) );
      $this->assertNull( stringTransform::formatUrl( 'google.' ) );
      $this->assertNull( stringTransform::formatUrl( 'www.invalid*&^#characters.org' ) );
      $this->assertNull( stringTransform::formatUrl( 'google dot com' ) );
      $this->assertNull( stringTransform::formatUrl( 'http:///www.google.com' ) );
  }

  /**
   * Test price ranges
   */
  public function testFormatPriceRange()
  {
    $this->assertEquals( '', stringTransform::formatPriceRange( '0', '0.00', '£ ' ) );
    $this->assertEquals( '£ 1.00', stringTransform::formatPriceRange( '1', '0.00', '£ ' ) );
    $this->assertEquals( 'between £ 1.00 and £ 5.00', stringTransform::formatPriceRange( '1', '5', '£ ' ) );
    $this->assertEquals( 'between £ 1.50 and £ 3.00', stringTransform::formatPriceRange( '1.50', '3.00', '£ ' ) );
    $this->assertEquals( '£ 1.50 - £ 3.00', stringTransform::formatPriceRange( '1.50', '3.00', '£ ', 'short' ) );
    $this->assertEquals( '$1.50 - $3.00', stringTransform::formatPriceRange( '1.50', '3.00', '$', 'short' ) );
  }

  /**
   * Test concatNonBlankStrings
   */
  public function testConcatNonBlankStrings()
  {
    $values = array( 'one', '', 'two', ' ', 'three' );
    $this->assertEquals( 'one, two, three', stringTransform::concatNonBlankStrings( ', ', $values ) );

    $values = array( 'goo' );
    $this->assertEquals( 'goo', stringTransform::concatNonBlankStrings( ', ', $values ) );
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

  public function testCommaThe()
  {
    $string = 'foo bar, The';
    $this->assertEquals( 'The foo bar', stringTransform::move_CommaThe_FromEndToBeginning($string));

    $string = 'foo bar, the';
    $this->assertEquals( 'the foo bar', stringTransform::move_CommaThe_FromEndToBeginning($string));
  }

  public function testRemoveEmptyDelimiters()
  {
    $this->assertEquals(                           'foo, bar, baz',
      stringTransform::removeEmptyDelimiters( ', ', ', foo, , , bar, , baz' )
      );

    $this->assertEquals(                           'foo|bar|baz',
      stringTransform::removeEmptyDelimiters( '|', '||foo|bar||||baz|' )
      );
  }

  public function testRemoveTrailingCommas()
  {
    $this->assertEquals(                     'foo',
      stringTransform::removeTrailingCommas( 'foo,' )
      );
  }

  public function testToDBTime()
  {
      $beforeArray = array( '2', '2pm', '14:00' );
      $afterArray = array( '', '14:00:00', '14:00:00' );

      for ( $i=0; $i < count( $beforeArray ); $i++ )
      {
          $this->assertEquals( $afterArray[ $i ], stringTransform::toDBTime( $beforeArray[ $i ] ) );
      }
  }

  public function testToDBTimeByFormat()
  {
      $formatArray = array( 'H', 'h', 'ha', 'h', 'H:m' );
      $beforeArray = array( '', '2', '2pm', '2pm', '14:00' );
      $afterArray = array( '', '02:00:00', '14:00:00', '', '14:00:00' );

      for ( $i=0; $i < count( $beforeArray ); $i++ )
      {
          $this->assertEquals( $afterArray[ $i ], stringTransform::toDBTimeByFormat( $formatArray[ $i ], $beforeArray[ $i ] ) );
      }
  }

  public function testExtractStartTime()
  {
      $beforeArray = array( 'Mondays-Fridays (12pm-7pm); Saturdays (10am-5pm).Closed Sundays and Public Holidays',
                            'n/a',
                            '12pm-8pm',
                            '10 pm (Every Thursday)',
                            '9am',
                            '2-8pm',
                            '2.30pm-5.30pm',
                            '9pm to 12.45am on weekdays and 9pm to 1.30am on weekends.',
                            '12pm to 6pm',
                            '8pm to',
                            '6pm-',
                            '12p',
                            'a12pm',
                            '12pmp',
                            '12am',
                     );
      $afterArray = array( '',
                           '',
                           '12pm',
                           '10 pm',
                           '9am',
                           '2pm',
                           '2.30pm',
                           '9pm',
                           '12pm',
                           '8pm',
                           '6pm',
                           '12',
                           '',
                           '12pm',
                           '12am',
                         );
      $afterArrayFormatted = array( '',
                                    '',
                                    '12:00:00',
                                    '22:00:00',
                                    '09:00:00',
                                    '14:00:00',
                                    '14:30:00',
                                    '21:00:00',
                                    '12:00:00',
                                    '20:00:00',
                                    '18:00:00',
                                    '',
                                    '',
                                    '12:00:00',
                                    '00:00:00',
                         );

      for ( $i=0; $i < count( $beforeArray ); $i++ )
      {
          $this->assertEquals( $afterArray[ $i ], stringTransform::extractStartTime( $beforeArray[ $i ], false ) );
      }

      for ( $i=0; $i < count( $beforeArray ); $i++ )
      {
          $this->assertEquals( $afterArrayFormatted[ $i ], stringTransform::extractStartTime( $beforeArray[ $i ] ) );
      }
  }
  
  public function testRemoveMultipleLines()
  {
      $string = <<<EOF
first






second line











































































































































test 3
EOF;
     
$asert = <<<EOF
first
second line
test 3
EOF;
      $string = stringTransform::removeMultipleLines($string);
      $this->assertEquals( $asert, $string);
  }

}
?>
