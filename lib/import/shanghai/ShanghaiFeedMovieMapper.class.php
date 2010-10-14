<?php
/**
 * Shanghai Import Base Mapper
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

class ShanghaiFeedMovieMapper extends DataMapper
{
    /**
     * Store Vendor object for Mapper use
     * @var Vendor
     */
    private $vendor;

    /**
     * Store Array Values
     * @var array
     */
    private $params;
    
    /**
     * Store Loaded SimpleXML data
     * @var SimpleXML
     */
    private $xmlNodes;

    /**
     * Shanghai Movie Mapper
     * @param Vendor $vendor
     * @param array $params
     */
    public function  __construct( Vendor $vendor, $params ) {

        if( !$vendor )
            throw new ShanghaiMovieMapperException( 'Invalid vendor object' );

        if( !is_array( $params ) || empty( $params ) )
            throw new ShanghaiMovieMapperException ( 'Invalid Parameter' );

        // Validate Params
        if( !isset( $params['datasource']['classname'] ) || empty( $params['datasource']['classname'] ) )
            throw new ShanghaiMovieMapperException ( 'Invalid datasource::classname ' );

        if( !isset( $params['datasource']['url'] ) || empty( $params['datasource']['url'] ) )
            throw new ShanghaiMovieMapperException ( 'Invalid datasource::url ' );

        // Set local variables
        $this->vendor = $vendor;
        $this->params = $params;


        $this->getXMLFeedData(); 
    }

    public function mapMovie()
    {
        foreach( $this->xmlNodes as $xmlNode)
        {
            try{

                $vendorMovieID = trim( (string) $xmlNode['id'] );

                $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $vendorMovieID );
                if( $movie === false)
                {
                    $movie = new Movie();
                }

                // Map Data
                $movie['Vendor']            = $this->vendor;
                $movie['vendor_movie_id']   = $vendorMovieID;
                $movie['name']              = (string)$xmlNode->name;
                $movie['plot']              = $this->cleanHTML( (string)$xmlNode->plot );
                $movie['review']            = $this->cleanHTML( (string)$xmlNode->review );
                $movie['director']          = (string)$xmlNode->director;
                $movie['writer']            = (string)$xmlNode->diwriterctor;
                $movie['rating']            = $this->getRatingOrNull( (string)$xmlNode->rating );
                $movie['url']               = (string)$xmlNode->url;
                $movie['utf_offset']        = (string) $movie['Vendor']->getUtcOffset();

                // add Timeout LINK
                if( trim( (string)$xmlNode->timeout_url ) != '' )
                {
                    $movie->setTimeoutLinkProperty( (string)$xmlNode->timeout_url );
                }

                // generate Cast List
                if( isset($xmlNode->cast) )
                {
                    $cast = array();
                    foreach( $xmlNode->cast->role as $role )
                    {
                        $cast[] = stringTransform::mb_trim( (string) $role->actor );
                    }
                    $movie['cast'] = stringTransform::concatNonBlankStrings( ', ', $cast );
                }

                // add genre
                if( isset( $xmlNode->genres ) )
                {
                    foreach( $xmlNode->genres->genre as $genre )
                    {
                        $movie->addGenre( stringTransform::mb_trim( (string)$genre->name) );
                    }
                }

                if( isset( $xmlNode->medias) )
                {
                    foreach( $xmlNode->medias->media as $media)
                    {
                        $movie->addMediaByUrl( stringTransform::mb_trim( (string) $media->url ) ); // Given in feed
                    }
                }

                // save
                $this->notifyImporter( $movie );
                
            }catch( Exception $exception ) {
                echo 'Exception: ' . $exception->getMessage() . PHP_EOL;
                $this->notifyImporterOfFailure($exception, isset($movie) ? $movie : null );
            }
        }
    }

    /**
     * Shanghai rating comes as 7.1/10 in the feed, this method will extract the rating
     * and round it under 5
     * @param string $rating
     * @return int
     */
    private function getRatingOrNull( $rating )
    {
        // split rating by / delimiter
        $ratingSplit = explode( '/', trim( $rating ) ); // [ 7.1/10 ] = array{ 7.1, 10}

        if( count($ratingSplit) == 2 )
        {
            $round = round( $ratingSplit[0] / 2 );
            return ( $round > 5 ) ? 5 : $round; // Maximum of 5
        }

        return null;
    }

    private function cleanHTML( $string )
    {
        return strip_tags( $string, '<p><i><b><string><em><pre><br>' );
    }

    /**
     * Download the XML feed and Store it in $this->xmlNodes variable
     */
    protected function getXMLFeedData()
    {
        $curl = new $this->params['datasource']['classname']( $this->params['datasource']['url'] );
        $curl->exec(); // Download the Data
        $this->xmlNodes = simplexml_load_string( $curl->getResponse() );
    }
}

// Movie Mapper Exception
class ShanghaiMovieMapperException extends Exception
{
    
}