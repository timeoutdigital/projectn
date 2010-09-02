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
                $lang = (string) $venueElement[ 'lang' ];

                // This mapper class is used for
                //     * importing from data entry database:
                //         - in this case, app_data_entry_onUpdateFindById is FALSE and vpid field in the input XML is interpreted as   vendor_poi_id
                //     * updating data_entry database
                //         - steps for update :
                //            * in projectn installation, we ran an export to create the XML files
                //            * for some cities, the runner configuration has exportForDataEntry: true value, for those cities, prepareExportXMLsForDataEntryTask is called to create
                //              new XML files with the modified IDs
                //         - in data_entry instance the modified XML files are used to import the data back to data_entry database ,in this case, app_data_entry_onUpdateFindById is
                //              TRUE and vpid field in the input XML is interpreted as the ID of the poi to be updated

                if( sfConfig::get( 'app_data_entry_onUpdateFindById' ) )
                {
                    $vendorPoiId = (int) $venueElement[ 'vpid' ]  ;

                    if( !$vendorPoiId )
                    {
                         $this->notifyImporterOfFailure( new Exception( 'VendorPoiId not found for poi name: ' . (string) @$venueElement->name . ' and city: ' . @$this->vendor['city'] ) );
                         continue;
                    }
                    $poi = Doctrine::getTable( 'Poi' )->find( $vendorPoiId );

                    if( $poi === false )
                    {
                       $this->notifyImporterOfFailure( new Exception( 'Could not found Poi to update!' ) );
                       continue;
                    }

                }
                else
                {
                     $vendorPoiId = (int) substr( (string) $venueElement[ 'vpid' ], 5) ;

                     if( !$vendorPoiId )
                     {
                         $this->notifyImporterOfFailure( new Exception( 'VendorPoiId not found for poi name: ' . (string) @$venueElement->name . ' and city: ' . @$this->vendor['city'] ) );
                         continue;
                     }

                     $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $vendorPoiId );

                     if( $poi === false )
                     {
                        $poi = new Poi();
                     }

                     $poi[ 'vendor_poi_id' ] = $vendorPoiId;
                     $poi->addMeta('vendor_poi_id' , $vendorPoiId);

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
