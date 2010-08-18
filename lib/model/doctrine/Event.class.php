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
   * the media added to event is stored in this array and the largest one will be downloaded in downloadMedia method
   *
   * @var $media
   */
  private $media = array();

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
    $this->downloadMedia();
    $this->removeMultipleImages();
    $this->removeMultipleOccurrences();
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
            if($this[ $field ] !== null) $this[ $field ] = stringTransform::mb_trim( $this[ $field ] );

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

    $name = stringTransform::concatNonBlankStrings(' | ', $name);

    if( stringTransform::mb_trim($name) == '' )
        return false;

    foreach( $this[ 'VendorEventCategory' ] as $existingCategory )
    {
      // This will unlink all vendor category relationships that dont match the event vendor.
      if( $existingCategory[ 'vendor_id' ] != $vendorId )
          $this->unlinkInDb( 'VendorEventCategory', array( $existingCategory[ 'id' ] ) );
    }

    $vendorEventCategoryObj = new VendorEventCategory();
    $vendorEventCategoryObj[ 'name' ] = $name;
    $vendorEventCategoryObj[ 'vendor_id' ] = $vendorId;

    $recordFinder = new recordFinder();
    $uniqueRecord = $recordFinder->findEquivalentOf( $vendorEventCategoryObj )
                                     ->comparingAllFieldsExcept( 'id' )
                                     ->getUniqueRecord();

    $this[ 'VendorEventCategory' ][ $name ] = $uniqueRecord;
  }

  /**
   * tidy up function for events with more than one image attached to them
   * read the headers of the images and select the largest one in size
   * remove other images
   *
   */
  private function removeMultipleImages()
  {
     // if there is more than 1 image for this Event we need to find the largest one and remove the rest
     if( count( $this[ 'EventMedia' ] ) > 1 )
     {
        $largestImg = $this[ 'EventMedia' ][ 0 ] ;
        $largestSize = 0;

        foreach ($this[ 'EventMedia' ] as $eventMedia )
        {
             $headers = get_headers( $eventMedia['url'] , 1);

             if( $headers[ 'Content-Length' ] >  $largestSize)
             {
                $largestSize = $headers[ 'Content-Length' ];
                $largestImg  = $eventMedia;
             }
        }

        $this['EventMedia'] = new Doctrine_Collection( 'EventMedia' );

        $this['EventMedia'] [] = $largestImg;

     }
  }

   /**
   * selects the largest image in media array and downloads the image
   *
   *
   */
  private function downloadMedia()
  {

    // if addMediaByUrl wasn't called, there is no change in media
    if( count( $this->media) == 0 )  return;

    $largestImg = $this->media[ 0 ] ;

    //find the largest image
    foreach ( $this->media as $img )
    {
       if( $img[ 'contentLength' ] > $largestImg[ 'contentLength' ]  )
       {
        $largestImg = $img;
       }
    }

    // check if the largestImg is larger than the one attached already if any
    foreach ($this[ 'EventMedia' ] as $eventMedia )
    {

        if( $eventMedia['content_length']  > $largestImg[ 'contentLength' ]  )
        {
            //we already have a larger image so ignore this
            return;
        }
    }

    $eventMediaObj = Doctrine::getTable( 'EventMedia' )->findOneByIdent( $largestImg[ 'ident' ] );

    if ( $eventMediaObj === false )
    {
        $eventMediaObj = new EventMedia( );
    }

    try
    {
        $eventMediaObj->populateByUrl( $largestImg[ 'ident' ], $largestImg['url'], $this[ 'Vendor' ][ 'city' ] );

        // add the $eventMediaObj to the Event
        $this[ 'EventMedia' ] [] =  $eventMediaObj;
    }
    catch ( Exception $e )
    {
        /** @todo : log this error */
    }

  }

   /**
   * adds a event media to the media array and the largest one will be downloaded by downloadMedia method
   *
   * @param string $urlString
   */
  public function addMediaByUrl( $urlString )
  {
    if( empty( $urlString ) )
      return;

    if ( !isset($this[ 'Vendor' ][ 'city' ]) || $this[ 'Vendor' ][ 'city' ] == '' )
    {
        throw new Exception('Failed to add Event Media due to missing Vendor city');
    }

    $headers = get_headers( $urlString , 1);

    // When Image redirected with 302/301 get_headers will return morethan one header array
    $contentType = ( is_array($headers [ 'Content-Type' ]) ) ? array_pop($headers [ 'Content-Type' ]) : $headers [ 'Content-Type' ];
    $contentLength = ( is_array($headers [ 'Content-Length' ]) ) ? array_pop($headers [ 'Content-Length' ]) : $headers [ 'Content-Length' ];

    // check the header if it's an image
    if( $contentType != 'image/jpeg' )
    {
        return false;
    }

    $this->media[] = array(
        'url'           => $urlString,
        'contentLength' => $contentLength,
        'ident'         => md5( $urlString ),
     );
    return true;
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

}
