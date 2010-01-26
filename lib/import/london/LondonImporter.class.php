<?php

/**
 * Imports data from London database
 *
 * @package projectn.lib.import.london
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 *
 * @todo confirm address mapping
 * @todo enquire category mapping
 * @todo fax?
 * @todo confirm url for web
 * @todo confirm description
 * @todo confirm TubeExportName
 * @todo confirm TubeStationID
 * @todo enquire opening times
 * @todo enquire ratings
 * @todo enquire provider
 */
class LondonImporter
{
  /**
   * @var SimpleXMLElement
   */
  private $_venueData;

  /**
   * @var SimpleXMLElement
   */
  private $_eventData;
  
  /**
   * @var SimpleXMLElement
   */
  private $_venueCategoryInformationData;

  /**
   * @var Vendor
   */
  private $_vendor;

  /**
   * @var PoiCategory
   */
  private $_defaultPoiCategory;

  /**
   * var geoEncode
   */
  private $_geoEncode;
  
  /**
   * @param string Path to Yaml config file
   */
  public function  __construct( PoiCategory $defaultCategory, geoEncode $geoEncode )
  {
    $this->_vendor = Doctrine::getTable( 'Vendor' )->getVendorByCityAndLanguage( 'london', 'en-GB' );
    $this->_defaultPoiCategory = $defaultCategory;
    $this->_geoEncode = $geoEncode;
  }
  
  /**
   * Runs venue & event processes
   */
  public function run( )
  {
    $this->processVenues( );
  }

  /**
   * Processes the data that has been loadedGB-en
   *
   * @param array $data
   * 
   */
  private function processVenues( )
  {
    if( !$this->getVenueData() )
    {
      throw new ImportException('No venue data set.');
    }

    if( !$this->getVenueCategoryInformationData() )
    {
      throw new ImportException('No venue category information data set.');
    }

    foreach( $this->_venueData->Venues->Venue as $venue )
    {
      $poi = new Poi( );

      $poi[ 'review_date' ] = $venue->ModifiedDate;

      $poi[ 'vendor_poi_id' ] = 1;
      $poi[ 'local_language' ] = 'en-GB';

      $poi[ 'poi_name' ] = (string) $venue->Name;
      $poi[ 'house_no' ] = $venue->BuildingNo;
      $poi[ 'street' ] = (string) $venue->Address;
      $poi[ 'city' ] = (string) $venue->City;
      $poi[ 'district' ] = '';

      $poi[ 'country' ] = 'GBR';

      $poi[ 'additional_address_details' ] = Importer::concatNonBlankStrings(', ', array(
        $venue->Address1,
        $venue->Address2,
        $venue->Address3,
        $venue->Address4
      ));

      $postCode = $venue->PostCode;
      $poi[ 'zips' ] = $postCode;

      $poi[ 'country_code' ] = (string) 'GB';
      $poi[ 'extension' ] = '';

      $this->_geoEncode->setAddress( $postCode );
      $this->_geoEncode->getGeoCode( );
      $poi[ 'latitude' ] = $this->_geoEncode->getLatitude();
      $poi[ 'longitude' ] = $this->_geoEncode->getLongitude();

      $poi[ 'email' ] = $venue->GenEmail;

      $poi[ 'url' ] = $venue->URL; //or URLForWeb?

      $poi[ 'phone' ] = $venue->Phone;

      $poi[ 'phone2' ] = '';

      $poi[ 'fax' ] = ''; //?

      $poi[ 'vendor_category' ] = '';//?

      $poi[ 'keywords' ] = '';

      $poi[ 'short_description' ] = '';//?
      $poi[ 'description' ] = '';//?

      $poi[ 'public_transport_links' ] = Importer::concatNonBlankStrings(', ', array(
        $venue->BusInfo,
        $venue->TubeInfo,
        $venue->TubeStationID,
        $venue->RailInfo
      ));

      $cinemaPriceInfo = $venue->CinemaPriceInfo;
      $musicPriceInfo = $venue->MusicPriceInfo;

      $poi[ 'price_information' ] = Importer::concatNonBlankStrings( ', ', array(
        $venue->CinemaPriceInfo,
        $venue->MusicPriceInfo
      ));

      $placeId = (string) $venue->PlaceID;

      $relatedCatInfo = $this->_venueCategoryInformationData
        ->xpath( '/Exchange/VenueSectionLinks/VenueSectionLink[PlaceID=' . $placeId . ']' );
      $relatedCatInfo = $relatedCatInfo[ 0 ];

      $poi[ 'openingtimes' ] = (string) $relatedCatInfo->TimesExport;
      $poi[ 'star_rating' ] = '';//?
      $poi[ 'rating' ] = '';//?

      $poi[ 'provider' ] = '';

      $poiCategories = new Doctrine_Collection( Doctrine::getTable( 'PoiCategory' )  );
      $poiCategories[] = $this->_defaultPoiCategory;
      $poi[ 'PoiCategories' ] = $poiCategories;

      $poi[ 'Vendor' ] = $this->_vendor;

      $poi->save( );
    }
  }

  /**
   * Processes the data that has been loaded
   *
   * @param array $data
   */
  private function processEvents( )
  {
  }

  public function setVenueCategoryInformationData( SimpleXMLElement $venueCategoryData )
  {
    $this->_venueCategoryInformationData = $venueCategoryData;
  }

  public function getVenueCategoryInformationData( )
  {
    return $this->_venueCategoryInformationData;
  }

  public function getVenueData(  )
  {
    return $this->_venueData;
  }

  public function setVenueData( SimpleXMLElement $venueData )
  {
    $this->_venueData = $venueData;
  }

  public function getEventData( )
  {
    return $this->_eventData;
  }

  public function setEventData( SimpleXMLElement $eventData )
  {
    $this->_eventData = $eventData;
  }


}
