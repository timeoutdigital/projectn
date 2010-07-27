<?php

require_once dirname(__FILE__).'/../lib/geocode_uiGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/geocode_uiGeneratorHelper.class.php';

/**
 * geocode_ui actions.
 *
 * @package    sf_sandbox
 * @subpackage vegeocode_uinue
 * @author
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class geocode_uiActions extends autoGeocode_uiActions
{

    public function executeChooseList( sfWebRequest $request )
    {
        $list = $request->getParameter( 'list' );
        
        $filters = $this->getUser()->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );

        $filters[ 'list' ] = $list;
       
        //add list paramater into filters
        $this->getUser()->setAttribute( 'geocode_ui.filters', $filters, 'admin_module' );

        //reset the page to 1
        $this->getUser()->setAttribute( 'geocode_ui.page', 1, 'admin_module' );

        //default sort is created_at
        $this->getUser()->setAttribute( 'geocode_ui.sort', array( 'created_at', 'asc' ), 'admin_module' );

        $this->redirect( 'geocode_ui/index' );
    }

     /**
     *
     * @param $request
     * @return unknown_type
     */
    public function executeIndex( sfWebRequest $request )
    {
        $filters = $this->getUser()->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );

        if ( !isset( $filters[ 'list' ] ) )
        {
            $filters[ 'list' ] = 'non-geocoded';
            
            $this->getUser()->setAttribute( 'geocode_ui.filters', $filters, 'admin_module' );
            
            $this->getUser()->setAttribute( 'geocode_ui.sort', array( 'created_at', 'asc' ), 'admin_module' );
        }

        if(!isset($filters['vendor_id']) || $filters['vendor_id'] == null || empty($filters['vendor_id']))
        {
            // Filter [Hack] Permitted Cities for Authenticated User.
            // See PoiDataEntryFormFilter.php addVendorIdColumnQuery().
            $filters[ 'vendor_id' ] = 0;
            $this->getUser()->setAttribute( 'geocode_ui.filters', $filters, 'admin_module' );
        }
        parent::executeIndex( $request );

    }

    public function executeFilter( sfWebRequest $request )
    {
        
        $filter = $this->getUser()->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );

        $list = 'non-geocoded';

        if ( isset( $filter['list'] ) )
            $list = $filter['list'];

        if ( $request->hasParameter( '_reset' ) )
        {
            $this->setFilters( $this->configuration->getFilterDefaults() );

            $this->getUser()->setAttribute( 'geocode_ui.filters', array( 'list' => $list ), 'admin_module' );

            $this->redirect( 'geocode_ui/index' );
        }

        parent::executeFilter( $request );
    }

    /**
     * ajax response for returning the venue details
     *
     * @param sfWebRequest $request
     * @return json
     */
   public function executeVenueDetails( sfWebRequest $request )
   {
        $venueId = $request->getParameter( 'venueId' );

        $venue = Doctrine::getTable( 'poi' )->find( $venueId );

        // validate
         if ( $venue && !$this->getUser()->checkIfVendorIdIsAllowed( $venue['vendor_id'] ) )
         {
             return $this->renderText( json_encode( array('error' => sprintf('You don\' have permissions to read this record [%s]', $venue['poi_name'] ) ) ) );
         }


        $result = array( );

        if( $venue )
        {
            $result [ 'name' ] = $venue[ 'poi_name' ];
            $result [ 'address1' ] = $venue[ 'house_no' ];
            $result [ 'address2' ] = $venue[ 'street' ];
            $result [ 'latitude' ] = $venue[ 'latitude' ];
	    $result [ 'longitude' ] = $venue[ 'longitude' ];
            $result [ 'geocode_accuracy' ] = 0;//$venue[ 'geocode_accuracy' ];
            $result [ 'city' ] = $venue[ 'city' ];
            $result [ 'id' ] = $venue[ 'id' ];
        }

        return $this->renderText( json_encode( $result ) );
   }

   /**
    * ajax response to update latitude and longitude
    *
    * @param sfWebRequest $request
    * @return string
    */
   public function executeSaveVenueDetails( sfWebRequest $request )
   {
       $venueId = $request->getParameter( 'venueId' );

       // validate
       if ( !$this->getUser()->checkRecordPermissions( 'poi', $venueId  ) )
       {
           return $this->renderText( json_encode( array('error' => sprintf('You don\' have permissions to change this record') ) ) );
       }

      
      $latitude = $request->getParameter( 'latitude' );
      $longitude = $request->getParameter( 'longitude' );
      $geocode_lookup = $request->getParameter( 'geocode_lookup' );
      $geocode_accuracy = $request->getParameter( 'geocode_accuracy' );

      
      if( empty( $latitude ) ) $latitude = null;
      if( empty( $longitude ) ) $longitude = null;
      if( empty( $geocode_accuracy ) ) $geocode_accuracy = 0;

      $poi = Doctrine::getTable( 'Poi' )->find( $venueId );

      if( $poi )
      {
        // For Meta Data
        $last_lat = $poi[ 'latitude' ];
        $last_long = $poi[ 'longitude' ];

        $poi[ 'latitude' ] = $latitude;
        $poi[ 'longitude' ] = $longitude;

        // Add / Update GEO Source
        if($last_lat != $latitude || $last_long != $longitude)
        {
            $poi->addMeta('Geo_Source', 'GeocodeUI', sprintf('Changed %s:%s - %s:%s - Accuracy:%s Geocode Lookup:%s', $last_lat, $last_long, $latitude, $longitude,$geocode_accuracy, $geocode_lookup ));
        }

        $poi->save(); // Save ALL

        return $this->renderText( json_encode( array('alert' => sprintf('Record Updated', $poi['poi_name'] ) ) ) );
      }else
          
        return $this->renderText( json_encode( array('error' => sprintf('POI Not found!' ) ) ) );
   }

}
