<?php
require_once 'PHPUnit/Framework.php';
require_once dirname( __FILE__ ) . '/../../test/bootstrap/unit.php';
require_once dirname( __FILE__ ) . '/../../test/unit/bootstrap.php';

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
class dataEntryUpdateTest extends PHPUnit_Framework_TestCase
{
  protected $vendor;

  const PROJECTN            = 0;
  const PROJECTN_DATA_ENTRY = 1;

  public function setUp()
  {
    $today = date('Ymd');
    @unlink($this->getRootPath( self::PROJECTN_DATA_ENTRY  ) .'export/export_' .$today.'/poi/beijing.xml');
    @unlink($this->getRootPath() .'export/export_' .$today.'/poi/beijing.xml');
    @unlink($this->getRootPath() .'export/data_entry/export_' .$today.'/poi/beijing.xml');
  }
  public function testDataEntryUpdateProcessWithImportAndExportTasks()
  {

    //clear databases
    $this->callCmd( './symfony doctrine:build --all --and-load --env=test --no-confirmation' );
    $this->callCmd( './symfony doctrine:build --all --and-load --env=test --no-confirmation' ,
        self::PROJECTN_DATA_ENTRY );

    //create a beijing database to mock live searchlight database,
    //add some sample venues into beijing dabase
    $this->addBeijingData();
    //import venues into data_entry_instance

    $this->callCmd( './symfony projectn:import --env=test --city=beijing --type=poi  --application=data_entry ',
        self::PROJECTN_DATA_ENTRY);

    $today = date('Ymd');

    //create export directories in both data_entry and prod instances
    $cmd = 'mkdir -p export/export_' .$today.'/poi';
    $this->callCmd( $cmd , self::PROJECTN_DATA_ENTRY);
    $this->callCmd( $cmd );

    //run the beijing import on data_entry
    $this->callCmd( './symfony projectn:export --env=test --city=beijing --type=poi --validation=false  --application=data_entry --destination=export/export_' .$today.'/poi/beijing.xml' ,
        self::PROJECTN_DATA_ENTRY );


    //import the beijing venues from data_entry into main database
    $this->callCmd( './symfony projectn:import --env=test --city=beijing-data-entry --type=poi' );

    //now change a record and export it back to data _entry

    //in the main database change a name of the venue
    //previously =>Sofitel Wanda
    //after running editPoi => Sofitel Wanda International
    $this->editPoi( );

    //export the beijing venues in main database
    $this->callCmd( './symfony projectn:export --env=test --city=beijing --type=poi --validation=false --destination=export/export_' .$today.'/poi/beijing.xml' );

    //create the data_entry export directory
    $this->callCmd(  'mkdir -p export/data_entry/export_' .$today.'/poi' );

    //fix the IDs for the beijng XML to it can be used to update the data_entry database
    $this->callCmd( './symfony projectn:prepareExportXMLsForDataEntry --env=test --type=poi --xml=export/export_' .$today.'/poi/beijing.xml --destination=export/data_entry/export_' .$today.'/poi/beijing.xml'  );

    //run the beijing import in data_entry
    //please note that the mock beijing database is used in this stage
    $this->callCmd( './symfony projectn:import --env=test --city=beijing --type=poi  --application=data_entry',
        self::PROJECTN_DATA_ENTRY);

    //export the beijing venues in data_entry
    $this->callCmd( './symfony projectn:export --env=test --city=beijing --type=poi --validation=false  --application=data_entry  --destination=export/export_' .$today.'/poi/beijing.xml',
        self::PROJECTN_DATA_ENTRY);


    //load the xml and check the venue to make sure that the update we made in the main database isn't applied yet
    //we ran the import from beijing database but haven't updated data_entry database with the data coming from main database
    $xml = simplexml_load_file( $this->getRootPath( self::PROJECTN_DATA_ENTRY  ) .'export/export_' .$today.'/poi/beijing.xml' );
    $poi = $xml->entry [0];
    $this->assertEquals( 'Sofitel Wanda' ,(string) $poi->name  );


    //now run the update
    $this->callCmd( './symfony projectn:import --env=test --city=beijing-data-entry --type=poi --application=data_entry' ,
        self::PROJECTN_DATA_ENTRY );

    //export after update and test it if the update is applied in data_entry database
    $this->callCmd( './symfony projectn:export --env=test --city=beijing --type=poi --validation=false  --application=data_entry  --destination=export/export_' .$today.'/poi/beijing.xml',
        self::PROJECTN_DATA_ENTRY);

    $xml = simplexml_load_file( $this->getRootPath( self::PROJECTN_DATA_ENTRY  ) .'export/export_' .$today.'/poi/beijing.xml' );
    $poi = $xml->entry [0];
    $this->assertEquals( 'Sofitel Wanda International' ,(string) $poi->name  );

  }

