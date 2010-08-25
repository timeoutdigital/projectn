<?php

class mediaDownloadTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'media-download';
    $this->briefDescription = 'Download & Update Media Files.';
    $this->detailedDescription = <<<EOF
The [media-download|INFO] task does things.
Call it with:

  [php symfony media-download|INFO]
EOF;
  }

  protected function setUp( $options = array() )
  {
    // Configure Database.
    $databaseManager = new sfDatabaseManager( $this->configuration );
    $connection = $databaseManager->getDatabase( $options['connection'] ? $options['connection'] : null )->getConnection();

    // Valid File Types.
    $this->validFileTypes = array( 'image/jpeg' );
  }

  protected function execute( $arguments = array(), $options = array())
  {
    $this->setUp( $options );
    $this->batchDownload( 'Poi' );
    $this->batchDownload( 'Event' );
    $this->batchDownload( 'Movie' );
  }

  protected function batchDownload( $model )
  {
    $media = Doctrine::getTable( $model . 'Media' )->findByStatus( 'new' );

    foreach( $media as $m )
    {
        if( $this->recordIsNew( $m ) || $this->recordHasChanged( $m ) )
        {
            $this->download( $m );
        }
    }
  }

  protected function recordIsNew( $record )
  {
      if( !isset( $record['url'] ) ) return false;
      return ( $record['status'] == 'new' ) ? true : false;
  }

  protected function recordHasChanged( $record )
  {
      if( !isset( $record['url'] ) ) return false;

      $headers = $this->fetchFinalHeader( $record['url'] );

      return ( $headers['Content-Type']      != $record['mime_type'] ) ||
             ( $headers['Content-Length']    != $record['content_length'] ) ||
             ( $headers['ETag']              != $record['etag'] );
  }

  protected function download( $record )
  {
      if( !isset( $record['Poi']['Vendor']['city'] ) || !isset( $record['ident'] ) || !isset( $record['url'] ) )
          return;

      $type         = strtolower( str_replace( 'Media', '', get_class( $record ) ) );
      $city         = str_replace( ' ', '_', $record['Poi']['Vendor']['city'] );
      $destination  = sfConfig::get( 'sf_root_dir' ) . "/import/{$city}/{$type}/media/{$record['ident']}.jpg";
      
      $curl = new Curl( $record['url'] );
      $curl->downloadTo( $destination );

      $record['mime_type']          = $curl->getContentType();
      $record['file_last_modified'] = $curl->getLastModified();
      $record['etag']               = $curl->getETag();
      $record['content_length']     = $curl->getContentLength();
      $record['status']             = 'valid';
      $record['last_header_check']  = date("Y-m-d H:i:s");

      try {
        $this->validateDownload( $curl, $destination, $record );
      }
      catch( MediaException $e )
      {
        $record['mime_type']          = NULL;
        $record['file_last_modified'] = NULL;
        $record['etag']               = NULL;
        $record['content_length']     = NULL;
        $record['status']             = 'error';
        $record->save();

        throw $e;
      }

      $record->save();
  }

  protected function validateDownload( $curl, $destination, $record )
  {
      $curlInfo             = $curl->getCurlInfo();
      $responseCode         = $curlInfo[ 'http_code' ];

      switch( false )
      {
          case $responseCode == 200 /* success */ || $responseCode == 304 /* not modified */ :
              $errorMessage = "Invalid HTTP Code: '{$responseCode}'"; break;

          case in_array( $record['mime_type'], $this->validFileTypes ) :
              $errorMessage = "Invalid MIME Type: '{$record['mime_type']}'"; break;

          case is_numeric( $record['content_length'] ) && $record['content_length'] > 0 :
              $errorMessage = "Invalid Byte Length: '{$record['content_length']}'"; break;

          case file_exists( $destination ) :
              $errorMessage = "Failed to Save to Destination: '{$destination}'"; break;

          default : $errorMessage = '';
      }

      if( !empty( $errorMessage ) )
      {
        unlink( $destination );
        throw new MediaException( "{$errorMessage} for ".get_class( $record )." id:".$record['id'] );
      }
  }

  /**
   * Get the last set of headers, removing redirect headers.
   */
  protected function fetchFinalHeader( $url )
  {
      $headers = get_headers( $url, 1 );
      if( $headers === false ) return array();

      foreach( $headers as $key => $value )
      {
          if( is_array( $value ) )
          {
            $headers[ $key ] = array_pop( $value );
          }
          if( is_numeric( $key ) )
          {
            $headers[ 'Status-Code' ] = preg_replace( "/[^0-9]/", "", $value );
          }
      }

      return $headers;
  }
}