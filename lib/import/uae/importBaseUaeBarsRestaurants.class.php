<?php
//ini_set('display_errors', 0);
/**
 * Base class for the UAE bars and restuarante.
 *
 * This class sets all the generic fields and leaves it to its children to set any
 * non-generic field.
 *
 * @package projectn
 * @subpackage uae.import.lib
 *
 *
 * @author Tim Bower <timbowler@timeout.com>
 *
 * @version 1.0.1
 *
 * @todo Create unit tests refs #116
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
        //Loop over the xml
        foreach($this->xmlObj as $poi)
        {
            //Only process if there is a record id and its not closed
            
            if($poi->{"id"})
            {
                $this->importPoi($poi);
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
        $currentPoi = Doctrine::getTable('Poi')->findOneByVendorPoiIdAndVendorId( $xmlObj->{'id'}, $this->$vendorObj['id'] );

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
             $geocoderLookUpString = $poiObj['poi_name'] .  ', '. $poiObj['street'] . ', ' . $poiObj['district'] . ', '.$poiObj['city'] .', UAE' ;
        }
        else
        {
             $poiObj['street'] = trim(ucfirst($addressArray[0]));
             $poiObj['city'] = trim(ucfirst($addressArray[1]));
             $geocoderLookUpString = $poiObj['poi_name'] . ', '.$poiObj['city']. ', UAE'  ;
        }

        $poiObj->setgeocoderLookUpString($geocoderLookUpString);
        
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
           throw new Exception( "You called an empty method! 'importPoi()'" );
    }

    

}
?>
