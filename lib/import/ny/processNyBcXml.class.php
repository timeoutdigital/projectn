<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of processNyBcclass
 *
 * @author timmy
 */
class processNyBcXml extends processXml
{

    public $poiObj;

    public $totalPoi;

    /**
     * Constructor method
     *
     */
    public function  __construct($sourceFile)
    {


      $handle = fopen($sourceFile, "r");
      $contents = fread($handle, filesize($sourceFile));


      //$contents = ereg_replace( chr(149), "&#8226;", $contents );    # bullet •
      //$contents = ereg_replace( chr(150), "&ndash;", $contents );    # en dash
      //$contents = ereg_replace( chr(151), "&mdash;", $contents );    # em dash
      //$contents = ereg_replace( chr(153), "&#8482;", $contents );    # trademark
      //$contents = ereg_replace( chr(169), "&copy;", $contents );    # copyright mark
      //$contents = ereg_replace( chr(174), "&reg;", $contents );        # registration mark
     /* $contents = str_replace("‘", "'", $contents);
      $contents = str_replace("’", "'", $contents);
      $contents = str_replace("”", '"', $contents);
      $contents = str_replace("“", '"', $contents);
      $contents = str_replace("–", "-", $contents);
      */

      //$contents = str_replace('barkey.1', '', $contents);

     // $contents = preg_replace("/&/", "&amp;", $contents);
      /*$contents = preg_replace("/<type>/", "<type><![CDATA[", $contents);
      $contents = preg_replace("/<\/type>/", "]]></type>", $contents);
      $contents = preg_replace("/<title>/", "<title><![CDATA[", $contents);
      $contents = preg_replace("/<\/title>/", "]]></title>", $contents);
      $contents = preg_replace("/<email>/", "<email><![CDATA[", $contents);
      $contents = preg_replace("/<\/email>/", "]]></email>", $contents);
      $contents = preg_replace("/<description>/", "<description><![CDATA[", $contents);
      $contents = preg_replace("/<\/description>/", "]]></description>", $contents);
      $contents = preg_replace("/<location>/", "<location><![CDATA[", $contents);
      $contents = preg_replace("/<\/location>/", "]]></location>", $contents);
      $contents = preg_replace("/<link>/", "<link><![CDATA[", $contents);
      $contents = preg_replace("/<\/link>/", "]]></link>", $contents);
      

      $contents = trim($contents);
*/
     // echo $contents;

     //Remove crazy weird chars
      $contents = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $contents);

      fclose($handle);

      $handle = fopen($sourceFile, "w");

       if (fwrite($handle, $contents) === FALSE) {
         echo "Cannot write to file ($filename)";
        exit;
        }
     fclose($handle);

      parent::__construct($sourceFile);
    }

}
?>
