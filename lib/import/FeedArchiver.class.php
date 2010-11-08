<?php
/**
 * @package projectn
 * @subpackage lib
 *
 *
 * @author Peter Johnson <peterjohnson@timeout.com>
 * @copyright Timeout Communications Ltd &copyright; 2009
 *
 * @version 1.0.0
 *
 *
 */
class FeedArchiver
{
  private $enabled = true;
  private $archivePath;
  private $date;

  private $vendor;
  private $data;
  private $description;

  public function __construct( $vendor, $data, $description = 'feed' )
  {
      if( $this->enabled !== true ) return;

      $this->date           = date( 'Y-m-d-H:i:s' );
      $this->vendor         = $vendor;
      $this->data           = $data;
      $this->description    = $description;

      if( $this->validate() !== true ) return;
      
      $this->storeArchive();
  }

  private function storeArchive()
  {
      // Set Archive Path.
      $this->archivePath = sfConfig::get( 'sf_root_dir' ) . "/vendor_feeds/{$this->date}_{$this->vendor['city']}_{$this->description}.archive";

      // Write to File.
      if( file_put_contents( $this->archivePath, $this->data ) == false )
      {
          $this->log( 'Failed to Write Archive to Disk.' );
      }
  }

  private function validate()
  {
      // Validate Vendor Object.
      if( !is_object( $this->vendor ) || get_class( $this->vendor ) != 'Vendor' ||
          !isset( $this->vendor['city'] ) || !is_string( $this->vendor['city'] ) )
      {
          $this->log( 'Invalid Vendor Object.' );
          return false;
      }

      // Validate Data String.
      if( !is_string( $this->data ) )
      {
          $this->log( 'Archive Data is Not a String.' );
          return false;
      }

      // Validate Description String.
      if( !is_string( $this->description ) )
      {
          $this->log( 'Archive Description is Not a String.' );
          return false;
      }

      return true;
  }

  private function log( $message )
  {
      $archiverLog = sfConfig::get( 'sf_root_dir' ) . '/log/archiver.log';
      file_put_contents( $archiverLog, "{$this->date} {$this->vendor['city']} {$this->description} -- {$message}\n", FILE_APPEND );
  }
}