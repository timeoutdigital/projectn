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
  const USE_CDATA = true;

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
   * @var DOMDocument
   */
  protected $domDocument;

  /**
   * @param Vendor $vendor
   * @param string $destination Path to file to write export to
   * @param Doctrine_Model $model The model to be exported
   */
  public function __construct( $vendor, $destination, $model )
  {
    if( !( $vendor instanceof Vendor ) )
    {
      throw new ExportException( 'Vendor provided is not an instance of Vendor. Got: ' . var_export($vendor, true) );
    }
    $this->vendor = $vendor;

    if( !is_writable( dirname( $destination ) ) )
    {
      throw new ExportException( 'Destination is not writeable: ' . $destination );
    }
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
    $xml = $this->mapDataToDOMDocument( $data, $this->getDomDocument() );
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
    $data = Doctrine::getTable( $this->model )->findByVendorId( $this->vendor->getId() );
    return $data;
  }

  /**
   * Returns the current DOMDocument. If none available, a new instance is
   * created.
   *
   * @return DOMDocument
   */
  protected function getDomDocument()
  {
    if( !$this->domDocument )
    {
      $this->domDocument = new DOMDocument('1.0', 'UTF-8');
    }
    return $this->domDocument;
  }

  /**
   *
   * @return DOMDocument
   */
  abstract protected function mapDataToDOMDocument( $data, $domDocument );

  /**
   * Append and return an element named $elementName to $node if $element is not
   * empty
   *
   * @params DOMNode $node
   * @params string $elementName
   * @params string $elementContent
   *
   * @return DOMElement
   */
  public function appendNonRequiredElement( DOMNode $node, $elementName, $elementContent=null, $useCDATA = false )
  {
    if( !empty( $elementContent ) )
    {
      return $this->appendRequiredElement($node, $elementName, $elementContent, $useCDATA);
    }
    return null;
  }

  /**
   *
   */
  public function appendRequiredElement( DOMNode $node, $elementName, $elementContent=null, $useCDATA = false )
  {
    if( $useCDATA )
    {
      return $this->appendCDATAElement( $node, $elementName, $elementContent );
    }
    else
    {
      return $node->appendChild( new DOMElement( $elementName, $elementContent ) );
    }
    return null;
  }
  
  /**
   *
   */
  protected function appendCDATAElement( DOMNode $node, $elementName, $elementContent=null )
  {
    $element = $node->appendChild( new DOMElement( $elementName ) );
    $element->appendChild( $node->ownerDocument->createCDATASection( $elementContent ) );
    return $element;
  }

  /**
   * @param DOMDocument $domDocument
   */
  protected function writeXMLToFile( DOMDocument $domDocument )
  {
    if( file_exists( $this->destination ) )
    {
      unlink( $this->destination );
    }

    $domDocument->save($this->destination);
  }

}
?>
