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
   * @var boolean
   */
  private $geoEncodeByPass = false;

  /**
   * @var geoEncode
   */
  private $geoEncoder;

  public function setGeoEncodeLookUpString( $lookup )
  {
    $this['geocode_look_up'] = $lookup;
  }

  public function getGeoEncodeLookUpString()
  {
    return $this['geocode_look_up'];
  }

  public function setGeoEncoder( geoEncode $geoEncoder )
  {
    $this->geoEncoder = $geoEncoder;
  }

  public function getGeoEncoder()
  {
    if( !$this->geoEncoder )
      $this->geoEncoder = new geoEncode();

    return $this->geoEncoder;
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

    foreach( $this[ 'VendorPoiCategory' ] as $existingCategory )
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

    $this[ 'VendorPoiCategory' ][] = $vendorPoiCategoryObj;
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
     $this->fixPhone();
     $this->fixUrl();
     $this->lookupAndApplyGeocodes();
     $this->truncateGeocodeLengthToMatchSchema();
     $this->applyOverrides();
  }

  private function applyOverrides()
  {
    $override = new recordFieldOverrideManager( $this );
    $override->applyOverridesToRecord();
  }

  private function fixPhone()
  {
     if(strlen($this['phone']) > 0)
     {
      $this['phone'] = stringTransform::formatPhoneNumber( trim($this['phone']), $this['Vendor']['inernational_dial_code'] );
     }
  }

  private function fixUrl()
  {
     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
  }

  private function lookupAndApplyGeocodes()
  {
    if( $this->geoEncodeByPass )
      return;

    if( !$this->hasValidGeocode() )
      return;

    if( empty( $this['geocode_look_up'] ) )
    {
      throw new GeoCodeException( 'geocode_look_up is required to lookup a geoCode for this POI.' );
    }

    $geoEncoder = $this->getGeoEncoder();
    
    $geoEncoder->setAddress(  $this['geocode_look_up'], $this['Vendor']  );

    $this['longitude'] = $geoEncoder->getLongitude();
    $this['latitude']  = $geoEncoder->getLatitude();

    if( $geoEncoder->getAccuracy() < 8 )
    {
      $this['longitude'] = null;
      $this['latitude'] = null;
    //  throw new GeoCodeException('Geo encode accuracy below 5' );
    }
  }

  private function hasValidGeocode()
  {
    $isZero = ( $this['longitude'] == 0  || $this['latitude'] == 0 );
    $isNull = ( $this['longitude'] == null  || $this['latitude'] == null );

    return $isZero || $isNull;
  }

  private function truncateGeocodeLengthToMatchSchema()
  {
    $longitudeLength = (int) $this->getColumnDefinition( 'longitude', 'length' ) + 1;//add 1 for decimal
    $latitudeLength  = (int) $this->getColumnDefinition( 'latitude', 'length' ) + 1;//add 1 for decimal

     if( strlen( $this['longitude'] ) > $longitudeLength )
       $this['longitude'] = substr( (string) $this['longitude'], 0, $longitudeLength );

     if( strlen( $this['latitude'] ) > $latitudeLength )
       $this['latitude'] = substr( (string) $this['latitude'], 0, $latitudeLength );
  }

  private function getColumnDefinition( $column, $part=null )
  {
    $definition = $this->getTable()->getColumnDefinition( 'longitude' );

    if( is_null( $part ) )
      return $definition;

    if( !array_key_exists( $part, $definition ) )
      throw new Exception( "'$part' is not in the column definition for the Poi column '$column'."  );

    return $definition[$part];
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
