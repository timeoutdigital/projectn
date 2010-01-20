<?php
/**
 * Base class for CSV feeds.
 *
 * @package import.lib.projectn
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class processCsv
{

    /**
     * @var object tmCsvReader object
     *
     */
    public $csvReader;
    

    /**
     * Constructor class
     *
     * @param string The CSV file.
     *
     */
    public function  __construct( $sourceFile )
    {

      if( file_exists( $sourceFile ) )
      {
       
        /*$this->csvReader = new tmCsvReader ( $sourceFile, array('header'=>true) );
        $arr = $this->csvReader->toArray();*/

        /*$this->csvReader = new tmDoctrineReader( $sourceFile, array('header'=>true) );
        
        $this->csvReader->

        print_r($arr);*/

        //var_export( mb_detect_encoding( file_get_contents( $sourceFile ) ) );

        $csvParser = new tmCsvReader( $sourceFile );

        return $this->csvReader = new tmCsvReader ( $sourceFile, array('header'=>true, 'from'=>'UTF-8', 'to'=>'UTF-8' ) );

      }
      else
      {
        $this->csvReader = False;
      }
    }

    public function getCsv(){ return $this->csvReader; }

    public function getCsvAsArray(){ return $this->csvReader->toArray(); }
}
?>