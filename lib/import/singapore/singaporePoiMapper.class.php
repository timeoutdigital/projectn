<?php
/**
 * Maps singapore Pois for the Importer
 *
 * @package projectn
 * @subpackage singapore.import.lib
 *
 * @author Emre Basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 *
 */
class singaporePoiMapper extends DataMapper
{

    public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null )
    {
        $this->vendor     = Doctrine::getTable( 'Vendor' )->findOneByCityAndLanguage( 'singapore', 'en-US' );
        $this->geoEncoder = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->xml        = $xml;
    }

 public function mapVenues()
  {

    for( $i=0, $venueElement = $this->xml->venue[ 0 ]; $i<$this->xml->venue->count(); $i++, $venueElement = $this->xml->venue[ $i ] )
    {
        $poi = null;
        try
        {
            $poi = Doctrine::getTable( 'poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $this->clean( (string) $venueElement->id ) );

            if( $poi === false )
            {
                $poi = new Poi();
            }

            $poi[ 'vendor_poi_id' ]              = (string) $venueElement->id;
            $poi[ 'review_date' ]                = date("Y-m-d H:i:s" , strtotime( (string) $venueElement->data_change ) ) ;
            $poi[ 'local_language' ]             =  $this->vendor[ 'language' ] ;
            $poi[ 'poi_name' ]                   = (string) $venueElement->name;
            $poi[ 'country' ]                    = 'SGP';
            $poi[ 'url' ]                        = (string) $venueElement->website;
            $poi[ 'description' ]                = (string) $venueElement->excerpt;
            //$poi[ 'price_information' ]          = $this->extractPriceInformation( $venueElement );
            $poi[ 'openingtimes' ]               = (string) $venueElement->opentime;
            $poi[ 'vendor_id' ]                  = $this->vendor[ 'id' ];


            //$address = $this->getAddressInfo( $venueElement );
            $address = $venueElement->addresses->address_slot;
            if ( $address )
            {
                $poi->applyFeedGeoCodesIfValid( (string) $address->mm_lat, (string) $address->mm_lon );

                $poi[ 'public_transport_links' ]     = $this->extractPublicTransportLinks( $address );
                $poi[ 'phone' ]                      = '+65 ' .  (string) $address->phone;
                $poi[ 'additional_address_details' ] = (string) $address->location;
                $poi[ 'zips' ]                       = (string) $address->postcode;
                $poi[ 'street' ]                     = trim( (string) $address->address, ", " );
                $poi[ 'city' ]                       = $this->vendor['city'];

                $poi->setGeoEncodeLookUpString( $this->extractGeoEncodeLookupString( $poi ) );
            }
            //$poi->applyFeedGeoCodesIfValid( (string) address->mm_lat, (string) $address->mm_lon );
            //@todo test the rest of this function
            $poi->addProperty( 'Critics_choice', (string) $venueElement->critic_choice );
            $poi->addProperty( 'Timeout_link', (string) $venueElement->link );

            $categoriesArray = array();
            if ( (string) $venueElement->section != '' )  $categoriesArray[] = (string) $venueElement->section;
            if ( (string) $venueElement->category != '' ) $categoriesArray[] = (string) $venueElement->category;
            if ( 0 < count( $categoriesArray ) )
            {
              $poi->addVendorCategory( $categoriesArray,  $this->vendor[ 'id' ]);
            }

            // add images
            $poi->addMediaByUrl( (string) $venueElement->highres );
            $poi->addMediaByUrl( (string) $venueElement->large_image );
            $poi->addMediaByUrl( (string) $venueElement->image );
            $poi->addMediaByUrl( (string) $venueElement->thumb );
            $poi->addMediaByUrl( (string) $venueElement->thumbnail );

            $this->notifyImporter( $poi );
        }
        catch ( Exception  $exception)
        {
            echo $exception;

            $this->notifyImporterOfFailure( $exception, $poi );
        }

    }
  }

    private function extractPublicTransportLinks( $address )
    {
      $transportInfo = array();

      if( isset(  $address->near_station ) )
      {
        $nearStation = (string) $address->near_station;
        $transportInfo[] = 'Near station: ' . $nearStation;
      }

      if( isset( $address->buses ) )
      {
          $buses = (string) $address->buses;
          $transportInfo[]= 'Buses: ' . $buses;
      }

      return stringTransform::concatNonBlankStrings( ', ', $transportInfo );
    }


    private function extractGeoEncodeLookupString( Poi $poi )
    {
      return stringTransform::concatNonBlankStrings( ', ', array( $poi[ 'street' ], $poi[ 'additional_address_details' ], $poi[ 'zips' ], $poi[ 'city' ]  ) );
    }
}