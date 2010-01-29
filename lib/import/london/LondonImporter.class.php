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

		if (! $this->_vendor instanceof Vendor)
		{
			throw new Exception( 'Cannot load Vendor' );
		}
	}

	public function run( )
	{
		$this->process( );
	}

	private function process( )
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

		$zone = new DateTimeZone( 'Europe/London' );
		$from = date( 'Y-m-d' );
		$to   = date_add( new DateTime( ), new DateInterval( 'P2M' ) )->format( 'Y-m-d' );

		$query = Doctrine_Query::create( )->select( 'o.*, v.*, e.*' )
		                                  ->from( 'SLLOccurrence o' )
		                                  ->leftJoin( 'o.SLLVenue v' )
		                                  ->leftJoin( 'o.SLLEvent e' )
		                                  ->where( 'o.date_start >= ?', $from )
		                                  ->andWhere( 'o.date_start <= ?', $to );

		do
		{
			$pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );

			$items = $pager->execute( );

			foreach ( $items as $item )
			{
				$poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( $item[ 'venue_id' ] );

				if ( $poi === false ) $poi = new Poi( );


				$poi[ 'Vendor' ] = $this->_vendor;

				$poi[ 'vendor_poi_id' ] = $item[ 'venue_id' ];

				$poi[ 'poi_name' ] = $item[ 'SLLVenue' ][ 'name' ];
				$poi[ 'house_no' ] = $item[ 'SLLVenue' ][ 'building_name' ];
				$poi[ 'street' ] = $item[ 'SLLVenue' ][ 'address' ];
				$poi[ 'city' ] = 'London';
				$poi[ 'zips' ] = $item[ 'SLLVenue' ][ 'postcode' ];

				$poi[ 'country' ] = 'GBR';
				$poi[ 'country_code' ] = ( string ) 'GB';
				$poi[ 'local_language' ] = 'en-GB';

				$poi[ 'latitude' ] = $item[ 'SLLVenue' ][ 'latitude' ];
				$poi[ 'longitude' ] = $item[ 'SLLVenue' ][ 'longitude' ];

				$poi[ 'email' ] = $item[ 'SLLVenue' ][ 'email' ];
				$poi[ 'url' ] = $item[ 'SLLVenue' ][ 'url' ];
				$poi[ 'phone' ] = $item[ 'SLLVenue' ][ 'phone' ];
				$poi[ 'public_transport_links' ] = $item[ 'SLLVenue' ][ 'travel' ];
				$poi[ 'openingtimes' ] = $item[ 'SLLVenue' ][ 'opening_times' ];

				$poi->save( );


				// insert event
				$event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( $item[ 'event_id' ] );

				if ( $event === false ) $event = new Event( );


				$event[ 'Vendor' ] = $this->_vendor;

				$event[ 'vendor_event_id' ] = $item[ 'event_id' ];

				$event[ 'name' ] = $item[ 'SLLEvent' ][ 'title' ];

				$event->save( );



				// insert occurrence
				$occurrence = Doctrine::getTable( 'EventOccurrence' )->find( $item[ 'id' ] );

				if ( $occurrence === false ) $occurrence = new EventOccurrence( );


				$occurrence[ 'vendor_event_occurrence_id' ] = $item[ 'id' ];

				$occurrence[ 'Event' ] = $event;
				$occurrence[ 'Poi' ] = $poi;

				$occurrence[ 'start' ] = $item[ 'date_start' ];
				$occurrence[ 'end' ]   = $item[ 'date_end' ];

				// calc offset
				$timeOffset = $zone->getOffset( new DateTime( $item[ 'date_start' ], $zone ) );
				$occurrence[ 'utc_offset' ] = $timeOffset / 3600;

				$occurrence->save( );



				// free memory
				$poi->free( );
				$event->free( );
				$occurrence->free( );
			}

			$currentPage++;

			// free memory
			$items->free( true );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}

}