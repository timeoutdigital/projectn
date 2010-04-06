<?php

define( 'TO_PROJECT_ROOT_PATH', dirname( __FILE__ ) . '/../..' );
define( 'TO_TEST_ROOT_PATH', dirname( __FILE__ ) );
define( 'TO_TEST_DATA_PATH', TO_TEST_ROOT_PATH . '/data' );
define( 'TO_TEST_IMPORT_PATH', TO_TEST_ROOT_PATH . '/import' );

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
   *
   */
   static public function today( $format = 'Y-m-d' )
   {
     return date( $format );
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
    $classMap = array(
      'poi'                 => PoiFixture,
      'poicategory'         => PoiCategoryFixture,
      'vendor'              => VendorFixture,
      'event'               => EventFixture,
      'eventoccurrence'     => EventOccurrenceFixture,
      'eventcategory'       => EventCategoryFixture,
      'eventproperty'       => EventPropertyFixture,
      'movie'               => MovieFixture,
      'vendoreventcategory' => VendorEventCategoryFixture,
      'vendorpoicategory'   => VendorPoiCategoryFixture,
    );

    $model = strtolower( $model );
    if( array_key_exists( $model, $classMap ) )
    {
      $modelClass = $classMap[ $model ];
    }
    else
    {
      throw new Exception( 'There is current no fixture generator for the model: ' . $model . '. Please make one :)' );
    }

    return $modelClass::create( $data, $autoCreateRelatedObjects );
  }

  /**
   * @param string $modelName
   * @param string $columnName
   * @param string $part
   * @return mixed
   */
  static public function getColumnDefinition( $modelName, $column, $part=null)
  {
    $definition = Doctrine::getTable( $modelName )->getColumnDefinition( 'longitude' );

    if( is_null( $part ) )
      return $definition;

    if( !array_key_exists( $part, $definition ) )
      throw new Exception( "'$part' is not in the column definition for the Poi column '$column'."  );

    return $definition[$part];
  }

  /**
   * get the first $model record from the database
   * will add a record and return it if none exists
   */
  static public function autoCreate( $model )
  {
    $record = null;

    $table = Doctrine::getTable( $model );

    if( $table->count() > 0 )
      $record = $table->findOneById( 1 );
    else
      $record = ProjectN_Test_Unit_Factory::add( $model );

    return $record;
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
    $poi->setGeoEncodeLookUpString( 'foo' );

    if( $autoCreateRelatedObjects )
    {
      foreach( array( 'Vendor', 'PoiCategory', 'VendorPoiCategory' ) as $model )
      {
        if( Doctrine::getTable( $model )->count() < 1 )
        {
          ProjectN_Test_Unit_Factory::add( $model );
        }
      }
    }

    if( $autoCreateRelatedObjects )
    {
      $poi->link( 'PoiCategory', array( 1 ) );
      $poi->link( 'VendorPoiCategory', array( 1 ) );
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
      'name' => 'theatre-music-culture'
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
      'language' => 'en-GB',
      'time_zone' => 'Europe/London',
      'airport_code' => 'LHR',
      'inernational_dial_code' => '+44',
      'country_code' => 'gb'
    );
  }
}

class EventFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = EventFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $record = new Event();
    $record->fromArray( $defaults );

    if( $autoCreateRelatedObjects )
    {
      $record[ 'Vendor' ] = ProjectN_Test_Unit_Factory::autoCreate( 'Vendor' );
    }

    return $record;
  }

  static private function getDefaults()
  {
    return array(
      'name' => 'test name',
      'short_description' => 'test short description',
      'description' => 'test description',
      'booking_url' => 'http://timeout.com',
      'url' => 'http://timeout.com',
      'price' => 'test price',
      'vendor_event_id' => 0000,
    );
  }
}

class EventOccurrenceFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = EventOccurrenceFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $vendor = new EventOccurrence();
    $vendor->fromArray( $defaults );
    $vendor[ 'Poi' ] = ProjectN_Test_Unit_Factory::autoCreate( 'Poi' );

    return $vendor;
  }

  static private function getDefaults()
  {
    return array(
      'name' => 'test name',
      'start_date' => '2001-01-01',
      'start_time' => '00:00:00',
      'end_date' => '2001-01-01',
      'end_time' => '00:00:00',
      'utc_offset' => '+00:00:00',
      'vendor_event_occurrence_id' => '0000'
    );
  }
}

class MovieFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = MovieFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $vendor = new Movie();
    $vendor->fromArray( $defaults );

    return $vendor;
  }

  static private function getDefaults()
  {
    return array(
      'name'       => 'test name',
      'plot'       => 'test plot',
      'review'     => 'test review',
      'url'        => 'http://www.timeout.com',
      'rating'     => '1.0',
      'utf_offset' => '+00:00:00',
      'vendor_id' => '1',
      'vendor_movie_id' => '1',
    );
  }
}

class EventCategoryFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = EventCategoryFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $record = new EventCategory();
    $record->fromArray( $defaults );

    return $record;
  }

  static private function getDefaults()
  {
    return array(
      'name'       => 'test name',
    );
  }
}

class EventPropertyFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = EventPropertyFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $record = new EventProperty();
    $record->fromArray( $defaults );

    return $record;
  }

  static private function getDefaults()
  {
    return array(
      'lookup'       => 'test lookup',
      'value'       => 'test value',
    );
  }
}

class VendorEventCategoryFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = VendorEventCategoryFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $record = new VendorEventCategory();
    $record->fromArray( $defaults );

    if( $autoCreateRelatedObjects )
    {
      $record[ 'Vendor' ] = ProjectN_Test_Unit_Factory::autoCreate( 'Vendor' );
    }

    return $record;
  }

  static private function getDefaults()
  {
    return array(
      'name'       =>  'test name',
    );
  }
}

class VendorPoiCategoryFixture
{
  static public function create( $data=null, $autoCreateRelatedObjects=true )
  {
    $defaults = VendorPoiCategoryFixture::getDefaults();

    if( is_array( $data ) )
    {
      $defaults = array_merge( $defaults, $data );
    }

    $record = new VendorPoiCategory();
    $record->fromArray( $defaults );

    if( $autoCreateRelatedObjects )
    {
      $record[ 'Vendor' ] = ProjectN_Test_Unit_Factory::autoCreate( 'Vendor' );
    }

    return $record;
  }

  static private function getDefaults()
  {
    return array(
      'name'       =>  'test name',
    );
  }
}

