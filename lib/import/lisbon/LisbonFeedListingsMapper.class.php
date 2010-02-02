<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class LisbonFeedListingsMapper extends DataMapper
{
  /**
   * @var SimpleXMLElement
   */
  private $xml;

  public function __construct( SimpleXMLElement $xml )
  {
    $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'Lisbon', 'pt' );
    if( !$vendor )
    {
      throw new Exception( 'Vendor not found.' );
    }
    $this->vendor = $vendor;
    $this->xml = $xml;
  }

  public function mapVenues()
  {
    foreach( $this->xml->listings as $listingElement )
    {
      $event = new Event();
      $event['vendor_event_id'] = $listingElement[''];
      $event['name'] = '';
      $event['short_description'] = '';
      $event['description'] = '';
      $event['booking_url'] = '';
      $event['url'] = '';
      $event['price'] = '';
      $event['rating'] = '';
      $event['vendor_id'] = '';
      $this->notifyImporter($event);
      $event->free();
    }
  }
}
?>
