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
  /**
   * @var projectNDataMapperHelper
   */
  protected $dataMapperHelper;

  protected $vendorCategories = array();

  protected $vendor;

  protected $type;

  public function __construct( $type = 'all' )
  {
    $this->type = $type;
    $this->setVendor();
    $this->setDefaultPoiCategory();
    $this->setDataMapperHelper();
  }

  /**
   * 
   */
  public function mapAll()
  {
    $this->loadCategories( );

    $zone = new DateTimeZone( 'Europe/London' );
    $from = date( 'Y-m-d' );
    $to   = date_add( new DateTime( ), new DateInterval( 'P6M' ) )->format( 'Y-m-d' );

    switch( $this->type )
    {
        case 'poi' :
            $this->processPois( $from, $to );
            break;
        case 'event' :
            $this->processEvents( $from, $to );
            break;
        case 'event-occurrence' :
            $this->processEventOccurrences( $from, $to );
            break;
        case 'all' :
            $this->processPois( $from, $to );
            $this->processEvents( $from, $to );
            $this->processEventOccurrences( $from, $to );
            break;
    }

  }

  private function setVendor()
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'london', 'en-GB' );
    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->vendor = $vendor;
  }

  private function setDataMapperHelper()
  {
    $this->dataMapperHelper = new projectNDataMapperHelper( $this->vendor );
  }

  private function setDefaultPoiCategory()
  {
    $this->defaultPoiCategory = Doctrine::getTable( 'PoiCategory' )->findOneByName( 'theatre-music-culture' );
  }

	/**
	 * load categories in memory so that they can be referenced later
	 */
	private function loadCategories( )
	{
            $items = Doctrine_Query::create( )->select( 'c.*' )
                                              ->from( 'SLLCategory c' )
                                              ->execute( );

            foreach ( $items as $item )
            {
              $id = $item[ 'id' ];
              $this->vendorCategories[ $id ] = $item;
            }
	}


        private function processPois( $from, $to )
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

                /*
                 * the following query must use inner joins, as we only want
                 * venues with actuall events (and occurrences) attached
                 */

		$query = Doctrine_Query::create( )->select( 'o.id, v.*, e.*' )
                                                  ->from( 'SLLOccurrence o' )
		                                  ->innerJoin( 'o.SLLVenue v' )
		                                  ->innerJoin( 'o.SLLEvent e' )
		                                  ->where( 'o.date_start >= ?', $from )
		                                  ->andWhere( 'o.date_start <= ?', $to )
                                                  ->groupBy( 'v.id, e.id' );
		do
		{
			$pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );

			$items = $pager->execute( array(), Doctrine::HYDRATE_ARRAY );

                        $currentVendorPoiId = null;
                        $currentVendorEventId = null;

			foreach ( $items as $item )
			{
                            $this->categories = $this->extractCategoriesFrom( $item );
                            $poi = $this->mapPoiFrom( $item );
                            $this->categories = null;
			}

			$currentPage++;
			unset( $items );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}


	private function processEvents( $from, $to )
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

                /*
                 * the following query must use inner joins, as we only want
                 * events with actuall occurrences attached
                 */

		$query = Doctrine_Query::create( )->select( 'o.id, e.*' )
		                                  ->from( 'SLLOccurrence o' )
		                                  ->innerJoin( 'o.SLLEvent e' )
		                                  ->where( 'o.date_start >= ?', $from )
		                                  ->andWhere( 'o.date_start <= ?', $to )
                                                  ->groupBy( 'e.id' );

		do
		{
			$pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );

			$items = $pager->execute( array(), Doctrine::HYDRATE_ARRAY );

                        $currentVendorPoiId = null;
                        $currentVendorEventId = null;

			foreach ( $items as $item )
			{
                            $this->categories = $this->extractCategoriesFrom( $item );
                            $event = $this->mapEventFrom( $item );
                            $this->categories = null;
			}

			$currentPage++;
			unset( $items );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}

        private function processEventOccurrences( $from, $to )
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

		$query = Doctrine_Query::create( )->select( 'o.*' )
		                                  ->from( 'SLLOccurrence o' )
		                                  ->where( 'o.date_start >= ?', $from )
		                                  ->andWhere( 'o.date_start <= ?', $to )
                                                  ->orderBy( 'o.venue_id, o.event_id' );

                do
		{
			$pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );
			$items = $pager->execute( array(), Doctrine::HYDRATE_ARRAY );

                        $currentVendorPoiId = null;
                        $currentVendorEventId = null;

			foreach ( $items as $item )
			{
                            if ( $currentVendorPoiId != $item[ 'venue_id' ] )
                            {
                                if ( isset( $poi ) && $poi instanceof Poi )
                                {
                                    $poi->free( true );
                                    unset( $poi );
                                }

                                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor[ 'id' ], $item[ 'venue_id' ], Doctrine::HYDRATE_ARRAY );

                                if ( isset( $poi ) && $poi === false )
                                {
                                    continue;
                                }
                                else
                                {
                                    $currentVendorPoiId = $item[ 'venue_id' ];
                                }
                            }

                            if ( $currentVendorEventId != $item[ 'event_id' ] )
                            {
                                if ( isset( $event ) && $event instanceof Event )
                                {
                                    $event->free( true );
                                    unset( $event );                                    
                                }

                                $event = Doctrine::getTable( 'Event' )->findOneByVendorIdAndVendorEventId( $this->vendor[ 'id' ], $item[ 'event_id' ], Doctrine::HYDRATE_ARRAY );
                                
                                if ( $event === false)
                                {
                                    continue;
                                }
                                else
                                {
                                    $currentVendorEventId = $item[ 'event_id'];
                                }
                            }

                            $this->mapEventOccurrenceFrom( $item, $poi[ 'id' ], $event[ 'id' ] );
			}

			$currentPage++;
                        unset( $items );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}


