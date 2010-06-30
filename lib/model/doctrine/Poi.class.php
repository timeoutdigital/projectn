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

  /**
   *
   * @var $minimumAccuracy
   */
  private $minimumAccuracy = 8;


  public function setMinimumAccuracy( $acc )
  {
      if( is_numeric( $acc ) )
        $this->minimumAccuracy = $acc;
  }

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

  public function fixPoiName()
  {
    $this['poi_name'] = preg_replace( '/[, ]*$/', '', $this['poi_name'] );
  }

  /**
   * Return an Array of column names for which the column type is 'string'
   */
  protected function getStringColumns()
  {
    $column_names = array();
    foreach( Doctrine::getTable( get_class( $this ) )->getColumns() as $column_name => $column_info )
      if( $column_info['type'] == 'string' )
          $column_names[] = $column_name;
    return $column_names;
  }

  /**
   * Removes HTML Entities for all fields of type 'string'
   */
  protected function fixHTMLEntities()
  {
    foreach ( $this->getStringColumns() as $field )
        if( is_string( @$this[ $field ] ) )
            $this[ $field ] = html_entity_decode( $this[ $field ], ENT_QUOTES, 'UTF-8' );
  }

  /**
   * Applies vendor-specific address regexp transformations from app.yml
   */
  public function applyAddressTransformations( $transformations = null )
  {
    if ( $transformations == null || !is_array( $transformations ) ) {
      // Get transformations
      $transformations = $this[ 'Vendor' ]->getAddressTransformations();
    }
 
    if ( count( $transformations ) )
    {
      // Loop through transforms, applying them
      foreach ( $transformations as $transform )
      {
        if ( !isset( $transform[ 'regexp' ] ) || !isset( $transform[ 'type' ] ) || !isset( $transform[ 'field' ] ) )
          continue;
        $regexp = $transform[ 'regexp' ];
        $fieldName = $transform[ 'field' ];
        $type = $transform[ 'type' ];
        //print "$regexp $type $fieldName\n"; 
        try
        {
          $value = $this[ $fieldName ];

          switch ( $type )
          {
            case 'append':
              if ( preg_match( $regexp, $value, $matches ) ) // Match regexp
              {
                $this[ $fieldName ] = trim( preg_replace( $regexp, '', $value ) );
                $move = $matches[ 1 ];
                $toField = $transform[ 'to' ];

                if( strpos( $this[ $toField ], $move ) !== false ) continue; // Already In $toField
                if( !empty( $this[ $toField ] ) ) $this[ $toField ] .= ", ";
                $this[ $toField ] .= $move;
              }
              break;
            case 'prepend':
              if ( preg_match( $regexp, $value, $matches ) ) // Match regexp
              {
                $this[ $fieldName ] = trim( preg_replace( $regexp, '', $value ) );
                $move = $matches[ 1 ];
                $toField = $transform[ 'to' ];

                if( strpos( $this[ $toField ], $move ) !== false ) continue; // Already In $toField
                $this[ $toField ] = $move . !empty( $this[ $toField ] ) ? ", " . $this[ $toField ] : $this[ $toField ];
              }
              break;
            case 'remove':
              if ( preg_match( $regexp, $value, $matches ) )
              {
                $this[ $fieldName ] = trim( preg_replace( $regexp, '', $value ) );
              }
              break;
          }
        }
        catch ( Exception $e ) { } // Fail silently
      }
    }
  }

  public function getGeoEncoder()
  {
    if( !$this->geoEncoder )
      $this->geoEncoder = new geoEncode();

    return $this->geoEncoder;
  }

  public function setCriticsChoiceProperty( $isCriticsChoice )
  {
    if( !is_bool($isCriticsChoice))
      throw new Exception( 'Parameter must be a boolean value.' );

    if( $isCriticsChoice )
      $this->addProperty( 'Critics_choice', 'Y' );
    //@todo else removeProperty
  }

  public function getCriticsChoiceProperty()
  {
    foreach ( $this['PoiProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Critics_choice' )
      {
        return $property[ 'value' ];
      }
    }
  }

  public function setRecommendedProperty($isRecommended)
  {
    if( !is_bool( $isRecommended ))
      throw new Exception( 'Parameter must be a boolean value.' );

    if( $isRecommended )
      $this->addProperty( 'Recommended', 'Y' );
    //@todo else removeProperty
  }

  public function getRecommendedProperty()
  {
    foreach ( $this['PoiProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Recommended' )
      {
        return $property[ 'value' ];
      }
    }
  }

  public function setFreeProperty( $isFree )
  {
    if( !is_bool($isFree))
      throw new Exception( 'Parameter must be a boolean value.' );

    if( $isFree )
      $this->addProperty( 'Free', 'Y' );
    //@todo else removeProperty
  }

  public function getFreeProperty()
  {
    foreach ( $this['PoiProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Free' )
      {
        return $property[ 'value' ];
      }
    }
  }

  public function setTimeoutLinkProperty( $url )
  {
    if( empty( $url ) )
      return; //@todo consider logging

    $this->addProperty( 'Timeout_link', $url );
  }

  public function getTimeoutLinkProperty()
  {
    foreach ( $this['PoiProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Timeout_link' )
      {
        return $property[ 'value' ];
      }
    }
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
  * @param string | array $name The category name
  * @param int $vendorId The vendors Id
  * @return boolean false if the name is null
  *
  */
  public function addVendorCategory( $name, $vendorId = null )
  {

    if( !$vendorId )
      $vendorId = $this[ 'vendor_id' ];

    if( !$vendorId )
      throw new Exception( 'Cannot add a vendor category to an POI record without a vendor id.' );

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

    // This is a possible fix to ticket #400
//    $pc = new LinkingVendorPoiCategory();
//    $pc['vendor_poi_category_id'] = $vendorPoiCategoryObj['id'];
//    $pc['poi_id'] = $this['id'];
//    $pc->save();
//    // need to $vendorPoiCategoryObj save and remove line below,
      // need to edit LisbonFeedListingsMapper, remove poi save.

    $this[ 'VendorPoiCategory' ][] = $vendorPoiCategoryObj;
  }


  public function getName()
  {
    return $this[ 'poi_name' ];
  }

  /**
   * Attempts to fix and / or format fields, e.g. finds a lat long if none provided
   */
  public function applyFixes()
  {
     // NOTE - All Fixes MUST be Multibyte compatible.
     $this->fixHTMLEntities();
     $this->fixPoiName();
     $this->applyDefaultGeocodeLookupStringIfNull();
     $this->fixPhone();
     $this->fixUrl();
     $this->lookupAndApplyGeocodes();
     $this->truncateGeocodeLengthToMatchSchema();
     $this->applyAddressTransformations();
     $this->cleanStreetField();
     $this->setDefaultLongLatNull();
     $this->applyOverrides();
  }

  /**
   * PreSave Method
  */
  public function preSave( $event )
  {
    $this->applyFixes();
  }

  private function cleanStreetField()
  {
     $vendorCityName = array( $this->Vendor->city );
     // A list of City Name Aliases
     $vendorCityNameAliasMap = array();
     $vendorCityNameAliasMap[ "Lisbon" ] = array( "Lisbon", "Lisboa" );
     $vendorCityNameAliasMap[ "ny" ] = array( 'ny', 'New York' );

     // Use aliases if they are available
     if( array_key_exists( $vendorCityName[0], $vendorCityNameAliasMap ) )
          $vendorCityName = $vendorCityNameAliasMap[ $vendorCityName[0] ];

     // Clean all the rubbish off the beginning and end, added weird protugese space.
     $this['street'] = stringTransform::mb_trim( $this['street'], "  ,." );

     // Remove all City Name Aliases from street field
     foreach( $vendorCityName as $vendorCityAlias )
     {
        $patt = '/,\s*' . $vendorCityAlias . '\s*$/i';
        $this['street'] = mb_ereg_replace( $patt, '', $this['street'] );
     }

     // Clean all the rubbish off the beginning and end once more
     $this['street'] = stringTransform::mb_trim( $this['street'], "  ,." );
  }

  private function applyDefaultGeocodeLookupStringIfNull()
  {
     if( is_null( $this['geocode_look_up'] ) )
       $this['geocode_look_up'] = stringTransform::concatNonBlankStrings( ', ', array( $this['house_no'], $this['street'], $this['city'], $this['zips'], $this['country']) );
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

  public function lookupAndApplyGeocodes()
  {
    if( $this->geoEncodeByPass )
      return;

    if( $this->geoCodeIsValid() )
      return;

    if( empty( $this['geocode_look_up'] ) )
    {
      throw new GeoCodeException( 'geocode_look_up is required to lookup a geoCode for this POI.' );
    }

    $geoEncoder = $this->getGeoEncoder();

    $geoEncoder->setAddress( $this['geocode_look_up'] );
    $geoEncoder->setBounds( $this['Vendor']->getGoogleApiGeoBounds() );
    $geoEncoder->setRegion( $this['Vendor']['country_code'] );

    $this['longitude'] = $geoEncoder->getLongitude();
    $this['latitude']  = $geoEncoder->getLatitude();

    if( $geoEncoder->getAccuracy() < $this->minimumAccuracy )
    {
      $this['longitude'] = null;
      $this['latitude']  = null;
    //  throw new GeoCodeException('Geo encode accuracy below 5' );
    }
  }

  public function geoCodeIsValid()
  {
    $isZero = ( $this['longitude'] == 0  || $this['latitude'] == 0 );
    $isNull = ( $this['longitude'] == null  || $this['latitude'] == null );

    return !$isZero && !$isNull;
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
    //@todo log missing images
    if( empty( $urlString ) )
      return;

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


  /**
   * Sets the longitude and latitude of the object to null if it matches a default coordinate
   * - Loads default coordinates from app.yml
   */
  public function setDefaultLongLatNull()
  {
    $pairs = sfConfig::get( 'app_poi_default_coordinates', array() );

    foreach ( $pairs as $coordinate )
    {
        if ( isset( $coordinate[ 'long' ] ) && isset( $coordinate[ 'lat' ] ) )
        {
            if ( (float) $this['longitude'] == (float) $coordinate['long'] && (float) $this['latitude'] == (float) $coordinate['lat'] )
            {
                $this['longitude'] = null;
                $this['latitude'] = null;
            }
        }
    }
  }

}
