<?php

/**
 * RecordFieldOverridePoi form base class.
 *
 * @method RecordFieldOverridePoi getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverridePoiForm extends RecordFieldOverrideForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('record_field_override_poi[%s]');
  }

  public function getModelName()
  {
    return 'RecordFieldOverridePoi';
  }

}
