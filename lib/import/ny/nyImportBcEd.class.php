<?php
/**
 * Class that imports NY Bars and clubs
 *
 *
 * @package projectn
 * @subpackage ny.import.lib
 *
 * @author Timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Comunications Ltd
 *
 * @version 1.0.0
 *
 *
 *
 *
 */
class nyImportBcEd extends nyFeedBaseMapper{

    /**
     * Constructor
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct(Vendor $vendor, $params) {
        parent::__construct($vendor, $params);

        $this->getXMLData();

        
    }

    /**
     * Import the Poi's
     */
    public function mapBCEDData()
    {
        //Loop over the xml
        foreach($this->xmlNodes as $poi)
        {
            //Only process if there is a record id and its not closed
            if($poi->xpath('@RECORDID') && $poi->{'closed.0'} != 'yes')
            {
                $this->importPoi($poi);
            }
        }
    }


    /**
     *
     * Test if the poi already exists
     *
     * @param <simpleXml> $poi
     * @return <boolean> Whether the poi has been found
     *
     */
    public function getPoi(SimpleXMLElement $poi)
    {

        //Check database for existing Poi by vendor id
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId($poi->{'ID'}, $this->vendor['id']);

        if($currentPoi)
        {
            return $currentPoi;
        }
        else
        {
            $currentPoi = new Poi();
        }

        return $currentPoi;
    }


    /**
     * import the bars from the feed
     *
     * @param SimpleXMLElement Poi node of the XML
     */
    public function importPoi(SimpleXMLElement $poi)
    {

        //Get the POI object
        $poiObj = $this->getPoi( $poi );

        try {
            //Add the main details that should not change
            $poiObj[ 'vendor_poi_id' ]           = (string) $poi->{'ID'};

            $streetAddress = (string) $poi->{'location.0'};

            $pos = strpos( $streetAddress, "between" );
            if( $pos !== false )
            {
                $betweenSection = substr( $streetAddress, $pos );
                $streetAddress = substr( $streetAddress, 0, $pos );
                $additionalAddressDetails = $betweenSection;
            }else
            {
               $streetInfo =  stringTransform::parseStreetName( $streetAddress );
               $streetAddress = $streetInfo[ 'street' ];
               $additionalAddressDetails = $streetInfo[ 'additional_address_details' ];
            }

            $poiObj[ 'additional_address_details' ] = $additionalAddressDetails;
            $poiObj[ 'street' ]                  = (string) trim( $streetAddress );
            $poiObj[ 'poi_name' ]                = (string) $poi->{'name.0'};
            $poiObj[ 'public_transport_links' ]  = (string) $poi->{'subway.0'};
            $poiObj[ 'local_language' ]          = substr( $this->vendor[ 'language' ], 0, 2 );
            $poiObj[ 'zips' ]                    = (string) $poi->{'zip.0'};
            $poiObj[ 'phone' ]                   = (string) $poi->{'phone.0'};
            $poiObj[ 'url' ]                     = (string) $poi->{'url.0'};

            //The B/C and E/D have different column names for the description
            if((string) $poi->{'BAR.body'})
            {
                $poiObj[ 'description' ]             = (string) $poi->{'BAR.body'};
            }
            else
            {
                $poiObj[ 'description' ]             = (string) $poi->{'body'};
            }



            $poiObj[ 'price_information' ]       = (string) $poi->{'prices.0'};
            $poiObj[ 'openingtimes' ]            = (string) $poi->{'hours.0'};

            //Get state and city - All forms of NY should be New York
            $stateCityArray                      = explode(',', (string) $poi->{'city.state.0'});
            if(count($stateCityArray) < 1)
            {
               $poiObj[ 'city' ]                 = 'New York';
            }
            else
            {

                $city = trim($stateCityArray[0]);

                if($city == 'NY')
                {
                    $city = 'New York';
                }


                $poiObj[ 'city' ]                 = $city;

            }


            if($poi->{'hood.shortcalc.0'})
            {
                $poiObj['district']               = (string) $poi->{'hood.shortcalc.0'};
            }
            else
            {
                $poiObj['district']               = (string) $poi->{'hood.0'};
            }


            $poiObj[ 'country' ]                 = 'USA';
            $poiObj[ 'Vendor' ]                  = $this->vendor;
            $poiObj[ 'geocode_look_up' ]         = stringTransform::concatNonBlankStrings( ', ', array( $poiObj[ 'street' ], $poiObj[ 'city' ], $poiObj[ 'zips' ], $poiObj[ 'country' ]   ) );

           $category = $this->extractCategory( $poi );
           $poiObj->addVendorCategory($category, $this->vendor['id']);

           //Add the cuisine property
           if( $this->params['type'] == 'restaurant' )
           {
               $cuisineString = (string) $poi->{'PrimaryCuisine'};
               $priceString = ": $";
               $findPriceString = strpos( (string) $cuisineString, $priceString );

               // Cuisine contains price info, fix as per refs #260
               if( $findPriceString !== false )
               {
                   $priceSectionString = substr( $cuisineString, $findPriceString + strlen( $priceString ) -1 );
                   $cuisineString = substr( $cuisineString, 0, $findPriceString );

                   // Create a 'price_general_remark' property to hold the price info.
                   if( (string) $priceSectionString && (string) substr( $priceSectionString, 0, 1 ) == "$" )
                   {
                      $poiObj->addProperty( 'price_general_remark', $priceSectionString );
                   }
               }
               $poiObj->addProperty( 'cuisine', $cuisineString );
           }


            ImportLogger::saveRecordComputeChangesAndLog( $poiObj );


           //Return Poi for testing
           return $poiObj;

        }

        catch(Doctrine_Validator_Exception $error)
        {
           $log =  "Error processing Poi: \n Vendor = ". $this->vendvendororObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           ImportLogger::getInstance()->addError($error, $poiObj, $log);

            return $poiObj;
        }

        catch(Exception $e)
        {
           $log =  "Error processing Poi: \n Vendor = ". $this->vendor['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           ImportLogger::getInstance()->addError($e, $poiObj, $log);

           return $poiObj;
        }
    }

    public function extractCategory( $poi )
    {
        $category = '';

        /**
         * Later changed on licensee's front end
         */
        switch( $this->params['type'] )
        {
          case 'bar_club':
            $category = 'Bar-club';
            break;

          case 'restaurant':
            $category = 'Restaurant';

            break;
        }

        if( !empty( $category ) )
        {
          $categoryNameSchemaDefinition = Doctrine::getTable('VendorPoiCategory')->getColumnDefinition('name');

          if( strlen( $category ) > $categoryNameSchemaDefinition['length'] )
          {
            throw new Exception( 'Category is too long: "' . $category  . '"' );
          }
        }

        return $category;
    }

    /**
     * Use FTP connection to Download Data based on $this->params variable
     */
    private function getXMLData()
    {
        // Download File
        $contents = file_get_contents( $this->ftpGetXMLFile() );
        // Clean the Feed
        $contents = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $contents);
        // Parse it as XML
        $this->xmlNodes = simplexml_load_string( $contents );
    }


}
?>
