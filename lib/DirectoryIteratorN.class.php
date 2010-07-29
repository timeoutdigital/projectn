<?php

/*
 * Don't Extend this, update it please.
 */

final class DirectoryIteratorN
{
    const DIR_ALL = 'all';
    const DIR_FILES = 'files';
    const DIR_FOLDERS = 'folders';

    final public static function iterate( $dir = ".", $which = self::DIR_ALL, $extension = "" )
    {
        $filesArray = array();
        $path = realpath( $dir );
        
        if( $path === false || !is_dir( $path ) )
            throw new Exception( "Folder Not Found '" . $dir . "'" );

        else $d = dir( $path );

        while ( false !== ( $entry = @$d->read() ) )
        {
            if( $entry == '.' || $entry == '..' )
                continue;

            if( $which === self::DIR_FOLDERS )
            {
                if( is_dir( realpath( $d->path . "/" . $entry ) ) )
                    $filesArray[] = $entry;
            }

            elseif( $which === self::DIR_FILES )
            {
                if( is_file( realpath( $d->path . "/" . $entry ) ) )
                    if( strlen( $extension ) === 0 || strtolower( $extension ) === strtolower( substr(strrchr($entry, '.'), 1) ) )
                        $filesArray[] = $entry;
            }

            else $filesArray[] = $entry;
        }

        $d->close();
        sort( $filesArray );
        return $filesArray;
    }
}