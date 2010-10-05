<?php

/**
 * Event
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Event extends BaseEvent
{
  /**
   * cache for vendorCategories
   *
   * @var array
   */
  public static $vendorCategories;



  /**
   * Attempts to fix and / or format fields, e.g. url
   */
  public function applyFixes()
  {
     $this->cleanStringFields();

     if( $this['url'] != '')
        $this['url'] = stringTransform::formatUrl($this['url']);

     if( $this['booking_url'] != '')
        $this['booking_url'] = stringTransform::formatUrl($this['booking_url']);

    $this->applyOverrides();
    //$this->removeMultipleOccurrences();
  }

  /**
   * Return an Array of column names for which the column type is 'string'
   */
  protected function getStringColumns()
  {
    $columns = array();
    foreach( Doctrine::getTable( get_class( $this ) )->getColumns() as $column_name => $column_info )
      if( $column_info['type'] == 'string' )
          $columns[$column_name] = $column_info ;
    return $columns;
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
            if( $field_info['notnull'] === false && stringTransform::mb_trim( $this[ $field ] ) =='' ) $this[ $field ] = null;
        }
  }

  /**
   * PreSave Method
   */
  public function preSave( $event )
  {
     $this->applyFixes();
  }

  private function applyOverrides()
  {
    $override = new recordFieldOverrideManager( $this );
    $override->applyOverridesToRecord();
  }

  public function addProperty( $lookup, $value )
  {
    if( $this->exists() )
    {
      foreach( $this['EventProperty'] as $property )
      {
        $lookupIsSame = ( $lookup == $property[ 'lookup' ] );
        $valueIsSame  = ( $value  == $property[ 'value' ]  );

        if( $lookupIsSame && $valueIsSame )
        {
          return;
        }
      }
    }
    $eventPropertyObj = new EventProperty();
    $eventPropertyObj[ 'lookup' ] = (string) $lookup;
    $eventPropertyObj[ 'value' ] = (string) $value;

    $this[ 'EventProperty' ][] = $eventPropertyObj;
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
    foreach ( $this['EventProperty'] as $property )
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
    foreach ( $this['EventProperty'] as $property )
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
    foreach ( $this['EventProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Free' )
      {
        return $property[ 'value' ];
      }
    }
  }

  public function setTimeoutLinkProperty( $url )
  {
    if( empty($url) )
      return; //@todo consider logging

    $this->addProperty( 'Timeout_link', $url );
  }

  public function getTimeoutLinkProperty()
  {
    foreach ( $this['EventProperty'] as $property )
    {
      if ( $property[ 'lookup' ] == 'Timeout_link' )
      {
        return $property[ 'value' ];
      }
    }
  }

  public function addVendorCategory( $name, $vendorId = null )
  {
    if( $vendorId instanceof Vendor )
    {
        $vendorId = $vendorId[ 'id' ];
    }

    if( !$vendorId )
      $vendorId = $this[ 'vendor_id' ];

    if( !$vendorId )
      throw new Exception( 'Cannot add a vendor category to an Event record without a vendor id.' );

    if(!is_array($name) && !is_string($name))
        throw new Exception ('$name parameter must be string or array of strings');

    if( !is_array($name) )
        $name = array( $name );

    $name = html_entity_decode( stringTransform::concatNonBlankStrings(' | ', $name) );

    //#645 if the category is Film save it as Art
    if( strtolower( $name ) == 'film' )
    {
        $name = 'Art';
    }

    if( stringTransform::mb_trim($name) == '' )
        return false;

    // This will enable the ussage of String Index insted of numeric Index in Doctrine Collection array
    $this[ 'VendorEventCategory' ]->setKeyColumn( 'name' );
    
    foreach( $this[ 'VendorEventCategory' ] as $existingCategory )
    {
      // This will unlink all vendor category relationships that dont match the event vendor.
      if( $existingCategory[ 'vendor_id' ] != $vendorId )
          $this->unlinkInDb( 'VendorEventCategory', array( $existingCategory[ 'id' ] ) );
    }

    if( is_null( self::$vendorCategories ) || !isset( self::$vendorCategories[ $vendorId ]) )
    {
        if( is_null( self::$vendorCategories ) )
        {
            self::$vendorCategories = array();
        }

        self::$vendorCategories[ $vendorId ] = array();
        $vendorEventCategories = Doctrine::getTable( 'VendorEventCategory' )->findByVendorId( $vendorId );
        foreach( $vendorEventCategories as $vendorCategory )
        {
            $vendorCategoryName = $vendorCategory['name'];
            self::$vendorCategories[ $vendorId ][ $vendorCategoryName ] = $vendorCategory;
        }
    }

    if( key_exists( $name, self::$vendorCategories[ $vendorId ] ) )
    {
      $category = self::$vendorCategories[ $vendorId ][ $name ];
    }
    else
    {
      $category = new VendorEventCategory();
      $category[ 'name' ] = $name;
      $category[ 'vendor_id' ] = $vendorId;
      self::$vendorCategories[ $vendorId ][ $name ] = $category;
    }
    
    $this[ 'VendorEventCategory' ][ $name ] = $category;
  }

  /**
   * Add EventMedia to Event
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

  public function getPois()
  {
    $pois = new Doctrine_Collection(Doctrine::getTable('Poi'));
    foreach( $this['EventOccurrence'] as $occurrence )
    {
      $pois[] = $occurrence['Poi'];
    }
    return $pois;
  }

  public function getEventCategory()
  {
    $eventCategoryQuery = Doctrine::getTable( 'VendorEventCategory' )
      ->createQuery( 'vec' )
      ->leftJoin( 'vec.EventCategory ec' )
      ->addWhere( 'vec.id = ?' )
    ;

    $categories = new Doctrine_Collection( Doctrine::getTable( 'EventCategory' ) );
    foreach( $this[ 'VendorEventCategory' ] as $vendorCategory )
    {
      //wtf? Executing the query seems to hydrate the object more fully...
      $eventCategoryQuery->execute( array( $vendorCategory[ 'id' ] ) );
      foreach( $vendorCategory[ 'EventCategory' ] as $eventCategory )
      {
        $categories[] = $eventCategory;
      }
    }

    return $categories;
  }

  public function removeMultipleOccurrences()
  {
    $occurrences = array();

    foreach ( $this[ 'EventOccurrence' ] as $occurrence )
    {
        $date = $occurrence[ 'start_date' ];
        $poiId = $occurrence[ 'poi_id' ];
        $startTime = $occurrence[ 'start_time' ];

        //if two occurrences have the same date, startTime and poiId we should only use one of them
        //using a combination of those as a key in an array will provide unique occurrences
        $uniqueId = $date . $startTime . $poiId;

        $occurrences [ $uniqueId  ] = $occurrence;
    }
    //reset the occurrences
    $this['EventOccurrence'] = new Doctrine_Collection( 'EventOccurrence' );

    //add the unique occurrences
    foreach ($occurrences as $occurrence)
    {
        $this['EventOccurrence'] [] =$occurrence;
    }

  }

  public function addMeta( $lookup, $value, $comment = null )
  {
    $eventMetaObj = new EventMeta();
    $eventMetaObj[ 'lookup' ] = (string) $lookup;
    $eventMetaObj[ 'value' ] = (string) $value;
    if(!is_null($comment) && !is_object($comment))
        $eventMetaObj[ 'comment' ] = (string) $comment;

    $this[ 'EventMeta' ][] = $eventMetaObj;
  }

}
