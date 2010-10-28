<?php

/**
 * Check export files are ready for delivery
 *
 * @package projectn
 * @subpackage task
 *
 * @author Peter Johnson <peterjohnson@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 * This script checks yesterdays exports and compares todays exports.
 * It will check various different metrics and respond with error if any
 * major or unexpected result is returned.
 *
 */

class exportCheckTask extends nagiosTask
{
    protected   $description  = 'Check export files are ready for delivery';
    private     $filesizeWarningPercent = 90;

    protected function executeNagiosTask( $arguments = array(), $options = array() )
    {
        // Set yesterdays directory.
        $yesterdayDir = $this->getExportDirectoryPathForDate( strtotime( '-1 day' ) );

        // Set todays directory.
        $todayDir = $this->getExportDirectoryPathForDate( time() );

        // Make sure yesterdays export directory exists.
        if( !is_dir( $yesterdayDir ) ) throw new NagiosException( 'Folder not found: ' . $yesterdayDir );

        // Make sure todays export directory exists.
        if( !is_dir( $todayDir ) ) throw new NagiosException( 'Folder not found: ' . $todayDir );

        // Make sure we have the same export folders yesterday and today eg. [poi,event,movie]
        if( DirectoryIteratorN::iterate( $yesterdayDir, DirectoryIteratorN::DIR_FOLDERS ) !==
            DirectoryIteratorN::iterate( $todayDir, DirectoryIteratorN::DIR_FOLDERS ) )
                $this->addError( 'Yesterdays export folder names dont match todays folder names.');

        // Cycle through yesterdays export directories.
        foreach( DirectoryIteratorN::iterate( $yesterdayDir, DirectoryIteratorN::DIR_FOLDERS ) as $folder )
        {
            // Cycle through yesterdsys export files.
            foreach( DirectoryIteratorN::iterate( $yesterdayDir . DS . $folder, DirectoryIteratorN::DIR_FILES, 'xml' ) as $yFile )
            {
                // Cycle through todays export files.
                foreach( DirectoryIteratorN::iterate( $todayDir . DS . $folder, DirectoryIteratorN::DIR_FILES, 'xml' ) as $tFile )
                {
                    // Set full file paths.
                    $yFilePath = realpath( $yesterdayDir . DS . $folder . DS . $yFile );
                    $tFilePath = realpath( $todayDir . DS . $folder . DS . $tFile );

                    // Check file name from yesterday exists today.
                    if( $yFile === $tFile )
                    {
                        // Filesize margin.
                        $todayFilesizePercentOfYesterday = round( filesize( $tFilePath ) / ( filesize( $yFilePath ) / 100 ) );

                        // Filesize checks.
                        if( filesize( $tFilePath ) < filesize( $yFilePath ) * ( $this->filesizeWarningPercent / 100 ) )
                            $this->addWarning( 'Filesize dropped by more than acceptable margin: ' . $tFilePath . ' ('. round( filesize( $tFilePath ) / 1024, 2 ) . 'kb -' . (100 - $todayFilesizePercentOfYesterday) .'%)' );

                        continue 2;
                    }
                }

                // File not found.
                $this->addError( 'Todays exports missing a file that existed yesterday: ' . $yFile );
            }
        }
    }
}
