<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../../../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

/**
 * Test of
 *
 * @package test
 * @subpackage london.import.lib.unit
 *
 * @author Emre Basala <emrebasala@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class DataEntryImportManagerTest extends PHPUnit_Framework_TestCase
{
  
  protected $object;

  protected function setUp()
  {

      
      $this->object = new DataEntryImportManager(  );


  }

  protected function tearDown()
  {

  }

  

  public function testGetFileList()
  {
       $baseDir = sfConfig::get( 'sf_test_dir' ) . DIRECTORY_SEPARATOR .
                  'unit' .DIRECTORY_SEPARATOR .
                  'data' .DIRECTORY_SEPARATOR .
                  'data_entry' .DIRECTORY_SEPARATOR   ;

       $randomFolderName = substr( md5( date("YmdHis") ),0,10 );

       $tempFiles = array(
           'export_20100709' => array(
                'event' => 'test_city_1_event.xml' ,
                'poi'   => 'test_city_1_poi.xml' ,
                'movie' => 'test_city_1_movie.xml'
                 ),
           'export_20100710' => array(
                'event' => 'test_city_2_event.xml' ,
                'poi'   => 'test_city_2_poi.xml' ,
                'movie' => 'test_city_2_movie.xml'
                 ),
           'export_20100708' => array(
                'event' => 'test_city_3_event.xml' ,
                'poi'   => 'test_city_3_poi.xml' ,
                'movie' => 'test_city_3_movie.xml'
             )
        );

        foreach ($tempFiles as $key => $value)
        {
            foreach ( $value as $item => $fileName)
            {
                $results = array();
                $cmd = 'mkdir -p ' . $baseDir . $randomFolderName . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $item ;
                exec( $cmd , $results );
                file_put_contents( $baseDir . $randomFolderName . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $fileName , '' );
            }
        }

        $this->object->setImportDir( $baseDir .   $randomFolderName .DIRECTORY_SEPARATOR  );

        $fileList = $this->object->getFileList(  'poi' );
        //test if the importManager picked the latest folder with the right item subfolder (eg : event , movie)
        $this->assertEquals( $fileList [ 0 ],  $baseDir .   $randomFolderName .DIRECTORY_SEPARATOR . 'export_20100710' . DIRECTORY_SEPARATOR .'poi' . DIRECTORY_SEPARATOR . 'test_city_2_poi.xml' , 'latest poi files should be selected' );

        $fileList = $this->object->getFileList(  'event' );
        $this->assertEquals( $fileList [ 0 ],  $baseDir .   $randomFolderName .DIRECTORY_SEPARATOR . 'export_20100710' . DIRECTORY_SEPARATOR .'event' . DIRECTORY_SEPARATOR . 'test_city_2_event.xml' , 'latest event files should be selected' );

        $fileList = $this->object->getFileList(  'movie' );
        $this->assertEquals( $fileList [ 0 ],  $baseDir .   $randomFolderName .DIRECTORY_SEPARATOR . 'export_20100710' . DIRECTORY_SEPARATOR .'movie' . DIRECTORY_SEPARATOR . 'test_city_2_movie.xml' , 'latest movie files should be selected' );

        //delete the ramdomFolder
        exec( 'rm -rf ' .  $baseDir .   $randomFolderName  );

  }
}