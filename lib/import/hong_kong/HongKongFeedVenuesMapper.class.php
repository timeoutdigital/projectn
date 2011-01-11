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

              //map the vendor first so that we can reference to $poi['Vendor'] rather than $this->vendor in order to not use the
              //guise later (if we use $this->vendor, then apply guise and then assign it to $poi['Vendor'], the assignmement seems
              //to always reload the vendor again, meaning we would loose the guise. but we still need it on presave on the model
              $poi['Vendor'] = clone $this->vendor;

              //this call is probably not necessary as the assignement above seems to require a fresh lazy lode of the vendor anyway
              $poi['Vendor']->stopUsingGuise();

              //yes we could use useGuiseIfExists() instead here, but since its only 2 options lets deal with them here rather than
              //search for it every time in the db
              if ( in_array( (string) $venueElement->district, array( 'Macau', 'Shenzhen' ) ) )
              {
                  $poi['Vendor']->useGuise( (string) $venueElement->district );
              }

              $poi['vendor_poi_id']                 = (string) $vendor_venue_id;
              $poi['poi_name']                      = (string) $venueElement->name;
              $poi['street']                        = (string) $venueElement->street;
              $poi['city']                          = ucwords( $poi['Vendor']['city'] ); // HK feed's Cityname == District Name, we could use our Database Cityname
              $poi['district']                      = (string) $venueElement->district;
              $poi['country']                       = $poi['Vendor']['country_code_long'];

              $poi['phone']                         = (string) $venueElement->phone;
              $poi['url']                           = (string) $venueElement->url;

              $poi['description']                   = $this->fixHtmlEntities( (string) $venueElement->description ); // Requires Double Entity Decoding
              $poi['openingtimes']                  = (string) $venueElement->opening_times;
              $poi['rating']                        = $this->roundNumberOrReturnNull( (string) $venueElement->rating );
              $poi['geocode_look_up']               = stringTransform::concatNonBlankStrings(', ', array( $poi['street'], $poi['city'] ) );

              // Categories
              if( isset( $venueElement->categories->category ) )
              {
                  $categories = array();
                  foreach( $venueElement->categories->category as $category ) stringTransform::mb_trim($categories[] = (string) $category); // TRIM as addVendorCategory Don't Trim!
                  $poi->addVendorCategory( $categories, $poi['Vendor']['id'] );
              }
              // Extract and Apply Lat/Long
              $mapCode                              = (string) $venueElement->mapcode;
              $mapCodeSplit                         = explode( ',', $mapCode);
              if( is_array( $mapCodeSplit) && count( $mapCodeSplit ) == 2 )
              {
                  $poi->applyFeedGeoCodesIfValid( $mapCodeSplit[0], $mapCodeSplit[1] );
              }

              // #837 Any poi that have ( will be ignored as this may refer to another area code!
              $poi['phone'] = ($this->_isValidPhoneNumber( $poi[ 'phone' ] ) ) ? $poi['phone'] : null;
              $poi['phone2'] = ($this->_isValidPhoneNumber( $poi[ 'phone2' ] ) ) ? $poi['phone2'] : null;
              
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

  private function _isValidPhoneNumber( $subject )
  {
      if( $subject == null || trim( $subject ) == '' )
      {
          return false;
      }

      // return false if ( found
      return ( strpos( $subject, ')' ) === false );
  }
}

?>
