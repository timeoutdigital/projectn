<?php
/**
 * Maps singapore Pois for the Importer
 *
 * @package projectn
 * @subpackage singapore.import.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @author Clarence Lee <clarencelee@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 * @todo go through the file after all the questions are answered
 *
 *
 */
class singaporeVenuesMapper extends DataMapper
{
    /*
   * @var Vendor
    */
    private $_vendor;

    /*
   * @var curlImporter
    */
    protected $_curlImporter;

    /**
     * Construct
     *
     * @param $curlImporter curlImporter
     */
    public function __construct( curlImporter $curlImporter )
    {
      $this->_curlImporter = $curlImporter;
      $this->_vendor = Doctrine::getTable( 'Vendor' )->getVendorByCityAndLanguage( 'Singapore', 'en-US' );
    }

    public function mapPois()
    {
      $url = 'http://www.timeoutsingapore.com/xmlapi/venues/';
      $xml = $this->curl( $url );
      $venues = $xml->xpath( '/rss/channel/item' );

      foreach( $venues as $venue )
      {
        $venueDetail = $this->fetchDetailUrl( (string) $venue->link );
        if ( !( $venueDetail instanceof SimpleXMLElement ) )
        {
            $e = new Exception( 'could not retrieve valid venue node by url: ' . (string) $poiXmlObj->link );
            $this->notifyImporterOfFailure( $e, $poi );
        }
        $this->mapPoi( $venueDetail );
      }
    }

    /**
     * @param $poiXml
     */
    private function mapPoi( SimpleXMLElement $poiXml )
    {
      $poi = ProjectN_Test_Unit_Factory::get( 'Poi' );
      $poi[ 'vendor_poi_id' ]              = (string) $poiXml->id;
      $poi[ 'review_date' ]                = (string) $poiXml->data_change;
      $poi[ 'local_language' ]             = substr( $this->_vendor[ 'language' ], 0, 2 );
      $poi[ 'poi_name' ]                   = (string) $poiXml->name;
      $poi[ 'country' ]                    = 'SGP';
      $poi[ 'url' ]                        = (string) $poiXml->website;
      $poi[ 'description' ]                = (string) $poiXml->excerpt;
      $poi[ 'price_information' ]          = $this->extractPriceInformation( $poiXml );
      $poi[ 'openingtimes' ]               = (string) $poiXml->opentime;
      $poi[ 'vendor_id' ]                  = $this->_vendor[ 'id' ];

      $address = $this->getAddressInfo( $poiXml );
      if ( $address )
      {
          $poi->applyFeedGeoCodesIfValid( (string) $address->mm_lat, (string) $address->mm_lon );

          $poi[ 'public_transport_links' ]     = $this->extractPublicTransportLinks( $address );
          $poi[ 'phone' ]                      = '+65 ' .  (string) $address->phone;
          $poi[ 'additional_address_details' ] = (string) $address->location;
          $poi[ 'zips' ]                       = (string) $address->postcode;
          $poi[ 'street' ]                     = trim( (string) $address->address, ", " );
          $poi[ 'city' ]                       = $this->_vendor['city'];

          $poi->setgeocoderLookUpString( $this->extractgeocoderLookupString( $poi ) );
      }

      //@todo test the rest of this function
      $poi->addProperty( 'Critics_choice', (string) $poiXml->critic_choice );
      $poi->addProperty( 'Timeout_link', (string) $poiXml->link );

      $categoriesArray = array();
      if ( (string) $poiXml->section != '' )  $categoriesArray[] = (string) $poiXml->section;
      if ( (string) $poiXml->category != '' ) $categoriesArray[] = (string) $poiXml->category;
      if ( 0 < count( $categoriesArray ) )
      {
          $poi->addVendorCategory( $categoriesArray,  $this->_vendor[ 'id' ]);
      }

      //@todo refactor to use the method from the parent class
      // add images
      $this->addImage( $poi, $poiXml->highres );
      $this->addImage( $poi, $poiXml->large_image );
      $this->addImage( $poi, $poiXml->thumbnail );
      $this->addImage( $poi, $poiXml->thumb );
      $this->addImage( $poi, $poiXml->image );

      $this->notifyImporter( $poi );
    }

    private function extractPriceInformation( $poiXml )
    {
      setlocale(LC_MONETARY, 'en_US.UTF-8');
      return stringTransform::formatPriceRange( (float) $poiXml->min_price, (float) $poiXml->max_price );
    }

    private function getAddressInfo( $poiXml )
    {
      $address = null;

      $addresses = $poiXml->xpath( '//addresses[1]/address_slot' );
      if( !empty( $addresses ) )
        $address = $addresses[0];

      return $address;
    }

    private function extractgeocoderLookupString( Poi $poi )
    {
      return stringTransform::concatNonBlankStrings( ', ', array( $poi[ 'street' ], $poi[ 'additional_address_details' ], $poi[ 'zips' ], $poi[ 'city' ]  ) );
    }

    private function extractPublicTransportLinks( $address )
    {
      $nearStation = (string) $address->near_station;
      if( $nearStation )
      {
        $nearStation = 'Near station: ' . $nearStation;
      }

      $bus = (string) $address->bus;
      if( $bus )
      {
        $bus = 'Buses' . $nearStation;
      }

      return stringTransform::concatNonBlankStrings( ', ', array( $nearStation, $bus ) );
    }

    private function curl( $url )
    {
      $parametersArray = array( 'section' => 'index', 'full' => '', 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
      $this->_curlImporter->pullXml ( $url, '', $parametersArray, 'GET', true );
      return $this->_curlImporter->getXml();
    }

   /*
    * fetch details
    *
    * valid url format:
    * http://www.timeoutsingapore.com/xmlapi/xml_detail/?event=8514&key=ffab6a24c60f562ecf705130a36c1d1e
    * http://www.timeoutsingapore.com/xmlapi/xml_detail/?venue=2154&key=ffab6a24c60f562ecf705130a36c1d1e
    * http://www.timeoutsingapore.com/xmlapi/xml_detail/?movie=758&key=ffab6a24c60f562ecf705130a36c1d1e
    *
    * @param string $url
    *
    */
    public function fetchDetailUrl( $url )
    {
        $urlPartsArray = array();

        preg_match ( '/^(http:\/\/.*)\?(event|venue|movie)=(.*)&(?:amp;)?key=(.*)$/', $url, $urlPartsArray );

        if ( count( $urlPartsArray ) == 5 )
        {
            $parametersArray = array( $urlPartsArray[ 2 ] => $urlPartsArray[ 3 ], 'key' => $urlPartsArray[ 4 ] );
            $this->_curlImporter->pullXml ( $urlPartsArray[ 1 ], '', $parametersArray, 'GET', true );

            return $this->_curlImporter->getXml();
        }
        else
        {
            throw new Exception( "invalid detail url" );
        }
    }

    /**
     * helper function to add images
     *
     * @param Doctrine_Record $storeObject
     * @param SimpleXMLElement $element
     */
    protected function addImage( Doctrine_Record $storeObject, SimpleXMLElement $element )
    {
        if ( (string) $element != '' )
        {
            try
            {
                $storeObject->addMediaByUrl( (string) $element );
            }
            catch( Exception $e )
            {
                 $this->notifyImporterOfFailure( $e );
            }
        }
    }
}
?>
