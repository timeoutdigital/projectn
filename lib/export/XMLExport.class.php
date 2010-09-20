<?php
/**
 *
 * @package projectn
 * @subpackage export.lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */

abstract class XMLExport
{
  const USE_CDATA = true;
  const VENDOR_NAME = 'timeout';

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
   * @var string
   */
  protected $xsdPath;

  /**
   * @var logExport
   */
  protected $logExport;

  /**
   * @var validation
   */
  protected $validation;

  /**
   * @var amazonResources
   */
  protected $amazonResources = array();

  /**
   * @var amazon s3cmd access class, Marked for Mocking Purposes
   */
  protected $s3cmdClassName = 's3cmd';

  /**
   * @param Vendor $vendor
   * @param string $destination Path to file to write export to
   * @param Doctrine_Model $model The model to be exported
   */
  public function __construct( $vendor, $destination, $model, $xsdFilename=null, $validation = true )
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

    if( !is_null( $xsdFilename ) )
    {
      $this->xsdPath = $xsdFilename;
    }
    $this->validation = $validation;
  }

  /**
   * run the export
   */
  public function run()
  {
    //$this->logExport = new logExport( $this->vendor[ 'id' ], $this->model );
    $this->modifiedTimeStamp = date( 'Y-m-d\TH:i:s' );
    $data = $this->getData();
    $xml = $this->mapDataToDOMDocument( $data, $this->getDomDocument() );
    $this->writeXMLToFile( $xml );
    $this->validateAgainst( $xml );
   // $this->logExport->save();
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
   * check the export against its XSD
   * @todo should enforce an XSD
   */
  public function validateAgainst( DOMDocument $xml )
  {
    if( !is_null( $this->xsdPath ) )
    {
        libxml_use_internal_errors( true );
        $xml->schemaValidate( $this->xsdPath );

        foreach ( libxml_get_errors() as $error )
        {
            $error_message = (string) date( "Y-m-d H:i:s" ) . " -- libxml XSD validation for '".$this->xsdPath."' -- ";

            switch ($error->level)
            {
                case LIBXML_ERR_WARNING:
                    $error_message .= "Warning Code: '$error->code' -- ";
                    break;
                case LIBXML_ERR_ERROR:
                    $error_message .= "Error Code: '$error->code' -- ";
                    break;
                case LIBXML_ERR_FATAL:
                    $error_message .= "Fatal Error Code: '$error->code' -- ";
                    break;
            }
            $error_message .= "Message: '" . trim( $error->message ) . "' -- ";
            if ($error->file) $error_message .= " File: '$error->file' -- ";
            if ($error->line) $error_message .= " Line: '$error->line' -- ";

            echo $error_message . PHP_EOL;
        }

        libxml_clear_errors();
        libxml_use_internal_errors( false );
    }
  }

  /**
   * Retrieve data from database for the Model filtered by Vendor
   * @return Doctrine_Collection
   */
  protected function getData()
  {
    $data = Doctrine::getTable( $this->model )->findByVendorId( $this->vendor->getId() );
    $this->loadListOfMediaAvailableOnAmazon( $this->vendor['city'], $this->model );
    
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

    $domDocument->formatOutput = true;
    $domDocument->save($this->destination);
  }

  /**
   * @param string $html
   * @return string
   */
  protected function cleanHtml( $html )
  {
    //em to i tags
    $html = str_replace('<em>', '<i>', $html);
    $html = str_replace('</em>', '</i>', $html);

    //strong to b tags
    $html = str_replace('<strong>', '<b>', $html);
    $html = str_replace('</strong>', '</b>', $html);

    //remove consecutively repeated br tags
    $html = preg_replace(':(<br\s*/>)+:', '<br />', $html);

    $html = html_entity_decode( $html, ENT_NOQUOTES, 'UTF-8' );
    $html = html_entity_decode( $html, ENT_NOQUOTES, 'UTF-8' );

    return stringTransform::purifyHTML( $html );
  }

  /**
   * @param integer $recordId
   *
   * @todo consider putting this in its own class
   */
  protected function generateUID( $recordId )
  {
    return $this->vendor['airport_code'] . str_pad( $recordId, 30, 0, STR_PAD_LEFT );
  }

  /**
   * Pull a list of available images for this city & record type off amazon via the API.
   *
   * @return array of Media File Names, such as 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA.jpg'
   */
  protected function loadListOfMediaAvailableOnAmazon( $vendorCity, $recordClass )
  {
      $s3cmd = new $this->s3cmdClassName();
      $this->amazonResources = $s3cmd->getListOfMediaAvailableOnAmazon( $vendorCity, $recordClass );
      unset( $s3cmd ); // Free the class
  }


  /**
   * Filter a list of media records down to just the biggest valid image.
   */
  protected function filterByExportPolicyAndVerifyMedia( $mediaRecords )
  {
    if( empty( $mediaRecords ) ) return $mediaRecords;

    // Export Policy: Only Export the Largest Image.
    $validRecords = array();

    foreach( $mediaRecords as $media )
    {
        // Image MUST be valid (not error or new).
        if( isset( $media['status'] ) && $media['status'] === 'valid' )
        {
            // Image MUST exist on Amazon.
            if( in_array( "{$media['ident']}.jpg", $this->amazonResources ) )
            {
                // Select the largest Image.
                if( empty( $validRecords ) || (int) $media['content_length'] > (int) $validRecords[0]['content_length'] )
                {
                    $validRecords[0] = $media;
                }
            }
        }
    }

    return $validRecords;
  }

  /**
   * Set the S3CMD class name for mockup...
   * @param string $className
   */
  public function setS3cmdClassName( $className )
  {
      if( !class_exists( $className ) )
      {
          throw new Exception( $className . ' do not exists.' );
      }
      
      $this->s3cmdClassName = $className;
  }
}