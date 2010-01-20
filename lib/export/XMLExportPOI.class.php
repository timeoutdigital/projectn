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
   * 
   * @return string XML string
   */
  public function generateXML( $data )
  {
    
    $xmlElement = new SimpleXMLElement( '<vendor-pois />' );

    //poi_vendor
    $xmlElement->addAttribute( 'vendor', $this->vendor->getName() );
    $xmlElement->addAttribute( 'modified', $this->modifiedTimeStamp );

    //entry
    foreach( $data as $poi )
    {
      $entry = $xmlElement->addChild( 'entry' );
      $entry->addAttribute( 'vpid', 'vpid_' . $poi->getVendorPoiId() );
      $entry->addAttribute( 'lang', $poi->getLocalLanguage() );
      $entry->addAttribute( 'modified', $this->modifiedTimeStamp );

      $geoPosition = $entry->addChild( 'geo-position' );
      $geoPosition->addChild( 'longitude', $poi->getLongitude() );
      $geoPosition->addChild( 'latitude', $poi->getLatitude() );

      $entry->addChild( 'name', htmlspecialchars( $poi->getPoiName() ) );

      $entry->addChild( 'category', $poi->getPoiCategory()->getName() );

      $address = $entry->addChild( 'address' );
      $address->addChild( 'street', htmlspecialchars( $poi->getStreet() ) );
      $address->addChild( 'houseno',htmlspecialchars( $poi->getHouseNo() ) );
      $address->addChild( 'zip', htmlspecialchars( $poi->getZips() ) );
      $address->addChild( 'city', htmlspecialchars($poi->getCity() ) );
      $address->addChild( 'district', htmlspecialchars($poi->getDistrict() ) );
      $address->addChild( 'country', htmlspecialchars($poi->getCountry() ) );

      $contact = $entry->addChild( 'contact' );
      $contact->addChild( 'email', htmlspecialchars( $poi->getEmail() ) );
      $contact->addChild( 'url',htmlspecialchars( $poi->getUrl() ) );
      $contact->addChild( 'phone', htmlspecialchars( $poi->getPhone() ) );
      $contact->addChild( 'phone2', htmlspecialchars($poi->getPhone2() ) );
      $contact->addChild( 'fax', htmlspecialchars($poi->getFax() ) );

      $contact = $entry->addChild( 'content' );
      $contact->addChild( 'short-description', htmlspecialchars( $poi->getShortDescription() ) );
      $contact->addChild( 'description',htmlspecialchars( $poi->getDescription() ) );
      $contact->addChild( 'public-transport', $poi->getPublicTransportLinks() );
      $contact->addChild( 'opening-times', htmlspecialchars($poi->getOpeningTimes() ) );
    }

    return $xmlElement;
  }
  
}
?>
