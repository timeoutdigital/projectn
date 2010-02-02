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
class LisbonFeedBaseMapper extends DataMapper
{
  /**
   * Maps all the attributes to the Event's properties unless stated otherwise
   * in getListingsMap() and / or getListingsIgnoreMap();
   *
   * The $propertiesKey parameter is the name of the properties key,
   * e.g. 'PoiProperty' for Poi.
   *
   * @param array $recordArray
   * @param SimpleXMLElement $element
   * $param string $propertiesKey
   */
  protected function mapAvailableData( &$record, SimpleXMLElement $element, $propertiesKey )
  {
    $map = $this->getMap();
    $ignoreMap = $this->getIgnoreMap();

    foreach( $element->attributes() as $key => $value )
    {
      if( in_array( $key, $ignoreMap ) )
      {
        //do nothing
      }
      else if( key_exists( $key, $map ) )
      {
        $recordKey = $map[ $key ];
        $record[ $recordKey ] = (string) $value;
      }
      else
      {
        echo $key . '->' . (string) $value . PHP_EOL;
        $record->addProperty( $key, (string) $value );
      }
    }
  }

  /**
   * Return an array of mappings from xml attributes to record fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array();
  }

  /**
   * Return an array of attributes to ignore when mapping
   *
   * @return array
   */
  protected function getIgnoreMap()
  {
    return array();
  }

  /**
   * Add a property to the data array
   *
   * @return array
   */
  protected function addProperty( $propertiesKey, &$recordArray, $key, $value )
  {
    $property = array();
    $property[ 'lookup' ] = $key;
    $property[ 'value' ] = (string) $value;

    if( !key_exists( $propertiesKey, $recordArray ) )
    {
      $recordArray[ $propertiesKey ] = array();
    }
    $recordArray[ $propertiesKey ][] = $property;
  }
}
?>
