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
           // print_r( $eventElement );
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
           $event[ 'booking_url' ] = (string) $eventElement->version->booking_url;
           $event[ 'url']  = (string) $eventElement->version->url;
           $event[ 'price' ] = (string) $eventElement->version->price;
           $event[ 'rating' ] = (string) $eventElement->version->rating;
           $event[ 'vendor_id' ] =  $this->vendor[ 'id' ];

           $event['EventOccurrence']->delete();

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
                    $this->notifyImporterOfFailure( new Exception( 'Could not find a Poi with id: ' . (string) $xmlOccurrence->venue . ' for Event ' . $vendorEventId . ' in ' . $this->vendor['city'] . "." ) );
                    continue;
                }
                /** @todo find a way to create unique occurrence ids **/
                $vendor_occurence_id = 1;

                $occurrence = new EventOccurrence();
                $occurrence[ 'vendor_event_occurrence_id' ]     = $vendor_occurence_id;
                $occurrence[ 'booking_url' ]                    = (string) $xmlOccurrence->booking_url;
                $occurrence[ 'start_date' ]                     = (string) $xmlOccurrence->start_date;
                $occurrence[ 'start_time' ]                     = $this->extractTimeOrNull( (string) $xmlOccurrence->start_time );
                $occurrence[ 'end_date' ]                       = (string) $xmlOccurrence->end_date;
                $occurrence[ 'end_time' ]                       = $this->extractTimeOrNull( (string) $xmlOccurrence->end_time );
                $occurrence[ 'utc_offset' ]                     = $poi['Vendor']->getUtcOffset();
                $occurrence[ 'Poi' ] = $poi;

                $event['Vendor'] = $this->vendor;

           }
           die("");
        }


    }
}