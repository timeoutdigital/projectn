<?php

/**
 * PoiDataEntryForm form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PoiDataEntryForm extends BasePoiForm
{
  public function configure()
  {
    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->configureVendorPoiCategoryWidget();
  }


  private function configureVendorPoiCategoryWidget()
  {
    $widget = new widgetFormPoiVendorCategoryChoice( array( 'record' => $this->object ) );
    $this->widgetSchema[ 'vendor_poi_category_list' ] = $widget;

    $validator = new validatorVendorPoiCategoryChoice( array( 'poi' => $this->object ) );
    $this->validatorSchema[ 'vendor_poi_category_list' ] = $validator;
  }
}
