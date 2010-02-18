<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dubaiImportclass
 *
 * @author timmy
 */
class dubaiImportBars{


    public $xmlObj;
    public $vendorsObj;
    public $poiType;

    /**
     *
     * @var logImport
     */
    public $poiLoggerObj;

    /**
     *
     * @param <type> $xmlObj
     * @param <type> $vendorObj
     *
     * @todo get by Vendo and type
     */
    public function  __construct( SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        $this->xmlObj = $xmlObj;
        $this->vendorsObj = $vendorObj;
        $this->currentPois = Doctrine::getTable('Poi')->getPoiByVendor($vendorObj['city']);
        $poiType = $poiType;
        $this->poiLoggerObj = new logImport( $vendorObj );
        $this->poiLoggerObj->setType( logImport::POI );
       
    }

    public function importPoi()
    {
       $count = 0;

        foreach($this->xmlObj as $poi)
        {
            $found = false;
            
            if($poi->title != '')
            {
                
                //loop through all the existing venues to see if the venue exists
                foreach($this->currentPois as $currentPoi)
                {
                    if($this->currentPois['vendor_id'] == $currentPoi->id)
                    {
                        $found = true;
                    }
                }

                //Add the venue if its not found
                if(!$found)
                {
                   $this->addRestaurantPoi($poi);
                }

            }

            if($count > 10){
                exit;
            }
            $count++;
        }

        $poiLoggerObj->saveStats();
    }

    /**
     * Save the bar
     *
     * @param <string> $bar
     */
    public function addBarPoi($bar)
    {


    }

    /**
     * Save the restaurant
     *
     * @param <string> $restaurant
     */
    public function addRestaurantPoi($restaurant)
    {
        //start transaction
        $conn = Doctrine_Manager::connection();

        try {
          $conn->beginTransaction();

          //Poi object
          $poiObj = new Poi;
          $poiObj['vendor_poi_id'] = (int) $restaurant->id;
          $poiObj['poi_name'] = (string) $restaurant->title;
          $poiObj['url'] = (string) $restaurant->website;
          
          $poiObj['openingtimes'] = (string)  $restaurant->timings;
          $poiObj['email'] = (string) $restaurant->email;
          $poiObj['local_language'] = 'english';
          $poiObj['phone'] = (string) $restaurant->telephone;


          if($restaurant->longitude == '' || $restaurant->latitude == '')
          {
              $geoObj = new geoEncode();
              $geoObj->setAddress( $restaurant->location );
              $poiObj['longitude'] = $geoObj->getLongitude();
              $poiObj['latitude'] = $geoObj->getLatitude();

          }
          else
          {
              $poiObj['longitude'] = (float)  $restaurant->longitude;
              $poiObj['latitude'] = (float)  $restaurant->latitude;
          }
       
          //Get the address
          /**
           * @todo Test the formatting
           */
          $addressArray = explode(',', $restaurant->location);
          $poiObj['street'] = $addressArray[0];
          $poiObj['city'] = $addressArray[1];
          $poiObj['country'] = 'ARE';



          //Associate the vendor to the poi
          $poiObj['Vendor'] = $this->vendorsObj;
          
          //Save poi to get PK
          $poiObj->save();

        
          //Poi Proterties
          $poiPropArray = new Doctrine_Collection(Doctrine::getTable('PoiProperty'));

          $cuisineArray = explode(',', $restaurant->cuisine);

          foreach($cuisineArray as $cuisine)
          {
            $poiPropObj = new PoiProperty();
            $poiPropObj['lookup'] = 'cuisine';
            $poiPropObj['value'] = $cuisine;
            $poiPropArray[] = $poiPropObj;
          }

          $poiPropObj['Poi'] = $poiObj;
          $poiPropObj->save();


          //Save poi and all relations
          $poiObj->save();
          print_r($poiPropObj->toArray());
          //Commit transaction
           $conn->commit();
           //Count the new insert
          $this->poiLoggerObj->countNewInsert();

        }
        catch(Exception $e)
        {
            $conn->rollback(); // deletes all savepoints
        }

        return $poiObj;
    }
}
?>
