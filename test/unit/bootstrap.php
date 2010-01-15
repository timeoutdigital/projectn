<?php

class ProjectN_Test_Unit_Factory
{ 
  /**
   * creates a new Sqlite db in Memory with a $connection name
   * The connection name provides a handle to get back to this
   * connection if we need to switch to another connection for
   * any reason, for example:
   *
   * <code>
   * //switch to external database
   * Doctrine_Manager::connection( 'mysql://user:password@123.4.567.89/db' );
   * 
   * //collect data, do tests
   * 
   * //switch back to test database
   * Doctrine_manager::setCurrentConnection( 'test' );
   * </code>
   *
   * @param string $connectionName Defaults to 'test'
   */
  static public function createSqliteMemoryDb( $connectionName = 'test' )
  {
    Doctrine_Manager::connection( new PDO('sqlite::memory:'), $connectionName );
    Doctrine::createTablesFromModels( dirname(__FILE__).'/../../lib/model/doctrine');
  }

  /**
   * Destroy a connection
   * A connection name can be used to identify the connection to close
   *
   * @param string $connectionName
   */
  static public function destroySqliteMemoryDb( $connectionName = 'test' )
  {
    $instance = Doctrine_Manager::getInstance();
    $connection = $instance->getConnection( $connectionName );
    $instance->closeConnection( $connection );
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
    $prepMethod = strtolower( $model ) . 'SpecificPreparation';
    if( ProjectN_Test_Unit_Factory::hasClassMethod( $prepMethod ) )
    {
      ProjectN_Test_Unit_Factory::$prepMethod( $autoCreateRelatedObjects );
    }

    if( is_null( $data ) )
    {
      $data = array();
    }
    $defaultData = ProjectN_Test_Unit_Factory::getDefaultsForModel( $model );
    $completeData = array_merge( $defaultData, $data );

    $object = new $model();
    $object->fromArray( $completeData, true );

    $finishMethod = strtolower( $model ) . 'SpecificFinish';
    if( ProjectN_Test_Unit_Factory::hasClassMethod( $finishMethod ))
    {
      ProjectN_Test_Unit_Factory::$finishMethod( $object, $autoCreateRelatedObjects );
    }
    
    return $object;
  }

  static private function poiSpecificPreparation( $autoCreateRelatedObjects )
  {
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
  }

  static private function poiSpecificFinish( $object, $autoCreateRelatedObjects )
  {
    if( $autoCreateRelatedObjects )
    {
      $object->link( 'PoiCategory', array( 1 ) );
      $object->link( 'Vendor', array( 1 ) );
    }
  }

  static private function hasClassMethod( $methodName )
  {
    $lazyReflection = new ReflectionClass( 'ProjectN_Test_Unit_Factory' );
    $lazyMethods = $lazyReflection->getMethods();
    foreach ( $lazyMethods as $aMethod )
    {
      if( $aMethod->name == $methodName )
      {
        return true;
      }
    }
  }

  static private function getDefaultsForModel( $model )
  {
    $defaults = array(
      
      'vendor' => array(
        'city' => 'test city',
        'language' => 'test language'
      ),

      'poicategory' => array(
        'name' => 'test name'
      ),

      'poi' => array(
        'poi_name' => 'test name',
        'street' => 'test street',
        'city' => 'test town',
        'country' => 'test country',
        'vendor_poi_id' => '0',
        'local_language' =>'aa',
        'country_code' => 'aa',
        'longitude' => '0.0',
        'latitude' => '0.0',
      ),
      
    );

    return $defaults[ strtolower( $model ) ];
  }
}

?>
