<?php
/**
 * Beijing Import Base Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class BeijingFeedBaseMapper extends DataMapper
{
    /**
     * @var PDO Database Connection Object
     */
    protected $pdoDB;

    /**
    * @var geoEncode
    */
    protected $geoEncoder;

    /**
    * @var Vendor
    */
    protected $vendor;

    /**
     * Create Base Mapper
     * @param PDO $pdoDB (Connected object)
     * @param geoEncode $geoEncoder
     */
    public function  __construct( $pdoDB, geoEncode $geoEncoder = null ) {

        if( is_null($pdoDB) )
            throw new Exception ('Invalid PDO Database object');

        // Get Vendor Beijing
        $vendor = Doctrine::getTable('Vendor')->findOneByCityAndLanguage( 'beijing', 'en-GB' );

        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'Vendor not found.' );


        $this->pdoDB        = $pdoDB; // Set DB
        $this->vendor       = $vendor; // Set Vendor
        $this->geoEncoder   = is_null( $geoEncoder ) ? new geoEncode() : $geoEncoder;
    }

    protected function query( $tableName, $offset = 0, $limit = 500, $cityID = 2 )
    {
        if( !$this->pdoDB )
        {
            throw new Exception ('Invalid PDO Database object');
        }

        $query = null;
        try{
            $query = $this->pdoDB->query( 'SELECT * FROM ' . $tableName . ' LIMIT '. $offset . ', '. $limit );
            
        } catch ( Exception $exception )
        {
            throw new Exception( ' PDO Query Error: ' . $exception->getMessage() . PHP_EOL );
        }

        return $query;
    }


    protected function fixHtmlEntities( $string )
    {
        $string = html_entity_decode( (string) $string, ENT_QUOTES, 'UTF-8' );

        return $string;
    }

    protected function roundNumberOrReturnNull( $string )
    {
        return is_numeric( (string) $string ) ? round( (string) $string ) : null;
    }

    protected function extractTimeOrNull( $string )
    {
        $date = DateTime::createFromFormat( 'H:i', $string );

        return ( $date === false ) ? null : $string;
    }

    protected function clean( $string , $chars = '' )
    {
        return stringTransform::mb_trim( $string, $chars );
    }
}
?>