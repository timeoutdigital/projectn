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
  private $geocoderByPass = false;

  /**
   * @var geocoder
   */
  private $geocoderr;

  /**
   *
   * @var $minimumAccuracy
   */
  private $minimumAccuracy = 8;

  /**
   * THis variable is created to 
   * @var int
   */
  private $master_poi = null;


  public function getDuplicate()
  {     
      foreach( $this['PoiMeta'] as $meta )
      {
          if( isset( $meta['lookup'] ) && $meta['lookup'] == 'Duplicate' )
          {
              return true;
          }
      }

      return false;
  }

  public function setDuplicate( $value = NULL )
  {
      if( $value === 'on' )
      {
            if( $this->getDuplicate() )
                return;

            $pm = new PoiMeta();
            $pm['lookup']   = 'Duplicate';
            $pm['value']    = 'Duplicate';
            $pm['comment']  = 'Producer Marked as Duplicate';
            $this['PoiMeta'][] = $pm;
      }
      
      elseif( is_null( $value ) )
      {
            if( !$this->getDuplicate() )
                return;

            foreach( $this['PoiMeta'] as $key => $meta )
            {
                if( isset( $meta['lookup'] ) && $meta['lookup'] == 'Duplicate' )
                {
                    unset( $this['PoiMeta'][ $key ] );
                    $meta->delete();
                }
            }
      }
  }

  /**
   * Set this POI's Master poi. Use "false" to remove relastionship
   * @param int $poi_id
   */
  public function setMasterPoi( Poi $poi )
  {
      if( $poi !== false && !$poi )
      {
          throw new PoiException( 'Invalid paramer $poi_id. Should be valid POI ID or FALSE to remove existing relationship' );
      }

      $this->master_poi = $poi;
  }
  /**
   * Check is this Poi Master of duplicate pois
   * @return boolean
   */
  public function isMaster()
  {
      // Refresh reset Unsaved data causing data loss.. hence we query database directly
      return ( Doctrine::getTable( 'PoiReference' )->findByMasterPoiId( $this['id'])->count() > 0  ) ? true : false;
  }

  /**
   * Check this for being Duplicate of another Poi
   * @return boolean
   */
  public function isDuplicate()
  {
      // Refresh reset Unsaved data causing data loss.. hence we query database directly
      return ( Doctrine::getTable( 'PoiReference' )->findByDuplicatePoiId( $this['id'])->count() > 0  ) ? true : false;
  }

  public function setMinimumAccuracy( $acc )
  {
      if( is_numeric( $acc ) )
        $this->minimumAccuracy = $acc;
  }

  public function setgeocoderLookUpString( $lookup )
  {
    $this['geocode_look_up'] = $lookup;
  }

  public function getgeocoderLookUpString()
  {
    return $this['geocode_look_up'];
  }

  public function setgeocoderr( geocoder $geocoderr )
  {
    $this->geocoderr = $geocoderr;
  }

  public function fixStreetName()
  {
    $cityName =  $this[ 'city' ];

    $this[ 'street' ] = str_ireplace( ','.$cityName , ', '.$cityName , trim( $this[ 'street' ] ) );
    //remove the last word and comma if it's the city name
    $streetNameParts = explode( ' ', $this[ 'street' ] );

    if( strtolower( trim( end( $streetNameParts  ) ))  == strtolower ( $cityName ) )
    {
        unset( $streetNameParts [ count( $streetNameParts ) -1 ]  );
        $this[ 'street' ] = implode( ' ',$streetNameParts );
    }
  }

  /**
   * Return an Array of column names for which the column type is 'string'
   */
  protected function getStringColumns()
  {
    $column = array();
    foreach( Doctrine::getTable( get_class( $this ) )->getColumns() as $column_name => $column_info )
      if( $column_info['type'] == 'string' )
          $column[$column_name] = $column_info;
    return $column;
  }

  /**
   * Clean all fields of type 'string', Removes HTML and Trim
   */
  protected function cleanStringFields()
  {
    foreach ( $this->getStringColumns() as $field => $field_info )
        if( is_string( @$this[ $field ] ) )
        {
            // fixHTMLEntities
            $this[ $field ] = html_entity_decode( $this[ $field ], ENT_QUOTES, 'UTF-8' );

            // Refs #525 - Trim All Text fields on PreSave
            if($this[ $field ] !== null) $this[ $field ] = stringTransform::mb_trim( $this[ $field ], ',' );

            // Refs #538 - Nullify all Empty string that can be Null in database Schema
            if( $field_info['notnull'] === false && stringTransform::mb_trim( $this[ $field ] ) == '' ) $this[ $field ] = null;
        }

    // Null review date when empty string found
    $this['review_date'] = ( trim( $this['review_date'] ) == '' ) ? null : $this['review_date'];
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

  public function getgeocoderr()
  {
    if( !$this->geocoderr )
      $this->geocoderr = new googleGeocoder();

    return $this->geocoderr;
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
   * @param boolean $geocoderByPass
   */
  public function setgeocoderByPass( $geocoderByPass = false )
  {
    $this->geocoderByPass = $geocoderByPass;
  }


  /**
   * Add the Poi Meta Data
   *
   * @param string $lookup
   * @param string $value
   * @return boolean if value is null or existing
   */
  public function addMeta( $lookup, $value, $comment = null )
  {
    $poiMetaObj = new PoiMeta();
    $poiMetaObj[ 'lookup' ] = (string) $lookup;
    $poiMetaObj[ 'value' ] = (string) $value;
    if(!is_null($comment) && !is_object($comment))
        $poiMetaObj[ 'comment' ] = (string) $comment;

    $this[ 'PoiMeta' ][] = $poiMetaObj;
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

    if(!is_array($name) && !is_string($name))
        throw new Exception ('$name parameter must be string or array of strings');

    if( !is_array($name) )
        $name = array( $name );

    // require HTML cleaning before storing into Database
    $name = html_entity_decode( stringTransform::concatNonBlankStrings(' | ', array_unique( $name ) ) );

    // #909 Pass categories as array to Filter black listed categories
    // insted of cleaning html_entity_decode each category, I used Implode -> clean -> explode to filter black listed categories
    $filteredNames = Doctrine::getTable( 'VendorCategoryBlackList' )->filterByCategoryBlackList( $vendorId, explode(' | ', $name) );

    if( !is_array( $filteredNames ) || empty($filteredNames) )
        return false;

    // implode again to continue with existing check and adding to database
    $name = stringTransform::concatNonBlankStrings( ' | ', $filteredNames );

    if( stringTransform::mb_trim($name) == '' )
        return false;

    foreach( $this[ 'VendorPoiCategory' ] as $existingCategory )
    {
      // This will unlink all vendor category relationships that dont match the poi vendor.
      if( $existingCategory[ 'vendor_id' ] != $vendorId )
          $this->unlinkInDb( 'VendorPoiCategory', array( $existingCategory[ 'id' ] ) );

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
  public function applyFixes()
  {
     // NOTE - All Fixes MUST be Multibyte compatible.
     $this->cleanStringFields();
     $this->fixStreetName();
     $this->applyDefaultGeocodeLookupStringIfNull();
     $this->fixPhoneNumbers();
     $this->fixUrl();
     $this->fixEmail();
     $this->truncateGeocodeLengthToMatchSchema();
     $this->applyAddressTransformations();
     $this->cleanStreetField();
     $this->applyOverrides();
     $this->lookupAndApplyGeocodes();
  }

  /**
   * PreSave Method
  */
  public function preSave( $event )
  {
      $this->applyFixes();
  }

  /**
   * postSave: specailly created to manage the relationship between
   * Poi and PoiReference
   * @param <type> $event 
   */
  public function  postSave($event) {
        parent::postSave($event);
        $this->saveMasterPoi();

  }

  /**
   * Save logic for Master Poi, this will be executed on PostSave
   */
  private function saveMasterPoi()
  {
      if( $this->master_poi === null ) return; // No need to take any action.

      // Delete existing relationship When false given
      if( $this->master_poi === false )
      {
          Doctrine::getTable( 'PoiReference' )->removeRelationShip( $this['id'] );
      }
      else
      {
          // add new relationship
          Doctrine::getTable( 'PoiReference' )->relatePois( $this->master_poi['id'], $this['id'] );
      }
  }

  /**
   * Get this poi's master POI 
   * @param Doctrine_Core_Hydrate $hydrationMode
   * @return mixed
   */
  public function getMasterPoi( $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
  {
      if( !$this->isDuplicate() ) return null;

      return Doctrine::getTable( 'Poi' )->getMasterOf( $this['id'], $hydrationMode );
  }
  
  /**
   * Get this poi's Duplicate poi's
   * @param Doctrine_Core_Hydrate $hydrationMode
   * @return mixed
   */
  public function getDuplicatePois( $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
  {
      if( !$this->isMaster() ) return null;

      return Doctrine::getTable( 'Poi' )->getDuplicatesOf( $this['id'], $hydrationMode );
  }

  /**
   * This method will Delete all existing references to this POI as mster
   */
  public function removeDuplicatePois()
  {
      Doctrine::getTable( 'PoiReference' )->removeDuplicateReferences( $this['id'] );
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
     if( is_null( $this['geocode_look_up'] ) || ( is_string( $this['geocode_look_up'] ) && empty( $this['geocode_look_up'] ) ) )
       $this['geocode_look_up'] = stringTransform::concatNonBlankStrings( ', ', array( $this['house_no'], $this['street'], $this['city'], $this['zips'] ) );
  }

  private function applyOverrides()
  {
    $override = new recordFieldOverrideManager( $this );
    $override->applyOverridesToRecord();
  }

  private function fixPhoneNumbers()
  {
     $phoneFields = array( 'phone', 'phone2', 'fax' );

     foreach ( $phoneFields as $phoneField )
     {
        if(strlen($this[$phoneField]) > 0)
        {
            $this[$phoneField] = stringTransform::formatPhoneNumber( trim($this[$phoneField]), $this['Vendor']['inernational_dial_code'] );
        }
     }
  }

  private function fixUrl()
  {
     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
  }


  private function fixEmail()
  {
     if( ! stringTransform::isValidEmail ( $this['email'] ) )
     {
        $this['email'] = null;
     }
  }

  public function lookupAndApplyGeocodes()
  {
    if( $this->geocoderByPass )
      return;

    if( $this->geoCodeIsValid() )
      return;

    if( empty( $this['geocode_look_up'] ) )
    {
      return;
    }

    $geocoderr = $this->getgeocoderr();

    $geocoderr->setAddress( $this['geocode_look_up'] );
    $geocoderr->setBounds( $this['Vendor']->getGoogleApiGeoBounds() );
    $geocoderr->setRegion( $this['Vendor']['country_code'] );

    $long = $geocoderr->getLongitude();
    $lat = $geocoderr->getLatitude();

    if( $geocoderr->getAccuracy() < $this->minimumAccuracy )
    {
      $this['longitude'] = null;
      $this['latitude']  = null;
    //  throw new GeoCodeException('Geo encode accuracy below 5' );
      return;
    }

    $longitudeLength = (int) $this->getColumnDefinition( 'longitude', 'length' ) + 1;//add 1 for decimal
    $latitudeLength  = (int) $this->getColumnDefinition( 'latitude', 'length' ) + 1;//add 1 for decimal

    if( strlen( $long ) > $longitudeLength )
        $long = substr( (string) $long, 0, $longitudeLength );

    if( strlen( $lat ) > $latitudeLength )
        $lat = substr( (string) $lat, 0, $latitudeLength );

    if( $this['latitude'] != $lat || $this['longitude'] != $long )
        $this->addMeta( "Geo_Source", get_class( $geocoderr ), "Changed: " . $this['latitude'] . ',' . $this['longitude'] . ' to ' . $lat . ',' . $long );

    $this['longitude'] = $long;// $geocoderr->getLongitude();
    $this['latitude']  = $lat; //$geocoderr->getLatitude();
  }

  public function geoCodeIsValid()
  {
    $isZero = ( $this['longitude'] == 0  || $this['latitude'] == 0 );
    $isNull = ( $this['longitude'] == null  || $this['latitude'] == null );
    $isEmpty = ( $this['longitude'] == ""  || $this['latitude'] == "" );

    return !$isZero && !$isNull && !$isEmpty;
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
   * Add PoiMedia to Poi
   *
   * @param string $url
   *
   * This function is deprecated in favour of Media::addMedia( $model, $url ).
   * refs #626 -pj 31-Aug-10
   */
  public function addMediaByUrl( $url = "" )
  {
    Media::addMedia( $this, $url );
  }

  /**
   * Function to be used by importers, this ensures that feed lat/longs are valid before attaching them to a POI.
   */
  public function applyFeedGeoCodesIfValid( $lat = "", $long = "" )
  {
        if( is_numeric( $lat ) && is_numeric( $long )  && 
                floatval( $lat ) != 0 && floatval( $long ) != 0)
        {
            // validate for Boundary
            if( !$this['Vendor']->isWithinBoundaries( $lat, $long ) )
            {
                throw new PoiException( "Geocode provided in the feed ouside vendor boundaries. City: {$this['Vendor']['city']}, Vendor poi id: {$this['vendor_poi_id']}, Latitude: {$lat} & longitude: {$long}" );
            }

            $longitudeLength = (int) $this->getColumnDefinition( 'longitude', 'length' ) + 1;//add 1 for decimal
            $latitudeLength  = (int) $this->getColumnDefinition( 'latitude', 'length' ) + 1;//add 1 for decimal

            if( strlen( $long ) > $longitudeLength )
                $long = substr( (string) $long, 0, $longitudeLength );

            if( strlen( $lat ) > $latitudeLength )
                $lat = substr( (string) $lat, 0, $latitudeLength );

            if( $this['latitude'] != $lat || $this['longitude'] != $long )
                $this->addMeta( "Geo_Source", "Feed", "Changed: " . $this['latitude'] . ',' . $this['longitude'] . ' to ' . $lat . ',' . $long );

            $this['latitude']                      = $lat;
            $this['longitude']                     = $long;
        }
  }

  public function setUnsolvable( $is_unsolvable, $comment = null )
  {
      if( !is_bool($is_unsolvable) )
      {
          throw new PoiException('Invalid parameter value');
      }

      // Get if any exists as this should not be duplicated
      // Whe this field record exists means this is marked as skipped by producer
      $existing_meta = null;
      foreach( $this['PoiMeta'] as $meta )
      {
          if( $meta['lookup'] == 'unsolvable' )
          {
              $existing_meta = $meta;
              break;
          }
      }

      if( $existing_meta == null )
      {
          $existing_meta = new PoiMeta();
          $existing_meta['lookup'] = 'unsolvable';
      }

      if($is_unsolvable )
      {
          
          $existing_meta['value'] = $comment;
          $this['PoiMeta'][] = $existing_meta;

      }else{
          
          $this->unlink( 'PoiMeta', $existing_meta['id'] );
          $existing_meta->delete();
          unset($existing_meta);
      }
  }

  public function getUnsolvable()
  {
      foreach( $this['PoiMeta'] as $meta )
      {
          if( $meta['lookup'] == 'unsolvable' )
          {
              return true;
          }
      }

      return false;
  }

  public function getUnsolvableReason()
  {
      foreach( $this['PoiMeta'] as $meta )
      {
          if( $meta['lookup'] == 'unsolvable' )
          {
              return $meta['value'];
          }
      }

      return null;
  }

}

class PoiException extends Exception{};
