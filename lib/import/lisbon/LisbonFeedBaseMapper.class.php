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
   * @param Vendor $vendor
   * @param array $params
   */
  public function __construct( Vendor $vendor, array $params )
  {
    $this->_validateConstructorParams( $vendor, $params );
    $this->vendor = $vendor;

    $this->dataMapperHelper = new projectNDataMapperHelper( $this->vendor );

    $this->dateTimeZoneLondon = new DateTimeZone( 'Europe/London' );
    $this->dateTimeZoneLisbon = new DateTimeZone( 'Europe/Lisbon' );

    $this->_loadXML( $vendor, $params, isset( $params['curl']['pager'] ) );
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

    private function _loadXML( $vendor, $params, $pagination = false )
    {
        if( $pagination === true )
        {
            $fromDate   = strtotime( $params['curl']['pager']['from'] );
            $toDate     = strtotime( $params['curl']['pager']['to'] );
            $daysAhead  = (int) $params['curl']['pager']['days_per_page']; //lisbon caps the request at max 9 days

            $eventDataSimpleXMLSegmentsArray = array();

            while ( $fromDate < $toDate ) // Only page so far in the future ( specified by $params['curl']['pager']['to'] )
            {
                try
                {
                    $parameters = array(
                        'from' => date( 'Y-m-d', $fromDate ),
                        'to' => date( 'Y-m-d', strtotime( "+$daysAhead day", $fromDate ) ) // Query x days ahead
                    );

                    echo "Getting Lisbon Events for Period: " . $parameters[ 'from' ] . "-" . $parameters[ 'to' ] . PHP_EOL;

                    // -- [start] CURL -- //

                    $curlObj = new $params['curl']['classname']( $params['curl']['src'], $parameters );
                    $curlObj->exec();

                    new FeedArchiver( $this->vendor, $curlObj->getResponse(), 'event_' . $parameters[ 'from' ] . '_to_' . $parameters[ 'to' ] );

                    $dataFixer = new xmlDataFixer( $curlObj->getResponse() );
                    $dataFixer->removeHtmlEntiryEncoding();

                    // -- [end] CURL -- //

                    // Add segment to array
                    $eventDataSimpleXMLSegmentsArray[] = $dataFixer->getSimpleXML();

                    // Move start date ahead one day from last end date
                    $fromDate = strtotime( '+'.( $daysAhead +1 ).' day', $fromDate );
                }
                catch ( Exception $e )
                {
                    // Add error
                    ImportLogger::getInstance()->addError( $e );

                    // Stop while loop.
                    break;
                }
            }

            $this->xml = XmlConcatenator::concatXML( $eventDataSimpleXMLSegmentsArray, 'geral' );
        }
        else
        {
            $curlInstance = new $params['curl']['classname']( $params['curl']['src'] );
            $curlInstance->exec();

            new FeedArchiver( $vendor, $curlInstance->getResponse(), $params['type'] );

            $dataFixer = new xmlDataFixer( $curlInstance->getResponse() );
            $dataFixer->removeHtmlEntiryEncoding();

            $this->xml = $dataFixer->getSimpleXML();
        }
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
