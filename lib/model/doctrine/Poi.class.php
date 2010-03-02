<?php

/**
 * Poi
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Poi extends BasePoi
{
  /**
   *
   */
  private $geoEncodeLookUpString;

  /**
   *
   */
  private $geoEncodeByPass = false;


  public function setGeoEncodeLookUpString( $geoEncodeLookUpString )
  {
    $this->geoEncodeLookUpString = $geoEncodeLookUpString;
  }

  public function setGeoEncodeByPass( $geoEncodeByPass = false )
  {
    $this->geoEncodeByPass = $geoEncodeByPass;
  }
  
  public function addProperty( $lookup, $value )
  {
    if( $this->exists() )
    {
      foreach( $this['PoiProperty'] as $property )
      {
        $lookupIsSame = ( $lookup == $property[ 'lookup' ] );
        $valueIsSame  = ( $value  == $property[ 'value' ]  );

        if( $lookupIsSame && $valueIsSame )
        {
          return;
        }
      }
    }
    $poiPropertyObj = new PoiProperty();
    $poiPropertyObj[ 'lookup' ] = (string) $lookup;
    $poiPropertyObj[ 'value' ] = (string) $value;

    $this[ 'PoiProperty' ][] = $poiPropertyObj;
  }


  public function addVendorCategory( $name, $vendorId )
  {
    if ( is_array( $name ) )
    {
      $name = implode( ' | ', $name );
    }

    $vendorPoiCategoryObj = Doctrine::getTable( 'VendorPoiCategory' )->findOneByNameAndVendorId( $name, $vendorId );

    if ( $vendorPoiCategoryObj === false )
    {
      $vendorPoiCategoryObj = new VendorPoiCategory();
      $vendorPoiCategoryObj[ 'name' ] = $name;
      $vendorPoiCategoryObj[ 'vendor_id' ] = $vendorId;
    }

    $this[ 'VendorPoiCategories' ][] = $vendorPoiCategoryObj;
  }


  public function getName()
  {
    return $this[ 'poi_name' ];
  }

  /**
   * Attempts to fix and / or format fields, e.g. finds a lat long if none provided
   */
  public function preSave( $event )
  {
     
     if(strlen($this['phone']) > 0)
     {
      $this['phone'] = stringTransform::formatPhoneNumber( trim($this['phone']), $this['Vendor']['inernational_dial_code'] );
     }

     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
     
     //get the longitute and latitude
     $geoEncoder = new geoEncode();
     
     if( !$this->geoEncodeByPass && ( !is_numeric( $this['longitude'] ) || !is_numeric( $this['latitude'] ) ) )
     {
       if( empty( $this->geoEncodeLookUpString ) )
       {
         throw new GeoCodeException( 'geoEncodeLookupString is required to lookup a geoCode for this POI.' );
       }
       
       $geoEncoder->setAddress(  $this->geoEncodeLookUpString );

       $this['longitude'] = $geoEncoder->getLongitude();
       $this['latitude'] = $geoEncoder->getLatitude();

       if( $geoEncoder->getAccuracy() < 5 )
       {
         //$this['longitude'] = null;
         //$this['latitude'] = null;
         //throw new GeoCodeException('Geo encode accuracy below 5' );
       }
     }
  }

  /**
   * adds a poi media and invokes the download for it
   * 
   * @param string $urlString 
   */
  public function addMediaByUrl( $urlString )
  {
    if ( !isset($this[ 'Vendor' ][ 'city' ]) || $this[ 'Vendor' ][ 'city' ] == '' )
    {
        throw new Exception('Failed to add Poi Media due to missing Vendor city');
    }

    $identString = md5( $urlString );
    $poiMediaObj = Doctrine::getTable( 'PoiMedia' )->findOneByIdent( $identString );
    
    if ( $poiMediaObj === false )
    {
      foreach( $this['PoiMedia'] as $poiMedia )
      {
        if( $identString == $poiMedia[ 'ident' ] )
        {
          return;
        }
      }
      $poiMediaObj = new PoiMedia();
    }

    $poiMediaObj->populateByUrl( $identString, $urlString, $this[ 'Vendor' ][ 'city' ] );
    $this[ 'PoiMedia' ][] = $poiMediaObj;
  }

}
