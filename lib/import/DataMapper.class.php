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

  protected function notifyImporter( Doctrine_Record $record )
  {
    $this->importer->onRecordMapped( $record );
  }
  
  protected function notifyImporterOfFailure( Exception $exception , Doctrine_Record $record = NULL, $message='' )
  {
    $this->importer->onRecordMappingException( $exception ,$record, $message );
  }

    /**
     * helper function to add images
     *
     * @param Doctrine_Record $storeObject
     * @param $url
     */
    protected function addImageHelper( Doctrine_Record $storeObject, $url )
    {
        if ( (string) $url != '' )
        {
            try
            {
                $storeObject->addMediaByUrl( (string) $url );
                return true;
            }
            catch( Exception $e )
            {
                $this->notifyImporterOfFailure( $e, $storeObject, "Failed to add media for object" );
            }
        }
    }
}
?>
