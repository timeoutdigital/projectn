<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportMoviesclass
 *
 * @author timmy
 */
class ImportUaeMovies {
    //put your code here

    public $xmlObj;

    public $vendorObj;

    public $poiLoggerObj;

    public $moviePoi;

    /**
     * Consrtuctor 
     *
     * @param SimpleXMLElement $xmlObj
     * @param Vendor $vendorObj 
     */
    public function  __construct(SimpleXMLElement $xmlObj, Vendor $vendorObj)
    {
        $this->xmlObj = $xmlObj;
        $this->vendorObj = $vendorObj;
        $this->poiLoggerObj = new logImport($vendorObj);
        $this->poiLoggerObj->setType('poi');
    }

    public function import()
    {

        $venuesObj = $this->xmlObj->xpath('//cinemas');
        $filmsObj = $this->xmlObj->xpath('//films');


        foreach($venuesObj[0] as $venue)
        {
            $this->importPoi($venue);
        }

        //print_r($venuesObj);

    }

    public function importPoi(SimpleXMLElement $xmlObj)
    {
        
    }

    public function getPoi(SimpleXMLElement $xmlObj)
    {
        
    }


    public function importMovies()
    {
        
    }

    



}
?>
