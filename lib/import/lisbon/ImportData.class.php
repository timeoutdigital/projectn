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
abstract class ImportData
{

  /**
   * Requires an Importer to listen to onMap( Doctrine_Record )
   *
   * @var Importer
   */
  private $importer;

  /**
   * @var Importer $importer
   */
  public function __construct( $importer )
  {
    $this->importer = $importer;
  }

  /**
   * Retrieves and maps POI data
   */
  abstract public function mapPois();

  /**
   * Retrieves and maps Event data
   */
  abstract public function mapEvents();

  /**
   * Retrieves and maps EventOccurrence data
   */
  abstract public function mapEventOccurrences();

  /**
   * Retrieves and maps Movie data
   */
  abstract public function mapMovies();

  protected function notifyImporter( Doctrine_Record $record )
  {
    $this->importer->onMap( $record );
  }
}
?>
