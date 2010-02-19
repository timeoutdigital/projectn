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
      $event['vendor_id'] = $this->vendor['id'];
      $event['vendor_event_id'] = (int) $listingElement['RecurringListingID'];

      $occurrence = $this->getRecord('EventOccurrence', 'vendor_event_occurrence_id', (int) $listingElement['musicid']);
      
      $occurrence['vendor_event_occurrence_id'] = (int) $listingElement['musicid'];
      $occurrence['start'] = str_replace('T', ' ', (string) $listingElement['ListingDate'] );
      $occurrence['utc_offset'] = 0;
      $occurrence['event_id'] = $event['id'];
      try
      {
        $placeid = (int) $listingElement['placeid'];
        $occurrence['Poi'] =  Doctrine::getTable('Poi')->findOneByVendorPoiId( $placeid );
      }
      catch( Exception $e )
      {
        $this->notifyImporterOfFailure($e, $occurrence, 'Could not find Lisbon Poi with vendor_poi_id of '. $placeid );
        continue;
      }
      $event['EventOccurrence'][] = $occurrence;

       //we will try to find the event with name
      if( is_null (  $event[ 'id' ] ) && Doctrine::getTable('Event')->findOneByName( (string) $listingElement['gigKey'] ) )
      {
          $this->notifyImporterOfFailure( new Exception( 'An event of this name already exists; suspicious...' ) , $event );
          continue;
      }
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
