<?php
/**
 * Media
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Media extends BaseMedia
{
    /**
     *
     */
    public function getAwsUrl()
    {
        // eg. http://projectn.s3.amazonaws.com/singapore/event/media/2e67b4c713718ea4583a2bb823bb1723.jpg
        $type = str_replace( 'Media', '', get_class( $this ) );
        return "http://projectn.s3.amazonaws.com/" . str_replace( ' ', '_', $this[ $type ]['Vendor']['city'] ) . "/" . strtolower( $type ) . "/media/" . $this['ident'] . ".jpg";
    }

    /**
     * populates the media table with media information and invokes the actual
     * file download
     *
     * @param string $urlString
     * @param string $identString
     * @param string $vendorCity
     */
    public function populateByUrl( $identString, $urlString, $vendorCity )
    {
        $this[ 'url' ] = $urlString;
        $this[ 'ident' ] = $identString;

        $curl = new Curl( $urlString );

        $type = strtolower( str_replace( 'Media', '', get_class( $this ) ) );

        $vendorCity = str_replace( ' ', '_', $vendorCity );

        if ( $type == '')
        {
            $filename = sfConfig::get( 'sf_root_dir' ) . '/import/' . $vendorCity . '/media/' . $identString . '.jpg';
        }
        else
        {
            $filename = sfConfig::get( 'sf_root_dir' ) . '/import/' . $vendorCity . '/' . $type . '/media/' . $identString . '.jpg';
        }

        if ( $this[ 'file_last_modified' ] === NULL || $this[ 'file_last_modified' ] == '' || !file_exists( $filename ) )
        {
            $curl->downloadTo(  $filename );
            if( !in_array( $curl->getContentType(),  array( 'image/jpeg' ) ) )
            {
              unlink( $filename );
              throw new MediaException( 'Download failed, mime-type required is image/jpeg, got "'. $curl->getContentType() . '" from url: "'  . $urlString . '" with ident: "' . $identString . '"' );
            }
        }
        else
        {
            $curl->downloadTo( $filename, $this[ 'file_last_modified' ] );
        }

        // Get cURL info
        $curlInfo = $curl->getCurlInfo();
        
        // Throw error when http code 200 or 304 not returned. validate http code  200 with mime/type
        if( ( $curlInfo[ 'http_code' ] !== 200 && $curlInfo[ 'http_code' ] !== 304 ) ||
                ( $curlInfo[ 'http_code' ] !== 200 && !in_array( $curl->getContentType(),  array( 'image/jpeg' ) ) ) )
        {
            unlink( $filename );
            throw new MediaException( 'Download failed, mime-type required is image/jpeg, got "'. $curl->getContentType() . '" from url: "'  . $urlString . '" with ident: "' . $identString . '"' );
        }
        // update / add Only when 200 found
        if($curlInfo[ 'http_code' ] === 200)
        {
            $this[ 'mime_type' ] = $curl->getContentType();
            $this[ 'file_last_modified' ] = $curl->getLastModified();
            $this[ 'etag' ] = $curl->getETag();
            $this[ 'content_length' ] = $curl->getContentLength();
        }

        if ( !file_exists( $filename ) || $this[ 'content_length' ] < 1 )
        {
            throw new MediaException( 'Failed to successfully download / store media url: ' . $urlString . ' / ident: ' . $identString );
        }
        return file_exists( $filename );
    }
}


class MediaException extends Exception {}