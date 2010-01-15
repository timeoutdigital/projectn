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
      $address->addChild( 'zip', $poi->getZips() );
      $address->addChild( 'city', htmlspecialchars($poi->getCity() ) );
      $address->addChild( 'district', htmlspecialchars($poi->getDistrict() ) );
      $address->addChild( 'country', htmlspecialchars($poi->getCountry() ) );

    }

    return $xmlElement;
  }
  
}
?>
