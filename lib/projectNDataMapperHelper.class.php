<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class projectNDataMapperHelper
{
  /**
   * @var Vendor
   */
  private $vendor;
  
  /**
   *
   * @param Vendor $vendor
   */
  public function __construct( Vendor $vendor )
  {
    $this->vendor = $vendor;
  }
  
  /**
   *
   * @param  $vendorUid
   * @return Event 
   */
  public function getEventRecord( $vendorUid )
  {
    $table = Doctrine::getTable( 'Event' );
    
    $eventRecord = $table->findOneByVendorIdAndVendorEventId
    ( 
      $this->vendor['id'],
      $vendorUid
    );
    
    return $this->getRecordByForce( $eventRecord, $table );
  }

  /**
   * @param Event $event
   * @param string $vendorUid
   * @return Event
   */
  public function getEventOccurrenceRecord( Event $event, $vendorUid )
  {
    if( is_null( $event['id'] ) )
    {
      return new EventOccurrence();
    }
    
    $table = Doctrine::getTable('EventOccurrence');

    $eventRecord = $table->findOneByEventIdAndVendorEventOccurrenceId
    (
      $event['id'],
      $vendorUid
    );

    return $this->getRecordByForce( $eventRecord, $table );
  }

  /**
   *
   * @param  $vendorUid
   * @return Poi
   */
  public function getPoiRecord( $vendorUid )
  {
    $table = Doctrine::getTable( 'Poi' );

    $eventRecord = $table->findOneByVendorIdAndVendorPoiId
    (
      $this->vendor['id'],
      $vendorUid
    );

    return $this->getRecordByForce( $eventRecord, $table );
  }

  /**
   *
   * @param  $vendorUid
   * @return Poi
   */
  public function getMovieRecord( $vendorUid )
  {
    $table = Doctrine::getTable( 'Movie' );

    $eventRecord = $table->findOneByVendorIdAndVendorMovieId
    (
      $this->vendor['id'],
      $vendorUid
    );

    return $this->getRecordByForce( $eventRecord, $table );
  }

  /**
   *
   * @param mixed $record boolean|Doctrine_Record
   * @param Doctrine_Table $table
   * @return <type>
   */
  public function getRecordByForce( $record, Doctrine_Table $table )
  {
    if( !$record )
    {
      $record = $table->create();
    }
    return $record;
  }
}
?>
