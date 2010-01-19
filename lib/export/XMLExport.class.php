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

  /**
   *
   * @var Doctrin_Model The Vendor providing the data
   */
  protected $vendor = null;

  /**
   * @var date The time import started
   */
  protected $modifiedTimeStamp;

  /**
   * @var string Path to the file to write to
   */
  protected $destination;

  /**
   * @var Doctrine_Model The model to export
   */
  protected $model;

  /**
   * @param Vendor $vendor
   * @param string $destination Path to file to write export to
   * @param Doctrine_Model $model The model to be exported
   */
  public function __construct( $vendor, $destination, $model )
  {
    $this->vendor = $vendor;
    $this->destination = $destination;
    $this->model = $model;
  }

  /**
   * run the export
   */
  public function run()
  {
    $this->modifiedTimeStamp = date( 'Y-m-d\TH:i:s' );
    $data = $this->getData();
    $xml = $this->generateXML( $data );
    $this->writeXMLToFile( $xml );
  }

  /**
   * returns the start time of the import
   * @return data
   */
  public function getStartTime()
  {
    return $this->modifiedTimeStamp;
  }

  /**
   * Retrieve data from database for the Model filtered by Vendor
   * @return Doctrine_Collection
   */
  protected function getData()
  {
    $data = Doctrine::getTable( $this->model )->findOneByVendorId( $this->vendor->getId() );
    return $data;
  }

  /**
   *
   * @return string XML string
   */
  abstract protected function generateXML( $data );

  /**
   * @param SimpleXMLElement $xml
   */
  protected function writeXMLToFile( $xml )
  {
    if( file_exists( $this->destination ) )
    {
      unlink( $this->destination );
    }
    $xml->asXML( $this->destination );
  }

}
?>
