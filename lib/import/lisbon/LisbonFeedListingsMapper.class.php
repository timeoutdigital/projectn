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
class LisbonFeedListingsMapper extends LisbonFeedBaseMapper
{

  public function mapListings()
  {
    foreach( $this->xml->listings as $listingElement )
    {
      $event = $this->getRecord('Event', 'vendor_event_id', (int) $listingElement['musicid'] );

      $this->mapAvailableData( $event, $listingElement, 'EventProperty' );

      $event['booking_url'] = '';
      $event['url'] = '';
      //$event['rating'] = '';
      $event['vendor_id'] = $this->vendor['id'];

      $this->notifyImporter( $event );

      $occurrence = $this->getRecord('EventOccurrence', 'vendor_event_occurrence_id', (int) $listingElement['RecurringListingID']);
      $occurrence['vendor_event_occurrence_id'] = '';
      $occurrence['start'] = '';
      $occurrence['utc_offset'] = 0; 
      $occurrence['event_id'] = $event['id'];
      $occurrence['poi_id'] = 1;//$listingElement->placeid;

      $this->notifyImporter($occurrence);
     
    }
  }

  /**
   * Return an array of mappings from xml attributes to event fields
   *
   * @return array
   */
  protected function getMap()
  {
    return array(
      'musicid' => 'vendor_event_id',
      'gigKey' => 'name',
      'Notesline1' => 'short_description',
      'AnnotationForWeb' => 'description',
      'priceinfo' => 'price',
    );
  }
}
?>
