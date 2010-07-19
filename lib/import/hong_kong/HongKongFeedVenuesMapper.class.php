<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class HongKongFeedVenuesMapper extends HongKongFeedBaseMapper
{
  public function mapPlaces()
  {
      foreach ($this->fixIteration($this->xml->channel->venues->venue)  as $venueElement)
      {
          try{

              $vendor_venue_id = $venueElement['id']; // get ID From Attribute

              $poi = Doctrine::getTable( 'Poi' )->findByVendorPoiIdAndVendorLanguage( $vendor_venue_id, 'en-HK' );

              if( !$poi )
                  $poi = new Poi();

              // Map Columns
              $poi['vendor_poi_id']                 = (string) $vendor_venue_id;
              $poi['poi_name']                      = (string) $venueElement->name;
              $poi['street']                        = (string) $venueElement->street;
              $poi['city']                          = (string) $venueElement->city;
              $poi['district']                      = (string) $venueElement->district;
              $poi['country']                       = "HKG";

              $poi['phone']                         = (string) $venueElement->phone;
              $poi['url']                           = (string) $venueElement->url;

              $poi['description']                   = $this->fixHtmlEntities( (string) $venueElement->description ); // Requires Double Entity Decoding
              $poi['openingtimes']                  = (string) $venueElement->opening_times;
              $poi['rating']                        = $this->roundNumberOrReturnNull( (string) $venueElement->rating );
              $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['street'], $poi['city'] ) );
              $poi['Vendor']                        = clone $this->vendor;

              // Categories
              $categories = array();
              foreach( $venueElement->categories->category as $category ) stringTransform::mb_trim($categories[] = (string) $category); // TRIM as addVendorCategory Don't Trim!
              $poi->addVendorCategory( $categories, $this->vendor->id );
                  
              // Timeout Link
              //if( (string) $venueElement->timeout_url != "" )
                      //$poi['TimeoutLinkProperty'] = (string) $venueElement->timeout_url;

              // Done and Save
              $this->notifyImporter( $poi );
              echo '.'; // Went OK

          }catch(Exception $exception)
          {
              $this->notifyImporterOfFailure($exception);
              echo '#'; // Exception
              //print_r($exception->getMessage() . ' - VENDOR POI ID@ '.$poi['vendor_poi_id'].PHP_EOL);
          }

          unset($poi, $categories, $vendor_venue_id);
          
      } // END FOREACH
  }
}

?>
