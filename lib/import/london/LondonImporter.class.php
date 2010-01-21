<?php

/**
 * Imports data from London database
 *
 * @package projectn.lib.import.london
 *
 * @author clarence <clarencelee@timeout.com>
 */
class LondonImporter
{

  private $_venueData;

  private $_eventData;

  private $_vendor;

  
  /**
   * @param string Path to Yaml config file
   */
  public function  __construct( )
  {
    $this->_vendor = Doctrine::getTable( 'Vendor' )->getVendorByCityAndLanguage( 'london', 'en-GB' );
  }

  
  /**
   * Runs venue & event processes
   *
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
   * @todo get real latitude longitude
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
   * 
   */
  private function processVenues( )
  {
    $poi = new Poi( );

    $poi[ 'review_date' ] = $this->_venueData->Venues->Venue[ 0 ]->ModifiedDate;

    $poi[ 'vendor_poi_id' ] = 1;
    $poi[ 'local_language' ] = 'en-GB';

    $poi[ 'poi_name' ] = (string) $this->_venueData->Venues->Venue[ 0 ]->Name;
    $poi[ 'house_no' ] = $this->_venueData->Venues->Venue[ 0 ]->BuildingNo;
    $poi[ 'street' ] = (string) $this->_venueData->Venues->Venue[ 0 ]->Address;
    $poi[ 'city' ] = (string) $this->_venueData->Venues->Venue[ 0 ]->City;
    $poi[ 'district' ] = '';

    $poi[ 'country' ] = 'GBR';

    $poi[ 'additional_address_details' ] = Importer::concatNonBlankStrings(', ', array(
      $this->_venueData->Venues->Venue[ 0 ]->Address1,
      $this->_venueData->Venues->Venue[ 0 ]->Address2,
      $this->_venueData->Venues->Venue[ 0 ]->Address3,
      $this->_venueData->Venues->Venue[ 0 ]->Address4 
    ));

    $poi[ 'zips' ] = $this->_venueData->Venues->Venue[ 0 ]->PostCode;

    $poi[ 'country_code' ] = (string) 'GB';
    $poi[ 'extension' ] = '';

    $poi[ 'longitude' ] = (string) '-0.1000000';
    $poi[ 'latitude' ] = (string) '51.000000';

    $poi[ 'email' ] = $this->_venueData->Venues->Venue[ 0 ]->GenEmail;
    $poi[ 'url' ] = $this->_venueData->Venues->Venue[ 0 ]->URL; //or URLForWeb?

    $poi[ 'phone' ] = $this->_venueData->Venues->Venue[ 0 ]->Phone;

    $poi[ 'phone2' ] = '';

    $poi[ 'fax' ] = ''; //?

    $poi[ 'vendor_category' ] = '';//?

    $poi[ 'keywords' ] = '';

    $poi[ 'short_description' ] = '';//?
    $poi[ 'description' ] = '';//?

    $poi[ 'public_transport_links' ] = Importer::concatNonBlankStrings(', ', array(
      $this->_venueData->Venues->Venue[ 0 ]->BusInfo,
      $this->_venueData->Venues->Venue[ 0 ]->TubeInfo,
      $this->_venueData->Venues->Venue[ 0 ]->TubeStationID,
      $this->_venueData->Venues->Venue[ 0 ]->RailInfo
    ));
      

    $cinemaPriceInfo = $this->_venueData->Venues->Venue[ 0 ]->CinemaPriceInfo;
    $musicPriceInfo = $this->_venueData->Venues->Venue[ 0 ]->MusicPriceInfo;

    $poi[ 'price_information' ] = Importer::concatNonBlankStrings(', ', array(
      $this->_venueData->Venues->Venue[ 0 ]->CinemaPriceInfo,
      $this->_venueData->Venues->Venue[ 0 ]->MusicPriceInfo
    ));

    $poi[ 'openingtimes' ] = '';//?
    $poi[ 'star_rating' ] = '';//?
    $poi[ 'rating' ] = '';//?

    $poi[ 'provider' ] = '';

    $poiCategory = new PoiCategory( );
    $poiCategory[ 'name' ] = 'Non-Category Category';

    $poi[ 'PoiCategory' ] = $poiCategory;

    $poi[ 'Vendor' ] = $this->_vendor;

    $poi->save( );
  }


  /**
   * Processes the data that has been loaded
   *
   * @param array $data
   */
  private function processEvents( )
  {
    
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
