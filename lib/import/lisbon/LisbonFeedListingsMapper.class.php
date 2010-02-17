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
      $event = $this->getRecord('Event', 'vendor_event_id',  (int) $listingElement['RecurringListingID'] );
        
      $this->mapAvailableData( $event, $listingElement, 'EventProperty' );

      $event['url'] = '';
      //$event['rating'] = '';
      $event['vendor_id'] = $this->vendor['id'];
      $event['vendor_event_id'] = (int) $listingElement['RecurringListingID'];
 
      $occurrence = $this->getRecord('EventOccurrence', 'vendor_event_occurrence_id', (int) $listingElement['musicid']);
      $occurrence['vendor_event_occurrence_id'] = (int) $listingElement['musicid'];
      $occurrence['start'] = str_replace('T', ' ', (string) $listingElement['ListingDate'] );
      $occurrence['utc_offset'] = 0; 
      $occurrence['event_id'] = $event['id'];
      $occurrence['poi_id'] = 1;

      $event['EventOccurrence'][] = $occurrence;
      $this->notifyImporter( $event );
     
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
