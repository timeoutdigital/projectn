<?php
/**
 * Description
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

class DuplicatePoisForm extends BaseFormDoctrine
{

    private $poi;
    
    public function __construct( $poi  = null )
    {
        $this->poi = $poi;
        
        parent::__construct( );
    }

    public function configure()
    {
    }

    public function getModelName()
    {
        return 'PoiReference';
    }
    
    public function setup()
    {
        $this->setWidget( 'duplicate_pois', new widgetFormDuplicatePois( array( 'label' =>  false ), array( 'model' => $this->poi ) ) );
        $this->setValidator( 'duplicate_pois', new sfValidatorPass() ); // skip validation
        parent::setup();
    }

//    public function saveEmbeddedForms($con = null, $forms = null)
//    {
//        // Process all Data
//        $duplicatePois = $this->getWidget( 'duplicate_pois' );
//
//        $values = $this->getValues();
//        print_r( $values );
//        die( $this->getModelName() );
//    }

    public function doSave($con = null)
    {
        // Check for Current POI for being Duplicate
        if( $this->poi->isDuplicate() )
        {
            return;
        }

        // Delete All Existing Duplicate POIS and Add New once
        $this->poi->removeDuplicatePois();
        
        // Add only what we have on Postback
        $newDuplicatePoisID = $this->getValue( 'duplicate_pois' );
        foreach( $newDuplicatePoisID as $duplicatePoi )
        {
            $poi = Doctrine::getTable( 'Poi' )->findOneById( $duplicatePoi );
            if( $poi === false )
            {
                // Throw Error?
                continue;
            }

            if( $poi->isDuplicate() || $poi->isMaster() || $poi['id'] == $this->poi['id'])
            {
                // throw error, as you cannot add another master or duplicate as duplicate
                // or it self!
                continue;
            }

            $poi->setMasterPoi( $this->poi['id'] );
            $poi->save();
        }
    }
}