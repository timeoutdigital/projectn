<?php

/**
 * RecordFieldOverrideEvent form base class.
 *
 * @method RecordFieldOverrideEvent getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideEventForm extends RecordFieldOverrideForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('record_field_override_event[%s]');
  }

  public function getModelName()
  {
    return 'RecordFieldOverrideEvent';
  }

}
