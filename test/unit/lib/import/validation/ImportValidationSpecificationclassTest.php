<?php
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__).'/../../../../../lib/import/validation/BaseValidationSpecification.class.php';

/**
 * Test class for ImportValidationSpecificationclass.
 * Generated by PHPUnit on 2010-01-08 at 16:01:35.
 */
class BaseValidationSpecificationclassTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var  BaseValidationSpecificationclass
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new  BaseValidationSpecification;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * Checks isNonEmptyString returns true for a value that
   * is a string
   * is not empty or just whitespace
   */
  public function testIsNonEmptyString()
  {
    $this->assertFalse(  $this->object->isNonEmptyString( '' ) );
    $this->assertFalse(  $this->object->isNonEmptyString( ' ' ) );
    $this->assertTrue(  $this->object->isNonEmptyString( ' 4' ) );
    $this->assertTrue(  $this->object->isNonEmptyString( ' codD' ) );
  }

  /**
   * Checks has returns true for a value that
   * has word characters, e.g. not just space and numbers
   */
  public function testHasWords()
  {
    $this->assertFalse(  $this->object->hasWords( '45 -_/' ) );
    //$this->assertFalse( ImportValidationSpecificationclass::hasWords( ' ' ) );
  }

  /**
   * Checks isFreeOfOddCharacters returns true for a value that
   * has only alphanumeric and whitespace characters
   * and any characters passed into $except as a string, e.g. ':,/'
   */
  public function testIsFreeOfOddCharacters()
  {
    $this->assertFalse( $this->object->isFreeOfOddCharacters( '45 -_/' ) );
    $this->assertFalse( $this->object->isFreeOfOddCharacters( 'abcXYZ:' ) );
    $this->assertFalse( $this->object->isFreeOfOddCharacters( 'abcXYZ:', ',;' ) );
    $this->assertTrue(  $this->object->isFreeOfOddCharacters( 'abcXYZ:', ':' ) );
  }
}
?>
