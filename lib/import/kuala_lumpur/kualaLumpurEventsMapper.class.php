<?php

class kualaLumpurEventsMapper extends DataMapper
{
  /**
   * @var Vendor
   */
  private $vendor;

  /**
   * @var SimpleXMLElement
   */
  private $xml;

  public function __construct( Vendor $vendor, SimpleXMLElement $xml)
  {
    $this->vendor = $vendor;
    $this->xml = $xml;
    $this->dataMapperHelper = new ProjectNDataMapperHelper( $vendor );
  }

  public function mapVenues()
  {
    foreach( $this->xml->eventDetails as $event )
    {
      $record = $this->dataMapperHelper->getEventRecord( (string) $event->id );

      $record['vendor_event_id']   = (string) $event->id;
      $record['name']              = (string) $event->name;
      $record['url']               = (string) $event->url;
      $record['short_description'] = (string) $event->short_description;
      $record['description']       = (string) $event->descripton;
      $record['price']             = (string) $event->price;
      $record['Vendor']            = $this->vendor;

      $record->addVendorCategory( array(
        $event->categories->category,
        $event->categories->subCategory,
      ), 
      $this->vendor );

      //$event->addMediaByUrl( (string) $event->medias->big_image );

      $this->notifyImporter( $record );
    }
  }
}
