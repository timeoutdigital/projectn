<?php
/**
 * Base class for XML feeds.
 *
 * @package projectn
 * @subpackage import.lib
 *
 * @author Tim Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 *
 */
class processXml
{

    /**
     * @var object Simple XML object
     *
     */
    public $xmlObj;
    

    /**
     * Constructor class
     *
     * @param string The XML file.
     *
     */
    public function  __construct($sourceFile)
    {
      if(file_exists($sourceFile))
      {
        $contents = file_get_contents($sourceFile);
     
        
        $contents = str_replace('&', '&amp;',$contents );
        //echo $contents;
        $this->xmlObj = simplexml_load_file($contents);
      }
      else
      {
        $this->xmlObj = False;
      }
    }

    public function getXml(){ return $this->xmlObj; }
}
?>