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
   * @var DateTimeZone
   */
  private $dateTimeZoneLondon;

  /**
   * @var DateTimeZone
   */
  private $dateTimeZoneLisbon;

  /**
   * @var Vendor
   */
  protected $vendor;

  /**
   * @var geoEncode
   */
  protected $geoEncoder;

  /**
   * @var SimpleXMLElement
   */
  protected $xml;

  public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null )
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'Lisbon', 'pt' );

    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->vendor = $vendor;
    $this->xml = $xml;

    $this->dateTimeZoneLondon = new DateTimeZone( 'Europe/London' );
    $this->dateTimeZoneLisbon = new DateTimeZone( 'Europe/Lisbon' );

    if( is_null( $geoEncoder ) )
    {
      $geoEncoder = new geoEncode();
    }
    $this->geoEncoder = $geoEncoder;
  }

  /**
   * Gets the utc offset for a Lisbon date
   */
  protected function getUtcOffset( $time )
  {
    $offsetSeconds = $this->dateTimeZoneLondon->getOffset(
      new DateTime( $time, $this->dateTimeZoneLisbon )
    );
    return $offsetSeconds / 3600;
  }

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
  protected function mapAvailableData( $record, SimpleXMLElement $element )
  {
    $map = $this->getMap();
    $ignoreMap = $this->getIgnoreMap();
    foreach( $element->attributes() as $key => $value )
    {
      if( in_array( $key, $ignoreMap ) )
      {
        continue;
      }
      else if( key_exists( $key, $map ) )
      {
        $recordKey = $map[ $key ];
        $record[ $recordKey ] = (string) $value;
      }
      else
      {
        //this seems to cause elements to be looped twice
        $record->addProperty( $key, $value );
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
