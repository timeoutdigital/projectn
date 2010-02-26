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
class nyImportBcEd {

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

    /**
     * Logger
     *
     * @var logImport
     */
    public $loggerObj;


    /**
     * Constructor
     *
     * @param processNyBcXml $bcObj
     * @param Vendor $vendorObj
     */
    public function  __construct(processNyBcXml $bcObj, Vendor $vendorObj)
    {
        $this->bcObj = $bcObj;
        $this->vendorObj = $vendorObj;
        $this->loggerObj = new logImport($vendorObj);
        $this->loggerObj->setType('poi');
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
            if($poi->xpath('@RECORDID') && $poi->{'closed.0'} != 'yes')
            {
                $this->importPoi($poi);
            }
        }

        //Save the logger
        $this->loggerObj->save();
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
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId($poi->{'ID'}, $this->vendorObj['id']);

        if($currentPoi)
        {
            //Count thisi as existing
            $this->loggerObj->countExisting();
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
        $poiObj = $this->getPoi($poi);
  

        try {
            //Add the main details that should not change
            $poiObj[ 'vendor_poi_id' ]           = (string) $poi->{'ID'};
            $poiObj[ 'street' ]                  = (string) $poi->{'location.0'};
            $poiObj[ 'poi_name' ]                = (string) $poi->{'name.0'};
            $poiObj[ 'public_transport_links' ]  = (string) $poi->{'subway.0'};
            $poiObj[ 'local_language' ]          = substr( $this->vendorObj[ 'language' ], 0, 2 );
            $poiObj[ 'zips' ]                    = (string) $poi->{'zip.0'};


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

            //Get state and city
            $stateCityArray                      = explode(',', (string) $poi->{'city.state.0'});
            if(count($stateCityArray) < 1)
            {
               $poiObj[ 'city' ]                 = 'NY';
            }
            else
            {
               $poiObj[ 'city' ]                 = trim($stateCityArray[1]);
              
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
            $poiObj[ 'Vendor' ]                  = $this->vendorObj;



            /**
             * Try and get the longitude and latitude for POI
             */
            try{
               //Add the problematic areas that can cause exceptions
                $addressString = $poiObj[ 'poi_name' ].', ' . $poiObj[ 'street' ] .', ' . ', ' . $poiObj[ 'city' ]  . ', '  . $poiObj[ 'country' ];


                if($poiObj[ 'longitude' ] == '' || $poiObj[ 'latitude' ] == '')
                {
                    //Get longitude and latitude for venue
                    $geoEncode = new geoEncode();
                    $geoEncode->setAddress( $addressString );

                    //Set longitude and latitude
                    $poiObj[ 'longitude' ] = $geoEncode->getLongitude();
                    $poiObj[ 'latitude' ]  = $geoEncode->getLatitude();
                }
            }
            catch(Exception $e)
            {
                //Force a Long/Lat or validation will fail
               // $poiObj[ 'longitude' ] = 0.00;
               // $poiObj[ 'latitude' ]  = 0.00;

                  $log =  "Error processing Long/Lat for Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
                 $this->loggerObj->addError($e, $poiObj, $log);
            }

            /**
             * Try to convert the phone number
             */
            try{
                
                $phoneNumber = strtolower((string) $poi->{'phone.0'});

                if( $phoneNumber != "no phone")
                {
                    $poiObj[ 'phone' ] = stringTransform::formatPhoneNumber($phoneNumber , $this->vendorObj['inernational_dial_code']);
                }

            }
            catch(Exception $e)
            {
                $log =  "Error processing Phone number for Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
                $this->loggerObj->addError($e, $poiObj, $log);
            }
            

            $category = (string) $poi->Category;

           //Add category
            
           if( !empty( $category ) ) 
           {
             $categoryNameSchemaDefinition = Doctrine::getTable('VendorPoiCategory')->getColumnDefinition('name');
             
             if( strlen( $category ) > $categoryNameSchemaDefinition['length'] )
             {
               throw new Exception( 'Category is too long: "' . $category  . '"' );
             }
             $poiObj->addVendorCategory($category, $this->vendorObj['id']);
           }

           //Add the cuisine property
           $poiObj->addProperty('cuisine',  (string) $poi->{'PrimaryCuisine'});


           //Save the object and log the changes
           //pre-save
           $logIsNew = $poiObj->isNew();
           $logChangedFields = $poiObj->getModified();
           //save
           $poiObj->save();
           //post-save
           ( $logIsNew ) ? $this->loggerObj->countNewInsert() : $this->loggerObj->addChange( 'update', $logChangedFields );

           //Return Poi for testing
           return $poiObj;

        }

        catch(Doctrine_Validator_Exception $error)
        {           
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           $this->loggerObj->addError($error, $poiObj, $log);
            
            return $poiObj;
        }

        catch(Exception $e)
        {
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) (string) $poi->{'ID'}. " \n";
           $this->loggerObj->addError($e, $poiObj, $log);

           return $poiObj;
        }
    }
}
?>
