<?php
/**
 * China Feed Movie Import mapper
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


class ChinaFeedMovieMapper extends ChinaFeedBaseMapper{

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
                $movie['plot']              = (string)$xmlNode->plot;
                $movie['review']            = (string)$xmlNode->review;
                $movie['director']          = (string)$xmlNode->director;
                $movie['writer']            = (string)$xmlNode->writer;
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
                        $this->addImageHelper( $movie, stringTransform::mb_trim( (string) $media->url ) );
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
}