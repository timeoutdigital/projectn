<?php
/**
 * Description of singaporeImport
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 * </code>
 *
 */
class singaporeImport {

  /*
   * @var SimpleXMLElement
   */
  private $_dataXml;

  /*
   * @var Vendor
   */
  private $_vendor;


  /**
   * Construct
   *
   * @param $dataXml SimpleXMLElement
   * @param $vendorObj Vendor
   *
   */
  public function  __construct( $dataXml, $vendorObj )
  {
    $this->_dataXml = $dataXml;
    $this->_vendor = $vendorObj;

    if ( ! $this->_vendor instanceof Vendor )
      throw new Exception( 'Invalid Vendor' );
    if ( ! $this->_dataXml instanceof SimpleXMLElement )
      throw new Exception( 'Invalid SimpleXmlElement' );
  }


  /*
   * insertCategoriesPoisEvents
   */
  public function insertCategoriesPoisEvents()
  {

    
    var_export( $this->_dataXml );

    $eventsObj = $this->_dataXml->xpath( '/rss/channel/item' );

    foreach( $eventsObj as $itemObj )
    {
      $this->fetchPoiAndPoiCategory( (string) $itemObj->link );
      exit();
    }

    return true;
  }

  /*
   *fetchPoiAndPoiCategory
   *
   * 
   *
   */
  public function fetchPoiAndPoiCategory( $url )
  {



    exit();

    $curlImporterObj = new curlImporter();
    $parametersArray = array( 'event' => $eventId, 'key' => 'ffab6a24c60f562ecf705130a36c1d1e' );
    $curlImporterObj->pullXml ('http://www.timeoutsingapore.com/xmlapi/xml_detail/', '', $parametersArray );
    $xmlObj = $curlImporterObj->getXml();

    var_export( $xmlObj );


  }

}
?>
