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
        // override vendor ID, vendor_id is allways required to Limit the Number of records queried!
        if( !isset( $filters[ 'vendor_id' ] ) || !is_numeric( $filters[ 'vendor_id' ] ) || $filters[ 'vendor_id' ] <= 0)
        {
            $filters[ 'vendor_id' ]  = 1; // Set to NY vendor by Default
        }
        
        if ( !isset( $filters[ 'list' ] ) )
        {
            $filters[ 'list' ] = 'duplicate';                
            $this->getUser()->setAttribute( 'geocode_ui.sort', array( 'poi_name', 'asc' ), 'admin_module' );
        }
        
        // Update Filter
        $this->getUser()->setAttribute( 'geocode_ui.filters', $filters, 'admin_module' );
        
        parent::executeIndex( $request );
    }

    public function executeFilter( sfWebRequest $request )
    {
        
        $filter = $this->getUser()->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );
        $list = 'duplicate';

        if ( isset( $filter['list'] ) )
            $list = $filter['list'];
        
        if ( $request->hasParameter( '_reset' ) )
        {
            $this->setFilters( $this->configuration->getFilterDefaults() );

            $this->getUser()->setAttribute( 'geocode_ui.filters', array( 'list' => $list ), 'admin_module' );

            $this->redirect( 'geocode_ui/index' );
        }
        //reset the page to 1
        $this->getUser()->setAttribute( 'geocode_ui.page', 1, 'admin_module' );
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

        $result = array( );

        if( $venue )
        {
            $geocodeAccuracy = $this->getPoiGeocodeAccuracyMeta( $venue );
            
            $result [ 'name' ] = $venue[ 'poi_name' ];
            $result [ 'address1' ] = !is_null( $venue[ 'house_no' ] ) ? $venue[ 'house_no' ] : '';
            $result [ 'address2' ] = $venue[ 'street' ];
            $result [ 'address3' ] = $venue[ 'additional_address_details' ];
            $result [ 'latitude' ] = $venue[ 'latitude' ];
	    $result [ 'longitude' ] = $venue[ 'longitude' ];
            $result [ 'geocode_accuracy' ] = ( $geocodeAccuracy ) ? $geocodeAccuracy['value'] : 0;//$venue[ 'geocode_accuracy' ];
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
      $latitude = $request->getParameter( 'latitude' );
      $longitude = $request->getParameter( 'longitude' );
      $geocode_lookup = $request->getParameter( 'geocode_lookup' );
      $geocode_accuracy = (int)$request->getParameter( 'geocode_accuracy' );

      
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

        // Save Accuracy
        if( $geocode_accuracy >= 0 )
        {
            $geocodeAccuracy = $this->getPoiGeocodeAccuracyMeta( $poi );

            if( !$geocodeAccuracy )
            {
                $geocodeAccuracy    = new PoiMeta();
                $poi['PoiMeta'][]   = $geocodeAccuracy;
            }

            $geocodeAccuracy['lookup']  = 'Geocode_accuracy';
            $geocodeAccuracy['value']   = $geocode_accuracy;

        }

        $poi->save(); // Save ALL

        // Diable All previous Overrides
        $query = Doctrine_Query::create()
                ->update( 'RecordFieldOverridePoi' )
                ->set( 'is_active', 0)
                ->andWhere( 'record_id = ?', $poi['id'] )
                ->andWhere( 'is_active = ? ', 1 )
                ->andWhere( 'field = ? OR field = ?', array('latitude', 'longitude') );
               
        $rows = $query->execute();
//
//        $query  = Doctrine::getTable( 'RecordFieldOverridePoi' )->createQuery()
//                ->set( 'is_active', 0)
//
//                ->andWhere( 'record_id = ?', $poi['id'] )
//                ->andWhere( 'field = ? OR field = ?', 'latitude', 'longitude' )
//                ->andWhere( 'is_active = ? ', 1 );

        // Save Override
        $ov = new RecordFieldOverridePoi();
        $ov['record_id'] = $poi['id'];
        $ov['field'] = 'latitude';
        $ov['received_value'] = $last_lat;
        $ov['edited_value'] = $poi[ 'latitude' ];
        $ov['is_active'] = 1;
        $ov->save();
        
        $ov = new RecordFieldOverridePoi();
        $ov['record_id'] = $poi['id'];
        $ov['field'] = 'longitude';
        $ov['received_value'] = $last_long;
        $ov['edited_value'] = $poi[ 'longitude' ];
        $ov['is_active'] = 1;
        $ov->save();
        
        return $this->renderText( json_encode( array('alert' => sprintf('Record Updated', $poi['poi_name'] ) ) ) );
      }else
          
        return $this->renderText( json_encode( array('error' => sprintf('POI Not found!' ) ) ) );
   }

   private function getPoiGeocodeAccuracyMeta( Doctrine_Record $poi )
   {
        foreach ( $poi['PoiMeta'] as $meta )
        {
            if( $meta['lookup'] == 'Geocode_accuracy' )
            {
                return $meta;
            }
        }

        return null;
   }

}
