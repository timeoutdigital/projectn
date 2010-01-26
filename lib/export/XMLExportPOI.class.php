<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XMLExportPOIclass
 *
 * @author ralph
 */
class XMLExportPOI extends XMLExport
{
  
 /**
   *
   * @param Vendor $vendor
   */
  public function __construct( $vendor, $destination )
  {
    parent::__construct(  $vendor, $destination, 'Poi' );
  }

  protected function getData()
  {
    $data = Doctrine::getTable( $this->model )->findByVendorId( $this->vendor->getId() );
    return $data;
  }
  /**
   * @todo PoiCateory needs to be one-to-many not one-to-one
   * @param Poi $data
   * @param DOMDocument $domDocument
   * 
   * @return string XML string
   */
  public function mapDataToDOMDocument( $data, $domDocument )
  {    
    $rootElement = $domDocument->appendChild( new DOMElement('vendor-pois') );

    //poi_vendor
    $rootElement->setAttribute( 'vendor', $this->vendor->getName() );
    $rootElement->setAttribute( 'modified', $this->modifiedTimeStamp );

    //entry
    foreach( $data as $poi )
    {
      $entryElement = $rootElement->appendChild( new DOMElement( 'entry' ) );
      $entryElement->setAttribute( 'vpid', 'vpid_' . $poi->getVendorPoiId() );
      $entryElement->setAttribute( 'lang', $poi->getLocalLanguage() );
      $entryElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      $geoPositionElement = $entryElement->appendChild( new DOMElement( 'geo-position' ) );
      $geoPositionElement->appendChild( new DOMElement( 'longitude', $poi->getLongitude() ) );
      $geoPositionElement->appendChild( new DOMElement( 'latitude', $poi->getLatitude() ) );

      $nameElement = $entryElement->appendChild( new DOMElement( 'name' ) );
      $nameElement->appendChild( $domDocument->createCDATASection( $poi->getPoiName() ) );

      foreach( $poi[ 'PoiCategories' ] as $category )
      {
        $categoryElement = $entryElement->appendChild( new DOMElement( 'category' ) );
        $categoryElement->appendChild( $domDocument->createCDATASection( $category['name'] ) );
      }
      
      $addressElement = $entryElement->appendChild( new DOMElement( 'address' ) );

      $streetElement = $addressElement->appendChild( new DOMElement( 'street' ) );
      $streetElement->appendChild( $domDocument->createCDATASection( $poi['street'] ) );
      
      $houseElement = $addressElement->appendChild( new DOMElement( 'houseno' ) );
      $houseElement->appendChild( $domDocument->createCDATASection( $poi['house_no'] ) );

      $zipsElement = $addressElement->appendChild( new DOMElement( 'zip' ) );
      $zipsElement->appendChild( $domDocument->createCDATASection( $poi['zips'] ) );

      $cityElement = $addressElement->appendChild( new DOMElement( 'city' ) );
      $cityElement->appendChild( $domDocument->createCDATASection( $poi['city'] ) );

      $districtElement = $addressElement->appendChild( new DOMElement( 'district' ) );
      $districtElement->appendChild( $domDocument->createCDATASection( $poi['district'] ) );

      $countryElement = $addressElement->appendChild( new DOMElement( 'country' ) );
      $countryElement->appendChild( $domDocument->createCDATASection( $poi['country'] ) );

      $contactElement = $entryElement->appendChild( new DOMElement( 'contact' ) );
      
      $emailElement = $contactElement->appendChild( new DOMElement( 'email' ) );
      $emailElement->appendChild( $domDocument->createCDATASection( $poi['email'] ) );

      $urlElement = $contactElement->appendChild( new DOMElement( 'url' ) );
      $urlElement->appendChild( $domDocument->createCDATASection( $poi['url'] ) );

      $contactElement->appendChild( new DOMElement( 'phone',  $poi->getPhone() ) );
      $contactElement->appendChild( new DOMElement( 'phone2', $poi->getPhone2() ) );
      $contactElement->appendChild( new DOMElement( 'fax',    $poi->getFax() ) );

      $contactElement = $entryElement->appendChild( new DOMElement( 'content' ) );

      $shortDescriptionElement = $contactElement->appendChild( new DOMElement( 'short-description' ) );
      $shortDescriptionElement->appendChild( $domDocument->createCDATASection( $poi['short_description'] ) );
      
      $descriptionElement = $contactElement->appendChild( new DOMElement( 'description' ) );
      $descriptionElement->appendChild( $domDocument->createCDATASection( $poi['description'] ) );

      $publicTransportElement = $contactElement->appendChild( new DOMElement( 'public-transport' ) );
      $publicTransportElement->appendChild( $domDocument->createCDATASection( $poi['public_transport_links'] ) );

      $openingtimesElement = $contactElement->appendChild( new DOMElement( 'opening-times' ) );
      $openingtimesElement->appendChild( $domDocument->createCDATASection( $poi['openingtimes'] ) );

      foreach( $poi[ 'PoiProperty' ] as $property )
      {
        $propertyElement = $entryElement->appendChild( new DOMElement( 'property' ) );
        $propertyElement->appendChild( $domDocument->createCDATASection( $property['value'] ) );
        $propertyElement->setAttribute( 'key', $property['lookup'] );
      }
    }

    return $domDocument;
  }
  
}
?>
