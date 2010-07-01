<?php
/**
 * Class that imports Chicago Bars, clubs, eating and drinking
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
class chicagoImportBcEd {

    /**
     * Process simple XML for B/C
     *
     * @var processNyBcXml
     */
    public $bcObj;

    /**
     * NY Vendor
     *
     * @var Vendor
     */
    public $vendorObj;   


    public function  __construct(processNyBcXml $bcObj, Vendor $vendorObj )
    {
        $this->bcObj = $bcObj;
        $this->vendorObj = $vendorObj;
        ImportLogger::getInstance()->setVendor( $vendorObj );
        Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    }

    /**
     * Import the Poi's
     */
    public function import()
    {
        //Loop over the xml
        foreach($this->bcObj->xmlObj as $poi)
        {
            //Only process if there is a record id and its not closed
            if($poi->xpath('@RECORDID') && $poi->{'closed'} == '')
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
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId( (string) $poi->{'ID'}, $this->vendorObj['id'] );

        if( !$currentPoi )
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
        $poiObj = $this->getPoi($poi);

        $isNew = $poiObj->isNew();


        try {

            //Add the main details that should not change
            $poiObj[ 'vendor_poi_id' ]           = (string) (string) $poi->{'ID'};

            $poiObj[ 'street' ]                  = (string) $poi->{'location'};
            $poiObj[ 'poi_name' ]                = (string) $poi->{'name'};
            $poiObj[ 'public_transport_links' ]  = (string) $poi->{'cta'};
            $poiObj[ 'local_language' ]          = substr( $this->vendorObj[ 'language' ], 0, 2 );
            $poiObj[ 'zips' ]                    = (string) $poi->{'zip'};
            $poiObj[ 'description' ]             = (string) $poi->{'body'};
            $poiObj[ 'price_information' ]       = (string) $poi->{'prices'};
            $poiObj[ 'openingtimes' ]            = (string) $poi->{'hours'};
            $poiObj['url']                       = (string) $poi->{'url'};


            $stateCityArray                      = explode(',', (string) $poi->{'city.state'});
            $poiObj[ 'city' ]                    = trim($stateCityArray[0]);
            $poiObj[ 'district' ]                = (string) $poi->{'hood'};
            $poiObj[ 'country' ]                 = 'USA';
            $poiObj[ 'Vendor' ]                  = $this->vendorObj;

            $poiObj[ 'geocode_look_up' ]         = stringTransform::concatNonBlankStrings(',', array(
                $poiObj['poi_name'],
                $poiObj['street'],
                $poiObj['zips'],
                $poiObj['city']
            ));

            /**
             * Try to convert the phone number
             */
            try{

                $phoneNumber = strtolower((string) $poi->{'phone'});
                
                if( $phoneNumber != "no phone")
                {
                    $poiObj[ 'phone' ] = stringTransform::formatPhoneNumber($phoneNumber , $this->vendorObj['inernational_dial_code']);
                }

            }
            catch(Exception $e)
            {
                echo "Phone number error \n \n";
                $log =  "Error processing Phone number for Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
                ImportLogger::getInstance()->addError($e, $poiObj, $log);
            }
            

             //Add category
               if((string) $poi->{'category'})
               {
                   $poiObj->addVendorCategory((string) $poi->{'category'}, $this->vendorObj['id']);
               }
               

           //Add the properties
           if((string) $poi->{'cuisine.1'})
           {
               $cuisineString = (string) $poi->{'cuisine.1'};
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

           if((string) $poi->{'features'} != '')
           {
                $featuresString = (string) $poi->{'features'};
                
                // Clean up features property, to remove the string "Cheap (entrees under $10)" and
                // ...associated new line characters, if you want to clean this up, feel free. refs #251
                $featuresString = str_replace( "\nCheap (entrees under $10)", "", $featuresString );
                $featuresString = str_replace( "Cheap (entrees under $10)", "", $featuresString );
                
                $poiObj->addProperty( 'features', trim( $featuresString, "\n " ) );
           }

           
           //Save the object
           ImportLogger::saveRecordComputeChangesAndLog( $poiObj );

        }

        catch(Doctrine_Validator_Exception $error)
        {
           echo "Validation error \n \n";
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           ImportLogger::getInstance()->addError($error, $poiObj, $log);
           return $poiObj;
        }

        catch(Exception $e)
        {

           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           ImportLogger::getInstance()->addError($e, $poiObj, $log);
           return $poiObj;
        }

        //Return Poi for testing
        return $poiObj;
    }
}
?>
