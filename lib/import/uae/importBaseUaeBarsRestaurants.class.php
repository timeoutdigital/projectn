<?php
ini_set('display_errors', 0);
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of importUaeBarsclass
 *
 * @author timmy
 */
class importBaseUaeBarsRestaurants{

    /**
     *
     * @var Vendor
     */
    public $vendorObj;

    /**
     *
     * @var SimpleXMLElement
     */
    public $xmlObj;

   /**
     * Logger
     *
     * @var logImport
     */
    public $poiLoggerObj;

     /**
     * Poi
     *
     * @var Poi
     */
    public $poiObj;



    public $newPoi;

    /**
     * Class constuctor
     *
     * @param processNyBcXml Simple XML object containing the feed
     * @param Vendor The vendor
     */
    public function  __construct(SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        $this->xmlObj = $xmlObj;
        $this->vendorObj = $vendorObj;
        $this->poiLoggerObj = new logImport($vendorObj);
        $this->poiLoggerObj->setType('poi');
        Doctrine_Manager::getInstance()->setAttribute( Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL );
    }


     /**
     * Import the Poi's
     */
    public function importPois()
    {

        $count = 0;

        //Loop over the xml
        foreach($this->xmlObj as $poi)
        {
            if($count < 20)
            {

//Only process if there is a record id and its not closed
            if($poi->{'id'})
            {
                $this->importPoi($poi);
                $count++;
            }
            }
            else
            {
                break;
            }

        }
      
        $this->poiLoggerObj->save();
    }


    /**000000000
     * Get the current Pois for one of the UAE cities
     *
     * @param <type> $city
     */
    public function getCurrentPois(SimpleXMLElement $xmlObj)
    {

        //Check database for existing Poi by vendor id
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiId($xmlObj->{'id'});

        if($currentPoi)
        {
            //Count thisi as existing
            $this->poiLoggerObj->countExisting();
        }
        else
        {
            $currentPoi = new Poi();
            $this->newPoi = $currentPoi->isNew();
        }

        //Add All of the xml elements
        $currentPoi = $this->addCommonElements($currentPoi, $xmlObj);

        return $currentPoi;
    }



    /**
     * Add the common elements from bars and restaurants
     *
     * @param Poi $poi The poi
     * @param SimpleXMLElement $xmlObj
     *
     */
    public function addCommonElements(Poi $poiObj, SimpleXMLElement $xmlObj )
    {

        $poiObj['poi_name'] = (string) $xmlObj->{'title'};
        $poiObj['vendor_poi_id'] = (int) $xmlObj->{'id'};
        $poiObj['url'] = (string) $xmlObj->{'website'};
        $poiObj['phone'] = (string) $xmlObj->{'telephone'};
        $poiObj['description'] = (string) $xmlObj->{'description'};
        $poiObj['review_date'] = (string) $xmlObj->{'pubDate'};
        $poiObj['local_language'] = (string) $this->vendorObj['language'];
        $poiObj['email'] = (string) $xmlObj->{'email'};
        $poiObj['openingtimes'] = (string) $xmlObj->{'timings'};
        $poiObj['Vendor'] = $this->vendorObj;
        $poiObj['country'] = 'ARE';

     
        $addressArray = explode(',', (string) $xmlObj->{'location'});

        //Address is either street, city or street, suburb, city
        if(count($addressArray) == 3)
        {
             $poiObj['street'] = trim($addressArray[0]);
             $poiObj['district'] =trim($addressArray[1]);
             $poiObj['city'] = trim(ucwords($addressArray[2]));
             $geoEncodeLookUpString = $poiObj['poi_name'] .  ', '. $poiObj['street'] . ', ' . $poiObj['district'] . ', '.$poiObj['city'] .', UAE' ;
        }
        else
        {
             $poiObj['street'] = trim(ucfirst($addressArray[0]));
             $poiObj['city'] = trim(ucfirst($addressArray[1]));
             $geoEncodeLookUpString = $poiObj['poi_name'] . ', '.$poiObj['city']. ', UAE'  ;
        }

        $poiObj->setGeoEncodeLookUpString($geoEncodeLookUpString);
        
        return $poiObj;
    }

    /**
     *
     * Method to be overridden by child class
     *
     * @param SimpleXMLElement $xmlObj
     *
     */
    public function importPoi( SimpleXMLElement $xmlObj ){
    }

    

}
?>
