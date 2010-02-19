<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage lisbon.import.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
abstract class DataMapper
{

  /**
   * Requires an Importer to listen to onMap( Doctrine_Record )
   *
   * @var Importer
   */
  private $importer;

  public function getMapMethods()
  {
    $reflection = new ReflectionObject( $this );
    $publicMethods = $reflection->getMethods( ReflectionMethod::IS_PUBLIC );

    $mapMethods = array_filter( $publicMethods,
      create_function(
        '$method',
        'return preg_match( "/^map[A-Z]*/", $method->name );'
      ) );
    
    return $mapMethods;
  }
  
  public function setImporter( $importer )
  {
    $this->importer = $importer;
  }

  protected function getRecord( $tableName, $vendorUidFieldname, $vendorUid )
  {
    $record = Doctrine::getTable( $tableName )->findOneBy( $vendorUidFieldname, $vendorUid );

    if( !$record )
    {
      $record = new $tableName;
    }
  
    return $record;
  }

  protected function notifyImporter( Doctrine_Record $record )
  {
    $this->importer->onRecordMapped( $record );
  }
  
  protected function notifyImporterOfFailure( Exception $exception ,Doctrine_Record $record, $message='' )
  {
    $this->importer->onRecordMappingException( $exception ,$record, $message );
  }
}
?>
