<?php

/**
 *
 * @author rhodri
 *
 */
class LondonImporter
{

	/**
	 * @var Vendor
	 */
	private $_vendor;

	public function __construct( )
	{
		$this->_vendor = Doctrine::getTable( 'Vendor' )->getVendorByCityAndLanguage( 'london', 'en-GB' );
	}

	public function run()
	{
        $this->process( );
	}



	private function process()
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

        $query = Doctrine_Query::create()
	                           ->select( 'o.*, v.*, e.*' )
	                           ->from( 'SLLOccurrence o' )
	                           ->leftJoin( 'o.SLLVenue v' )
	                           ->leftJoin( 'o.SLLEvent e' );

        $zone = new DateTimeZone( 'Europe/London' );

        do
        {
            $pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );

            $items = $pager->execute( );

            foreach ( $items as $item )
            {
	            // insert venue
	            $poi = new Poi( );

	            $poi[ 'Vendor' ] = $this->_vendor;

	            $poi[ 'vendor_poi_id' ] = $item[ 'venue_id' ];

				$poi[ 'poi_name' ] = $item[ 'SLLVenue' ][ 'name' ];
				$poi[ 'house_no' ] = $item[ 'SLLVenue' ][ 'building_name' ];
				$poi[ 'street' ]   = $item[ 'SLLVenue' ][ 'address' ];
				$poi[ 'city' ]     = 'London';
				$poi[ 'zips' ]     = $item[ 'SLLVenue' ][ 'postcode' ];

				$poi[ 'country' ] = 'GBR';
                $poi[ 'country_code' ] = (string) 'GB';
                $poi[ 'local_language' ] = 'en-GB';

                $poi[ 'latitude' ]  = $item[ 'SLLVenue' ][ 'latitude' ];
                $poi[ 'longitude' ] = $item[ 'SLLVenue' ][ 'longitude' ];

                $poi[ 'email' ] = $item[ 'SLLVenue' ][ 'email' ];
                $poi[ 'url' ] = $item[ 'SLLVenue' ][ 'url' ];
                $poi[ 'phone' ] = $item[ 'SLLVenue' ][ 'phone' ];
                $poi[ 'public_transport_links' ] = $item[ 'SLLVenue' ][ 'travel' ];
                $poi[ 'openingtimes' ] = $item[ 'SLLVenue' ][ 'opening_times' ];


	            $poi->save( );


                // insert event
	            $event = new Event( );

	            $event[ 'Vendor' ] = $this->_vendor;

	            $event[ 'vendor_event_id' ] = $item[ 'event_id' ];

	            $event[ 'name' ] = $item[ 'SLLEvent' ][ 'title' ];

                $event->save( );


                // insert occurrence
                $occurrence = new EventOccurrence( );

                $occurrence[ 'vendor_event_occurrence_id' ] = $item[ 'id' ];

                $occurrence[ 'Event' ] = $event;
                $occurrence[ 'Poi' ] = $poi;

                $occurrence[ 'start' ] = $item[ 'date_start' ];

                // calc offset
                $timeOffset = $zone->getOffset( new DateTime( $item[ 'date_start' ], $zone ) );
                $occurrence[ 'utc_offset' ] = $timeOffset / 3600;

                $occurrence->save( );
            }

            $currentPage++;

        } while ( $pager->getLastPage( ) >= $currentPage );

	}

}