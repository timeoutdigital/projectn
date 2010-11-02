<?php

/**
 * Poi form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiForm extends BasePoiForm
{
  public function configure()
  {
    $this->widgetSchema[ 'vendor_poi_id' ]  = new widgetFormFixedText();
    $this->widgetSchema[ 'review_date' ]    = new widgetFormFixedText();
    $this->widgetSchema[ 'local_language' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'created_at' ]     = new widgetFormFixedText();
    $this->widgetSchema[ 'updated_at' ]     = new widgetFormFixedText();
    $this->widgetSchema[ 'vendor_id' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'geocode_look_up' ]= new widgetFormFixedText();
    $this->widgetSchema[ 'duplicate' ]      = new sfWidgetFormInputCheckbox();

    $this->setDefault( 'duplicate', $this->getObject()->getDuplicate() );

    $this->validatorSchema[ 'duplicate' ] = new sfValidatorPass();

    $this->configureVendorPoiCategoryWidget();
  }

  public function save( $con = null )
  {
      $this->getObject()->setDuplicate( $this->values[ 'duplicate' ] );
      unset( $this->values[ 'duplicate' ] );

      parent::save( $con );
  }

  protected function doUpdateObject( $values = null )
  {
    parent::doUpdateObject( $values );

    $record   = $this->getObject();
    $override = new recordFieldOverrideManager( $record );
    
    $override->saveRecordModificationsAsOverrides();
  }

  private function configureVendorPoiCategoryWidget()
  {
    $record   = $this->getObject();
      
    $vendorId = ( isset( $record[ 'vendor_id' ] ) && $record[ 'vendor_id' ] !== NULL ) ? $vendorId = $record[ 'vendor_id' ] : $this->getOption('vendor_id');

    $widget = new widgetFormPoiVendorCategoryChoice( array( 'vendor_id' => $vendorId ) );
    $this->widgetSchema[ 'vendor_poi_category_list' ] = $widget;

    $validator = new validatorVendorPoiCategoryChoice( array( 'vendor_id' => $vendorId ) );
    $this->validatorSchema[ 'vendor_poi_category_list' ] = $validator;
  }
}
