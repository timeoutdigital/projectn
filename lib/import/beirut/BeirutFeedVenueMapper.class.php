<?php
/**
 * Beirut Venue Import Mapper
 *
 * @package projectn
 * @subpackage lib.import
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class BeirutFeedVenueMapper extends BeirutFeedBaseMapper
{

    public function mapVenues()
    {
        foreach( $this->xmlNodes->venue as $xmlNode )
        {echo '#';
            try
            {
                $vendorPoiID = $this->clean( (string)$xmlNode['id'] );
                $poi = Doctrine::getTable( 'Poi' )->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $vendorPoiID );
                if( $poi === false )
                {
                    $poi = new Poi();
                }

                // Map data
                $poi['Vendor']                      = $this->vendor;
                $poi['vendor_poi_id']               = $vendorPoiID;

                $poi['poi_name']                    = $this->clean( (string)$xmlNode->name );
                $poi['short_description']           = $this->clean( (string)$xmlNode->short_description );
                $poi['description']                 = $this->clean( (string)$xmlNode->description );

                $poi['house_no']                    = $this->clean( (string)$xmlNode->house_no );
                $poi['street']                      = $this->clean( (string)$xmlNode->street );
                $poi['city']                        = "Beirut";
                $poi['district']                    = $this->clean( (string)$xmlNode->district );
                $poi['country']                     = $this->vendor['country_code_long'];
                $poi['zips']                        = $this->clean( (string)$xmlNode->postcode );
                $poi['additional_address_details']  = $this->clean( (string)$xmlNode->additional_address_details );
                $poi['public_transport_links']      = $this->clean( (string)$xmlNode->public_transport );
                
                $poi['openingtimes']                = $this->clean( (string)$xmlNode->poopening_timestcode );
                $poi['email']                       = $this->clean( (string)$xmlNode->email );
                $poi['phone']                       = $this->clean( (string)$xmlNode->phone );
                $poi['phone2']                      = $this->clean( (string)$xmlNode->phone2 );
                $poi['fax']                         = $this->clean( (string)$xmlNode->fax );
                $poi['url']                         = $this->clean( (string)$xmlNode->url );
                $poi['provider']                    = $this->clean( (string)$xmlNode->provider );
                $poi['rating']                      = $this->roundNumberOrNull( $this->clean( (string)$xmlNode->rating ) );
                
                // use Feed Geocode
                $poi->applyFeedGeoCodesIfValid( $this->clean( (string)$xmlNode->lat ), $this->clean( (string)$xmlNode->long ) );

                /**
                 * Add City as Town Property, Beirut feed has lots of Cities refering out outside nearby city
                 * We will put this as Town in property and City In mapper column to Beirut static value....
                 */
                if( $this->clean( (string)$xmlNode->city ) != '' && strtolower( $this->clean( (string)$xmlNode->city ) ) !== 'beirut' )
                {
                    $poi->addProperty( 'Town',$this->clean( (string)$xmlNode->city ) );
                }

                if( $this->clean( (string)$xmlNode->timeout_url ) != '' )
                {
                    $poi->setTimeoutLinkProperty( $this->clean( (string)$xmlNode->timeout_url ));
                }
                
                $this->addVendorCategory( $poi, $xmlNode );

                $this->notifyImporter( $poi );

            } catch (Exception $e ) {

                $this->notifyImporterOfFailure( $e, isset( $poi ) ? $poi : null );
                
            }
        }
    }
}