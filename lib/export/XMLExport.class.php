<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XMLExportclass
 *
 * @author ralph
 */
abstract class XMLExport
{

  protected $vendor = null;
  protected $modifiedTimeStamp;
  protected $destination;
  protected $model;
  

  /**
   *
   * @param Vendor $vendor
   */
  public function __construct( $vendor, $destination, $model )
  {
    $this->vendor = $vendor;
    $this->destination = $destination;
    $this->model = $model;
  }

  public function run()
  {
    $data = $this->getData();
    $xml = $this->generateXML( $data );
    $this->writeXMLToFile( $xml );
  }

  /**
   *
   * @return Doctrine_Collection
   */
  public function getData( )
  {
    $this->modifiedTimeStamp = date( 'Y-m-d\TH:i:s' );
    $data = Doctrine::getTable( $this->model )->findByVendorId( $this->vendor->getId() );
    return $data;
  }

  /**
   *
   * @return string XML string
   */
  public abstract function generateXML( $data );

  /**
   *
   * @return string Path to write file
   */
  public function getDestination()
  {
    return $this->destination;
  }

  /**
   * @param SimpleXMLElement $xml
   */
  public function writeXMLToFile( $xml )
  {
    if( file_exists( $this->destination ) )
    {
      unlink( $this->destination );
    }
    $xml->asXML( $this->destination );
  }

}
?>
