<?php
class DataEntryPoisMapper extends DataEntryBaseMapper
{
    /**
    *
    * @param SimpleXMLElement $xml
    * @param geoEncode $geoEncoder
    * @param string $city
    */
    public function __construct( SimpleXMLElement $xml, geoEncode $geoEncoder = null, $city = false )
    {
        if( is_string( $city ) )
            $vendor = Doctrine::getTable('Vendor')->findOneByCity( $city );

        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'DataEntryPoisMapper:: Vendor not found.' );

        $this->dataMapperHelper = new projectNDataMapperHelper( $vendor );
        $this->geoEncoder           = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->vendor               = $vendor;
        $this->xml                  = $xml;
    }

    public function mapPois()
    {
        foreach ( $this->xml as $venueElement )
        {
            try
            {
                // Defaults
                $lang = $this->vendor['language'];

                foreach ($venueElement->attributes() as $attribute => $value)
                {
                    if( $attribute == 'vpid' )
                    {
                        if( sfConfig::get( 'app_data_entry_onUpdateFindById' ) )
                        {
                            $vendorPoiId =  (int) $value  ;
                        }else
                        {
                            $vendorPoiId = (int) substr( (string) $value,5) ;
                        }
                    }

                    if( $attribute == 'lang' )
                    {
                        $lang = (string) $value;
                    }
                }

                if( !$vendorPoiId )
                {
                     $this->notifyImporterOfFailure( new Exception( 'VendorPoiId not found for poi name: ' . (string) @$venueElement->name . ' and city: ' . @$this->vendor['city'] ) );
                     continue;
                }

                 //$poi = $this->dataMapperHelper->getPoiRecord( $vendorPoiId );

                 if( sfConfig::get( 'app_data_entry_onUpdateFindById' ) )
                 {
                     $poi = Doctrine::getTable( 'Poi' )->find( $vendorPoiId );
                 }else
                 {
                     $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $vendorPoiId );
                     if( $poi === false )
                     {
                        $poi = new Poi();
                     }

                    $poi[ 'vendor_poi_id' ] = $vendorPoiId;
                    $poi->addMeta('vendor_poi_id' , $vendorPoiId);
                 }
                 if( $poi === false )
                 {
                    $this->notifyImporterOfFailure( new Exception( '@todo write a messega here!' ) );
                    continue;
                 }

                $poi[ 'poi_name' ] = (string) $venueElement->name;

                $geoPosition = 'geo-position';
                $poi->applyFeedGeoCodesIfValid( (string) $venueElement->{$geoPosition}->latitude, (string) $venueElement->{$geoPosition}->longitude );

                // $poi['review_date'] = '';
                $poi['local_language'] = $lang;
                $poi['street'] =  (string) $venueElement->address->street;
                $poi['house_no'] =  (string) $venueElement->address->houseno;
                $poi['zips'] =  (string) $venueElement->address->zip;
                $poi['district'] =  (string) $venueElement->address->district;
                $poi['city'] =  (string) $venueElement->address->city;
                $poi['country'] =  (string) $venueElement->address->country;

                $poi['additional_address_details'] = '';
                $poi['Vendor'] = $this->vendor;
                $poi['phone'] =   (string) $venueElement->contact->phone;
                $poi['phone2'] =  (string) $venueElement->contact->phone2;
                $poi['fax'] =  (string) $venueElement->contact->fax;

                $poi['url'] =  (string) $venueElement->contact->url;
                $poi['email'] =  (string) $venueElement->contact->email;

                $vendorCategory = 'vendor-category';
                $shortDescription = 'short-description';
                $publicTransport = 'public-transport';
                if( $venueElement->version->content->{$vendorCategory} && isset( $venueElement->version->content->{$vendorCategory} ) )
                {
                    $poi->addVendorCategory( (string) $venueElement->version->content->{$vendorCategory}, $this->vendor['id'] );
                }
                $poi['keywords'] =  '';
                $poi['short_description'] =  (string) $venueElement->version->content->{$shortDescription};
                $poi['description'] =  (string) $venueElement->version->content->description;
                $poi['public_transport_links'] =  (string) $venueElement->version->content->{$publicTransport};
                $poi['price_information'] =  (string) $venueElement->version->content->price;
                $poi['openingtimes'] =  (string) $venueElement->version->content->openingtimes;
                $poi['star_rating'] =  (int) $venueElement->version->content->stars;
                $poi['rating'] =  (int) $venueElement->version->content->rating;

                $poi['geocode_look_up']  = stringTransform::concatNonBlankStrings(', ',
                        array( $poi['house_no'] ,
                               $poi['street'],
                               $poi['city'],
                               $poi['zips']
                        ) );

                if( isset( $venueElement->version->content->property ) )
                {
                    foreach ($venueElement->version->content->property as $property)
                    {
                        foreach ($property->attributes() as $attribute)
                        {
                            $poi->addProperty( (string) $attribute, (string) $property );
                        }
                    }
                }

                if( isset( $venueElement->version->content->media ) )
                {
                    foreach ( $venueElement->version->content->media as $media )
                    {
                        foreach ($media->attributes() as $key => $value)
                        {
                            if( (string) $key == 'mime-type' &&  (string) $value !='image/jpeg')
                            {
                                continue 2; //only add the images
                            }
                        }
                        try
                        {
                            // Generate Image [ http://www.timeout.com/projectn/uploads/media/event/$fileName ]
                            $urlArray = explode( '/', (string) $media );
                            // Get the Last IDENT
                            $imageFileName = array_pop( $urlArray );

                            $mediaURL = sprintf( 'http://www.timeout.com/projectn/uploads/media/poi/%s', $imageFileName );

                            $poi->addMediaByUrl( $mediaURL );
                        }
                        catch ( Exception $exception )
                        {
                             $this->notifyImporterOfFailure( $exception );
                        }
                    }
                }

               $this->notifyImporter( $poi );

           }
           catch (Exception  $exception )
           {
                $this->notifyImporterOfFailure($exception, $poi);
           }
        }
    }


}
