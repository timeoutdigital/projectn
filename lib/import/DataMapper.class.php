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
    ImportLogger::saveRecordComputeChangesAndLog( $record );
  }

  protected function notifyImporterOfFailure( Exception $exception , Doctrine_Record $record = NULL, $message='' )
  {
    ImportLogger::getInstance()->addError( $exception ,$record, $message );
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

  protected function fixIteration( $xml )
  {
      foreach( $xml as $fixSimpleXMLBug ) $data[] = $fixSimpleXMLBug;
      return $data;
  }

  protected function clean( $string )
  {
    return stringTransform::mb_trim( $string );
  }

  /**
   * Apply Feed geocode to Record if valid, or catch exception and call notifyImportFailure()
   * @param Poi $record
   * @param float $latitude
   * @param float $longitude
   */
  protected function applyFeedGeoCodesHelper( Poi $record, $latitude, $longitude )
  {
      // #881 Catch Geocode out of vendor boundary error
      try{

          $record->applyFeedGeoCodesIfValid( (float) $latitude, (float) $longitude );

      } catch ( Exception $exception ) {

          $this->notifyImporterOfFailure( $exception, $record );

      }
  }

}
?>
