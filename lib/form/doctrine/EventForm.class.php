<?php

/**
 * Event form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventForm extends BaseEventForm
{
  public function configure()
  {
    $this->widgetSchema[ 'vendor_event_id' ] = new widgetFormFixedText();
    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText();
    $this->widgetSchema[ 'vendor_id' ]       = new widgetFormFixedText();
    $this->configureVendorEventCategoryWidget();
  }

  protected function doUpdateObject( $values = null )
  {
    parent::doUpdateObject( $values );

    $record = $this->getObject();
    $override = new recordFieldOverrideManager( $record );
    $override->saveRecordModificationsAsOverrides();
  }

  private function configureVendorEventCategoryWidget()
  {
    $widget = new widgetFormEventVendorCategoryChoice( array( 'vendor_id' => $this->object[ 'vendor_id'] ) );
    $this->widgetSchema[ 'vendor_event_category_list' ] = $widget;

    $validator = new validatorVendorEventCategoryChoice( array( 'vendor_id' => $this->object[ 'vendor_id'] ) );
    $this->validatorSchema[ 'vendor_event_category_list' ] = $validator;
  }
}
