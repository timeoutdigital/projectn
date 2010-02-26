<?php
/**
 * Description of ImportUaeEventsclass
 *
 * @author timmy
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
        $this->poiLoggerObj = new logImport($vendorObj);
        $this->poiLoggerObj->setType('poi');
        $this->eventLoggerObj = new logImport($vendorObj);
        $this->eventLoggerObj->setType('event');
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
        $poiObj['url']                          = (string) $xmlObj->{'landing_url'};
        $poiObj['phone']                        = (string) $xmlObj->{'phone'};
        $poiObj['email']                        = (string) $xmlObj->{'email'};
        $poiObj['street']                       = (string) $xmlObj->{'neighourhood'};
        $poiObj['price_information']            = (string) $xmlObj->{'prices'};
        $poiObj['openingtimes']                 = (string) $xmlObj->{'hours'};
        $poiObj['public_transport_links']       = (string) $xmlObj->{'travel'};
        $poiObj['longitude']                    = (float) $xmlObj->coordinates->{'longitude'};
        $poiObj['latitude']                     = (float) $xmlObj->coordinates->{'latitude'};
        $poiObj['city']                         = $this->vendorObj['city'];
        $poiObj['country']                      = 'ARE';

        $addressString = $poiObj['poi_name'] . ', ' .$poiObj['street'] . ', '. $poiObj['city'] . ', United Arab Emirates';

        $poiObj->setGeoEncodeLookUpString($addressString);
        
        $category = (string) $xmlObj->{'mobile-section'}['value'];
          
        $poiObj->addVendorCategory($category, $this->vendorObj['id']);

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

        

    }

    /**
     * Import events
     *
     * @param SimpleXMLElement $xmlObj
     */
    public function importEvents(SimpleXMLElement $xmlObj)
    {

        $eventObj = $this->getEvent($xmlObj);

        $eventObj['name'] = (string) $xmlObj->{'name'};
        $eventObj['url'] = (string) $xmlObj->{'landing_url'};
        $eventObj['description'] = (string) $xmlObj->{'description'};
        $eventObj['price'] = (string) $xmlObj->{'prices'};
        $eventObj['vendor_event_id'] = (int) $xmlObj['id'];
        $eventObj['vendor_id'] = $this->vendorObj['id'];
        $eventObj->save();

       $this->AddEventOccurance($xmlObj->{'day-occurences'}, $eventObj);

        
    }



  public function AddEventOccurance(SimpleXMLElement $Occurrences, Event $eventObj)
  {
echo 'ere';

//Loop throught the actual occurances now
    foreach ( $Occurrences->{'day-occurence'} as $occurrence )
    {
      $occurrenceObj = new EventOccurrence();
      $occurrenceObj[ 'utc_offset' ] = '-05:00';
      $occurrenceObj[ 'start' ] = (string) $occurrence->{'start_date'};
      $occurrenceObj[ 'event_id' ] = $eventObj[ 'id' ];
     
      //set poi
      $poiObj = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId( (string) $occurrence->{'venue_id'}, $this->vendorObj['id'] );
      $occurrenceObj[ 'Poi' ] = $poiObj;

      $occurrenceObj[ 'vendor_event_occurrence_id' ] = Doctrine::getTable('EventOccurrence')->generateVendorEventOccurrenceId((string) $eventObj['id'],  $occurrenceObj[ 'poi_id' ], $occurrenceObj[ 'start' ] );


      $occurrenceObj->save();

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
