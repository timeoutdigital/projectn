<?php
/**
 * Import all the UAE Events
 *
 * @package projectn
 * @subpackage uae.import.lib
 *
 *
 * @author Tim Bower <timbowler@timeout.com>
 *
 * @version 1.0.1
 *
 * @todo Create unit tests
 */
class ImportUaeEvents
{

    /**
     *
     * @var SimpleXMLElement
     */
    public $xmlObj;

    /**
     *
     * @var Vendor
     */
    public $vendorObj;

    /**
     *
     * @var logImport
     */
    public $poiLoggerObj;

    /**
     *
     * @var logImport
     */
    public $eventLoggerObj;
    
    
    /* Class constuctor
     *
     * @param processNyBcXml Simple XML object containing the feed
     * @param Vendor The vendor
     */
    public function  __construct(SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        $this->xmlObj = $xmlObj;
        $this->vendorObj = $vendorObj;
        $this->poiLoggerObj = new logImport($vendorObj, 'poi');
        $this->eventLoggerObj = new logImport($vendorObj, 'event');
        Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    }

    /**
     * Import all events and pois
     */
    public function import()
    {
        $venues = $this->xmlObj->xpath('//venues');
        $events = $this->xmlObj->xpath('//events');

        foreach ($venues[0] as $venue)
        {
                $this->importPois($venue);
        }

        $this->poiLoggerObj->save();



        foreach($events[0] as $event)
        {
            $this->importEvents($event);
        }

    }

    /**
     * Import the Pois
     *
     * @param SimpleXMLElement $xmlObj The XML node
     */
    public function importPois(SimpleXMLElement $xmlObj)
    {
   
        //Get the poi if it exists
        $poiObj = $this->getPoi($xmlObj);

        $isNew = $poiObj->isNew();

        $poiObj['Vendor']                       = $this->vendorObj;
        $poiObj['vendor_poi_id']                = (string) $xmlObj['id'];
        $poiObj['poi_name']                     = (string) $xmlObj->{'name'};
        $poiObj['url']                          = null;
        $poiObj['phone']                        = (string) $xmlObj->{'phone'};
        $poiObj['email']                        = (string) $xmlObj->{'email'};
        $poiObj['price_information']            = (string) $xmlObj->{'prices'};
        $poiObj['openingtimes']                 = (string) $xmlObj->{'hours'};
        $poiObj['public_transport_links']       = (string) $xmlObj->{'travel'};
        $poiObj['longitude']                    = (float)  $xmlObj->coordinates->{'longitude'};
        $poiObj['latitude']                     = (float)  $xmlObj->coordinates->{'latitude'};
        $poiObj['city']                         = trim(ucwords($this->vendorObj['city']));
        $poiObj['country']                      = 'ARE';
        $poiObj['district']                     = trim(ucwords((string) $xmlObj->{'neighbourhood'}));
        $poiObj['street']                       = trim((string) $xmlObj->{'travel'});
        $poiObj['description']                  = trim((string) $xmlObj->{'description'});

        $addressString = $poiObj['poi_name'] . ', ' .$poiObj['street'] . ', '. $poiObj['city'] . ', United Arab Emirates';

        //Set the geocode
        $poiObj->setGeoEncodeLookUpString($addressString);

        //Add properties
        $poiObj->AddProperty('timeout_link', (string) $xmlObj->{'landing_url'});




        //Set the vendors categories
        $category = trim((string) $xmlObj->{'mobile-section'}['value']);

        
        if($category)
        {
            $poiObj->addVendorCategory($category, $this->vendorObj['id']);
        }
          
        

        $logChangedFields = $poiObj->getModified();

         try
         {
              $poiObj->save();
         }

         catch(Exception $e)
         {
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$xmlObj['id']. " \n";
           $this->poiLoggerObj->addError($e, $poiObj, $log);
           echo "\n\n". $e->__toString();
        }

        //Count the item
        ( $isNew ) ? $this->poiLoggerObj->countNewInsert() : $this->poiLoggerObj->addChange( 'update', $logChangedFields );

        //Kill the object
        $poiObj->free();
    }

