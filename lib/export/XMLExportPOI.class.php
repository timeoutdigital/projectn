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
  public function __construct( $vendor, $destination ,$validation =true )
  {
    $xsd =  sfConfig::get( 'sf_data_dir') . DIRECTORY_SEPARATOR . 'xml_schemas'. DIRECTORY_SEPARATOR . 'poi.xsd';
    parent::__construct(  $vendor, $destination, 'Poi', $xsd , $validation);

    ExportLogger::getInstance()->initExport( 'Poi' );
  }

  protected function getData()
  {
    if( $this->validation)
    {
        $data = Doctrine::getTable( $this->model )->findAllValidByVendorId( $this->vendor->getId() );
    }else
    {
        $data = Doctrine::getTable( $this->model )->findByVendorId( $this->vendor->getId() );
    }

    $this->loadListOfMediaAvailableOnAmazon( $this->vendor['city'], 'Poi' );

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
    $rootElement->setAttribute( 'vendor', XMLExport::VENDOR_NAME );
    $rootElement->setAttribute( 'modified', $this->modifiedTimeStamp );

    try {
        // Dont Do Dupe Lat Long Lookup for Russian Cities
        $duplicateLatLongs = ( $this->vendor['language'] != 'ru' ) ?
             Doctrine_Query::create()
                 ->select("p.latitude, p.longitude, CONCAT( latitude, ', ', longitude ) as myString")
                 ->from('Poi p')
                 ->where('p.vendor_id = ?', $this->vendor['id'])
                 ->groupBy('myString')
                 ->having('count( myString ) > 1')
                 ->execute( array(), Doctrine_Core::HYDRATE_ARRAY )
        : array();
    }
    catch( Exception $e )
    {
        ExportLogger::getInstance()->addError( 'FATAL Exception returned while finding duplicate lat/longs in export.', 'Poi' );
        echo "FATAL Exception returned while finding duplicate lat/longs in export." . PHP_EOL;
        echo $e->getMessage();
        die;
    }

    //entry
    foreach( $data as $poi )
    {
      //Skip Export for Pois with lat/long outside vendor boundaries.
      $bounds_array = explode( ";", $this->vendor['geo_boundries'] );

      if( $poi['latitude'] < $bounds_array[0] || $poi['latitude'] > $bounds_array[2] ||
          $poi['longitude'] < $bounds_array[1] || $poi['longitude'] > $bounds_array[3] )
      {
          if( $this->validation == true )
          {
            ExportLogger::getInstance()->addError( 'Skip Export for Pois Ouside Vendor Boundaries', 'Poi', $poi[ 'id' ] );
            continue;
          }
      }

     //skip the BEIJING pois if the status is not 10
     if( $this->validation == true && $poi[ 'vendor_id' ] == 22 ) //beijing vendor_id
     {
         foreach ($poi[ 'PoiMeta' ] as $meta)
         {
            if(  $meta[ 'lookup' ] == 'status'  && $meta[ 'value' ] != 10 )
            {
                ExportLogger::getInstance()->addError( 'Skip Export for Beijing Pois status is not LIVE (10), ' . $meta[ 'value' ] . ' is given', 'Poi', $poi[ 'id' ] );
                continue 2;
            }
         }
     }

      //Skip Export for Pois with Dupe Lat/Longs
      foreach( $duplicateLatLongs as $dupe )
      {
          if( $poi['latitude'] == $dupe['latitude'] && $poi['longitude'] == $dupe['longitude'] )
          {
              if( $this->validation == true )
              {
                ExportLogger::getInstance()->addError( 'Skip Export for Pois with Dupe Lat/Longs', 'Poi', $poi[ 'id' ] );
                continue 2;
              }
          }
      }

      if( count( $poi[ 'VendorPoiCategory' ] ) == 0 )
      {
          if( $this->validation == true )
          {
            ExportLogger::getInstance()->addError( 'Vendor Poi Category not found', 'Poi', $poi[ 'id' ] );
            continue;
          }
      }

      // check the city name, if it has a number in it continue because
      // city name could be set postcode or another wrong information
      preg_match( '(\d)', $poi['city'], $numbersInCityName );
      if( count( $numbersInCityName ) != 0  )
      {
          if( $this->validation == true )
          {
            ExportLogger::getInstance()->addError( 'Skip Export for Pois with number in city name', 'Poi', $poi[ 'id' ] );
            continue;
          }
      }

      if( stringTransform::mb_trim ( $poi['street'] ) == '' )
      {
          if( $this->validation == true )
          {
            ExportLogger::getInstance()->addError( 'Skip Export for Pois because of empty street', 'Poi', $poi[ 'id' ] );
            continue;
          }
      }

      if( stringTransform::mb_trim ( $poi['city'] ) == '' )
      {
          if( $this->validation == true )
          {
            ExportLogger::getInstance()->addError( 'Skip Export for Pois because of empty city', 'Poi', $poi[ 'id' ] );
            continue;
          }
      }

      $entryElement = $this->appendRequiredElement( $rootElement, 'entry' );
      $entryElement->setAttribute( 'vpid', $this->generateUID( $poi['id'] ) );
      $langArray = explode('-',$this->vendor['language']);
      $entryElement->setAttribute( 'lang', $langArray[0] );
      $entryElement->setAttribute( 'modified', $this->modifiedTimeStamp );

      $geoPositionElement = $entryElement->appendChild( new DOMElement( 'geo-position' ) );
      $this->appendRequiredElement( $geoPositionElement, 'longitude', $poi['longitude'] );
      $this->appendRequiredElement( $geoPositionElement, 'latitude', $poi['latitude'] );

      $this->appendRequiredElement( $entryElement, 'name', $poi['poi_name'], XMLExport::USE_CDATA );

      //Categories @todo this is dirty. Refactor asap!
      $cats = $this->getPoiCategories( $poi );
      foreach( $cats as $category )
      {
        $this->appendRequiredElement( $entryElement, 'category', $category['name'], XMLExport::USE_CDATA);
      }

      if ( count( $cats ) < 1 )
      {
        $this->appendRequiredElement( $entryElement, 'category', 'theatre-music-culture', XMLExport::USE_CDATA);
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
      $versionElement->setAttribute( 'lang', $langArray[0] );

      $this->appendRequiredElement($versionElement, 'name', $poi['poi_name'], XMLExport::USE_CDATA);

      //$this->appendNonRequiredElement($versionElement, 'alternative-name', $poi['poi_name'], XMLExport::USE_CDATA);

      $addressElement = $this->appendRequiredElement($versionElement, 'address');

      $this->appendRequiredElement(    $addressElement, 'street',   $poi['street'],   XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'houseno',  $poi['house_no'], XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'zip',      $poi['zips'],     XMLExport::USE_CDATA);
      $this->appendRequiredElement(    $addressElement, 'city',     $poi['city'],     XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $addressElement, 'district', $poi['district'], XMLExport::USE_CDATA);
      $this->appendRequiredElement(    $addressElement, 'country',  $poi['country'] );

      //content
      $contentElement = $this->appendRequiredElement( $versionElement, 'content' );

      foreach( $poi[ 'VendorPoiCategory' ] as $vendorPoiCategory )
      {
          $vendorPoiCategoryElement = $this->appendNonRequiredElement( $contentElement, 'vendor-category', $vendorPoiCategory['name'], XMLExport::USE_CDATA);
      }

      $cleanShortDescription = $this->cleanHtml($poi['short_description']);
      $this->appendNonRequiredElement( $contentElement, 'short-description', $cleanShortDescription, XMLExport::USE_CDATA);

      $cleanDescription = $this->cleanHtml($poi['description']);
      $this->appendNonRequiredElement( $contentElement, 'description', $cleanDescription, XMLExport::USE_CDATA);

      $this->appendNonRequiredElement( $contentElement, 'public-transport', $poi['public_transport_links'], XMLExport::USE_CDATA);
      $this->appendNonRequiredElement( $contentElement, 'openingtimes', $poi['openingtimes'], XMLExport::USE_CDATA);

      //event/version/media

      // Finds Largest Media File that Exists on s3.
      foreach( $this->filterByExportPolicyAndVerifyMedia( $poi[ 'PoiMedia' ] ) as $medium )
      {
        $mediaElement = $this->appendNonRequiredElement( $contentElement, 'media', $medium->getAwsUrl(), XMLExport::USE_CDATA );

        if ( $mediaElement instanceof DOMElement )
        {
          $mediaElement->setAttribute( 'mime-type', $medium[ 'mime_type' ] );
        }
        //$medium->free();
      }

      foreach( $poi[ 'PoiProperty' ] as $property )
      {
        if( isset( $property['lookup'] ) )
        {
          if( $property['lookup'] == "Critics_choice" && strtolower( $property['value'] ) != "y" )
          {
              break;
          }
          $propertyElement = $this->appendNonRequiredElement( $contentElement, 'property', $property['value'], XMLExport::USE_CDATA);
          if( $propertyElement instanceof DOMElement )
          {
            $propertyElement->setAttribute( 'key', $property['lookup'] );
          }
        }
      }

      // UI Category Exports.
      $avoidDuplicateUiCategories = array();
      foreach( $poi['VendorPoiCategory'] as $vendorCat )
        foreach( $vendorCat['UiCategory'] as $uiCat )
           if( isset( $uiCat['name'] ) )
            if( !in_array( (string) $uiCat['name'], $avoidDuplicateUiCategories ) )
            {
                $propertyElement = $this->appendNonRequiredElement( $contentElement, 'property', (string) $uiCat['name'], XMLExport::USE_CDATA );
                $propertyElement->setAttribute( 'key', 'UI_CATEGORY' );
                $avoidDuplicateUiCategories[] = (string) $uiCat['name'];
            }

      ExportLogger::getInstance()->addExport( 'Poi' );

    }
//    ExportLogger::getInstance()->showErrors();
    return $domDocument;
  }

  private function getPoiCategories( &$poi )
  {
    $poiWithPoiCategories = Doctrine::getTable( 'Poi' )
      ->createQuery( 'p' )
      ->select( 'p.id, vpc.*, pc.name' )
      ->leftJoin( 'p.VendorPoiCategory vpc' )
      ->leftJoin( 'vpc.PoiCategory pc' )
      ->addWhere( 'p.id = ?', $poi[ 'id' ])
      ->fetchOne( array(), Doctrine::HYDRATE_ARRAY )
    ;

    $ret = array();
    foreach( $poiWithPoiCategories[ 'VendorPoiCategory' ] as $vendorCategory )
    {
      foreach( $vendorCategory[ 'PoiCategory' ] as $poiCategory )
      {
        $ret[] = array( 'name' => $poiCategory[ 'name' ] );
      }
    }

    $ret = array_unique( $ret );

    return $ret;
  }

}
?>