  public function testDataEntryUpdateProcessWithRunner()
  {
    $today = date('Ymd');
    $this->callCmd( './symfony doctrine:build --all --and-load --env=test --no-confirmation ' );
    $this->callCmd( './symfony doctrine:build --all --and-load --env=test --no-confirmation ' , self::PROJECTN_DATA_ENTRY  );

    $this->addBeijingData();

    $this->callCmd( './symfony projectn:runner --env=test --city=beijing --application=data_entry',
        self::PROJECTN_DATA_ENTRY  );

    $this->callCmd( './symfony projectn:runner --env=test --city=beijing' );

    $this->editPoi( );

    $this->callCmd( './symfony projectn:runner --env=test --city=beijing' );

    $xml = simplexml_load_file( $this->getRootPath( self::PROJECTN_DATA_ENTRY  ) .'export/export_' .$today.'/poi/beijing.xml' );
    $poi = $xml->entry [0];
    $this->assertEquals( 'Sofitel Wanda' ,(string) $poi->name  );

    $this->callCmd(  './symfony projectn:runner --env=test --city=beijing --application=data_entry',
        self::PROJECTN_DATA_ENTRY );

    $xml = simplexml_load_file( $this->getRootPath( self::PROJECTN_DATA_ENTRY  ) .'export/export_' .$today.'/poi/beijing.xml' );
    $poi = $xml->entry [0];
    $this->assertEquals( 'Sofitel Wanda International' ,(string) $poi->name  );

  }

  protected function editPoi( )
  {
      $projectnDir = $this->getRootPath();

      $pdoDB = new PDO('sqlite:'.$projectnDir. 'data/project_n.db:' );

      $pdoDB->exec("INSERT INTO record_field_override_poi VALUES(1, 1, 'poi_name' , 'Sofitel Wanda' ,'Sofitel Wanda International' ,1, '2010-09-01 12:12:12', '2010-09-01 12:12:12' )");

      $pdoDB->exec("UPDATE poi set poi_name= 'Sofitel Wanda International' where id = 1");

  }

  protected function callCmd( $cmd, $installation = self::PROJECTN )
  {
    $cmd =  'cd ' . $this->getRootPath( $installation ) . ' && ' . $cmd;

    echo "RUNNING :" .$cmd . PHP_EOL.PHP_EOL;

    exec( $cmd  );

  }



