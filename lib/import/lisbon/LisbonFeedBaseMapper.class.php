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
   * @var geocoder
   */
  protected $geocoderr;

  /**
   * @var SimpleXMLElement
   */
  protected $xml;

  /**
   *
   * @var projectNDataMapperHelper
   */
  protected $dataMapperHelper;

  /**
   *
   * @param SimpleXMLElement $xml
   * @param geocoder $geocoderr
   */
  public function __construct( SimpleXMLElement $xml, geocoder $geocoderr = null )
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'Lisbon', 'pt' );

    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->dataMapperHelper = new projectNDataMapperHelper( $vendor );
    $this->vendor = $vendor;
    $this->xml = $xml;

    $this->dateTimeZoneLondon = new DateTimeZone( 'Europe/London' );
    $this->dateTimeZoneLisbon = new DateTimeZone( 'Europe/Lisbon' );

    if( is_null( $geocoderr ) )
    {
      $geocoderr = new googleGeocoder();
    }
    $this->geocoderr = $geocoderr;
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
      $value = (string) $value;

      if( in_array( $key, $ignoreMap ) || $value == '' )
      {
        continue;
      }
      elseif( key_exists( $key, $map ) )
      {
        $recordKey = $map[ $key ];
        $record[ $recordKey ] = $this->clean( (string) $value );
      }
      else
      {
        //this seems to cause elements to be looped twice
        $record->addProperty( $key, $this->clean( (string) $value ) );

      }
    }

  }

  /**
   * Removes Portuguese weird whitespace and apply mb_trim()
   * @param string $string
   * @return string
   */
  protected function clean( $string , $chars = '' )
  {
      $string = preg_replace( "/ /u", ' ', $string );
      return stringTransform::mb_trim( $string , $chars );
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
}
?>
