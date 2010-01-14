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

  private $vendor = null;


  public function run()
  {
    $this->getData();
    
  }

  /**
   *
   * @param Vendor $vendor
   */
  public function __construct( $vendor )
  {
    $this->vendor = $vendor;    
  }

  /**
   *
   * @return Doctrine_Collection
   */
  public function getData( )
  {
    $data = Doctrine::getTable( 'Poi' )->findByVendorId( $this->vendor->getId() );
    return $data;
  }

  /**
   *
   * @return string XML string
   */
  public function generateXML( $data )
  {
    
    $xmlElement = new SimpleXMLElement( '<vendor-pois />' );

    //poi_vendor
    $xmlElement->addAttribute( 'vendor', $this->vendor->getName() );
    $xmlElement->addAttribute( 'modified', date( 'Y-m-d\TH:i:s' ) );

    //entry
    foreach( $data as $poi )
    {
      $entry = $xmlElement->addChild( 'entry' );
      $entry->addAttribute( 'vpid', 'vpid_' . $poi->getVendorPoiId() );
    }
    
    return $xmlElement;
  }
  
}
?>
