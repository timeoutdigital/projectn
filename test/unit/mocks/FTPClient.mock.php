<?php

class FTPClientMock extends FTPClient
{
    // Override to make sure that Parent Don't get Called, as its using ftp_connect to check server exists!
    public function __construct( $host, $username, $password, $targetPath)
    {
       // Don;t call the parent
    }

    //getSourcePath
    public function fetchLatestFileByPattern( $srcFilePattern, $targetFile = false )
    {
        return $srcFilePattern;
    }

    public function close()
    {
        
    }

    public function fetchRawDirListing()
    {
        return null;
    }

    public function fetchFile( $srcFile, $targetFile = false )
    {
        return $srcFile;
    }
}

?>
