<?php

define( 'TO_PROJECT_ROOT_PATH', dirname( __FILE__ ) . '/../..' );
define( 'TO_TEST_ROOT_PATH', dirname( __FILE__ ) );
define( 'TO_TEST_DATA_PATH', TO_TEST_ROOT_PATH . '/data' );

ini_set( 'date.timezone', 'Europe/London' );

class ProjectN_Test_Unit_Factory
{
  /**
   * creates database connections from databases.yml
   *
   */
  static public function createDatabases( )
  {
  	$config = new ProjectConfiguration( );
  	$config = $config->getApplicationConfiguration( 'frontend', 'test', true );

  	$manager = new sfDatabaseManager( $config );

    Doctrine::createTablesFromModels( dirname(__FILE__).'/../../lib/model/doctrine' );
  }

  /**
   * Destroy a connection
   * A connection name can be used to identify the connection to close
   *
   */
  static public function destroyDatabases( )
  {
    $instance = Doctrine_Manager::getInstance( );

    $connections = $instance->getConnections();

    foreach ( $connections as $connection )
    {

    	if ( $connection instanceof Doctrine_Connection ) $connection->close( );
    }
  }

  /**
   * Creates and saves a new $model object to the Sqlite database with minimum required
   * fields. You can pass an array of data to override default values.
   *
   * <code>
   * ProjectN_Test_Unit_Lazy::add( 'Poi', array( 'name' => 'foo' ), true );
   *
   * //a new Poi will be added to the database with name 'foo'
   * //instead of the default 'test name' and a Vendor and Category will be
   * //will be created none exists for the Poi to link to.
   * </code>
   *
   * @see ProjectN_Test_Unit_Lazy::getPoi()
   *
   * @param array $data An array of data to override default values
   * @param boolean $autoCreateRelatedObjects Flags whether or not to automatically create a Vendor / Category if none available
   */
  static public function add( $model, $data = null, $autoCreateRelatedObjects = true )
  {
    $object = ProjectN_Test_Unit_Factory::get( $model, $data, $autoCreateRelatedObjects );
    $object->save();
    return $object;
  }

  /**
   * Creates a new $model object to the Sqlite database with minimum required
   * fields. You can pass an array of data to override default values.
   *
   * If not Vendor or Category is available, a default one will be created so
   * that the Poi can link to it. This is necessary for the Poi to be saved.
   * This feature can be set to false in the second parameter
   *
   * @param array $data An array of data to override default values
   * @param boolean $autoCreateRelatedObjects Flags whether or not to automatically create a Vendor / Category if none available
   */
  static public function get( $model, $data = null, $autoCreateRelatedObjects = true )
  {
    switch( strtolower( $model ) )
    {
      case 'poi':
        return PoiFixture::create( $data, $autoCreateRelatedObjects );
      case 'poicategory':
        return PoiCategoryFixture::create( $data, $autoCreateRelatedObjects );
      case 'vendor':
        return VendorFixture::create( $data, $autoCreateRelatedObjects );
    }
  }
}

class PoiFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = PoiFixture::getDefaults();
    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $poi = new Poi();
    $poi->fromArray( $defaults );

    if( $autoCreateRelatedObjects )
    {
      foreach( array( 'Vendor', 'PoiCategory' ) as $model )
      {
        if( Doctrine::getTable( $model )->count() < 1 )
        {
          ProjectN_Test_Unit_Factory::add( $model );
        }
      }
    }

    if( $autoCreateRelatedObjects )
    {
      $poi->link( 'PoiCategories', array( 1 ) );
      $poi->link( 'Vendor', array( 1 ) );
    }

    return $poi;
  }

  static private function getDefaults()
  {
    return array(
        'poi_name' => 'test name',
        'street' => 'test street',
        'city' => 'test town',
        'country' => 'GBR',
        'vendor_poi_id' => '1',
        'local_language' =>'aaa',
        'longitude' => '1.1',
        'latitude' => '1.1',
    );
  }
}

class PoiCategoryFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = PoiCategoryFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $poiCategory = new PoiCategory();
    $poiCategory->fromArray( $defaults );

    return $poiCategory;
  }

  static private function getDefaults()
  {
    return array(
      'name' => 'test name'
    );
  }
}

class VendorFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = VendorFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $vendor = new Vendor();
    $vendor->fromArray( $defaults );

    return $vendor;
  }

  static private function getDefaults()
  {
    return array(
      'city' => 'test city',
      'language' => 'test lang'
    );
  }
}

?>
