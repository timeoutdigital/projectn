<?php
/**
 * Bucharest movie import mapper
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */

class bucharestMovieMapper extends bucharestBaseMapper
{

    public function mapMovies()
    {
        foreach( $this->xmlNodes as $xmlNode )
        {
            try
            {
                $vendorMovieID = $this->clean( $xmlNode['id'] );
                $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $vendorMovieID );
                if( $movie === false )
                {
                    $movie = new Movie();
                }

                // Map data
                $movie['Vendor'] = $this->vendor;
                $movie['vendor_movie_id'] = $vendorMovieID;
                $movie['name'] = $this->clean( (string) $xmlNode->name );
                $movie['plot'] = $this->clean( (string) $xmlNode->plot );
                $movie['review'] = $this->clean( (string) $xmlNode->review );
                $movie['rating'] = $this->_getNumberOrNull( $this->clean( (string) $xmlNode->rating ) );
                $movie['director'] = $this->clean( (string) $xmlNode->director );
                $movie['utf_offset'] = $movie['Vendor']->getUtcOffset();
                
                // Add Cast Details
                if( isset( $xmlNode->cast->actor ) )
                {
                    $actors = array();
                    foreach( $xmlNode->cast->actor as $actor )
                    {
                        if( stringTransform::mb_trim( (string)$actor ) != '' )
                        {
                            $actors[] = stringTransform::mb_trim( (string)$actor );
                        }
                    }
                    $movie['cast'] = stringTransform::concatNonBlankStrings(', ', $actors );
                }

                // add genres
                if( isset( $xmlNode->genres->genre ) )
                {
                    foreach( $xmlNode->genres->genre as $genre )
                    {
                        if( stringTransform::mb_trim( (string) $genre->name ) != '' )
                        {
                            $movie->addGenre( stringTransform::mb_trim( (string) $genre->name ) );

                            // Check for Children
                            if( isset( $genre->children->name ) && stringTransform::mb_trim( (string)$genre->children->name ) != '' )
                            {
                                $movie->addGenre( stringTransform::mb_trim( (string)$genre->children->name ) );
                            }
                        }
                    }

                }

                // Add Media
                if( isset( $xmlNode->medias->media ) )
                {
                    foreach( $xmlNode->medias->media as $media )
                    {
                        if( trim( (string)$media->url ) != '' )
                        {
                            $movie->addMediaByUrl( (string)$media->url );
                        }
                    }
                }

                $this->notifyImporter( $movie );
                
            } catch ( Exception $e )
            {
                $this->notifyImporterOfFailure( $e, isset( $movie ) ? $movie : null );
            }
        }
    }

    /**
     * Convert string to Integer, Return null when invalid string or 0 integer found
     * @param string $string
     * @return mixed
     */
    private function _getNumberOrNull( $string )
    {
        $rating = intval( $string );
        return  ( $rating <= 0 ) ? null : $rating;
    }
}