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
  public function __construct( Vendor $vendor, array $params )
  {
    $this->_validateConstructorParams( $vendor, $params );
    $this->vendor = $vendor;

    $this->dataMapperHelper = new projectNDataMapperHelper( $this->vendor );

    $this->dateTimeZoneLondon = new DateTimeZone( 'Europe/London' );
    $this->dateTimeZoneLisbon = new DateTimeZone( 'Europe/Lisbon' );

    $this->_loadXML( $vendor, $params );
  }

    private function _validateConstructorParams( $vendor, $params )
    {
        if( !( $vendor instanceof Vendor ) || !isset( $vendor[ 'id' ] ) )
        {
            throw new $this->exceptionClass( 'Invalid Vendor Passed to LisbonFeedBaseMapper Constructor.' );
        }

        if( !isset( $params['curl']['classname'] ) || !isset( $params['curl']['src'] ) || !isset( $params['type'] ) )
        {
            throw new $this->exceptionClass( 'Invalid Params Passed to LisbonFeedBaseMapper Constructor.' );
        }
    }

    private function _loadXML( $vendor, $params )
    {
        $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
        $curlInstance->exec();

        new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );

        $dataFixer = new xmlDataFixer( $curlInstance->getResponse() );
        $dataFixer->removeHtmlEntiryEncoding();
            
        $this->xml = $dataFixer->getSimpleXML();
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
   * Returns the simplexml object
   *
   * @return SimpleXMLElement
   */
  public function getXml()
  {
      return $this->xml;
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
