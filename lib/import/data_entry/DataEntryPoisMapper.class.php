<?php
class DataEntryPoisMapper extends DataMapper
{
    /**
    *
    * @var projectNDataMapperHelper
    */
    protected $dataMapperHelper;

    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
    * @var SimpleXMLElement
    */
    protected $xml;

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
          throw new Exception( 'Vendor not found.' );

        $this->dataMapperHelper = new projectNDataMapperHelper( $vendor );
        $this->geoEncoder           = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
        $this->vendor               = $vendor;
        $this->xml                  = $xml;
       // echo $this->vendor['name'];

    }

    public function mapPois()
    {
        foreach ( $this->xml as $venueElement )
        {
            foreach ($venueElement->attributes() as $attribute => $value)
            {
                if( $attribute == 'vpid' )
                {
                    $vendorPoiId = (int) substr( (string) $value,5) ;
                }

                if( $attribute == 'lang' )
                {
                    $lang = (string) $value;
                }
            }

            if( !$vendorPoiId )
            {
                continue;
            }

            $poi = $this->dataMapperHelper->getPoiRecord( $vendorPoiId );

            $poi[ 'vendor_poi_id' ] = $vendorPoiId;
            $poi[ 'poi_name' ] = (string) $venueElement->name;

            $geoPosition = 'geo-position';

            $poi[ 'longitude' ] = (string) $venueElement->{$geoPosition}->longitude;
            $poi[ 'latitude' ] =  (string) $venueElement->{$geoPosition}->latitude;

            $poi['review_date'] = '';
            $poi['local_language'] = $lang;
            $poi['street'] =  (string) $venueElement->address->street;
            $poi['house_no'] =  (string) $venueElement->address->houseno;
            $poi['zips'] =  (string) $venueElement->address->zip;
            $poi['district'] =  (string) $venueElement->contact->district;
            $poi['city'] =  (string) $venueElement->address->city;
            $poi['country'] =  (string) $venueElement->address->country;

            $poi['additional_address_details'] = '';
            $poi['vendor_id'] = $this->vendor['id'];
            $poi['phone'] =   (string) $venueElement->contact->phone;
            $poi['phone2'] =  (string) $venueElement->contact->phone2;
            $poi['fax'] =  (string) $venueElement->contact->fax;

            $poi['url'] =  (string) $venueElement->contact->url;
            $poi['email'] =  (string) $venueElement->contact->email;

            $vendorCategory = 'vendor-category';
            $shortDescription = 'short-description';
            $publicTransport = 'public-transport';
            $poi->addVendorCategory( (string) $venueElement->content->{$vendorCategory}, $this->vendor['id'] );
            $poi['keywords'] =  '';
            $poi['short_description'] =  (string) $venueElement->content->{$shortDescription};
            $poi['description'] =  (string) $venueElement->content->description;
            $poi['public_transport_links'] =  (string) $venueElement->content->{$publicTransport};
            $poi['price_information'] =  (string) $venueElement->content->price;
            $poi['openingtimes'] =  (string) $venueElement->content->openingtimes;
            $poi['star_rating'] =  (int) $venueElement->content->stars;
            $poi['rating'] =  (int) $venueElement->content->rating;
            $poi['geocode_look_up']  =  $poi['house_no'] .' ';
            $poi['geocode_look_up'] .=  $poi['street'] .' ';
            $poi['geocode_look_up'] .=  $poi['city'] .' ';
            $poi['geocode_look_up'] .=  $poi['country'] .' ';
            $poi['geocode_look_up'] .=  $poi['zips']  ;

            $poi->save();

            //$this->notifyImporter( $poi );

            /*
              $poi['review_date'] = '';
              $poi['local_language'] = 'pt';
              $poi['city'] = 'Lisbon';
              $poi['country'] = 'PRT';
              $poi['additional_address_details'] = $this->extractAddress( $venueElement );
              $poi['phone'] =  (string) $venueElement[ 'phone' ];
              $poi->addVendorCategory( (string) $venueElement[ 'tipo' ], $this->vendor['id'] );
              $poi['public_transport_links'] = $this->extractTransportLinkInfo( $venueElement );
              $poi['vendor_id'] = $this->vendor['id'];

              $poi['street']                     = trim( (string) $venueElement[ 'address' ], " ," );

              $poi['description']                = $this->extractAnnotation( $venueElement );
              $poi['additional_address_details'] = $this->extractAddress( $venueElement );
              $poi['phone2']                     = $this->extractPhoneNumbers( $venueElement );
              $poi['public_transport_links']     = $this->extractTransportLinkInfo( $venueElement );
              $poi['price_information']          = $this->extractPriceInfo( $venueElement );
              $poi['openingtimes']               = $this->extractTimeInfo( $venueElement );
              $poi['house_no']                   = $this->extractHouseNumberAndName( $venueElement );

              $poi->setGeoEncodeLookUpString( $this->getGeoEncodeData( $poi ) );
              $this->notifyImporter( $poi );*/

            //echo $poi['poi_name'];

        }
    }


}