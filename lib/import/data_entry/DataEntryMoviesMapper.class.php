<?php
/**
 * Description
 *
 * @package projectn
 * @subpackage
 *
 * @author Emre Basala <emrebasala@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */

class DataEntryMoviesMapper extends DataEntryBaseMapper
{
    
  /**
   * @param SimpleXMLElement $xml
   */
    public function __construct( SimpleXMLElement $xml, geocoder $geocoderr = null, $city = false )
    {
        if( is_string( $city ) )
            $vendor = Doctrine::getTable('Vendor')->findOneByCity( $city );

        if( !isset( $vendor ) || !$vendor )
          throw new Exception( 'DataEntryMoviesMapper:: Vendor not found.' );

        $this->dataMapperHelper = new projectNDataMapperHelper( $vendor );
        $this->vendor               = $vendor;
        $this->xml                  = $xml;

    }


  public function mapMovies( )
  {
    foreach ( $this->xml as $movieElement)
    {
        try
        {
            foreach ( $movieElement->attributes() as $attribute => $value )
            {
                if( $attribute == 'id' )
                {
                    $vendorMovieId = (int) substr( (string) $value,5) ;
                }
            }

            $movie = Doctrine::getTable( 'Movie' )->findOneByVendorIdAndVendorMovieId( $this->vendor['id'], $vendorMovieId );
            if( $movie === false )
            {
                $movie = new Movie();
            }

            // version
            $movie['vendor_movie_id']   = $vendorMovieId ;
            $movie['Vendor']            = $this->vendor;
            $movie['name']              = (string) $movieElement->version->name ;

            $genreInfo  =  (string) $movieElement->version->genre;
            $genres = explode( ',' ,$genreInfo);
            foreach ($genres as $genre)
            {
                $movie->addGenre( trim( $genre ) );
            }

            $tagLine = 'tag-line';
            $movie['tag_line']          = (string) $movieElement->version->{$tagLine} ;
            $movie['plot']              = (string) $movieElement->version->plot ;
            $movie['review']            = (string) $movieElement->version->review ;
            $movie['rating']            = (int) $movieElement->version->rating ;
            $movie['director']          = (string) $movieElement->version->director ;
            $movie['writer']            = (string) $movieElement->version->writer ;

            $additionalDetails = 'additional-details';
            $ageRating = 'age-rating';
            $aspectRatio = 'aspect-ratio';
            $soundMix = 'sound-mix';

            $movie['url']           =  (string) $movieElement->{$additionalDetails}->website ;
            $movie['age_rating']    =  (string) $movieElement->{$additionalDetails}->{$ageRating} ;
            $movie['duration']      =  (string) $movieElement->{$additionalDetails}->duration ;
            $movie['language']      =  (string) $movieElement->{$additionalDetails}->language ;
            $movie['aspect_ratio']  =  (string) $movieElement->{$additionalDetails}->{$aspectRatio} ;
            $movie['sound_mix']  =  (string) $movieElement->{$additionalDetails}->{$soundMix} ;
            $movie['company']  =  (string) $movieElement->{$additionalDetails}->company ;

            $actors = array();

            if( isset( $movieElement->version->cast ) && isset( $movieElement->version->cast->actor ) )
            {
                foreach ( $movieElement->version->cast->actor as $actor)
                {
                    $actorName = 'actor-name';
                    $actors[] = trim( (string) $actor->$actorName );
                }
            }

            $movie['cast'] =  implode( ', ', $actors );

            if( isset( $movieElement->version->media ) )
            {
                foreach ( $movieElement->version->media as $media )
                {
                    foreach ($media->attributes() as $key => $value)
                    {
                        if( (string) $key == 'mime-type' &&  (string) $value !='image/jpeg')
                        {
                            continue 2; //only add the images
                        }
                    }
                    try
                    {
                        // Generate Image [ http://www.timeout.com/projectn/uploads/media/event/$fileName ]
                        $urlArray = explode( '/', (string) $media );
                        // Get the Last IDENT
                        $imageFileName = array_pop( $urlArray );

                        $mediaURL = sprintf( 'http://www.timeout.com/projectn/uploads/media/movie/%s', $imageFileName );

                        $movie->addMediaByUrl( $mediaURL );
                    }
                    catch ( Exception $exception )
                    {
                         $this->notifyImporterOfFailure( $exception );
                    }

                }
            }

            $movie[ 'utf_offset' ] = $this->vendor->getUtcOffset();

            if( isset( $movieElement->version->property ) && $movieElement->version->property )
            {
                foreach ($movieElement->version->property as $property)
                {
                    foreach ($property->attributes() as $attribute)
                    {
                        $movie->addProperty( (string) $attribute, (string) $property );
                    }
                }
            }


           $this->notifyImporter( $movie );

       }
       catch ( Exception $exception )
       {
            $this->notifyImporterOfFailure( $exception, $movie );
       }
    }

  }

}
