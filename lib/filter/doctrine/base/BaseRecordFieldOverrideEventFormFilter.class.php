<?php

/**
 * RecordFieldOverrideEvent filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideEventFormFilter extends RecordFieldOverrideFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('record_field_override_event_filters[%s]');
  }

  public function getModelName()
  {
    return 'RecordFieldOverrideEvent';
  }
}
