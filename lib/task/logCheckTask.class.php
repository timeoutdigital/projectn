<?php

class logCheckTask extends sfBaseTask
{

  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'project_n'),
    ));

    $this->namespace        = 'projectn';
    $this->name             = 'log-check';
    $this->briefDescription = 'Produce invoice from exports';
    $this->detailedDescription = <<<EOF
The [invoice|INFO] task does things.
Call it with:

  [php symfony invoice|INFO]
EOF;
  }

  protected function setUp( $options = array() )
  {
    
  }

  protected function execute($arguments = array(), $options = array())
  {
        $log = $this->iterateLog( '/n/london.live.log' );
        $this->printTable( $log );
  }

  protected function printTable( array $logData )
  {
    $spacing = 0;
    $eof = ',';
    $eol = PHP_EOL;
    $ignoreKeys = array( 'end' );

    if( $spacing > 0 ) echo str_repeat( '-', 170 ) . $eol;
    foreach( $logData[0] as $key => $message )
        if( !in_array( $key, $ignoreKeys ) )
            echo str_pad( $key, $spacing, " " ) . $eof;
    echo str_pad( 'running time', $spacing, " " ) . $eof;
    if( $spacing > 0 ) echo $eol . str_repeat( '-', 170 ) . $eol;

    foreach( $logData as $log )
    {
        foreach( $log as $key => $message )
            if( !in_array( $key, $ignoreKeys ) )
                echo str_pad( $message, $spacing, " " ) . $eof;
        
        echo str_pad( date( 'H:i:s', strtotime( $log['end'] ) - strtotime( $log['start'] ) - date( 'Z' ) ), $spacing, " " ) . $eof;

        echo $eol;
    }
  }

  private function iterateLog( $logPath )
  {
      $d = explode( PHP_EOL, trim( file_get_contents( $logPath ) ) );
      foreach( $d as $k => $v ) if( trim( $v ) == '' ) unset( $d[ $k ] );;
      $d = array_values( $d );

      $logRuns = array();

      foreach( $d as $k => $v )
      {
          if( substr( $v, 0, 4 ) !== '2010' ) continue;

          $logDate = strtotime( substr( $v, 0, 19 ) );
          $logDateMessage = trim( substr( $v, 23, -4 ) );

          if( substr( $logDateMessage, 0, strlen( 'start import for ' ) ) == 'start import for ' )
          {
              $i = $this->extractLogInfo( $v );
              $i[ 'start' ] = date( 'Y-m-d h:i:s', $i[ 'date' ] );
              unset( $i[ 'date' ] );
              $i[ 'end' ] = 'unknown';
              $logRuns[] = $i;
          }

          else if( substr( $logDateMessage, 0, strlen( 'end import for ' ) ) == 'end import for ' )
          {
              $i = $this->extractLogInfo( $v );
              foreach( $logRuns as $runkey => $run )
              {
                  if( $run[ 'city' ] == $i['city'] && $run[ 'type' ] == $i['type'] && $run[ 'env' ] == $i['env'] )
                  {
                      $logRuns[ $runkey ][ 'end' ] = date( 'Y-m-d h:i:s', $i[ 'date' ] );
                      $logRuns[ $runkey ][ 'memory' ] = $i[ 'memory' ];
                  }
              }
          }
      }
      return $logRuns;
  }

  private function extractLogInfo( $logLine )
  {
      $logDate = strtotime( substr( $logLine, 0, 19 ) );
      $logDateMessage = trim( substr( $logLine, 23, -3 ) );

      if( substr( $logDateMessage, 0, strlen( 'start import for ' ) ) == 'start import for ' )
          $logSettingsMessage = substr( $logDateMessage, strlen( 'start import for ' ) );
      else if( substr( $logDateMessage, 0, strlen( 'end import for ' ) ) == 'end import for ' )
          $logSettingsMessage = substr( $logDateMessage, strlen( 'end import for ' ) );

      $memoryUsage = 'unknown';
      $findMemory = stripos( $logSettingsMessage, ' -- Peak memory used:' );

      if( $findMemory !== false )
      {
          $memoryUsage = trim( substr( $logSettingsMessage, stripos( $logSettingsMessage, ' -- Peak memory used:' ) + strlen( ' -- Peak memory used:' ) ) );
          $logSettingsMessage = substr( $logSettingsMessage, 0, $findMemory );
      }

      $logCity = trim( substr( $logSettingsMessage, 0, stripos( $logSettingsMessage, '(' ) ) );

      $logSettingsMessage = substr( $logSettingsMessage, strlen( $logCity ) + 2 );
      $logType = trim( substr( $logSettingsMessage, strlen( 'type: ' ), stripos( $logSettingsMessage, ',' ) -strlen( 'type: ' ) ) );

      $logSettingsMessage = substr( $logSettingsMessage, strlen( 'type: ' ) + strlen( $logType ) + 2 );
      $logEnv = trim( substr( $logSettingsMessage, strlen( 'environment:' ), -1 ) );

      return array( 'date' => $logDate, 'city' => $logCity, 'type' => $logType, 'env' => $logEnv, 'memory' => $memoryUsage );

  }
}