    /**
     * Import events
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function importEvents(SimpleXMLElement $xmlObj)
    {

        $eventObj = $this->getEvent($xmlObj);

        $isNew = $eventObj->isNew();

        $eventObj['name'] = (string) $xmlObj->{'name'};
        $eventObj['url'] = (string) $xmlObj->{'landing_url'};
        $eventObj['description'] = (string) $xmlObj->{'description'};
        $eventObj['price'] = (string) $xmlObj->{'prices'};
        $eventObj['vendor_event_id'] = (int) $xmlObj['id'];
        $eventObj['vendor_id'] = $this->vendorObj['id'];

        $category = (string) $xmlObj->{'mobile-section'}['value'];
        $eventObj->addVendorCategory($category, $this->vendorObj['id']);

        $logChangedFields = $eventObj->getModified();

        try
        {
            if($isNew)
            {
                $eventObj->save();
            }
        }
        catch(Exception $e)
        {
            $log =  "Error processing Event: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_event_id = ".$xmlObj['id']. " \n";
            $this->eventLoggerObj->addError($e, $eventObj, $log);
            echo "\n\n". $e->__toString();
        }

        //Count the item
        ( $isNew ) ? $this->eventLoggerObj->countNewInsert() : $this->eventLoggerObj->addChange( 'update', $logChangedFields );

        $this->AddEventOccurance($xmlObj->{'day-occurences'}, $eventObj);

        //Kill the object
        $eventObj->free();
    }


    /**
     * Check if the occurance exists
     *
     * @param SimpleXMLElement $Occurrences
     * @param Event $eventObj
     * @return Boolean Return true if it exists or false if it doesn't
     */
  public function checkForDuplicateOccurrence(SimpleXMLElement $Occurrences, Event $eventObj)
  {
      foreach($eventObj['EventOccurrence'] as $existingOccurances)
      {
          if( $existingOccurances['start_date'] ==  (string) $Occurrences->{'start_date'})
          {
              return true;
          }
      }

      return false;
  }



  public function AddEventOccurance(SimpleXMLElement $Occurrences, Event $eventObj)
  {
    //Loop throught the actual occurances now
    foreach ( $Occurrences->{'day-occurence'} as $occurrence )
    {
      //Check offurance exists
      if($this->checkForDuplicateOccurrence($occurrence, $eventObj))
      {
         continue;
      }

      $occurrenceObj = new EventOccurrence();
      $occurrenceObj[ 'utc_offset' ] = $this->vendorObj->getUtcOffset();
      $occurrenceObj[ 'start_date' ] = (string) $occurrence->{'start_date'};
      $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];

      //set poi
      $poiObj = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId( (string) $occurrence->{'venue_id'}, $this->vendorObj['id'] );

      $occurrenceObj[ 'Poi' ] = $poiObj;

      $occurrenceObj[ 'vendor_event_occurrence_id' ] = Doctrine::getTable('EventOccurrence')->generateVendorEventOccurrenceId((string) $eventObj['id'],  $occurrenceObj[ 'poi_id' ], $occurrenceObj[ 'start_date' ] );

      try
      {
        $occurrenceObj->save();
      }
      catch(Exception $e)
      {
          $log =  "Error processing Occurrence: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_event_occurrence_id = ".$occurrenceObj[ 'vendor_event_occurrence_id' ]. " \n";
          $this->occurrenceLoggerObj->addError($e, $occurrenceObj, $log);
          echo "\n\n". $e->__toString();
      }

      //Kill the object
     $occurrenceObj->free();

    }//end foreach
  }

    
    /**
     * Get a Poi object
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function getPoi(SimpleXMLElement $xmlObj)
    {
        $poiObj = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId((int)  $xmlObj['id'], $this->vendorObj['id']);

        if(!$poiObj)
        {
            $poiObj = new Poi();
        }

        return $poiObj;
    }


    /**
     * Get an event object
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function getEvent(SimpleXMLElement $xmlObj)
    {

        $eventObj = Doctrine::getTable('Event')->findOneByVendorEventIdAndVendorId((string)  $xmlObj['id'], $this->vendorObj['id']);

        if(!$eventObj)
        {
            $eventObj = new Event();
        }

        return $eventObj;
    }
}
?>
