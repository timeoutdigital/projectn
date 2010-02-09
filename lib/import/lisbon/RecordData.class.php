<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class RecordData
{
  /**
   * @var string
   */
  private $_recordClass;

  /**
   * @var array
   */
  private $_data;

  /**
   * Instiate with the record class, e.g. 'Poi', 'Event', 'Movie' ...
   * and an array to hydrate a record of that class
   *
   * <code>
   * $poiData = new RecordData( 'Poi', array( 'poi_name' => 'A point of interest' ) );
   * </code>
   *
   * @param string $recordClass
   * @param array $data
   */
  public function __construct( $recordClass, $data=array() )
  {
    if( !class_exists( $recordClass ) )
    {
      throw new Exception( 'The class ' . $recordClass . ' does not exist.' );
    }
    if( !is_array( $data ) )
    {
      throw new Exception( 'The parameter $data expects an array.' );
    }
    $this->_recordClass = $recordClass;
    $this->_data = $data;
  }

  public function getClass()
  {
    return $this->_recordClass;
  }

  public function getData()
  {
    return $this->_data;
  }
}
?>
