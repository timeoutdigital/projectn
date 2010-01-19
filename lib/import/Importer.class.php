<?php
/**
 * @package import.lib
 *
 * @author Clarence Lee <clarencelee@timeout.com>
 */
abstract class Importer
{
  /**
   * @var mixed
   */
  protected $_data;

  /**
   * @var ImportSaver
   */
  protected $_importSaver;

  /**
   * retrieve and save data
   */
  final public function loadData()
  {
    $this->_data = $this->load();
    $this->save();
  }

  /**
   * load the data to be saved
   */
  abstract protected function load();

  /**
   * @returns boolean
   */
  final public function hasData()
  {
    return !is_null( $this->_data );
  }

  /**
   * set an ImportSaver to save data with
   */
  final public function setSaver( $saver )
  {
    if( $saver instanceof ImportSaver )
    {
      $this->_importSaver = $saver;
    }
    else
    {
      throw new ImportException( 'Tried to set saver using ' . $saver );
    }
  }

  /**
   * use the ImportSaver to save data
   */
  final protected function save()
  {
    if( !is_null( $this->_importSaver ) )
    {
      $this->_importSaver->save();
    }
    else
    {
      throw new ImportException( 'Importer requires a saver.' );
    }
  }
}
?>
