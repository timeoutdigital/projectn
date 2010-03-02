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
class XMLExportPOI extends XMLExport
{
  
 /**
   *
   * @param Vendor $vendor
   */
  public function __construct( $vendor, $destination )
  {
    parent::__construct(  $vendor, $destination, 'Poi', 'poi.xsd' );
  }

  protected function getData()
  {
    $data = Doctrine::getTable( $this->model )->findAllValidByVendorId( $this->vendor->getId() );
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
      $entryElement = $this->appendRequiredElement( $rootElement, 'entry' );
      $entryElement->setAttribute( 'vpid', 'vpid_' . $poi->getVendorPoiId() );
      $entryElement->setAttribute( 'lang', $this->vendor['language'] );
      $entryElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      //@todo if statement is not a proper fix. it should be fixed properly at import stage asap
      if( $poi['longitude'] > 0 && $poi['longitude'] < 0 && $poi['latitude'] > 0 && $poi['latitude'] < 0  )
      {
        $geoPositionElement = $entryElement->appendChild( new DOMElement( 'geo-position' ) );
        $this->appendRequiredElement( $geoPositionElement, 'longitude', $poi['longitude'] );
        $this->appendRequiredElement( $geoPositionElement, 'latitude', $poi['latitude'] );
      }

      $this->appendRequiredElement( $entryElement, 'name', $poi['poi_name'], XMLExport::USE_CDATA );

      foreach( $poi[ 'PoiCategories' ] as $category )
      {
        $this->appendRequiredElement( $entryElement, 'category', $category['name'], XMLExport::USE_CDATA);
      }

      
      // @todo this block adds a default others category. as it is not allowed as of the schema this will
      // need to be removed as soon as the category (mapping) is properly in place
      if ( count( $poi[ 'PoiCategories' ]) < 1 )
      {
        $this->appendRequiredElement( $entryElement, 'category', 'others', XMLExport::USE_CDATA);
      }
      
      
      $addressElement = $entryElement->appendChild( new DOMElement( 'address' ) );

      $this->appendRequiredElement(    $addressElement, 'street',   $poi['street'],   XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'houseno',  $poi['house_no'], XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'zip',      $poi['zips'], XMLExport::USE_CDATA );
      $this->appendRequiredElement(    $addressElement, 'city',     $poi['city'], XMLExport::USE_CDATA );
      $this->appendNonRequiredElement( $addressElement, 'district', $poi['district'], XMLExport::USE_CDATA );
      $this->appendRequiredElement(    $addressElement, 'country',  $poi['country'] );

      $contactElement = $this->appendRequiredElement( $entryElement, 'contact' );

      $this->appendNonRequiredElement( $contactElement, 'phone',  $poi['phone'] );
      $this->appendNonRequiredElement( $contactElement, 'fax',    $poi['fax'] );
      $this->appendNonRequiredElement( $contactElement, 'phone2', $poi['phone2'] );
      
      $this->appendNonRequiredElement( $contactElement, 'email', $poi['email'], XMLExport::USE_CDATA );

      $this->appendNonRequiredElement( $contactElement, 'url', $poi['url']);

      //version
      $versionElement = $this->appendRequiredElement( $entryElement , 'version');
      $versionElement->setAttribute( 'lang', 'en' );

      $this->appendRequiredElement($versionElement, 'name', $poi['poi_name'], XMLExport::USE_CDATA);

      $this->appendNonRequiredElement($versionElement, 'alternative-name', $poi['poi_name'], XMLExport::USE_CDATA);
      
      $addressElement = $this->appendRequiredElement($versionElement, 'address');

      $this->appendRequiredElement(    $addressElement, 'street',   $poi['street'],   XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'houseno',  $poi['house_no'], XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'zip',      $poi['zips'],     XMLExport::USE_CDATA);
      $this->appendRequiredElement(    $addressElement, 'city',     $poi['city'],     XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'district', $poi['district'], XMLExport::USE_CDATA);
      $this->appendRequiredElement(    $addressElement, 'country',  $poi['country'] );

      //content
      $contentElement = $this->appendRequiredElement( $versionElement, 'content' );

      $this->appendNonRequiredElement( $contentElement, 'short-description', $poi['short_description'], XMLExport::USE_CDATA);
      
      $cleanDescription = $poi['description'];
      $this->appendNonRequiredElement( $contentElement, 'description', $cleanDescription, XMLExport::USE_CDATA);
      
      $this->appendNonRequiredElement( $contentElement, 'public-transport', $poi['public_transport_links'], XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $contentElement, 'openingtimes', $poi['openingtimes'], XMLExport::USE_CDATA);

      //event/version/media
      /*foreach( $poi[ 'PoiMedia' ] as $medium )
      {
        $mediaElement = $this->appendNonRequiredElement($contentElement, 'media', $medium['url'], XMLExport::USE_CDATA);
        if ( $mediaElement instanceof DOMElement )
        {
          $mediaElement->setAttribute( 'mime-type', $medium[ 'mime_type' ] );
        }
        //$medium->free();
      }*/

      foreach( $poi[ 'PoiProperty' ] as $property )
      {
        $propertyElement = $this->appendNonRequiredElement( $contentElement, 'property', $property['value'], XMLExport::USE_CDATA);
        if( $propertyElement )
          $propertyElement->setAttribute( 'key', $property['lookup'] );
      }
    }

    return $domDocument;
  }
  
}
?>
