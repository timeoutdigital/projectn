<?php

/**
 * EventDataEntryForm form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventDataEntryForm extends BaseEventForm
{
  public function configure()
  {
    $this->widgetSchema[ 'created_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->widgetSchema[ 'updated_at' ]      = new widgetFormFixedText( array( 'default' => 'now' ) );
    $this->configureVendorEventCategoryWidget();
  }

  private function configureVendorEventCategoryWidget()
  {
    $widget = new widgetFormEventVendorCategoryChoice( array( 'record' => $this->object ) );
    $this->widgetSchema[ 'vendor_event_category_list' ] = $widget;

    $validator = new validatorVendorEventCategoryChoice( array( 'event' => $this->object ) );
    $this->validatorSchema[ 'vendor_event_category_list' ] = $validator;
  }
}
