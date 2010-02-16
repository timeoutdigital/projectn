<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of nyImportBcclass
 *
 * @author timmy
 */



class nyImportBc {


    public $bcObj;

    public $vendorObj;

    public $logger;

    public function  __construct(processNyBcXml $bcObj, Vendor $vendorObj, logImport $logger )
    {
        $this->bcObj = $bcObj;
        $this->vendorObj = $vendorObj;
        $this->logger = $logger;
        Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    }




    public function import()
    {
        //Loop over the xml
        foreach($this->bcObj->xmlObj as $poi)
        {
            if($poi->xpath('@RECORDID'))
            {
                 $this->importBars($poi);
            }
        }

        //Save the logger
        $this->logger->save();
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
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiId($poi['RECORDID']);

        if($currentPoi)
        {
            return $currentPoi;
        }
        else
        {
            return new Poi();
        }
    }

    
    
    /**
     * import the bars from the feed
     *
     * @param <SimpleXMLElement>
     */
    public function importBars(SimpleXMLElement $poi)
    {

        //Get the POI object
        $poiObj = $this->getPoi($poi);
        $isNew = true;
  

        try {
          

            //Add the main details that should not change
            $poiObj[ 'vendor_poi_id' ]           = (string) $poi['RECORDID'];
            $poiObj[ 'street' ]                  = (string) $poi->{'location.0'};
            $poiObj[ 'poi_name' ]                = (string) $poi->{'name.0'};
            $poiObj[ 'public_transport_links' ]  = (string) $poi->{'subway.0'};
            $poiObj[ 'local_language' ]          = substr( $this->vendorObj[ 'language' ], 0, 2 );
            $poiObj[ 'phone' ]                   = stringTransform::formatPhoneNumber((string) $poi->{'phone.0'} , $this->vendorObj['inernational_dial_code']);
            $poiObj[ 'zips' ]                    = (string) $poi->{'zip.0'};

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

            $poiObj[ 'country' ]                 = 'USA';
            $poiObj[ 'Vendor' ]                  = $this->vendorObj;


            /**
             * Add all other information that may change
             */
            $poiObj[ 'description' ]             = (string) $poi->{'BAR.body'};      
            $poiObj[ 'price_information' ]       = (string) $poi->{'prices.0'};
            
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


            //Check the modified fields for an existing fiel
            if($poiObj->isModified(true) && !$poiObj->isNew())
            {
                //The item is modified therefore log as an update
                foreach($poiObj->getModified() as $k => $v)
                {
                    $log.= "$k: $v \n";
                }

                $this->logger->addChange('update', $log);
                $isNew = false;
                
            }
            
            //Save the object
            $poiObj->save();

            
        }
        catch(Doctrine_Validator_Exception $error)
        {
           echo "logging validation error  \n \n \n \n";

           
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi['RECORDID']. " \n";
            $this->logger->addError($error, $log);
            
            return $poiObj;
        }

        catch(Exception $e)
        {
           echo "logging general error \n \n \n";
          
           $log =  "Error processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".(string) $poi['RECORDID']. " \n";
           $this->logger->addError($e, $log);
           
            return $poiObj;
            
        }


        //Update the logger and save it
        if($isNew)
        {
            $this->logger->countNewInsert();
        }
        else
        {
            $this->logger->countUpdate();
        }
        

        return $poiObj;
    }
}
?>
