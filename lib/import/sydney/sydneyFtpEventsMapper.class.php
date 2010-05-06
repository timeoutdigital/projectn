<?php
/**
 * Sydney venues mapper
 *
 * @package projectn
 * @subpackage sydney.import.lib.unit
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class sydneyFtpEventsMapper extends DataMapper
{
  /**
   * @var SimpleXMLElement
   */
  private $feed;

  /**
   * @var projectnDataMapperHelper
   */
  private $dataMapperHelper;

  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @param SimpleXMLElement $feed
   */
  public function __construct( Vendor $vendor, SimpleXMLElement $feed )
  {
    $this->feed = $feed;
    $this->vendor = $vendor;
    $this->dataMapperHelper = new projectnDataMapperHelper( $vendor );
  }

  public function mapVenues()
  {
      
    foreach( $this->feed->event as $eventNode )
    {
      
      try
      {
          $event = $this->dataMapperHelper->getEventRecord( substr( md5( (string) $eventNode->Name ), 0, 9 ) );

          $event['review_date']             = $this->extractDate( (string) $eventNode->DateUpdated );
          $event['vendor_event_id']         = substr( md5( (string) $eventNode->Name ), 0, 9 );
          $event['name']                    = (string) $eventNode->Name;
          $event['description']             = (string) $eventNode->Description;
          $event['url']                     = (string) $eventNode->Website;
          $event['price']                   = stringTransform::formatPriceRange( (int) $eventNode->PriceFrom, (int) $eventNode->PriceTo );;
          $event['rating']                  = (string) $eventNode->Rating;
          $event['Vendor']                  = $this->vendor;

          

          $event->addVendorCategory( $this->extractVendorCategories( $eventNode ), $this->vendor );

          if ( (string) $eventNode->CriticsPick == 'True' )
            $event['CriticsChoiceProperty'] = true;
          if ( (string) $eventNode->Recommended == 'True' )
            $event['RecommendedProperty'] = true;
          if ( (string) $eventNode->Free == 'True' )
            $event['FreeProperty'] = true;

          $event->addMediaByUrl( (string) $eventNode->ImagePath );

          $poi = Doctrine::getTable( 'Poi')->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $eventNode->VenueID );

          if ( $poi !== false )
          {
              $occurrence = new EventOccurrence();
              $occurrence[ 'vendor_event_occurrence_id' ] = $event['vendor_event_id'] . ':' . $occurrence[ 'vendor_event_occurrence_id' ];
              $occurrence[ 'start_date' ] = $this->extractDate( (string) $eventNode->DateFrom, true );
              $occurrence[ 'end_date' ] = $this->extractDate( (string) $eventNode->DateTo, true );
              $occurrence[ 'utc_offset' ] = $this->vendor->getUtcOffset();
              $occurrence[ 'Poi' ] = $poi;

              $event['EventOccurrence']->delete();

              $event['EventOccurrence'][] = $occurrence;
          }

          $this->notifyImporter( $event );
      }
      catch( Exception $exception )
      {
          $this->notifyImporterOfFailure( $exception );
      }
    }
  }

  private function extractDate( $dateString, $dateOnly = false )
  {
    if ( empty( $dateString ) )
      return;
    
    // swap 29/03/2010 9:59:00 AM  to   03/29/2010 9:59:00 AM
    $dateString = preg_replace( '/([0-9]+)\/([0-9]+)\/([0-9]{4} [0-9]+\:[0-9]{2}\:[0-9]{2} [AMP]{2})/', '$2/$1/$3', $dateString );

    $date = new DateTime( $dateString );
    if ($dateOnly)
    {
        return $date->format( 'Y-m-d' );
    }
    else
    {
        return $date->format( 'Y-m-d H:i:s' );
    }
  }

  private function extractVendorCategories( SimpleXMLElement $eventNode )
  {
    $vendorCats = array();

    $parentCategory = (string) $eventNode->categories->parent_category_name;

    if( !empty( $parentCategory ) && $parentCategory != 'N/A' )
      $vendorCats[] = $parentCategory;

    foreach( $eventNode->categories->childrens->children_category as $childCategory )
      $vendorCats[] = (string) $childCategory;

    return $vendorCats;
  }



}
