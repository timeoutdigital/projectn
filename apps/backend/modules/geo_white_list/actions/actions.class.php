<?php

require_once dirname(__FILE__).'/../lib/geo_white_listGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/geo_white_listGeneratorHelper.class.php';

/**
 * geo_white_list actions.
 *
 * @package    sf_sandbox
 * @subpackage geo_white_list
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class geo_white_listActions extends autoGeo_white_listActions
{

    public function executeWhitelist( sfWebRequest $request )
    {
        $latitude = $request->getParameter('lat');
        $longitude = $request->getParameter('long');

        if( $latitude == null || trim( $latitude ) == '' || $longitude == null || trim( $longitude ) == '' )
        {
            $this->getUser()->setFlash('error', 'Missing latitude / longitude');
            $this->redirect( '@poi_geo_white_list' );
            exit();
        }

        // Find all poi's with latitude and longitudes
        $this->pois = Doctrine::getTable('Poi')->findByLatitudeAndLongitude( $latitude, $longitude );

    }

    public function executeUpdate_whitelist( sfWebRequest $request )
    {
        $poi_id = trim( $request->getPostParameter( 'poi' ) );
        $meta = strtolower( $request->getPostParameter( 'meta' ) );

        $error_message = '';
        // validate
        if( !is_numeric( $poi_id ) || $poi_id <= 0 )
        {
            $error_message .= 'Invalid POI ID :' . $poi_id . "\n";
        }

        if( $meta != 'yes' && $meta != 'no' )
        {
            $error_message .= 'Invalid meta value :' . $meta;
        }

        if( !empty( $error_message ) )
        {
            return $this->renderText( json_encode( array('status' => 'failed', 'message' => $error_message ) ) );
            exit();
        }

        // Get the POI
        $poi =  Doctrine::getTable( 'Poi' )->find( $poi_id );
        if( $poi === false )
        {
            return $this->renderText( json_encode( array('status' => 'failed', 'message' => 'No poi found with ID: ' . $poi_id ) ) );
        }

        // update Poi META
        $poi->setWhitelistGeocode( ($meta == 'yes' ) ? true : false );
        $poi->save();
        return $this->renderText( json_encode( array('status' => 'success', 'message' => 'Poi Meta updated' ) ) );
    }
}
