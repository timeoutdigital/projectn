<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of processUaeXmlclass
 *
 * @author timmy
 */
class processUaeXmlclass  extends processXml
{
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

      $contents = preg_replace("/&/", "&amp;", $contents);
      $contents = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $contents);
      $contents = trim($contents);

      //echo $contents;

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
