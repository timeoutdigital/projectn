<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 
 *
 * @author clarence
 */
class LondonVenues
{
    private $connection;
    
    public function __construct()
    {
        $doctrineConnection = Doctrine_Manager::connection( 'mysql://timeout:65dali32@80.250.104.16/searchlight', 'searchlight' );
        $this->connection = $doctrineConnection->getDbh();
    }

    public function getAll()
    {
        $results = false;

        $sql = '
            SELECT
                name,
                SUBSTR( address, ",", 1 ) as street,
                "london" as city,
                "GBR" as country,
                latitude,
                longitude
            FROM
                venue
            LIMIT
                2
            ';
        $statement = $this->connection->prepare( $sql );

        if( $statement->execute() )
        {
            $results = $statement->fetchAll();
        }
        return $results;
    }
}
?>
