<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportUaeRestaurantsclass
 *
 * @author timmy
 */
class ImportUaeRestaurants extends importBaseUaeBarsRestaurants {


    /**
     * Constructor
     *
     * @param SimpleXMLElement $xmlObj
     * @param Vendor $vendorObj
     */
    public function  __construct(SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        parent::__construct($xmlObj, $vendorObj);
    }

      /**
     * Over ride the parent function for the functionality to import a Poi
     *
     * @param SimpleXMLElement $xmlObj
     * @return Poi
     */
    public function importPoi( SimpleXMLElement $xmlObj)
    {

        $poiObj = $this->getCurrentPois($xmlObj);


        //Add its categories
        $poiObj->addVendorCategory('Restaurant', $this->vendorObj['id']);

        //Add the cuisine property
        $cuisineArray = explode(',', $xmlObj->{'cuisine'});


        foreach($cuisineArray as $cuisine)
        {
            $poiObj->addProperty('Cuisine',  trim($cuisine));
        }

       

        $logChangedFields = $poiObj->getModified();

       try{
             $poiObj->save();
        }
        catch(Doctrine_Validator_Exception $error)
        {
            $log =  "Doctrine Validation Exception while processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$poiObj['vendor_poi_id']. " \n";
            $this->poiLoggerObj->addError($error, $poiObj, $log);
            echo $error->getMessage();
            //return $poiObj;
        }

        catch(Exception $error)
        {
            $log =  "GeoCodeException exception while processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$poiObj['vendor_poi_id']. " \n";
            $this->poiLoggerObj->addError($error, $poiObj, $log);

            //return $poiObj;

        }

        //Log it if its new
        ( $this->newPoi ) ? $this->poiLoggerObj->countNewInsert() : $this->poiLoggerObj->addChange( 'update', $logChangedFields );

    }
}
?>
