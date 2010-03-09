<?php

/**
 * Poi Model
 *
 * 
 * @package    projectn
 * @subpackage doctrine.model.lib
 *
 * @author     Tim Bowler <timbowler@timeout.com>
 * @copyright  Time Out Communications Ltd
 *
 * @version    1.0.1
 */
class Poi extends BasePoi
{
  /**
   * @var string
   */
  private $geoEncodeLookUpString;

  /**
   * @var boolean
   */
  private $geoEncodeByPass = false;


  /**
   * Set the address string that will be used to lookup the geocodes
   *
   * @param string $geoEncodeLookUpString
   */
  public function setGeoEncodeLookUpString( $geoEncodeLookUpString )
  {
    $this->geoEncodeLookUpString = $geoEncodeLookUpString;
  }

  /**
   * Set if Geoencoding is to be bypassed
   *
   * @param boolean $geoEncodeByPass
   */
  public function setGeoEncodeByPass( $geoEncodeByPass = false )
  {
    $this->geoEncodeByPass = $geoEncodeByPass;
  }


  /**
   * Add the Poi Properties
   *
   * @param string $lookup
   * @param string $value
   * @return boolean if value is null or existing
   */
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


 /**
  * Add the vendor Categories to the Poi
  *
  * @param string $name The category name
  * @param int $vendorId The vendors Id
  * @return boolean false if the name is null
  *
  */
  public function addVendorCategory( $name, $vendorId )
  {
 
    if ( is_array( $name ) )
    {
      $name = implode( ' | ', $name );
    }
    else
    {
        if(strlen($name) == 0)
        {
            return false;
        }
    }

    foreach( $this[ 'VendorPoiCategories' ] as $existingCategory )
    {
      if( $existingCategory[ 'name' ] == $name ) return;
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

     if( !$this->geoEncodeByPass && (( $this['longitude'] == 0  || $this['latitude'] == 0 ) ||  ( $this['longitude'] == null  || $this['latitude'] == null )) )
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

     if( !is_null( $this['longitude'] ) )
       $this['longitude'] = substr( (string) $this['longitude'], 0, 8 );
     if( !is_null( $this['longitude'] ) )
       $this['latitude'] = substr( (string) $this['latitude'], 0, 8 );
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
