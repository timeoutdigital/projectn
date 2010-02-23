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

  /**
   * Get a record by Vendor and that vendor's id for the record.
   * Returns a new empty record if none found
   *
   * @param string $tableName
   * @param Vendor $vendor
   * @param string $vendorRecordUid
   * @return Doctrine_Record
   */
  protected function getRecord( $tableName, $vendor, $vendorRecordUid )
  {
    $table = Doctrine::getTable( $tableName );

    $vendorUid = $table->getVendorUidFieldName();
    
    $record = $table
      ->createQuery( 'record' )
      ->addWhere( 'record.vendor_id = ?', $vendor['id'] )
      ->addWhere("record.$vendorUid = ?" , $vendorRecordUid )
      ->fetchOne();

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
