<?php
/**
 * UAE Feed Films Mapper
 *
 * @package projectn
 * @subpackage
 *
 * @author Rajeevan kumarathasan <rajeevankumarathasan@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class UAEFeedFilmsMapper extends UAEFeedBaseMapper
{
    public function mapFilms( )
    {
        foreach( $this->xml as $xmlNode)
        {
            try{
                // Get Existing Movie
                $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor_id, trim( (string)$xmlNode['id'] ) );
                if( $movie === false )
                {
                    $movie = new Movie();
                }

                // Map common Data
                $movie['vendor_id']                 = $this->vendor_id;
                $movie['vendor_movie_id']           = (string) $xmlNode['id'];
                $movie['name']                      = (string) $xmlNode->{'name'};
                $movie['plot']                      = (string) $xmlNode->{'description'};
                $movie['review']                    = (string) $xmlNode->{'full_review'};
                $movie['url']                       = stringTransform::formatUrl( (string) $xmlNode->{'website'} );
                $movie['director']                  = (string) $xmlNode->{'director'};
                $movie['cast']                      = (string) $xmlNode->{'cast'};
                $movie['release_date']              = (string) $xmlNode->{'release_date'};
                $movie['duration']                  = (string) $xmlNode->{'duration'};
                $movie['utf_offset']                = $movie['Vendor']->getUtcOffset();

                // add Timeout Link
                $movie->setTimeoutLinkProperty( stringTransform::formatUrl( (string) $xmlNode->{'landing_url'} ) );

                /**
                 * Example of tags
                 *
                 * "G,English,Action,Comedy,1 star"
                 * "PG15,English,Comedy,Romance"
                 * "18+,English,Action,Drama,4 star"
                 * "18+,English,Crime,Drama,Thriller,2 star"
                 * "PG15,English,Action,Adventure,Drama,Sci-Fi,Thriller,2 star"
                 *
                 */
                $tagsArray                          = explode(',', (string) $xmlNode->{'tags'});

                // Set Age rating                   // Do Nokia accept any age rating?
                $movie['age_rating']                = stringTransform::mb_trim( array_shift( $tagsArray ) ); // First one is allways Age ratings

                // Set Language
                $movie['language']                  = stringTransform::mb_trim( array_shift( $tagsArray ) ); // 2nd one is allways Language

                // Set Ratings If exists
                $this->setRating( $movie, $tagsArray );

                // add Genres
                foreach( $tagsArray as $genre)
                {
                    $movie->addGenre( stringTransform::mb_trim( $genre ) );
                } //$genre

                // Save
                $movie->save();
            
            } catch ( Exception $exc ) {
                echo 'Exception: UAEFeedFilmsMapper::mapFilms - ' . $exc->getTraceAsString() . PHP_EOL; // DEBUG only
                $this->notifyImporterOfFailure( $exc );
            }

        } // for each
    }

    /**
     * Extract Star Rating, If exists, add to Movie
     * @param Doctrine_Record $movie
     * @param array &$tagsArray
     */
    private function setRating( Doctrine_Record $movie, &$tagsArray )
    {
        // Get the Last Array and check for Rating
        $rating = end( $tagsArray );

        if( preg_match( '/^\d+\s+star+/i' , $rating) ) // see if something begins with number and space star* ///^\d+\s+\w+/
        {
            $rating             = explode( ' ', array_pop( $tagsArray ) ); // remove from Tags Array List
            $rating             = (int) $rating[0];
            $rating             = ( $rating > 5 ) ? 5 : $rating; // Set to Maximum of 5
            $movie['rating']    = $rating;
            
        }
    }
}