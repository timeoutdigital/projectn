<?php
/**
 * Base class for XML feeds.
 *
 * @package lib.ny.import
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
    protected $xmlObj;
    

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
        $this->xmlObj = simplexml_load_file($sourceFile);
        return true;
      }
      else
      {
        return false;
      }
    }
}
?>