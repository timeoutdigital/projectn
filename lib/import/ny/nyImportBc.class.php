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

    public $currentPois;


    public function  __construct(processNyBcXml $bcObj, Vendor $vendorObj )
    {
        $this->bcObj = $bcObj;
        $this->vendorObj = $vendorObj;
        $this->currentPois = Doctrine::getTable('Poi')->getPoiByVendor($vendorObj['city']);
    }




    public function import()
    {
        //Loop over the xml
        foreach($this->bcObj->xmlObj as $poi)
        {

            if($poi->xpath('@RECORDID'))
            {
                //Check Poi isn't already in DB
                if(!$this->testIfPoiExists($poi))
                {
                    //Insert it
                    $this->importBars($poi);
                }
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
    public function testIfPoiExists(SimpleXMLElement $poi)
    {
        //Loop over the current POIs to see if its already in the database.
        foreach($this->currentPois as $currentPoi)
        {
            //Check against the vendor's poi id
            if((string) $poi['RECORDID'] == $currentPoi['vendor_poi_id']  )
            {
                return true;
            }
        }

        return false;
    }

    
    
    /**
     * import the bars from the feed
     *
     * @param <SimpleXMLElement>
     */
    public function importBars(SimpleXMLElement $poi)
    {

        //Insert if POI is not in the database
        if(!$poiFound)
        {               
            $poiObj = new Poi();
            $poiObj[ 'vendor_poi_id' ]           = (string) $poi['RECORDID'];
            $poiObj[ 'description' ]             = (string) $poi->{'BAR.body'};
            $poiObj[ 'short_description' ]       = (string) $poi->{'barkey'};
            $poiObj[ 'price_information' ]       = (string) $poi->{'barkey.3'};
            $poiObj[ 'street' ]                  = (string) $poi->{'location.0'};
            $poiObj[ 'poi_name' ]                = (string) $poi->{'name.0'};
            $poiObj[ 'public_transport_links' ]  = (string) $poi->{'subway.0'};
            $poiObj[ 'price_information' ]       = (string) $poi->{'prices.0'};
            $poiObj[ 'local_language' ]          = substr( $this->vendorObj[ 'language' ], 0, 2 );
            $poiObj['phone' ]                    = stringTransform::formatPhoneNumber((string) $poi->{'phone.0'} , $this->vendorObj['inernational_dial_code']);

            print_r($this->vendorObj['inernational_dial_code']);


            //Get state and city
            $stateCityArray = explode(',', (string) $poi->{'city.state.0'});
            $poiObj['city'] = $stateCityArray[1];
            $poiObj['country'] = 'USA';
            $poiObj['Vendor'] = $this->vendorObj;



         }

         print_r($poiObj->toArray());
         exit;


    }
}
?>
