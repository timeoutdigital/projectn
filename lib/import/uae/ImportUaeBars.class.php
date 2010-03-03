<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportUaeBarsclass
 *
 * @author timmy
 */
class ImportUaeBars extends importBaseUaeBarsRestaurants {


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
        $poiObj->addVendorCategory((string) $xmlObj->{'type'}, $this->vendorObj['id']);

        //Add the cuisine property
        if((string) $xmlObj->{'link'})
        {
            $poiObj->addProperty('timeout-link',  (string) $xmlObj->{'link'});
        }

        $logChangedFields = $poiObj->getModified();

       try{
             $poiObj->save();


             if($poiObj['longitude'] == null || $poiObj['longitude'] == null )
             {
                throw new GeoCodeException('Geocode is null');
             }
        }
        catch(Doctrine_Validator_Exception $error)
        {
            $log =  "Doctrine Validation Exception while processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$poiObj['vendor_poi_id']. " \n";
            $this->poiLoggerObj->addError($error, $poiObj, $log);
            echo $error->getMessage();
            //return $poiObj;
        }

        

        catch(PhoneNumberException $error)
        {
            $log =  "PhoneNumberException while processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$poiObj['vendor_poi_id']. " \n";
            $this->poiLoggerObj->addError($error, $poiObj, $log);
            //echo $log;
            //return $poiObj;
        }

        catch(GeoCodeException $error)
        {
            $log =  "GeoCodeException exception while processing Poi: \n Vendor = ". $this->vendorObj['city']." \n type = B/C \n vendor_poi_id = ".$poiObj['vendor_poi_id']. " \n";
            $this->poiLoggerObj->addError($error, $poiObj, $log);
            
             //$poiObj->save();
            
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
