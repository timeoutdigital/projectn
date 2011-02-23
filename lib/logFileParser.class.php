<?php
/**
 * Parses log files
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class logFileParser
{

    private $_logTask;


    public function processFile( $logFile )
    {
        $handle = fopen( $logFile, 'r');

        if ($handle) {
            while ( ( $buffer = fgets( $handle ) ) !== false )
            {
                $this->parseLine( $buffer );
            }
            if (!feof($handle))
            {
                throw new logFileParserException( 'unexptected fgets() looks like not the whole file was processed' );
            }
            fclose($handle);
        }
    }
    

    private function parseLine( $line )
    {
        //find out of we can find a key line
        $matches = array();
        if ( preg_match( '/\>\>\s([A-Za-z]+)\:\s+(.*)/', $line, $matches ) === false )
        {
            throw new logFileParserException( 'regex parsing failed' );
        }

        //guess could remove this if
        if ( count( $matches ) === 3 )
        {
            //key line
            switch( $matches[ 1 ] )
            {
                case 'START':
                    $this->addStart( $matches[ 2 ] );
                    break;
                case 'File':
                    $this->addFile( $matches[ 2 ] );
                    break;
                case 'Params':
                    $this->addParams( $matches[ 2 ] );
                    break;
                case 'Message':
                    $this->addMessage( $matches[ 2 ], true );
                    break;
                case 'END':
                    $this->addEnd( $matches[ 2 ] );
                    break;
                default:
                    //part of multi line message (no key line)
                    $this->addMessage( $line );
            }
        }
        else
        {
            //part of multi line message (no key line)
            $this->addMessage( $line );
        }
        
        return true;
    }


    private function addStart( $preParsedLine )
    {        
        if ( $this->_logTask !== null )
        {
            $this->addMessage( 'log file parser: unexpected log start line found, looks like something serious happened', true );
            $this->_logTask[ 'status' ] = 'error';
            $this->_logTask->save();
            $this->_logTask->free();
            $this->_logTask = null;
        }

        $matches = array();
        if ( preg_match( '/^([a-zA-Z0-9]+)\s{1}([0-9]{4}\-[0-9]{2}\-[0-9]{2}\s{1}[0-9]{2}\:[0-9]{2}\:[0-9]{2})$/', $preParsedLine, $matches ) === false )
        {
            throw new logFileParserException( 'invalid start line found' );
        }

        if ( count( $matches ) === 3 )
        {
            if ( empty( $this->_logTask[ 'name' ] ) && empty( $this->_logTask[ 'execution_start' ] ) )
            {
                $this->_logTask = new LogTask();
                $this->_logTask['status'] = 'parsing';
                $this->_logTask[ 'name' ] = $matches[ 1 ];
                $this->_logTask[ 'execution_start' ] = $matches[ 2];
            }
            else
            {
                throw new logFileParserException( 'start line already exists' );
            }
        }
        else
        {
            throw new logFileParserException( 'incomplete start line found' );
        }

        $this->_logTask->save();
    }

    private function addFile( $preParsedLine )
    {
        if ( ! $this->_logTask instanceof  logTask )
        {
            throw new logFileParserException( 'cannot add file, no active logger, most probably there is no valid start line present' );
        }

        $matches = array();
        if ( preg_match( '/^(\/[a-zA-Z0-9]+.*$)/', $preParsedLine, $matches ) === false )
        {
            throw new logFileParserException( 'invalid command line found' );
        }

        if ( count( $matches ) === 2 )
        {
            if ( empty( $this->_logTask[ 'command' ] ) )
            {
                $this->_logTask[ 'command' ] = $matches[ 1 ];
            }
            else
            {
                throw new logFileParserException( 'command line is already existing' );
            }
        }
        else
        {
            throw new logFileParserException( 'incomplete command line' );
        }

        $this->_logTask->save();
    }

    private function addParams( $preParsedLine )
    {
        if ( ! $this->_logTask instanceof  logTask )
        {
            throw new logFileParserException( 'cannot add params, no active logger, most probably there is no valid start line present' );
        }

        if ( !empty( $preParsedLine ) )
        {
            $params = explode( ',', $preParsedLine );

            if ( count( $params)  === 0 )
                throw new logFileParserException( 'invalid param string found' );

            if ( $this->_logTask[ 'LogTaskParam' ]->count() != 0 )
            {
                throw new logFileParserException( 'params are already added' );
            }

            foreach( $params as $param )
            {
                $nameValue = explode( '=', $param );

                if ( count( $nameValue ) !== 2 )
                {
                    throw new logFileParserException( 'invalid param pair' );
                }

                $nameValue[ 0 ] = trim( $nameValue[ 0 ] );

                //trim and remove single and double quotes
                $nameValue[ 1 ] = trim( str_replace( array( '"', '\'' ), array( '', '' ), $nameValue[ 1 ]) );

                $this->_logTask->addParam( $nameValue[ 0 ],  $nameValue[ 1 ]);

                if ( $nameValue[ 0 ] == 'city' && !empty( $nameValue[ 1 ] ) )
                {
                    $this->tryToSetVendorByCityParam( $nameValue[ 1 ] );
                }
            }
        }

        $this->_logTask->save();
    }

    private function addMessage( $line, $startNewMessage = false )
    {
        if ( ! $this->_logTask instanceof  logTask )
        {
            throw new logFileParserException( 'cannot add message, no active logger, most probably there is no valid start line present' );
        }

        $this->_logTask['status'] = $this->detectErrorLevel( $line, $this->_logTask['status'] );

        if ( $startNewMessage || !$this->_logTask[ 'LogTaskMessage' ][ 0 ] )
        {
            $logTaskMessage = new LogTaskMessage();
            $logTaskMessage[ 'message' ] = $line;
            $this->_logTask[ 'LogTaskMessage' ][] = $logTaskMessage;            
        }
        else
        {
            $this->_logTask[ 'LogTaskMessage' ][ count( $this->_logTask[ 'LogTaskMessage' ] ) -1 ][ 'message' ] .= $line;
        }

        $this->_logTask->save();
    }

 

    private function addEnd( $preParsedLine )
    {
        if ( ! $this->_logTask instanceof  logTask )
        {
            throw new logFileParserException( 'cannot add message line, no active logger, most probably there is no valid start line present' );
        }

        $matches = array();
        if ( preg_match( '/^([a-zA-Z0-9]+)\s+([0-9]{4}\-[0-9]{2}\-[0-9]{2}\s{1}[0-9]{2}\:[0-9]{2}\:[0-9]{2})$/', $preParsedLine, $matches ) === false )
        {
            throw new logFileParserException( 'invalid end line found' );
        }

        if ( count( $matches ) === 3 )
        {
            if ( empty( $this->_logTask[ 'execution_end' ] ) )
            {
                if ( $this->_logTask[ 'name' ] !== $matches[ 1 ] )
                    throw new logFileParserException( 'wrong end line deteced' );

                $this->_logTask[ 'execution_end' ] = $matches[ 2];
            }
            else
            {
                throw new logFileParserException( 'end line is already exists' );
            }
        }
        else
        {
            throw new logFileParserException( 'incomplete end line found' );
        }

        if ( $this->_logTask['status'] == 'parsing' )
            $this->_logTask['status'] = 'success';

        $this->_logTask->save();
        $this->_logTask->free();
        $this->_logTask = null;
    }

    private function detectErrorLevel( $line, $currentErrorLevel )
    {
        $matchesArray = array();
        if ( preg_match_all( '/(Fatal error|Error|Notice|Warning)\:/i', $line, $matchesArray ) === false )
        {
            throw new logFileParserException( 'error parsing failed' );
        }

        foreach ( $matchesArray[1] as $match )
        {
            switch( $match )
            {
                case 'Notice':
                    if ( $currentErrorLevel != 'error' && $currentErrorLevel != 'warning' )
                        $currentErrorLevel = 'notice';
                    break;
                case 'Warning':
                    if ( $currentErrorLevel != 'error' )
                        $currentErrorLevel = 'warning';
                    break;
                default:
                    $currentErrorLevel = 'error';
                    break;
            }
        }

        return $currentErrorLevel;
    }

    private function getPossibleStatuses()
    {
      foreach( Doctrine::getTable( 'LogTask' )->getColumns() as $columnName => $columInfo )
      {
          if ( $columnName == 'status')
          {
               return $columInfo[ 'values' ];
               break;
          }
      }

      return  array();
    }

    private function tryToSetVendorByCityParam( $city )
    {
        $vendorLookup = Doctrine::getTable( 'Vendor' )->findByCity( $city );

        //must be exactly one, as otherwise not found or not unique
        if ( $vendorLookup->count() == 1 )
        {
            $this->_logTask['vendor_id'] = $vendorLookup[0]['id'];
        }
    }
    
}

class logFileParserException extends Exception{}