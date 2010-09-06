<?php

class SqliteGeoCache
{
    public static $enabled = true;
    public static $sqlpath = '/n/data/geocache.sqlite';
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
        
        if( file_exists( self::$sqlpath ) )
        {
            try {
                self::$pdo = new PDO( 'sqlite:'. self::$sqlpath );
                return true;
            }
            catch ( PDOException $e ) {
                echo 'Failed to Connect to Sqlite Database: ' . $e->getMessage();
            }
        }
        return false;
    }

    public static function get( $url = 'NULL' )
    {
        if( self::connect() )
        {
            $url = self::$pdo->quote( $url );
            
            $query = "SELECT response FROM cache WHERE url = {$url} LIMIT 1;";
            $prep = self::$pdo->prepare( $query );
            $prep->execute();
            $response = $prep->fetch( PDO::FETCH_ASSOC );

            return( $response !== false && isset( $response[ 'response' ] ) ) ? unserialize( $response[ 'response' ] ) : null;
        }
        return null;
    }

    public static function put( $url = 'NULL', $response = 'NULL' )
    {
        if( self::connect() )
        {
            $url = self::$pdo->quote( $url );
            $response = self::$pdo->quote( serialize( $response ) );
            
            $query = "INSERT INTO cache ( url, response ) VALUES ( {$url}, {$response} );";
            $count = self::$pdo->exec( $query );

            return( is_numeric( $count ) && $count > 0 );
        }
        return false;
    }
}