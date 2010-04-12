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
           $cuisineString = (string) trim($cuisine);
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
            $poiObj->addProperty('cuisine',  $cuisineString );
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
