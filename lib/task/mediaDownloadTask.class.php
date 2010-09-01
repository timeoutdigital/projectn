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

    // Schedule of which id suffixes ie. id:113 = 3, id:1024 = 4, to check on which day.
    // This is used to check existing media only once per week, spreading the load.
    $this->schedule = array();
    $this->schedule['Sunday']    = array( 0 );
    $this->schedule['Monday']    = array( 1, 7 );
    $this->schedule['Tuesday']   = array( 2 );
    $this->schedule['Wednesday'] = array( 3, 8 );
    $this->schedule['Thursday']  = array( 4 );
    $this->schedule['Friday']    = array( 5, 9 );
    $this->schedule['Saturday']  = array( 6 );
  }

  protected function execute( $arguments = array(), $options = array())
  {
    $this->setUp( $options );

    // Download new Media.
    $this->batchProcess( Doctrine::getTable( 'PoiMedia'   )->findByStatus( 'new' ) );
    $this->batchProcess( Doctrine::getTable( 'EventMedia' )->findByStatus( 'new' ) );
    $this->batchProcess( Doctrine::getTable( 'MovieMedia' )->findByStatus( 'new' ) );

    // Check Media scheduled for today.
    $this->batchProcess( Doctrine::getTable( 'PoiMedia' )->createQuery()
        ->where( 'RIGHT( id, 1 ) IN ( ? )', implode( $this->schedule[ date('l') ] ) )
        ->execute() );

    $this->batchProcess( Doctrine::getTable( 'EventMedia' )->createQuery()
        ->where( 'RIGHT( id, 1 ) IN ( ? )', implode( $this->schedule[ date('l') ] ) )
        ->execute() );

    $this->batchProcess( Doctrine::getTable( 'MovieMedia' )->createQuery()
        ->where( 'RIGHT( id, 1 ) IN ( ? )', implode( $this->schedule[ date('l') ] ) )
        ->execute() );
  }

  protected function batchProcess( Doctrine_Collection $collection )
  {
    foreach( $collection as $media )
    {
        if( $this->mediaIsNew( $media ) || $this->mediaHasChanged( $media ) )
        {
            try
            {
                $this->download( $media );
            }

            catch( Exception $e )
            {
                echo "MediaDownloadTask threw a " . get_class( $e ) . " Exception with message:" . PHP_EOL;
                echo $e->getMessage() . str_repeat( PHP_EOL, 2 );
            }
        }
    }

    if( method_exists( $collection, 'free' ) )
        $collection->free( true );
        
    unset( $collection );
  }

  protected function mediaIsNew( Doctrine_Record $media )
  {
      return isset( $media['status'] ) && $media['status'] == 'new' ? true : false;
  }

  protected function mediaHasChanged( Doctrine_Record $media )
  {
      if( !isset( $media['url'] ) ) return false;

      $headers = $this->fetchAuthoritativeHeader( $media['url'] );

      return ( isset( $headers['Content-Type'] )   && $headers['Content-Type']   != $media['mime_type'] ) ||
             ( isset( $headers['Content-Length'] ) && $headers['Content-Length'] != $media['content_length'] ) ||
             ( isset( $headers['ETag'] )           && $headers['ETag']           != $media['etag'] );
  }

  protected function download( Doctrine_Record $media )
  {
      $parentClass  = str_replace( 'Media', '', get_class( $media ) );
      
      if( !isset( $media[ $parentClass ]['Vendor']['city'] ) || !isset( $media['ident'] ) || !isset( $media['url'] ) )
          return;

      $type         = strtolower( str_replace( 'Media', '', get_class( $media ) ) );
      $city         = str_replace( ' ', '_', $media[ $parentClass ]['Vendor']['city'] );
      $destination  = sfConfig::get( 'sf_root_dir' ) . "/import/{$city}/{$type}/media/{$media['ident']}.jpg";
      
      $curl = new Curl( $media['url'] );
      $curl->downloadTo( $destination );

      $media['mime_type']          = $curl->getContentType();
      $media['file_last_modified'] = $curl->getLastModified();
      $media['etag']               = $curl->getETag();
      $media['content_length']     = $curl->getContentLength();
      $media['status']             = 'valid';
      $media['last_header_check']  = date("Y-m-d H:i:s");

      try {
        $this->validateDownload( $curl, $destination, $media );
      }
      catch( MediaException $exception )
      {
        $media['mime_type']          = NULL;
        $media['file_last_modified'] = NULL;
        $media['etag']               = NULL;
        $media['content_length']     = NULL;
        $media['status']             = 'error';
        $media->save();

        throw $exception; // Re-throw Exception
      }

      $media->save();
  }

  protected function validateDownload( Curl $curl, $destination, Doctrine_Record $media )
  {
      $curlInfo             = $curl->getCurlInfo();
      $responseCode         = $curlInfo[ 'http_code' ];
      $imageDimensions      = @getimagesize( $destination );

      switch( false )
      {
          case $responseCode == 200 /* success */ || $responseCode == 304 /* not modified */ :
              $errorMessage = "Invalid HTTP Code: '{$responseCode}'"; break;

          case file_exists( $destination ) :
              $errorMessage = "Failed to Save to Destination: '{$destination}'"; break;

          case in_array( mime_content_type( $destination ), $this->validFileTypes ) :
          case in_array( $media['mime_type'], $this->validFileTypes ) :
              $errorMessage = "Invalid MIME Type: '{$media['mime_type']}'"; break;

          case is_numeric( $media['content_length'] ) && $media['content_length'] > 0 :
              $errorMessage = "Invalid Byte Length: '{$media['content_length']}'"; break;

          case isset( $imageDimensions[0] ) && is_numeric( $imageDimensions[0] ) && $imageDimensions[0] > 0 :
          case isset( $imageDimensions[1] ) && is_numeric( $imageDimensions[1] ) && $imageDimensions[1] > 0 :
             $errorMessage = "Invalid Image Size: '{$imageDimensions[0]}x{$imageDimensions[1]}'"; break;

          default : $errorMessage = '';
      }

      if( !empty( $errorMessage ) )
      {
        unlink( $destination );
        throw new MediaException( "{$errorMessage} for ".get_class( $media )." id:".$media['id'] );
      }
  }

  /**
   * Get the last set of headers, removing redirect headers.
   */
  protected function fetchAuthoritativeHeader( $url )
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
            preg_match( "/\s([0-9]{3})\s/", $value, $matches );
            $headers[ 'Status-Code' ] = array_pop( $matches );
            unset( $headers[ $key ] );
          }
      }
      return $headers;
  }
}