<?php
class DataEntryEventsMapper extends DataMapper
{
    /**
    *
    * @var projectNDataMapperHelper
    */
    protected $dataMapperHelper;

    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    * @param string $city
    */
    public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null, $city = false )
    {
        if( is_string( $city ) )
            $vendor = Doctrine::getTable('Vendor')->findOneByCity( $city );

        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'Vendor not found.' );

        $this->dataMapperHelper = new projectNDataMapperHelper( $vendor );
        $this->geoEncoder           = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->vendor               = $vendor;
        $this->xml                  = $xml;
    }

    public function mapEvents()
    {
        foreach ( $this->xml as $eventElement )
        {
            try
            {
                foreach ( $eventElement->attributes() as $attribute => $value )
                {
                    if( $attribute == 'id' )
                    {
                        $vendorEventId = (int) substr( (string) $value,5) ;
                    }
                }

                foreach ( $eventElement->version->attributes() as $attribute => $value )
                {
                    if( $attribute == 'lang' )
                    {
                        $lang = (string) $value;
                    }
                }
                $event = Doctrine::getTable( 'Event' )->findByVendorEventIdAndVendorLanguage( $vendorEventId, $lang );

                if( !$event ) $event = new Event();

                $event[ 'review_date' ] = '';
                $event[ 'vendor_event_id' ] = $vendorEventId;
                $event[ 'name' ] = (string) $eventElement->name;
                $shortDescription = 'short-description';
                $event[ 'short_description' ] = (string) $eventElement->version->{$shortDescription};
                $event[ 'description' ] = (string) $eventElement->version->description;
                $event[ 'booking_url' ] = (string) $eventElement->version->booking_url;
                $event[ 'url']  = (string) $eventElement->version->url;
                $event[ 'price' ] = (string) $eventElement->version->price;
                $event[ 'rating' ] = (string) $eventElement->version->rating;
                $event[ 'vendor_id' ] =  $this->vendor[ 'id' ];

                $vendorCategory = 'vendor-category';

                foreach ( $eventElement->version->{$vendorCategory} as $vendorCategory)
                {
                 $event->addVendorCategory( trim( (string) $vendorCategory ) );
                }

                // before deleting occurrences get the ids of the current occurrences and reuse them while creating occurrences.
                // recyle the ids, save the planet!
                $occurrenceIdsOld = array();

                foreach ( $event['EventOccurrence'] as $occurrenceToDelete)
                {
                    $occurrenceIdsOld[] = $occurrenceToDelete[ 'id' ];
                }

                $occurrenceIdsOld = array_reverse( $occurrenceIdsOld );

                $event['EventOccurrence']->delete();

                $event['Vendor'] = $this->vendor;

                foreach ($eventElement->version->property as $property)
                {
                    foreach ($property->attributes() as $attribute)
                    {
                        $event->addProperty( (string) $attribute, (string) $property );
                    }
                }

                foreach ( $eventElement->version->media as $media )
                {
                    foreach ($media->attributes() as $key => $value)
                    {
                        if( (string) $key == 'mime-type' &&  (string) $value !='image/jpeg')
                        {
                            continue 2; //only add the images
                        }
                    }
                    try
                    {
                        $event->addMediaByUrl( (string) $media );
                    }
                    catch ( Exception $exception )
                    {
                         $this->notifyImporterOfFailure( $exception );
                    }
                }

                foreach ($eventElement->showtimes->place as $place)
                {
                    foreach ($place->attributes() as $attribute => $value )
                    {
                    	if( $attribute == 'place-id' )
                        {
                            $vendorPoiId = (int) substr( (string) $value,5) ;
                        }
                    }

                    $poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiIdAndVendorId( $vendorPoiId , $this->vendor['id'] );

                    if( !$poi )
                    {
                        $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi vendor poi id: ' . (string) $vendorPoiId . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                        continue;
                    }

                    foreach ( $place->occurrence as $xmlOccurrence )
                    {
                        $vendorOccurenceId  = $vendorEventId . '_' ;
                        $vendorOccurenceId .= $vendorPoiId . '_' ;
                        $vendorOccurenceId .= date( 'Ymd' , strtotime( (string) $xmlOccurrence->time->start_date ) ) . '_' ;
                        $vendorOccurenceId .= date( 'His' , strtotime( (string) $xmlOccurrence->time->event_time  ) ) ;

                        $occurrence = new EventOccurrence();
                        //reuse the id
                        $occurrence[ 'id' ] = array_pop( $occurrenceIdsOld );

                        $eventTime = ( string ) $xmlOccurrence->time->event_time ;
                        $endTime   = ( string ) $xmlOccurrence->time->end_time ;

                        $occurrence[ 'vendor_event_occurrence_id' ]     = $vendorOccurenceId;
                        $occurrence[ 'booking_url' ]                    = (string) $xmlOccurrence->booking_url;
                        $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->time->start_date;
                        $occurrence[ 'start_time' ]                     = empty( $eventTime ) ? null : $eventTime;
                        $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->time->end_date;
                        $occurrence[ 'end_time' ]                       = empty( $endTime ) ? null : $endTime;
                        $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
                        $occurrence[ 'Poi' ] = $poi;

                        $event['EventOccurrence'][] = $occurrence;
                    }

                }
               $event->save();
            }
            catch ( Exception  $exception)
            {
                $this->notifyImporterOfFailure($exception, $event);
            }

        }

    }
}