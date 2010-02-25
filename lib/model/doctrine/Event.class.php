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
   * Attempts to fix and / or format fields, e.g. url
   */
  public function preSave( $event )
  {

     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
     if( $this['booking_url'] != '')
     {
        $this['booking_url'] = stringTransform::formatUrl($this['booking_url']);
     }     

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

  public function addVendorCategory( $name, $vendorId )
  {
    if ( is_array( $name ) )
    {
      $name = implode( ' | ', $name );
    }

    $vendorEventCategoryObj = Doctrine::getTable( 'VendorEventCategory' )->findOneByNameAndVendorId( $name, $vendorId );

    if ( $vendorEventCategoryObj === false )
    {
      $vendorEventCategoryObj = new VendorEventCategory();
      $vendorEventCategoryObj[ 'name' ] = $name;
      $vendorEventCategoryObj[ 'vendor_id' ] = $vendorId;
    }

    $this[ 'VendorEventCategories' ][] = $vendorEventCategoryObj;
  }

   /**
   * adds a event media and invokes the download for it
   *
   * @param string $urlString
   */
  public function addMediaByUrl( $urlString )
  {
    if ( !isset($this[ 'Vendor' ][ 'city' ]) || $this[ 'Vendor' ][ 'city' ] == '' )
    {
        throw new Exception('Failed to add Event Media due to missing Vendor city');
    }

    $identString = md5( $urlString );
    $eventMediaObj = Doctrine::getTable( 'EventMedia' )->findOneByIdent( $identString );

    if ( $eventMediaObj === false )
    {
        $eventMediaObj = new EventMedia();
    }

    $eventMediaObj->populateByUrl( $identString, $urlString, $this[ 'Vendor' ][ 'city' ] );
    $this[ 'EventMedia' ][] = $eventMediaObj;
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
}
