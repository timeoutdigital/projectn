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

class BeijingFeedVenueMapper extends BeijingFeedBaseMapper
{
    public function mapVenues()
    {
        $tmp = 0;
        $offset = 0; // current offset
        $recordPerQuery = 500; // Get only 500 Records per Query

        $results = $this->queryVenue( $offset, $recordPerQuery );

        if( !$results )
            throw new Exception (' Query return NULL object ' );

        // Loop through Paging [ Limit Query to 500 at a time ]
        while ( is_array($results) && count($results) > 0 )
        {
            // Loop through Results Rows
            foreach( $results as $venue)
            {
                $this->addUpdateVenue( $venue );
            }

            $offset += $recordPerQuery;
            $results = $this->queryVenue( $offset, $recordPerQuery );
        } // end WHILE*/

    } // mapVenues()

    /**
     * Add venue [ROW] as POI
     * @param PDO Database Row Array $venue
     */
    private function addUpdateVenue( $venue )
    {
        try{
            // get POI if Exists
            $poi = Doctrine::getTable('Poi')->findOneByVendorIdAndVendorPoiId( $this->vendor['id'], $venue['id'] );

            // Create New POI if none Found
            if( !$poi )
                $poi = new Poi();

            // Data Mapping
            $poi['vendor_poi_id']               = $venue['id'];
            $poi['poi_name']                    = $venue['name'];

            //$poi['street']                      = '[invalid]' ; //$venue['address'];
            $poi['provider']                    = $venue['address'];
            $poi['additional_address_details']  = stringTransform::concatNonBlankStrings( ', ' , array( $venue['building_name'], $venue['neighbourhood_cityname'] ) );
            $poi['city']                        = $this->vendor['city'];
            $poi['zips']                        = $venue['postcode'];
            $poi['country']                     = $this->vendor['country_code_long'];

            $poi['email']                       = $venue['email'];
            $poi['url']                         = $venue['url'];
            $poi['phone']                       = $venue['phone'];

            $poi['description']                 = $this->fixHtmlEntities( $venue['annotation'] );
            $poi['public_transport_links']       = $venue['travel'];
            $poi['openingtimes']                 = $venue['opening_times'];

            $poi->applyFeedGeoCodesIfValid( $venue['latitude'], $venue['longitude'] );
            $poi['geocode_look_up']             =  stringTransform::concatNonBlankStrings(', ', array( $poi['house_no'], $poi['street'], $poi['zips'], $poi['city'] ) );

            $poi['Vendor']                      = clone $this->vendor;
            $poi['local_language']              = $this->vendor['language'];


            $poi->addMeta( 'status' , $venue[ 'status' ] );


            // Map Category
            $categories = array();
            $addedCategories = array();
            $ignoreList = array('International');
            $pdoCategories = $this->queryCategories( $venue['id'] );

            if( $pdoCategories )
            {
                while ( $category = $pdoCategories->fetch() )
                {
                    // get Parents
                    $categoryParents = $this->queryCategoryParents( $category['lft'], $category['rgt'] );
                    if( $categoryParents )
                    {
                        while( $parent = $categoryParents->fetch() )
                        {
                            if( in_array($parent['name'], $addedCategories ) || in_array($parent['name'], $ignoreList ) )
                                    continue;
                            $categories[] = $parent['name'];          // add to Category List
                            $addedCategories[] = $parent['name'];     // add to Added List for ignore next time
                        }
                    }

                    // Added Last as some category have Internation as child category and Restaurent as Parent!
                    // Main loop only go through the Child categoy!
                    if( in_array($category['name'], $addedCategories ) || in_array($category['name'], $ignoreList ) )
                            continue;

                    $categories[] = $category['name'];          // Add to Category List
                    $addedCategories[] = $category['name'];     // add to Added List for ignore next time
                }
            }
            $poi->addVendorCategory( $categories );

            // Add Images
            if( !is_null ($venue['image_id'])  && ( is_string($venue['image_id']) && !empty($venue['image_id']) ) )
            {
                // http://www.timeout.com/img/[ID]/image.jpg
                $imageURL = sprintf( 'http://www.timeout.com/img/%s/image.jpg', $venue['image_id'] );
                $this->addImageHelper($poi, $imageURL );
            }

            // Save to DB
            $this->notifyImporter( $poi );
        } catch ( Exception $exception )
        {
            $this->notifyImporterOfFailure( $exception );
            echo 'exception: ' . $exception->getMessage() . PHP_EOL;
        }

        unset( $pdoCategories, $poi, $categories ); // Free Memory! Really?
    }

