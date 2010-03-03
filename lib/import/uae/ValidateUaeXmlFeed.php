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
                //echo $this->feedObj;
                echo $e->getMessage();
                echo "\n Unable to force valid data \n";
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
      //$this->feedObj = preg_replace("/<email>/", "<email><![CDATA[", $this->feedObj);
      //$this->feedObj = preg_replace("/<\/email>/", "]]></email>", $this->feedObj);
      $this->feedObj = preg_replace("/<location>/", "<location><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/location>/", "]]></location>", $this->feedObj);
      $this->feedObj = preg_replace("/<link>/", "<link><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/link>/", "]]></link>", $this->feedObj);
      $this->feedObj = preg_replace("/<prices>/", "<prices><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/prices>/", "]]></prices>", $this->feedObj);
      $this->feedObj = preg_replace("/<times>/", "<times><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/times>/", "]]></times>", $this->feedObj);
      //$this->feedObj = preg_replace("/<description>/", "<description><![CDATA[", $this->feedObj);
      //$this->feedObj = preg_replace("/<\/description>/", "]]></description>", $this->feedObj);
      $this->feedObj = preg_replace("/<website>/", "<website><![CDATA[", $this->feedObj);
      $this->feedObj = preg_replace("/<\/website>/", "]]></website>", $this->feedObj);


      $this->feedObj = preg_replace("/&/", "&amp;", $this->feedObj);

      $this->feedObj = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $this->feedObj);

      $this->feedObj = trim($this->feedObj);

      $this->validatedFeedObj = $this->feedObj;
    }
}
?>
