<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validateXmlFeed
 *
 * @author timmy
 */
class ValidateXmlFeed {

    public $feedObj;

    public $validatedFeedObj;

    public $isValid = false;

    public function __construct($feedObj)
    {
        $this->feedObj = $feedObj;
    }

    public function testXml()
    {
      
            $this->validatedFeedObj = simplexml_load_string($this->feedObj);

            if(!$this->validatedFeedObj)
            {
                throw new Exception('invalid XML');
            }
    }
}
?>