//        private function processEvents( )
//	{
//		$currentPage = 1;
//		$resultsPerPage = 5000;
//
//		$zone = new DateTimeZone( 'Europe/London' );
//		$from = date( 'Y-m-d' );
//		$to   = date_add( new DateTime( ), new DateInterval( 'P2Y' ) )->format( 'Y-m-d' );
//
//		$query = Doctrine_Query::create( )->select( 'o.*, v.*, e.*' )
//		                                  ->from( 'SLLOccurrence o' )
//		                                  ->leftJoin( 'o.SLLVenue v' )
//		                                  ->leftJoin( 'o.SLLEvent e' )
//		                                  ->where( 'o.date_start >= ?', $from )
//		                                  ->andWhere( 'o.date_start <= ?', $to )
//                                                  ->orderBy( 'o.venue_id, o.event_id' );
//
//
//
//		do
//		{
//			$pager = new Doctrine_Pager( $query, $currentPage, $resultsPerPage );
//
//			$items = $pager->execute( array(), Doctrine::HYDRATE_ARRAY );
//
//
//                        //$items = $query->execute( array(), Doctrine::HYDRATE_ARRAY );
//
//                        $currentVendorPoiId = null;
//                        $currentVendorEventId = null;
//
//			foreach ( $items as $item )
//			{
//
//                            $this->categories = $this->extractCategoriesFrom( $item );
//
//                            if ( $currentVendorPoiId != $item[ 'venue_id' ] )
//                            {
//                                $poi = $this->mapPoiFrom( $item );
//                                $currentVendorPoiId = $item[ 'venue_id' ];
//                            }
//
//                            if ( $currentVendorEventId != $item[ 'event_id' ] )
//                            {
//                               $event = $this->mapEventFrom( $item, $poi );
//                               $currentVendorEventId = $item[ 'event_id' ];
//                            }
//
//                            $eventOccurrence = $this->mapEventOccurrenceFrom( $item, $poi, $event );
//
//                            $this->categories = null;
//			}
//
//			$currentPage++;
//
//			// free memory
//			//$items->free( true );
//		}
//		while ( $pager->getLastPage( ) >= $currentPage );
//
//	}

  private function mapEventOccurrenceFrom( $item, $poiId, $eventId )
  {
    if ( empty( $poiId ) || empty( $eventId ) )
    {
        return;
    }

    $occurrence = $this->dataMapperHelper->getEventOccurrenceRecordById( $eventId, $item[ 'id' ] );

    $occurrence[ 'vendor_event_occurrence_id' ] = $item[ 'id' ];
    $occurrence[ 'event_id' ] = $eventId;
    $occurrence[ 'poi_id' ] = $poiId;
    $occurrence[ 'start_date' ] = $item[ 'date_start' ];
    $occurrence[ 'end_date' ]   = $item[ 'date_end' ];

    $occurrence[ 'utc_offset' ] = $this->vendor->getUtcOffset( $item[ 'date_start' ] );
    $this->notifyImporter( $occurrence );
    $occurrence->free( true );
    unset( $occurrence );


  }

  /**
   * @todo Try to get data from Occurrence, not from event, as events and
   * occurences are handled differenctly in London to our schema.
   * Ie. For London: Occurences take priority and Events may not have much info entered.
   */
  private function mapEventFrom( $item )
  {

    $event = $this->dataMapperHelper->getEventRecord( $item[ 'SLLEvent' ][ 'id' ] );
    $event[ 'Vendor' ] = clone $this->vendor;
    $event[ 'vendor_event_id' ] = $item[ 'SLLEvent' ][ 'id' ];
    $event[ 'name' ]        = $item[ 'SLLEvent' ][ 'title' ];
    $event[ 'description' ] = $item[ 'SLLEvent' ][ 'annotation' ];
    $event[ 'url' ]         = $item[ 'SLLEvent' ][ 'url' ];
    $event[ 'price' ]       = $item[ 'SLLEvent' ][ 'price' ];

    // Add Images
    if( isset( $item[ 'SLLEvent' ]['image_id'] ) && is_numeric( $item[ 'SLLEvent' ]['image_id'] ) )
    {
        $imageUrl = "http://toimg.net/managed/images/". $item[ 'SLLEvent' ]['image_id'] ."/i.jpg";
        $this->addImageHelper( $event, $imageUrl );
    }

    $event->addVendorCategory( $this->categories, $this->vendor['id'] );

    $this->notifyImporter( $event );
  }

  private function mapPoiFrom( $item )
  {
    $poi = $this->dataMapperHelper->getPoiRecord( $item[ 'SLLVenue' ][ 'id' ] );
    $poi[ 'Vendor' ]                 = clone $this->vendor;
    $poi[ 'vendor_poi_id' ]          = $item[ 'SLLVenue' ][ 'id' ];
    $poi[ 'poi_name' ]               = $item[ 'SLLVenue' ][ 'name' ];

    $fix = new removeCommaLondonFromEndOfString($item[ 'SLLVenue' ][ 'address' ]);
    $poi[ 'street' ]                 = $fix->getFixedString();

    $poi[ 'city' ]                   = 'London';
    $poi[ 'zips' ]                   = $item[ 'SLLVenue' ][ 'postcode' ];
    $poi[ 'country' ]                = 'GBR';
    $poi[ 'local_language' ]         = $this->vendor[ 'language' ];//'en-GB';

    $poi->applyFeedGeoCodesIfValid( $item[ 'SLLVenue' ][ 'latitude' ], $item[ 'SLLVenue' ][ 'longitude' ] );

    $poi[ 'email' ]                  = $item[ 'SLLVenue' ][ 'email' ];
    $poi[ 'url' ]                    = $item[ 'SLLVenue' ][ 'url' ];
    $poi[ 'phone' ]                  = $item[ 'SLLVenue' ][ 'phone' ];
    $poi[ 'public_transport_links' ] = $item[ 'SLLVenue' ][ 'travel' ];
    $poi[ 'openingtimes' ]           = $item[ 'SLLVenue' ][ 'opening_times' ];
    $poi['geocoderLookUpString']    = stringTransform::concatNonBlankStrings(',', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'], 'UK' ) );

    // Add Images
    if( isset( $item[ 'SLLVenue' ]['image_id'] ) && is_numeric( $item[ 'SLLVenue' ]['image_id'] ) )
    {
        $imageUrl = "http://toimg.net/managed/images/". $item[ 'SLLVenue' ]['image_id'] ."/i.jpg";
        $this->addImageHelper( $poi, $imageUrl );
    }

    $building_name                   = $item[ 'SLLVenue' ][ 'building_name' ];
    if( strlen($building_name) <= 32 )
    {
      $poi[ 'house_no' ] = $building_name;
    }
    else
    {
      $poi[ 'additional_address_details' ] = $building_name;
    }

    // refs #640 .. Should be array or string...
    // @todo: Should wrap Mapper inti try-catch, otherwise this is could break all london imports for poi/events
    if( $this->categories && ( is_array( $this->categories ) || is_string( $this->categories ) ) )
    {
        $poi->addVendorCategory( $this->categories, $this->vendor['id'] );
    }

    $this->notifyImporter( $poi );
  }

  private function extractCategoriesFrom( $item )
  {
    $sllEventCategoryId = $item[ 'SLLEvent' ][ 'master_category_id' ];
    $sllEventCategory = $this->vendorCategories[ $sllEventCategoryId ];
    $categories = $this->flattenCategoryTree( $sllEventCategory );
    return $categories;
  }

  private function flattenCategoryTree( $category=null, &$collectedCategories = array() )
  {
    if( !$category )
        return;

    array_unshift( $collectedCategories, $category['name'] );

    $parentCategoryId = $category[ 'parent_category_id' ];
    if( array_key_exists( $parentCategoryId, $this->vendorCategories ) )
    {
      $this->flattenCategoryTree( $this->vendorCategories[ $parentCategoryId ], $collectedCategories );
    }

    return $collectedCategories;
  }

  private function successfullySaved( Doctrine_Record $record )
  {
    return $record->exists();
  }

}
