<?php
/**
 * Singapore movie mapper

 *
 * @package projectn
 * @subpackage singapore.import.lib.unit
 *
 * @author Rajeevan Kumarathasan <rajeevankumarathasan@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 */
class singaporeMovieMapper extends singaporeBaseMapper
{
    public function mapMovie()
    {
        foreach( $this->xmlNodes->movie as $movieNode)
        {
            try{
                $vendorMovieID  = (string) $movieNode->id;
                // Get Existing Movie Element or Create new
                $movie = Doctrine::getTable( 'Movie' )->findOneByVendorMovieIdAndVendorId( $vendorMovieID, $this->vendor[ 'id' ] );
                if( !$movie )
                {
                    $movie = new Movie();
                }

                // Map Data
                $movie[ 'vendor_id' ]           = $this->vendor[ 'id' ];
                $movie[ 'vendor_movie_id' ]     = (string) $movieNode->id;
                $movie[ 'name' ]                = (string) $movieNode->title;
                $movie[ 'review' ]              = (string) $movieNode->synopsis;
                $movie[ 'url' ]                 = (string) (string) $movieNode->website;
                $movie[ 'director' ]            = (string) $movieNode->director;
                $movie[ 'cast' ]                = (string) $movieNode->cast;
                $movie[ 'release_date' ]        = (string) $movieNode->opens;
                $movie[ 'duration' ]            = (string) $movieNode->length;
                $movie[ 'country' ]             = (string) $movieNode->origin;
                $movie[ 'age_rating' ]          = $this->getAgeRatingCode( (string) $movieNode->certificate);
                $movie[ 'utf_offset' ]          = $this->vendor->getUtcOffset();

                //genres
                if ( (string) $movieNode->category != '' )
                {
                    $movie->addGenre( (string) $movieNode->category );
                }

                //properties
                $movie->setCriticsChoiceProperty( ( trim( strtolower( (string) $movieNode->critic_choice ) ) == 'y' ) ? true : false );
                $movie->setTimeoutLinkProperty( (string) $movieNode->link );
                
                if ( (string) $movieNode->trailer_url != '' ) $movie->addProperty( 'Trailer_url', (string) $movieNode->trailer_url );
                if ( (string) $movieNode->certificate != '' ) $movie->addProperty( 'Certificate', (string) $movieNode->certificate );
                if ( (string) $movieNode->year_production != '' ) $movie->addProperty( 'year', (string) $movieNode->year_production );

                // -- Add Images --
                $this->addImageHelper( $movie , (string)$movieNode->highres );
                $this->addImageHelper( $movie , (string)$movieNode->large_image );
                $this->addImageHelper( $movie , (string)$movieNode->thumbnail );
                $this->addImageHelper( $movie , (string)$movieNode->image );
                $this->addImageHelper( $movie , (string)$movieNode->thumb );

                // Save data
                $this->notifyImporter( $movie );

            }
            catch( Exception $exception )
            {
                $this->notifyImporterOfFailure( $exception, isset( $movie ) ? $movie : null );
            }
        }
    }

    /**
     * Extract Age Rating Code from String
     * @param string $stringCertificate
     * @return string
     */
    private function getAgeRatingCode( $stringCertificate )
    {
        $ageratingArray = explode( '-',  $stringCertificate );
        $ageratingCodeString = trim( $ageratingArray[ 0 ] );

        if ( in_array( $ageratingCodeString, array( 'G', 'PG', 'NC16', 'M18', 'R18', 'R21' ) ) )
        {
            return $ageratingCodeString;
        }

        return null;
    }
}
?>
