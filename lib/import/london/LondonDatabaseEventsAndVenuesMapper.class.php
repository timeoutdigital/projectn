<?php
/**
 * London Database Events and Venues Mapper
 *
 * @package projectn
 * @subpackage london.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LondonDatabaseEventsAndVenuesMapper extends DataMapper
{

  public function __construct( )
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'london', 'en-GB' );

    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->vendor = $vendor;

    $this->defaultPoiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'theatre-music-culture' );
  }

  /**
   * 
   */
  public function mapAll()
  {
    //$this->processCategories( );
		$this->processEvents( );
  }

    

	/**
	 * @todo only import categories that have occurrences

	 */
	private function processCategories( )
	{
        $items = Doctrine_Query::create( )->select( 'c.*' )
                                          ->from( 'SLLCategory c' )
                                          ->execute( );

        foreach ( $items as $item )
        {
        	$category = new VendorEventCategory( );

        	$category[ 'Vendor' ] = $this->vendor;
        	$category[ 'name' ]   = $item[ 'name' ];
 
          $this->notifyImporter( $category );
        	//$category->free( );
        }

        //$items->free( true );
	}


    /**
     *
     */
	private function processEvents( )
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
				// insert/update poi
				$poi = Doctrine::getTable( 'Poi' )->findOneByVendorPoiId( $item[ 'venue_id' ] );

				if ( $poi === false ) $poi = new Poi( );

        $poi['PoiCategories'][] = $this->defaultPoiCategory;

				$poi[ 'Vendor' ] = $this->vendor;

				$poi[ 'vendor_poi_id' ] = $item[ 'venue_id' ];

				$poi[ 'poi_name' ] = $item[ 'SLLVenue' ][ 'name' ];
				$poi[ 'house_no' ] = $item[ 'SLLVenue' ][ 'building_name' ];
				$poi[ 'street' ] = $item[ 'SLLVenue' ][ 'address' ];
				$poi[ 'city' ] = 'London';
				$poi[ 'zips' ] = $item[ 'SLLVenue' ][ 'postcode' ];

				$poi[ 'country' ] = 'GBR';
				$poi[ 'local_language' ] = 'en-GB';

				$poi[ 'latitude' ] = $item[ 'SLLVenue' ][ 'latitude' ];
				$poi[ 'longitude' ] = $item[ 'SLLVenue' ][ 'longitude' ];

				$poi[ 'email' ] = $item[ 'SLLVenue' ][ 'email' ];
				$poi[ 'url' ] = $item[ 'SLLVenue' ][ 'url' ];
				//$poi[ 'phone' ] = stringTransform::formatPhoneNumber($item[ 'SLLVenue' ][ 'phone' ], '+44'); //@todo use dial code from vendor
				$poi[ 'phone' ] = $item[ 'SLLVenue' ][ 'phone' ]; //@todo use dial code from vendor
				$poi[ 'public_transport_links' ] = $item[ 'SLLVenue' ][ 'travel' ];
				$poi[ 'openingtimes' ] = $item[ 'SLLVenue' ][ 'opening_times' ];

        $this->notifyImporter( $poi );
        
        if( !$poi->exists() )
        {
          continue;
        }

				// insert/update event
				$event = Doctrine::getTable( 'Event' )->findOneByVendorEventId( $item[ 'event_id' ] );

				if ( $event === false ) $event = new Event( );


				$event[ 'Vendor' ] = $this->vendor;

				$event[ 'vendor_event_id' ] = $item[ 'event_id' ];

				$event[ 'name' ]        = $item[ 'SLLEvent' ][ 'title' ];
				$event[ 'description' ] = $item[ 'SLLEvent' ][ 'annotation' ];
				$event[ 'url' ]         = $item[ 'SLLEvent' ][ 'url' ];
				$event[ 'price' ]       = $item[ 'SLLEvent' ][ 'price' ];

        $this->notifyImporter( $event );
        if( !$event->exists() )
        {
          continue;
        }


				// insert/update occurrence
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

        $this->notifyImporter( $occurrence );

				// free memory
//				$poi->free( );
//				$event->free( );
//				$occurrence->free( );

			}

			$currentPage++;

			// free memory
			//$items->free( true );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}

}
 
 