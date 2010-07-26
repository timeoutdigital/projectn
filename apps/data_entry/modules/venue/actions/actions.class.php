<?php

require_once dirname(__FILE__).'/../lib/venueGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/venueGeneratorHelper.class.php';

/**
 * venue actions.
 *
 * @package    sf_sandbox
 * @subpackage venue
 * @author
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class venueActions extends autoVenueActions
{
    public function preExecute()
    {
         parent::preExecute();
         /*$filters = $this->getFilters() ;
         $this->user = $this->getUser();

         if ( !isset( $filters['vendor_id'] ) || !$this->user->checkIfVendorIdIsAllowed( $filters['vendor_id'] ) )
         {
              $this->setFilters( array( 'vendor_id' => $this->user->getCurrentVendorId() ) );
         }*/
         // Add Filters to Filter Vendor CITY fo
         $filters = $this->getFilters();
         //die( var_dump( $filters ) );
         //var_dump( $filters );
         
    }
    
    public function executeChooseList( sfWebRequest $request )
    {
        $list = $request->getParameter( 'list' );
        
        $filters = $this->getUser()->getAttribute( 'venue.filters', array(), 'admin_module' );

        $filters[ 'list' ] = $list;
       
        //add list paramater into filters
        $this->getUser()->setAttribute( 'venue.filters', $filters, 'admin_module' );

        //reset the page to 1
        $this->getUser()->setAttribute( 'venue.page', 1, 'admin_module' );

        //default sort is created_at
        $this->getUser()->setAttribute( 'venue.sort', array( 'created_at', 'asc' ), 'admin_module' );

        $this->redirect( 'venue/index' );
    }

     /**
     *
     * @param $request
     * @return unknown_type
     */
    public function executeIndex( sfWebRequest $request )
    {
        $filters = $this->getUser()->getAttribute( 'venue.filters', array(), 'admin_module' );

        if ( !isset( $filters[ 'list' ] ) )
        {
            $filters[ 'list' ] = 'non-geocoded';
            
            $this->getUser()->setAttribute( 'venue.filters', $filters, 'admin_module' );
            
            $this->getUser()->setAttribute( 'venue.sort', array( 'created_at', 'asc' ), 'admin_module' );
        }

        if(!isset($filters['vendor_id']) || $filters['vendor_id'] == null || empty($filters['vendor_id']))
        {
            $filters[ 'vendor_id' ] = 0;
            $this->getUser()->setAttribute( 'venue.filters', $filters, 'admin_module' );
        }
        parent::executeIndex( $request );

    }

    public function executeFilter( sfWebRequest $request )
    {
        
        $filter = $this->getUser()->getAttribute( 'venue.filters', array(), 'admin_module' );

        $list = 'non-geocoded';

        if ( isset( $filter['list'] ) )
            $list = $filter['list'];

        if ( $request->hasParameter( '_reset' ) )
        {
            $this->setFilters( $this->configuration->getFilterDefaults() );

            $this->getUser()->setAttribute( 'venue.filters', array( 'list' => $list ), 'admin_module' );

            $this->redirect( 'venue/index' );
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
             $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to read this record ['.$venue['poi_name'].']' );
             return array();
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
           $this->getUser()->setFlash ( 'error' , 'You don\' have permissions to change this record' );
           return $this->renderText(  'You don\' have permissions to change this record' );
       }

      
      $latitude = $request->getParameter( 'latitude' );
      $longitude = $request->getParameter( 'longitude' );
      $geocode_override = $request->getParameter( 'geocode_override' );
      $geocode_accuracy = $request->getParameter( 'geocode_accuracy' );

      
      if( empty( $latitude ) ) $latitude = null;
      if( empty( $longitude ) ) $longitude = null;
      if( empty( $geocode_accuracy ) ) $geocode_accuracy = null;

      $poi = Doctrine::getTable( 'Poi' )->find( $venueId );

      if( $poi )
      {
        // For Meta Data
        $last_lat = $poi[ 'latitude' ];
        $last_long = $poi[ 'longitude' ];

        $poi[ 'latitude' ] = $latitude;
        $poi[ 'longitude' ] = $longitude;
        #$venue[ 'geocode_override' ] = $geocode_override;
        #$venue[ 'geocode_accuracy' ] = $geocode_accuracy;

        // Add / Update GEO Source
        if($last_lat != $latitude || $last_long != $longitude)
        {
            // todo: Add Geo_Source to Poi Meta
        }

        $poi->save(); // Save ALL

        return $this->renderText( 'saved' );
      }else

      return $this->renderText( 'venue not found' );
   }

}
