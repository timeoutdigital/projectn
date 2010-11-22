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
              $poi['city']                          = ucwords( $this->vendor['city'] ); // HK feed's Cityname == District Name, we could use our Database Cityname
              $poi['district']                      = (string) $venueElement->district;
              $poi['country']                       = $this->vendor['country_code_long'];

              $poi['phone']                         = (string) $venueElement->phone;
              $poi['url']                           = (string) $venueElement->url;

              $poi['description']                   = $this->fixHtmlEntities( (string) $venueElement->description ); // Requires Double Entity Decoding
              $poi['openingtimes']                  = (string) $venueElement->opening_times;
              $poi['rating']                        = $this->roundNumberOrReturnNull( (string) $venueElement->rating );
              $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['street'], $poi['city'] ) );
              $poi['Vendor']                        = clone $this->vendor;

              // Categories
              if( isset( $venueElement->categories->category ) )
              {
                  $categories = array();
                  foreach( $venueElement->categories->category as $category ) stringTransform::mb_trim($categories[] = (string) $category); // TRIM as addVendorCategory Don't Trim!
                  $poi->addVendorCategory( $categories, $this->vendor->id );
              }
              // Extract and Apply Lat/Long
              $mapCode                              = (string) $venueElement->mapcode;
              $mapCodeSplit                         = explode( ',', $mapCode);
              if( is_array( $mapCodeSplit) && count( $mapCodeSplit ) == 2 )
              {
                  $poi->applyFeedGeoCodesIfValid( $mapCodeSplit[0], $mapCodeSplit[1] );
              }
              /*if( stringTransform::mb_trim( $mapCode ) != '' )
              {
                  $regEx = '/\&amp;ll=(.*?)\&amp;/i';
                  preg_match( $regEx, $mapCode, $geocodes );

                  if( is_array( $geocodes ) && count( $geocodes ) == 2 )
                  {
                      $geolatLong = explode(',', $geocodes[1] );
                      if( count( $geolatLong) == 2 )
                      {
                          $poi->applyFeedGeoCodesIfValid( $geolatLong[0], $geolatLong[1] );
                      }
                  }
              }*/

              // Done and Save
              $this->notifyImporter( $poi );

          }catch(Exception $exception)
          {
              $this->notifyImporterOfFailure($exception, isset($poi) ? $poi : null );
          }

          unset($poi, $categories, $vendor_venue_id);
          
      } // END FOREACH
  }

  protected function  getXMLFeedCleanUp( xmlDataFixer $xmlDataFixer )
  {
      $xmlDataFixer->removeMSWordHtmlTags( 'description', true );
      $xmlDataFixer->htmlEntitiesTag( 'description',  true );     
  }
}

?>
