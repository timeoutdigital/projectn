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

  public function __construct( )
  {
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
		$this->processEvents( );
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


    /**
     *
     */
	private function processEvents( )
	{
		$currentPage = 1;
		$resultsPerPage = 1000;

		$zone = new DateTimeZone( 'Europe/London' );
		$from = date( 'Y-m-d' );
		$to   = date_add( new DateTime( ), new DateInterval( 'P2Y' ) )->format( 'Y-m-d' );

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
        $this->categories = $this->extractCategoriesFrom( $item );

        $poi             = $this->mapPoiFrom(             $item );
        $event           = $this->mapEventFrom(           $item, $poi );
        $eventOccurrence = $this->mapEventOccurrenceFrom( $item, $poi, $event );

				//$poi->free( );
	   		//$event->free( );
				//$eventOccurrence->free( );
        $this->categories = null;
			}

			$currentPage++;

			// free memory
			$items->free( true );
		}
		while ( $pager->getLastPage( ) >= $currentPage );

	}

  private function mapEventOccurrenceFrom( $item, Poi $poi=null, Event $event=null )
  {
    if( !$poi || !$event )
      return;

    $occurrence = Doctrine::getTable( 'EventOccurrence' )->find( $item[ 'id' ] );
    if ( $occurrence === false ) $occurrence = new EventOccurrence( );

    $occurrence[ 'vendor_event_occurrence_id' ] = $item[ 'id' ];
    $occurrence[ 'Event' ] = $event;
    $occurrence[ 'Poi' ] = $poi;
    $occurrence[ 'start_date' ] = $item[ 'date_start' ];
    $occurrence[ 'end_date' ]   = $item[ 'date_end' ];
    $occurrence[ 'utc_offset' ] = $this->vendor->getUtcOffset( $item[ 'date_start' ] );
    $this->notifyImporter( $occurrence );

    if( $this->successfullySaved( $occurrence ) )
      return $occurrence;
  }

  private function mapEventFrom( $item, Poi $poi=null )
  {
    if( !$poi )
      return;

    $event = $this->dataMapperHelper->getEventRecord( $item[ 'event_id' ] );
    $event[ 'Vendor' ] = $this->vendor;
    $event[ 'vendor_event_id' ] = $item[ 'event_id' ];
    $event[ 'name' ]        = $item[ 'SLLEvent' ][ 'title' ];
    $event[ 'description' ] = $item[ 'SLLEvent' ][ 'annotation' ];
    $event[ 'url' ]         = $item[ 'SLLEvent' ][ 'url' ];
    $event[ 'price' ]       = $item[ 'SLLEvent' ][ 'price' ];
    $event->addVendorCategory( $this->categories, $this->vendor['id'] );

    $this->notifyImporter( $event );

    if( $this->successfullySaved( $event ) )
      return $event;
  }

  private function mapPoiFrom( $item )
  {
    $poi = $this->dataMapperHelper->getPoiRecord( $item[ 'venue_id' ] );
    $poi[ 'Vendor' ]                 = $this->vendor;
    $poi[ 'vendor_poi_id' ]          = $item[ 'venue_id' ];
    $poi[ 'poi_name' ]               = $item[ 'SLLVenue' ][ 'name' ];
    $poi[ 'street' ]                 = $item[ 'SLLVenue' ][ 'address' ];
    $poi[ 'city' ]                   = 'London';
    $poi[ 'zips' ]                   = $item[ 'SLLVenue' ][ 'postcode' ];
    $poi[ 'country' ]                = 'GBR';
    $poi[ 'local_language' ]         = $this->vendor[ 'language' ];//'en-GB';
    $poi[ 'latitude' ]               = $item[ 'SLLVenue' ][ 'latitude' ];
    $poi[ 'longitude' ]              = $item[ 'SLLVenue' ][ 'longitude' ];
    $poi[ 'email' ]                  = $item[ 'SLLVenue' ][ 'email' ];
    $poi[ 'url' ]                    = $item[ 'SLLVenue' ][ 'url' ];
    $poi[ 'phone' ]                  = $item[ 'SLLVenue' ][ 'phone' ];
    $poi[ 'public_transport_links' ] = $item[ 'SLLVenue' ][ 'travel' ];
    $poi[ 'openingtimes' ]           = $item[ 'SLLVenue' ][ 'opening_times' ];
    $poi['geoEncodeLookUpString']    = stringTransform::concatNonBlankStrings(',', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'], 'UK' ) );

    $building_name                   = $item[ 'SLLVenue' ][ 'building_name' ];
    if( strlen($building_name) <= 32 )
    {
      $poi[ 'house_no' ] = $building_name;
    }
    else
    {
      $poi[ 'additional_address_details' ] = $building_name;
    }

    $poi->addVendorCategory( $this->categories, $this->vendor['id'] );

    $this->notifyImporter( $poi );
    if( $this->successfullySaved( $poi ) )
      return $poi;
  }

  private function extractCategoriesFrom( $item )
  {
    $sllEventCategoryId = $item[ 'SLLEvent' ][ 'master_category_id' ];
    $sllEventCategory = $this->vendorCategories[ $sllEventCategoryId ];
    $categories = $this->flattenCategoryTree( $sllEventCategory );
    return $categories;
  }

  private function flattenCategoryTree( SLLCategory $category=null, &$collectedCategories = array() )
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