    /**
     * Enter description here...
     *
     */
    protected function addBeijingData()
    {
          if( file_exists( '/tmp/beijing.db:' ) )
          {
            unlink(  '/tmp/beijing.db:' );
          }
          $pdoDB = new PDO('sqlite:/tmp/beijing.db:');

          $pdoDB->beginTransaction();

          $pdoDB->query('DROP TABLE IF EXISTS neighbourhood; DROP TABLE IF EXISTS category; DROP TABLE IF EXISTS venue; DROP TABLE IF EXISTS venue_category_mapping;');

          // Create Tables
          $pdoDB->exec('CREATE TABLE neighbourhood(id INTEGER PRIMARY KEY, city_id INTEGER, name VARCHAR(100))');
          $pdoDB->exec('CREATE TABLE category(id INTEGER PRIMARY KEY, parent_category_id INTEGER, lft INTEGER, rgt INTEGER, name VARCHAR(100));');
          $pdoDB->exec('CREATE TABLE venue(id INTEGER PRIMARY KEY, neighbourhood_id INTEGER, name VARCHAR(255), alt_name VARCHAR(255), building_name VARCHAR(200), address VARCHAR(255), postcode VARCHAR(255),travel VARCHAR(255),opening_times VARCHAR(255),url VARCHAR(255),latitude VARCHAR(255), longitude VARCHAR(255), phone VARCHAR(255),email VARCHAR(255),image_id VARCHAR(255),status VARCHAR(255),source_id VARCHAR(255),annotation TEXT);');
          $pdoDB->exec('CREATE TABLE venue_category_mapping(venue_id INTEGER, category_id INTEGER);');

          $pdoDB->exec('INSERT INTO neighbourhood VALUES(1, 2, "The Centre");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(2, 2, "Chaoyang");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(3, 1, "None Beijing City");');
          $pdoDB->exec('INSERT INTO neighbourhood VALUES(4, 4, "None Beijing City 2");');

          $pdoDB->exec('INSERT INTO category VALUES(1, 0, 0, 1132, "Root" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(381, 1, 2, 233, "International" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(382, 381, 3, 232, "cn" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(383, 382, 4, 231, "en" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(384, 383, 5, 230, "Beijing" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(392, 384, 58, 229, "Venues" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(404, 392, 83, 178, "Restaurents" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(406, 404, 86, 127, "Chinese" );');
          $pdoDB->exec('INSERT INTO category(id, parent_category_id, lft, rgt, name) VALUES(561, 406, 123, 124, "Yunnan & SouthWest Minority" );');

          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(1, 2, 10, "Sofitel Wanda", "Wanda Plaza", "93 Jianguo Lu, Tower C Wanda Plaza, Chaoyang district", "", "", "", "http://www.theemperor.com.cn", "39.909988", "116.452393", "8599 6666", "", "", "<p>With three giant Swarovski peonies winking at you as soon as you enter and a daring mix of French chic and Tang dynasty chinoiserie characterising the decor throughout, this hotel is gorgeous. A fantastic French restaurant &ndash; Le Pre Lenotre &ndash; and a relaxing Le Spa add to the appeal.</p>");');
          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(2, 1, 10, "Meli Melo", "", "Second Floor, Les Millésimes, 16 Yonganli (next to Building 15 Jianwai SOHO), Chaoyang district", "", "", "11am-2.30pm, 6-10.30pm daily", "", "0.000000", "0.000000", "8521 9988", "", "44494", "<p>This French fusion restaurant tends towards the whimsical in its eclectic d&eacute;cor of peach-coloured sofas, gilded screens and red chandelier covers made of an Issey Miyake inspired fabric. Yet when it comes to food, Meli Melo gets serious. Meli Melo belongs to the three-storey Les Mill&eacute;simes development, which includes a wine bar, cigar bar, private club and the French seafood restaurant La Maree, located on the same floor as Meli Melo. The two restaurants share a kitchen and a wine list. That the wines are currently available only by the bottle is unfortunate since it is nearly impossible to match one wine with the many flavours that Meli Melo combines on one plate. Staff are polite and professional yet unlike many new restaurants around town, Meli Melo does not add an extra service charge.</p>");');
          $pdoDB->exec('INSERT INTO venue(id, neighbourhood_id, status, name, building_name, address, postcode, travel, opening_times, url, latitude, longitude, phone, email, image_id, annotation) VALUES(3, 4, 10, "None Beijin", "", "Invaid Address", "", "", "11am-2.30pm, 6-10.30pm daily", "", "0.000000", "0.000000", "8521 9988", "", "44494", "<p>This shoudn\'t be in. Beijing???</p>");');

          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(1, 406);');
          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(2, 404);');
          $pdoDB->exec('INSERT INTO venue_category_mapping(venue_id, category_id) VALUES(3, 561);');

          $pdoDB->commit();

    }



    private function getRootPath( $installation = self::PROJECTN )
    {
        $locateCommands= array(
            self::PROJECTN_DATA_ENTRY => 'locate projectn_data_entry/config/databases.yml',
            self::PROJECTN            => 'locate projectn/config/databases.yml',
        );

        $command = $locateCommands [ $installation ];
        if( !isset( $command ) )
        {
            throw new Exception( 'getRoot failed' );
        }

        exec( $command, $output );

        $resultCount = count( $output );

        if( $resultCount == 1 )
        {
            $path = str_replace( '/config/databases.yml' , '/', $output[ 0 ] );
            if( is_dir( $path .'httpdocs' ) )
            {
                $path = $path.'httpdocs';
            }

            return $path;
        }else
        {
            return NULL;
        }

    }

}