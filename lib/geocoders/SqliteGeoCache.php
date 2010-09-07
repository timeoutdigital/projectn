<?php

class SqliteGeoCache
{
    public static $enabled = true;
    public static $sqlpath;
    public static $persistent = true;
    public static $pdo;

    public static function enabled()
    {
        return self::$enabled && self::connect();
    }

    public static function connect()
    {
        if( self::$persistent && !is_null( self::$pdo ) && get_class( self::$pdo ) === 'PDO' )
            return true;
        
        try {
            self::$sqlpath = sfConfig::get( 'sf_data_dir') . '/geocache.sqlite';
            self::$pdo = new PDO( 'sqlite:'. self::$sqlpath );
            self::$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            self::$pdo->exec( "CREATE TABLE IF NOT EXISTS cache ( 'url' VARCHAR PRIMARY KEY NOT NULL UNIQUE , 'response' TEXT )" );
            return true;
        }
        catch ( PDOException $e ) {
            echo 'Failed to Connect to Sqlite Database: ' . $e->getMessage();
        }
        
        return false;
    }

    public static function get( $url = 'NULL' )
    {
        if( self::connect() )
        {
            try {
                $url = self::$pdo->quote( $url );

                $query = "SELECT response as r FROM cache WHERE url = {$url} LIMIT 1;";
                $prep = self::$pdo->prepare( $query );
                $prep->execute();
                $response = $prep->fetch( PDO::FETCH_ASSOC );
            }
            catch ( PDOException $e ) {
                return null;
            }

            return( $response !== false && isset( $response['r'] ) ) ? unserialize( $response['r'] ) : null;
        }
        return null;
    }

    public static function put( $url = 'NULL', $response = 'NULL' )
    {
        if( self::connect() )
        {
            try {
                $url = self::$pdo->quote( $url );
                $response = self::$pdo->quote( serialize( $response ) );

                $query = "INSERT INTO cache ( url, response ) VALUES ( {$url}, {$response} );";
                $count = self::$pdo->exec( $query );
            }
            catch ( PDOException $e ) {
                return false;
            }

            return( is_numeric( $count ) && $count > 0 );
        }
        return false;
    }

    public static function del( $url = 'NULL' )
    {
        if( self::connect() )
        {
            try {
                $url = self::$pdo->quote( $url );

                $query = "DELETE FROM cache WHERE url = {$url};";
                $count = self::$pdo->exec( $query );
            }
            catch ( PDOException $e ) {
                return false;
            }

            return( is_numeric( $count ) && $count > 0 );
        }
        return false;
    }

    public static function count()
    {
        if( self::connect() )
        {
            try {
                $query = "SELECT COUNT(*) as c FROM cache;";
                $prep = self::$pdo->prepare( $query );
                $prep->execute();
                $response = $prep->fetch( PDO::FETCH_ASSOC );
            }
            catch ( PDOException $e ) {
                return false;
            }

            return( isset( $response['c'] ) && is_numeric( $response['c'] ) ) ? (int) $response['c'] : false;
        }
        return false;
    }
}