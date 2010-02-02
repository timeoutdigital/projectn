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
class LisbonFeedListingsMapper extends LisbonFeedBaseMapper
{
  /**
   * @var Vendor
   */
  private $vendor;

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

  public function mapListings()
  {
    return;
    foreach( $this->xml->listings as $listingElement )
    {
      $event = array();
      //$this->mapAvailableData( $event, $listingElement );

      $event['booking_url'] = 'NA';
      $event['url'] = 'NA';
      $event['rating'] = 'NA';
      $event['vendor_id'] = $this->vendor['id'];

      $this->notifyImporter( new RecordData( 'Event', $event ) );
    }
  }

  /**
   * Return an array of mappings from xml attributes to event fields
   *
   * @return array
   */
  protected function getListingsMap()
  {
    return array(
      'musicid' => 'vendor_event_id',
      'gigKey' => 'name',
      'Notesline1' => 'short_description',
      'AnnotationForWeb' => 'description',
      'priceinfo' => 'price',
    );
  }
}
?>
