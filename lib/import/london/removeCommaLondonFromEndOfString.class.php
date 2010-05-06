<?php
/**
 * London API Bars and Pubs Mapper
 *
 * @package projectn
 * @subpackage london.import.lib.unit
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class removeCommaLondonFromEndOfString
{
  /**
   * @var string
   */
  private $string;

  /**
   * @param string
   */
  public function __construct( $string )
  {
    $this->string = $string;
  }

  /**
   * @param string
   */
  public function getFixedString()
  {
    return preg_replace( '/, ?(london)?,? ?$/i', '', $this->string );
  }
}