    /**
     * Query Databse for Venues, You can specify offset and limit...
     * @param Integer $offset
     * @param Integer $limit
     * @param Integer $cityID
     * @return PDO_Query - Return Array of Rows.. All pre-fetched
     */
    private function queryVenue( $offset = 0, $limit = 100, $cityID = 2 )
    {
        if( !$this->pdoDB )
        {
            throw new Exception ('Invalid PDO Database object in queryVenue()');
        }
        $results = null;
        try{
            // @query: SELECT v.*, n.name as ncityname FROM venue v left join neighbourhood n on n.id = v.neighbourhood_id WHERE n.city_id=2
            $sql = 'SELECT v.*, n.name as neighbourhood_cityname FROM venue v left join neighbourhood n on n.id = v.neighbourhood_id WHERE n.city_id= :city_id LIMIT :offset, :limit';

            $query = $this->pdoDB->prepare( $sql );
            // PHP will throw error when SQL query above failed (like Invalid column / table )

            if($query)
            {
                $query->bindParam(':city_id', $cityID, PDO::PARAM_INT);
                $query->bindParam(':offset', $offset, PDO::PARAM_INT);
                $query->bindParam(':limit', $limit, PDO::PARAM_INT);

                $query->execute();

                // PDO cannot work with rowCount() in Sqlite ???
                $results = $query->fetchAll();
            }

        } catch ( Exception $exception )
        {
            throw new Exception( ' PDO Query Error: ' . $exception->getMessage() . PHP_EOL );
        }

        return $results;
    }

    /**
     *  Search databse for venue categories by venue_id
     * @param Integer $venue_id
     * @return  PDO_Query Return collection of PDO results, use fetch() to get rows...
     */
    private function queryCategories( $venue_id )
    {
        if( !$this->pdoDB )
        {
            throw new Exception ('Invalid PDO Database object in queryCategories()');
        }

        $query = null;
        try{
            $sql = 'SELECT c.* FROM category c left join venue_category_mapping v on v.category_id = c.id WHERE venue_id = :venue_id ';

            $query = $this->pdoDB->prepare( $sql );

            $query->bindParam(':venue_id', $venue_id, PDO::PARAM_INT);

            $query->execute( );

        } catch ( Exception $exception )
        {
            throw new Exception( ' PDO Query Error: ' . $exception->getMessage() . PHP_EOL );
        }

        return $query;
    }

    /**
     * Get Categories Parent collections in Asccending Order
     * More information in category listing : http://articles.sitepoint.com/article/hierarchical-data-database/2
     * @param Integer $lowerID
     * @param Integer $higherID
     * @param Integer $top_most_parent_id
     * @return PDO_Query Top Most Parent is removed from the list
     */
    private function queryCategoryParents( $lowerID, $higherID, $top_most_parent_id = 392 )
    {
        if( !$this->pdoDB )
        {
            throw new Exception ('Invalid PDO Database object in queryCategoryParents()');
        }

        $query = null;
        try{
            $sql = 'SELECT * FROM category WHERE lft < :left AND rgt > :right and id > :top_parent ORDER BY lft ASC;';

            $query = $this->pdoDB->prepare( $sql );

            $query->bindParam(':left', $lowerID, PDO::PARAM_INT);
            $query->bindParam(':right', $higherID, PDO::PARAM_INT);
            $query->bindParam(':top_parent', $top_most_parent_id, PDO::PARAM_INT);

            $query->execute( );

        } catch ( Exception $exception )
        {
            throw new Exception( ' PDO Query Error: ' . $exception->getMessage() . PHP_EOL );
        }

        return $query;
    }
}

?>
