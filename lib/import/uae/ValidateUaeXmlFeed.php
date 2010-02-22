<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidateUaeXmlFeed
 *
 * @author timmy
 */
class ValidateUaeXmlFeed extends ValidateXmlFeed{


    public function __construct($feedObj)
    {
        parent::__construct($feedObj);
    }


    /**
     * Get the xml feed
     *
     * @return SimpleXMLElement
     */
    public function getXmlFeed()
    {
        try{
            $this->testXml();
            return $this->validatedFeedObj;
        }
        catch(Exception $e)
        {
            
            $this->ForceValiation();

            try
            {
                $this->testXml();
                return $this->validatedFeedObj;
            }
            catch(Exception $e)
            {
                echo "Unable to force valid data";
            }//end try
        }//end try
    }

    /**
     * Form CDATA tags around data where there is likely to be invalid characters
     *
     */
    public function ForceValiation()
    {
      $this->feedObj = preg_replace("/<type>/", "<type><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/type>/", "]]></type>", $this->feedObj);
      $this->feedObj = preg_replace("/<title>/", "<title><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/title>/", "]]></title>", $this->feedObj);
      $this->feedObj = preg_replace("/<email>/", "<email><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/email>/", "]]></email>", $this->feedObj);
      $this->feedObj = preg_replace("/<description>/", "<description><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/description>/", "]]></description>", $this->feedObj);
      $this->feedObj = preg_replace("/<location>/", "<location><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/location>/", "]]></location>", $this->feedObj);
      $this->feedObj = preg_replace("/<link>/", "<link><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/link>/", "]]></link>", $this->feedObj);
      $this->feedObj = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $this->feedObj);

      $this->feedObj = trim($this->feedObj);

      $this->validatedFeedObj = $this->feedObj;
    }
}
?>
